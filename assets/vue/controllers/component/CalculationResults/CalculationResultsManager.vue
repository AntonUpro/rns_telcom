<script setup>
import {ref, computed, onMounted} from 'vue';
import ResultsTablePillar from './ResultsTablePillar.vue';
import ResultsTableCrack from './ResultsTableСrack.vue';
import ResultsTableStress from './ResultsTableStress.vue';
import ResultsTableMaximumForcesBase from './ResultsTableMaximumForcesBase.vue';
import ResultsTableDeformation from './ResultsTableDeformation.vue';
import ResultsTableBase from './ResultsTableBase.vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

// ─── Состояние загрузки / расчёта ─────────────────────────────────────────────
const loading = ref(false);
const calculating = ref(false);
const error = ref(null);
const message = ref(null);   // { type: 'success'|'error', text: string }

// ─── Справочники (приходят с бэкенда) ─────────────────────────────────────────
const enums = ref({
    profileTypes: [],   // [{ value, label }]  — GaugeProfileTypeEnum
    pillarTypes: [],   // [{ value, allowableMoment, momentByCrackFormation }]
    elementTypes: [],   // [{ value, label }]
});

// ─── Управление опциональными таблицами ───────────────────────────────────────
const optionalTables = ref({
    brace_stress: false,
    superstructure_stress: false,
    platform_forces: false,
    base_forces: false,
    deformation: false,
    foundation: false,
});

const OPTIONAL_TABLE_META = [
    {key: 'brace_stress', label: 'Напряжения в подкосах'},
    {key: 'superstructure_stress', label: 'Напряжения в поясах надстройки'},
    {key: 'platform_forces', label: 'Усилия в площадке и стойке'},
    {key: 'base_forces', label: 'Усилия в основании опоры'},
    {key: 'deformation', label: 'Деформации опоры'},
    {key: 'foundation', label: 'Расчёт основания'},
];

// ─── Фабрики строк ────────────────────────────────────────────────────────────
const makePillarForcesRow = () => ({
    mark: null, poleType: '', mCalc: null,
    mAllowable: null,  // computed
    kMax: null,        // computed
});

const makeCrackOpeningRow = () => ({
    mark: null, poleType: '',
    crackWidthCalc: null,      // computed
    crackWidthAllowable: 0.3,
    kMax: null,                // computed
});

const makeStressRow = () => ({
    element: '', mark: null,
    profileType: '', sectionDesignation: '',
    area: null,             // TODO: auto-fill from backend by sectionDesignation
    momentResistance: null, // TODO: auto-fill from backend by sectionDesignation
    nCalc: null, mCalc: null, ry: 240,
    sigma: null,  // computed
    kUse: null,   // computed
});

const makeBaseForcesRow = () => ({
    loadType: '', n: null, q: null, m: null,
});

const makeDeformationRow = () => ({
    mark: null,
    displacement: null,    // computed
    angleMax: null,        // computed
    angleAllowable: 1.5,
    kUse: null,            // computed
});

const makeFoundationRow = () => ({
    q: null,                 // расч. попер. сила — ввод
    qU: null,                // предельная сила — computed
    beta: null,              // расч. угловое смещение — computed
    betaU: null,             // доп. смещение — ввод
    kUseStability: null,     // computed
    kUseDeformation: null,   // computed
});

// ─── Данные таблиц ────────────────────────────────────────────────────────────
const pillarForcesRows = ref([makePillarForcesRow()]);
const crackOpeningRows = ref([makeCrackOpeningRow()]);
const braceStressRows = ref([]);
const superstructureStressRows = ref([]);
const platformForcesRows = ref([]);
const baseForcesRows = ref([]);
const deformationRows = ref([]);
const foundationRows = ref([]);

