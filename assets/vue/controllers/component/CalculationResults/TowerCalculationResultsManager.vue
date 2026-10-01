<script setup>
import {ref, computed, onMounted, watch, nextTick} from 'vue';
import {useUnsavedChanges} from '../shared/useUnsavedChanges.js';
import ResultsTableSuperstructureStability from './ResultsTableSuperstructureStability.vue';
import ResultsTableTowerDeformation from './ResultsTableTowerDeformation.vue';
import ResultsTableTowerAnchorBolts from './ResultsTableTowerAnchorBolts.vue';
import ResultsTableTowerFlangeBolts from './ResultsTableTowerFlangeBolts.vue';
import ResultsTableTowerFoundationLoads from './ResultsTableTowerFoundationLoads.vue';
import ResultsTableTowerLoadComparison from './ResultsTableTowerLoadComparison.vue';

/**
 * Таб «Результаты расчёта» для башни.
 *
 * Использует тот же API, что и столб (/api/v1/calculation/calc-results/{id}),
 * но работает только с таблицами башни (ключи tower_*). Строки по умолчанию
 * (секции, элементы, пояса) формирует бэкенд.
 */
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

const {markDirty, markClean} = useUnsavedChanges('calc-results');

// ─── Справочники (приходят с бэкенда) ─────────────────────────────────────────
const enums = ref({
    profileTypes: [],
    elementTypes: [],
    loadTypes: [],
    connectionTypes: [],
    schemeNumbers: [],
    flexibilityBeltOptions: [],
    flexibilityOtherOptions: [],
    towerWindDirections: [],
    foundationLoadKinds: [],
    boltDiameters: [],
    anchorBoltSteels: [],
    boltStrengthClasses: [],
});

// ─── Таблицы башни в порядке вывода ───────────────────────────────────────────
const TABLES = [
    {key: 'tower_belt_stability', label: 'Напряжения в поясах башни'},
    {key: 'tower_brace_stability', label: 'Напряжения в раскосах башни'},
    {key: 'tower_spacer_stability', label: 'Напряжения в распорках башни'},
    {key: 'tower_deformation', label: 'Деформации от ветровых нагрузок'},
    {key: 'tower_anchor_bolts', label: 'Напряжения в анкерных болтах'},
    {key: 'tower_flange_bolts', label: 'Напряжения в фланцевых болтах'},
    {key: 'tower_flange_bolts_shear', label: 'Напряжения в фланцевых болтах на срез'},
    {key: 'tower_foundation_loads', label: 'Нагрузки, действующие на фундаменты'},
    {key: 'tower_load_comparison', label: 'Сравнение расчетных нагрузок с проектными'},
];

const enabled = ref(Object.fromEntries(TABLES.map((t) => [t.key, false])));
const rows = ref(Object.fromEntries(TABLES.map((t) => [t.key, []])));

// Таблицы устойчивости → элемент по умолчанию для новой строки
const STABILITY_ELEMENTS = {
    tower_belt_stability: 'belt',
    tower_brace_stability: 'brace',
    tower_spacer_stability: 'spacer',
};

const makeStabilityRow = (element) => ({
    element, sectionNumber: null, mark: null,
    profileType: '', sectionDesignation: '',
    elementLength: null,
    loadType: 'compressed', connectionType: 'welded_or_bolts',
    schemeNumber: element === 'belt' ? 'a' : null, flexibility: null,
    area: null, momentInertia: null,
    nCalc: null, ry: 240,
    sigma: null, kUse: null,
});

// Включённая таблица устойчивости без строк (нет таких элементов в секциях) получает пустую строку
const ensureStabilityRow = (key) => {
    const element = STABILITY_ELEMENTS[key];
    if (element && enabled.value[key] && rows.value[key].length === 0) {
        rows.value[key] = [makeStabilityRow(element)];
    }
};

// Варианты «Элемент» для раскосов и распорок — все, кроме поясов
const nonBeltElementOptions = computed(
    () => (enums.value.elementTypes ?? []).filter((o) => o.value !== 'belt'),
);

