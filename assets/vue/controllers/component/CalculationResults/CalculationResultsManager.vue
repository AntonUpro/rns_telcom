<script setup>
import { ref, onMounted } from 'vue';
import ResultsTable1     from './ResultsTable1.vue';
import ResultsTable2     from './ResultsTable2.vue';
import ResultsTableStress from './ResultsTableStress.vue';
import ResultsTable6     from './ResultsTable6.vue';
import ResultsTable7     from './ResultsTable7.vue';
import ResultsTable8     from './ResultsTable8.vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

// ─── Состояние загрузки / расчёта ─────────────────────────────────────────────
const loading     = ref(false);
const calculating = ref(false);
const error       = ref(null);
const message     = ref(null);   // { type: 'success'|'error', text: string }

// ─── Справочники (приходят с бэкенда) ─────────────────────────────────────────
const enums = ref({
    profileTypes:   [],   // [{ value, label }]  — GaugeProfileTypeEnum
    pillarTypes:      [],   // string[]             — типы ЖБ опор
    elementTypes: [],   // string[]             — элементы подкосов
});

// ─── Управление опциональными таблицами ───────────────────────────────────────
const optionalTables = ref({
    table3: false,
    table4: false,
    table5: false,
    table6: false,
    table7: false,
    table8: false,
});

const OPTIONAL_TABLE_META = [
    { key: 'table3', label: 'Напряжения в подкосах' },
    { key: 'table4', label: 'Напряжения в поясах надстройки' },
    { key: 'table5', label: 'Усилия в площадке и стойке' },
    { key: 'table6', label: 'Усилия в основании опоры' },
    { key: 'table7', label: 'Деформации опоры' },
    { key: 'table8', label: 'Расчёт основания' },
];

// ─── Фабрики строк ────────────────────────────────────────────────────────────
const makeRow1 = () => ({
    mark: null, poleType: '', mCalc: null,
    mAllowable: null,  // computed
    kMax: null,        // computed
});

const makeRow2 = () => ({
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

const makeRow6 = () => ({
    loadType: '', n: null, q: null, m: null,
});

const makeRow7 = () => ({
    mark: null,
    displacement: null,    // computed
    angleMax: null,        // computed
    angleAllowable: 1.5,
    kUse: null,            // computed
});

const makeRow8 = () => ({
    q: null,                 // расч. попер. сила — ввод
    qU: null,                // предельная сила — computed
    beta: null,              // расч. угловое смещение — computed
    betaU: null,             // доп. смещение — ввод
    kUseStability: null,     // computed
    kUseDeformation: null,   // computed
});

// ─── Данные таблиц ────────────────────────────────────────────────────────────
const table1Rows = ref([makeRow1()]);
const table2Rows = ref([makeRow2()]);
const table3Rows = ref([]);
const table4Rows = ref([]);
const table5Rows = ref([]);
const table6Rows = ref([]);
const table7Rows = ref([]);
const table8Rows = ref([]);

// Маппинг ключа таблицы → ref строк и фабрика строки
const TABLE_ROWS_MAP = {
    table3: { rows: table3Rows, make: makeStressRow },
    table4: { rows: table4Rows, make: makeStressRow },
    table5: { rows: table5Rows, make: makeStressRow },
    table6: { rows: table6Rows, make: makeRow6 },
    table7: { rows: table7Rows, make: makeRow7 },
    table8: { rows: table8Rows, make: makeRow8 },
};

// ─── Переключение опциональных таблиц ─────────────────────────────────────────
const toggleOptional = (key) => {
    optionalTables.value[key] = !optionalTables.value[key];
    const entry = TABLE_ROWS_MAP[key];
    if (optionalTables.value[key] && entry && entry.rows.value.length === 0) {
        entry.rows.value = [entry.make()];
    }
};

// ─── Загрузка справочных данных ───────────────────────────────────────────────
const fetchInitData = async () => {
    loading.value = true;
    error.value   = null;
    try {
        const response = await fetch(`/api/v1/calculation/calc-results/${props.calculationId}`);
        const data     = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка загрузки данных');
        }

        enums.value = data.data.enums;

        // TODO: если data.data.savedData !== null — восстановить сохранённые строки таблиц
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
    message.value     = null;

    try {
        const payload = {
            table1: { rows: table1Rows.value },
            table2: { rows: table2Rows.value },
            table3: { enabled: optionalTables.value.table3, rows: table3Rows.value },
            table4: { enabled: optionalTables.value.table4, rows: table4Rows.value },
            table5: { enabled: optionalTables.value.table5, rows: table5Rows.value },
            table6: { enabled: optionalTables.value.table6, rows: table6Rows.value },
            table7: { enabled: optionalTables.value.table7, rows: table7Rows.value },
            table8: { enabled: optionalTables.value.table8, rows: table8Rows.value },
        };

        const response = await fetch(
            `/api/v1/calculation/calc-results/${props.calculationId}/calculate`,
            {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload),
            },
        );
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка расчёта');
        }

        // TODO: когда бэкенд начнёт возвращать вычисленные поля, раскомментировать:
        // table1Rows.value = data.data.table1.rows;
        // table2Rows.value = data.data.table2.rows;
        // if (data.data.table3?.enabled) table3Rows.value = data.data.table3.rows;
        // if (data.data.table4?.enabled) table4Rows.value = data.data.table4.rows;
        // if (data.data.table5?.enabled) table5Rows.value = data.data.table5.rows;
        // if (data.data.table6?.enabled) table6Rows.value = data.data.table6.rows;
        // if (data.data.table7?.enabled) table7Rows.value = data.data.table7.rows;
        // if (data.data.table8?.enabled) table8Rows.value = data.data.table8.rows;

        message.value = { type: 'success', text: data.data.message ?? 'Данные переданы на сервер.' };
    } catch (err) {
        message.value = { type: 'error', text: err.message };
        console.error('Ошибка расчёта результатов:', err);
    } finally {
        calculating.value = false;
    }
};