// Маппинг ключа таблицы → ref строк и фабрика строки
const TABLE_ROWS_MAP = {
    brace_stress: {rows: braceStressRows, make: makeStressRow},
    superstructure_stress: {rows: superstructureStressRows, make: makeStressRow},
    platform_forces: {rows: platformForcesRows, make: makeStressRow},
    base_forces: {rows: baseForcesRows, make: makeBaseForcesRow},
    deformation: {rows: deformationRows, make: makeDeformationRow},
    foundation: {rows: foundationRows, make: makeFoundationRow},
};

// ─── Динамическая нумерация таблиц на странице ────────────────────────────────
const tableNumbers = computed(() => {
    let n = 0;
    const nums = /** @type {Record<string, number>} */ ({});
    nums.pillar_forces = ++n;
    nums.crack_opening = ++n;
    for (const key of Object.keys(optionalTables.value)) {
        nums[key] = optionalTables.value[key] ? ++n : 0;
    }
    return nums;
});

// ─── Переключение опциональных таблиц ─────────────────────────────────────────
const toggleOptional = (key) => {
    optionalTables.value[key] = !optionalTables.value[key];
    const entry = TABLE_ROWS_MAP[key];
    if (optionalTables.value[key] && entry && entry.rows.value.length === 0) {
        entry.rows.value = [entry.make()];
    }
};

// ─── Восстановление сохранённых данных ────────────────────────────────────────
const restoreSavedData = (savedData) => {
    if (!savedData) return;

    const restore = (key, rowsRef, defaultRow) => {
        const saved = savedData[key];
        if (!saved) return;
        rowsRef.value = saved.rows?.length ? saved.rows : [defaultRow()];
    };

    restore('pillar_forces', pillarForcesRows, makePillarForcesRow);
    restore('crack_opening', crackOpeningRows, makeCrackOpeningRow);
    restore('brace_stress', braceStressRows, makeStressRow);
    restore('superstructure_stress', superstructureStressRows, makeStressRow);
    restore('platform_forces', platformForcesRows, makeStressRow);
    restore('base_forces', baseForcesRows, makeBaseForcesRow);
    restore('deformation', deformationRows, makeDeformationRow);
    restore('foundation', foundationRows, makeFoundationRow);

    // Включаем опциональные таблицы, которые были сохранены с enabled=true
    for (const key of Object.keys(optionalTables.value)) {
        if (savedData[key]?.enabled) {
            optionalTables.value[key] = true;
        }
    }
};

// ─── Загрузка справочных данных ───────────────────────────────────────────────
const fetchInitData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await fetch(`/api/v1/calculation/calc-results/${props.calculationId}`);
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка загрузки данных');
        }

        enums.value = data.data.enums;
        restoreSavedData(data.data.savedData);
    } catch (err) {
        error.value = err.message;
        console.error('Ошибка загрузки данных результатов:', err);
    } finally {
        loading.value = false;
    }
};

// ─── Расчёт (вызывается локально и из родителя через $ref) ────────────────────
const calculate = async () => {
    calculating.value = true;
    message.value = null;

    try {
        const payload = {
            pillar_forces: {rows: pillarForcesRows.value},
            crack_opening: {rows: crackOpeningRows.value},
            brace_stress: {enabled: optionalTables.value.brace_stress, rows: braceStressRows.value},
            superstructure_stress: {
                enabled: optionalTables.value.superstructure_stress,
                rows: superstructureStressRows.value
            },
            platform_forces: {enabled: optionalTables.value.platform_forces, rows: platformForcesRows.value},
            base_forces: {enabled: optionalTables.value.base_forces, rows: baseForcesRows.value},
            deformation: {enabled: optionalTables.value.deformation, rows: deformationRows.value},
            foundation: {enabled: optionalTables.value.foundation, rows: foundationRows.value},
        };

        const response = await fetch(
            `/api/v1/calculation/calc-results/${props.calculationId}/calculate`,
            {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload),
            },
        );
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка расчёта');
        }

        // Обновляем строки вычисленными полями с бэкенда
        if (data.data.pillar_forces?.rows) pillarForcesRows.value = data.data.pillar_forces.rows;
        if (data.data.crack_opening?.rows) crackOpeningRows.value = data.data.crack_opening.rows;
        if (data.data.brace_stress?.rows) braceStressRows.value = data.data.brace_stress.rows;
        if (data.data.superstructure_stress?.rows) superstructureStressRows.value = data.data.superstructure_stress.rows;
        if (data.data.platform_forces?.rows) platformForcesRows.value = data.data.platform_forces.rows;
        if (data.data.base_forces?.rows) baseForcesRows.value = data.data.base_forces.rows;
        if (data.data.deformation?.rows) deformationRows.value = data.data.deformation.rows;
        if (data.data.foundation?.rows) foundationRows.value = data.data.foundation.rows;

        message.value = {type: 'success', text: data.data.message ?? 'Данные сохранены.'};
    } catch (err) {
        message.value = {type: 'error', text: err.message};
        console.error('Ошибка расчёта результатов:', err);
    } finally {
        calculating.value = false;
    }
};