// ─── Динамическая нумерация таблиц на странице ────────────────────────────────
const tableNumbers = computed(() => {
    let n = 0;
    return Object.fromEntries(TABLES.map((t) => [t.key, enabled.value[t.key] ? ++n : 0]));
});

const toggleTable = (key) => {
    enabled.value[key] = !enabled.value[key];
    ensureStabilityRow(key);
};

// ─── Загрузка справочных и сохранённых данных ─────────────────────────────────
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

        const savedData = data.data.savedData ?? {};
        for (const {key} of TABLES) {
            enabled.value[key] = savedData[key]?.enabled === true;
            rows.value[key] = savedData[key]?.rows ?? [];
            ensureStabilityRow(key);
        }
    } catch (err) {
        error.value = err.message;
        console.error('Ошибка загрузки данных результатов башни:', err);
    } finally {
        loading.value = false;
    }
};

// ─── Расчёт и сохранение (вызывается локально и из родителя через $ref) ───────
const calculate = async () => {
    calculating.value = true;
    message.value = null;

    try {
        const payload = Object.fromEntries(TABLES.map(({key}) => [
            key,
            {enabled: enabled.value[key], rows: rows.value[key]},
        ]));

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
        for (const {key} of TABLES) {
            if (data.data[key]?.rows) rows.value[key] = data.data[key].rows;
        }

        message.value = {type: 'success', text: data.data.message ?? 'Данные сохранены.'};

        await nextTick();
        markClean();
        return true;
    } catch (err) {
        message.value = {type: 'error', text: err.message};
        console.error('Ошибка расчёта результатов башни:', err);
        return false;
    } finally {
        calculating.value = false;
    }
};

// save — алиас для единообразного вызова из карты tabKey -> ref в родителе
defineExpose({calculate, save: calculate});

onMounted(async () => {
    await fetchInitData();
    watch([rows, enabled], () => markDirty(), {deep: true});
});
</script>