// Экспортируем calculate для вызова из родителя ConcretePillarCalc через template ref
defineExpose({ calculate });

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
            <ResultsTable1
                :rows="table1Rows"
                :pole-types="enums.pillarTypes"
                @update:rows="table1Rows = $event"
            />

            <ResultsTable2
                :rows="table2Rows"
                :pole-types="enums.pillarTypes"
                @update:rows="table2Rows = $event"
            />

            <!-- Таблица 3: Подкосы -->
            <ResultsTableStress
                v-if="optionalTables.table3"
                title="Таблица 3. Максимальные напряжения в элементах подкосов"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="table3Rows"
                :profile-types="enums.profileTypes"
                :has-element="true"
                :element-options="enums.elementTypes"
                @update:rows="table3Rows = $event"
            />

            <!-- Таблица 4: Пояса надстройки -->
            <ResultsTableStress
                v-if="optionalTables.table4"
                title="Таблица 4. Максимальные напряжения в элементах поясов надстройки"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="table4Rows"
                :profile-types="enums.profileTypes"
                :has-element="true"
                :element-options="enums.elementTypes"
                @update:rows="table4Rows = $event"
            />

            <!-- Таблица 5: Площадка и стойка -->
            <ResultsTableStress
                v-if="optionalTables.table5"
                title="Таблица 5. Максимальные усилия в площадке"
                subtitle="Проверка несущей способности по СП 16.13330.2017"
                :rows="table5Rows"
                :profile-types="enums.profileTypes"
                :has-element="false"
                :element-options="[]"
                @update:rows="table5Rows = $event"
            />

            <!-- Таблица 6: Усилия в основании -->
            <ResultsTable6
                v-if="optionalTables.table6"
                :rows="table6Rows"
                @update:rows="table6Rows = $event"
            />

            <!-- Таблица 7: Деформации опоры -->
            <ResultsTable7
                v-if="optionalTables.table7"
                :rows="table7Rows"
                @update:rows="table7Rows = $event"
            />

            <!-- Таблица 8: Расчёт основания -->
            <ResultsTable8
                v-if="optionalTables.table8"
                :rows="table8Rows"
                @update:rows="table8Rows = $event"
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
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
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
.crm-state--loading { background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9; }
.crm-state--error   { background: #ffebee; color: #b71c1c; border: 1px solid #ef9a9a; }

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
.crm-message--ok  { background: #d4edda; color: #1a6e3c; border: 1px solid #c3e6cb; }
.crm-message--err { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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
.crm-btn-calc:hover:not(:disabled) { background: #1565c0; }
.crm-btn-calc:disabled { background: #90bde8; cursor: not-allowed; }

/* ── Адаптив ── */
@media (max-width: 768px) {
    .crm-container { padding: 12px; gap: 12px; }
    .crm-toolbar   { flex-direction: column; align-items: flex-start; }
    .crm-optional-header { flex-direction: column; gap: 8px; }
}
</style>