// Экспортируем calculate для вызова из родителя ConcretePillarCalc через template ref
defineExpose({calculate});

onMounted(fetchInitData);
</script>

<template>
    <div class="crm-container">

        <!-- Загрузка -->
        <div v-if="loading" class="crm-state crm-state--loading">Загрузка справочных данных...</div>

        <!-- Ошибка загрузки -->
        <div v-else-if="error" class="crm-state crm-state--error">{{ error }}</div>

        <template v-else>

            <!-- ── Панель дополнительных таблиц ─────────────────────────────── -->
            <section class="crm-optional-panel">
                <div class="crm-optional-header">
                    <span class="crm-optional-label">Дополнительные таблицы</span>
                    <div class="crm-optional-toggles">
                        <label
                            v-for="meta in OPTIONAL_TABLE_META"
                            :key="meta.key"
                            class="crm-toggle"
                            :class="{ 'crm-toggle--on': optionalTables[meta.key] }"
                        >
                            <input
                                type="checkbox"
                                :checked="optionalTables[meta.key]"
                                @change="toggleOptional(meta.key)"
                            />
                            {{ meta.label }}
                        </label>
                    </div>
                </div>
            </section>

            <!-- ── Обязательные таблицы ──────────────────────────────────────── -->
            <ResultsTablePillar
                :table-number="tableNumbers.pillar_forces"
                :rows="pillarForcesRows"
                :pillar-types="enums.pillarTypes"
                @update:rows="pillarForcesRows = $event"
            />

            <ResultsTableCrack
                :table-number="tableNumbers.crack_opening"
                :rows="crackOpeningRows"
                :pole-types="enums.pillarTypes"
                @update:rows="crackOpeningRows = $event"
            />

            <!-- Напряжения в подкосах -->
            <ResultsTableStress
                v-if="optionalTables.brace_stress"
                :table-number="tableNumbers.brace_stress"
                table-name="Максимальные напряжения в элементах подкосов"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="braceStressRows"
                :profile-types="enums.profileTypes"
                :has-element="true"
                :element-options="enums.elementTypes"
                @update:rows="braceStressRows = $event"
            />

            <!-- Напряжения в поясах надстройки -->
            <ResultsTableStress
                v-if="optionalTables.superstructure_stress"
                :table-number="tableNumbers.superstructure_stress"
                table-name="Максимальные напряжения в элементах поясов надстройки"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="superstructureStressRows"
                :profile-types="enums.profileTypes"
                :has-element="true"
                :element-options="enums.elementTypes"
                @update:rows="superstructureStressRows = $event"
            />

            <!-- Усилия в площадке и стойке -->
            <ResultsTableStress
                v-if="optionalTables.platform_forces"
                :table-number="tableNumbers.platform_forces"
                table-name="Максимальные усилия в площадке и стойке"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="platformForcesRows"
                :profile-types="enums.profileTypes"
                :has-element="false"
                :element-options="[]"
                @update:rows="platformForcesRows = $event"
            />

            <!-- Усилия в основании опоры -->
            <ResultsTableMaximumForcesBase
                v-if="optionalTables.base_forces"
                :table-number="tableNumbers.base_forces"
                :rows="baseForcesRows"
                @update:rows="baseForcesRows = $event"
            />

            <!-- Деформации опоры -->
            <ResultsTableDeformation
                v-if="optionalTables.deformation"
                :table-number="tableNumbers.deformation"
                :rows="deformationRows"
                @update:rows="deformationRows = $event"
            />

            <!-- Расчёт основания -->
            <ResultsTableBase
                v-if="optionalTables.foundation"
                :table-number="tableNumbers.foundation"
                :rows="foundationRows"
                @update:rows="foundationRows = $event"
            />

            <!-- ── Статус ─────────────────────────────────────────────────────── -->
            <div
                v-if="message"
                :class="['crm-message', message.type === 'success' ? 'crm-message--ok' : 'crm-message--err']"
            >
                {{ message.text }}
            </div>

            <!-- ── Тулбар ─────────────────────────────────────────────────────── -->
            <div class="crm-toolbar">
                <span class="crm-hint">
                    Нажмите «Выполнить расчёт» внизу страницы, чтобы рассчитать все включённые таблицы.
                </span>
                <button
                    class="crm-btn-calc"
                    :disabled="calculating"
                    @click="calculate"
                >
                    {{ calculating ? 'Расчёт...' : 'Выполнить расчёт раздела' }}
                </button>
            </div>

        </template>
    </div>