<template>
    <div class="crm-container">

        <!-- Загрузка -->
        <div v-if="loading" class="crm-state crm-state--loading">Загрузка справочных данных...</div>

        <!-- Ошибка загрузки -->
        <div v-else-if="error" class="crm-state crm-state--error">{{ error }}</div>

        <template v-else>

            <!-- ── Панель выбора таблиц ─────────────────────────────────────── -->
            <section class="crm-optional-panel">
                <div class="crm-optional-header">
                    <span class="crm-optional-label">Таблицы расчета</span>
                    <div class="crm-optional-toggles">
                        <label
                            v-for="table in TABLES"
                            :key="table.key"
                            class="crm-toggle"
                            :class="{ 'crm-toggle--on': enabled[table.key] }"
                        >
                            <input
                                type="checkbox"
                                :checked="enabled[table.key]"
                                @change="toggleTable(table.key)"
                            />
                            {{ table.label }}
                        </label>
                    </div>
                </div>
            </section>

            <!-- Напряжения в поясах башни -->
            <ResultsTableSuperstructureStability
                v-if="enabled.tower_belt_stability"
                :table-number="tableNumbers.tower_belt_stability"
                table-name="Максимальные напряжения в поясах башни"
                subtitle="Проверка устойчивости по СП 16.13330.2017"
                :show-element-column="false"
                default-element="belt"
                :rows="rows.tower_belt_stability"
                :profile-types="enums.profileTypes"
                :load-types="enums.loadTypes"
                :connection-types="enums.connectionTypes"
                :scheme-numbers="enums.schemeNumbers"
                :flexibility-belt-options="enums.flexibilityBeltOptions"
                :flexibility-other-options="enums.flexibilityOtherOptions"
                @update:rows="rows.tower_belt_stability = $event"
            />

            <!-- Напряжения в раскосах башни -->
            <ResultsTableSuperstructureStability
                v-if="enabled.tower_brace_stability"
                :table-number="tableNumbers.tower_brace_stability"
                table-name="Максимальные напряжения в раскосах башни"
                subtitle="Проверка устойчивости по СП 16.13330.2017"
                :show-element-column="true"
                default-element="brace"
                :rows="rows.tower_brace_stability"
                :profile-types="enums.profileTypes"
                :element-options="nonBeltElementOptions"
                :load-types="enums.loadTypes"
                :connection-types="enums.connectionTypes"
                :scheme-numbers="enums.schemeNumbers"
                :flexibility-belt-options="enums.flexibilityBeltOptions"
                :flexibility-other-options="enums.flexibilityOtherOptions"
                @update:rows="rows.tower_brace_stability = $event"
            />

            <!-- Напряжения в распорках башни -->
            <ResultsTableSuperstructureStability
                v-if="enabled.tower_spacer_stability"
                :table-number="tableNumbers.tower_spacer_stability"
                table-name="Максимальные напряжения в распорках башни"
                subtitle="Проверка устойчивости по СП 16.13330.2017"
                :show-element-column="true"
                default-element="spacer"
                :rows="rows.tower_spacer_stability"
                :profile-types="enums.profileTypes"
                :element-options="nonBeltElementOptions"
                :load-types="enums.loadTypes"
                :connection-types="enums.connectionTypes"
                :scheme-numbers="enums.schemeNumbers"
                :flexibility-belt-options="enums.flexibilityBeltOptions"
                :flexibility-other-options="enums.flexibilityOtherOptions"
                @update:rows="rows.tower_spacer_stability = $event"
            />

            <!-- Деформации от ветровых нагрузок -->
            <ResultsTableTowerDeformation
                v-if="enabled.tower_deformation"
                :table-number="tableNumbers.tower_deformation"
                :rows="rows.tower_deformation"
                @update:rows="rows.tower_deformation = $event"
            />

            <!-- Напряжения в анкерных болтах -->
            <ResultsTableTowerAnchorBolts
                v-if="enabled.tower_anchor_bolts"
                :table-number="tableNumbers.tower_anchor_bolts"
                :rows="rows.tower_anchor_bolts"
                :diameters="enums.boltDiameters"
                :steels="enums.anchorBoltSteels"
                @update:rows="rows.tower_anchor_bolts = $event"
            />

            <!-- Напряжения в фланцевых болтах -->
            <ResultsTableTowerFlangeBolts
                v-if="enabled.tower_flange_bolts"
                :table-number="tableNumbers.tower_flange_bolts"
                :rows="rows.tower_flange_bolts"
                :diameters="enums.boltDiameters"
                :strength-classes="enums.boltStrengthClasses"
                @update:rows="rows.tower_flange_bolts = $event"
            />

            <!-- Напряжения в фланцевых болтах на срез -->
            <ResultsTableTowerFlangeBolts
                v-if="enabled.tower_flange_bolts_shear"
                shear
                :table-number="tableNumbers.tower_flange_bolts_shear"
                :rows="rows.tower_flange_bolts_shear"
                :diameters="enums.boltDiameters"
                :strength-classes="enums.boltStrengthClasses"
                @update:rows="rows.tower_flange_bolts_shear = $event"
            />

            <!-- Нагрузки, действующие на фундаменты -->
            <ResultsTableTowerFoundationLoads
                v-if="enabled.tower_foundation_loads"
                :table-number="tableNumbers.tower_foundation_loads"
                :rows="rows.tower_foundation_loads"
                :wind-directions="enums.towerWindDirections"
                @update:rows="rows.tower_foundation_loads = $event"
            />

            <!-- Сравнение расчетных нагрузок с проектными -->
            <ResultsTableTowerLoadComparison
                v-if="enabled.tower_load_comparison"
                :table-number="tableNumbers.tower_load_comparison"
                :rows="rows.tower_load_comparison"
                :load-kinds="enums.foundationLoadKinds"
                @update:rows="rows.tower_load_comparison = $event"
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