</template>

<style scoped>
/* ── Контейнер ── */
.crm-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    font-family: Arial, sans-serif;
    font-size: 13px;
    color: #212529;
}

/* ── Состояния ── */
.crm-state {
    padding: 16px 20px;
    border-radius: 6px;
    font-size: 14px;
    text-align: center;
}

.crm-state--loading {
    background: #e3f2fd;
    color: #1976d2;
    border: 1px solid #90caf9;
}

.crm-state--error {
    background: #ffebee;
    color: #b71c1c;
    border: 1px solid #ef9a9a;
}

/* ── Панель дополнительных таблиц ── */
.crm-optional-panel {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #fafbfc;
    overflow: hidden;
}

.crm-optional-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 10px 16px;
    flex-wrap: wrap;
}

.crm-optional-label {
    font-size: 11px;
    font-weight: 700;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
    padding-top: 4px;
    flex-shrink: 0;
}

.crm-optional-toggles {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.crm-toggle {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #fff;
    font-size: 12px;
    color: #495057;
    cursor: pointer;
    user-select: none;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
    white-space: nowrap;
}

.crm-toggle input[type="checkbox"] {
    margin: 0;
    width: 13px;
    height: 13px;
    cursor: pointer;
    accent-color: #1976d2;
}

.crm-toggle:hover {
    border-color: #1976d2;
    color: #1976d2;
}

.crm-toggle--on {
    border-color: #1976d2;
    background: #e3f2fd;
    color: #1565c0;
    font-weight: 600;
}

/* ── Сообщение ── */
.crm-message {
    padding: 10px 16px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 500;
}

.crm-message--ok {
    background: #d4edda;
    color: #1a6e3c;
    border: 1px solid #c3e6cb;
}

.crm-message--err {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ── Тулбар ── */
.crm-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 8px;
    border-top: 1px solid #dee2e6;
    flex-wrap: wrap;
}

.crm-hint {
    font-size: 12px;
    color: #6c757d;
    flex: 1;
    min-width: 200px;
}

.crm-btn-calc {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    background: #1976d2;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s;
}

.crm-btn-calc:hover:not(:disabled) {
    background: #1565c0;
}

.crm-btn-calc:disabled {
    background: #90bde8;
    cursor: not-allowed;
}

/* ── Адаптив ── */
@media (max-width: 768px) {
    .crm-container {
        padding: 12px;
        gap: 12px;
    }

    .crm-toolbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .crm-optional-header {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
