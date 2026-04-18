<script setup>
/**
 * Таблица 2. Максимальное раскрытие трещин в стволе опоры от нормативных нагрузок.
 *
 * Поля ввода:  Отметка, м | Тип опоры | Пред. доп. ширина, мм
 * Вычисляемые: Расч. ширина трещин, мм | k(max)  — заполняются сервером после расчёта.
 *
 * «Тип опоры» — select с вариантами из бэкенда (pillarType).
 */
const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
    // poleTypes: {
    //     type: Array,
    //     default: () => [],
    // },
    tableNumber: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['update:rows']);

const makeRow = () => ({
    mark: null,
    pillarType: '',
    crackWidthCalc: null,       // computed by backend
    crackWidthAllowable: 0.3,   // default per СП 63.13330 для нормально армированных конструкций
    kMax: null,                  // computed by backend
});

const updateCell = (idx, field, value) => {
    const updated = props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r);
    emit('update:rows', updated);
};

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
};

const fmt = (v) => (v !== null && v !== undefined) ? Number(v).toFixed(4) : '—';
const fmtK = (v) => (v !== null && v !== undefined) ? Number(v).toFixed(3) : '—';
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Максимальное раскрытие трещин в стволе опоры</h3>
                <p class="rt-subtitle">От нормативных нагрузок</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <th class="col-mark">Отметка,<br>м</th>
                        <th class="col-type">Тип опоры</th>
                        <th class="col-val col-comp">Расч. ширина<br>трещин, мм</th>
                        <th class="col-val">Пред. доп.<br>ширина, мм</th>
                        <th class="col-val col-comp">k(max)</th>
                        <th class="col-del"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">{{ idx + 1 }}</td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.mark"
                                @input="updateCell(idx, 'mark', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td class="rt-select">{{ row.pillarType }}</td>
                        <td class="td-computed">{{ fmt(row.crackWidthCalc) }}</td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.crackWidthAllowable"
                                @input="updateCell(idx, 'crackWidthAllowable', $event.target.valueAsNumber)"
                                placeholder="0.300"
                            />
                        </td>
                        <td class="td-computed td-kmax" :class="{ 'td-warn': row.kMax !== null && row.kMax > 1 }">
                            {{ fmtK(row.kMax) }}
                        </td>
                        <td class="td-center">
                            <button
                                class="rt-btn-del" title="Удалить строку"
                                :disabled="rows.length <= 1"
                                @click="removeRow(idx)"
                            >×</button>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="7" class="td-empty">Нет строк — нажмите «+ строка»</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
.rt-section {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}

.rt-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f4f6f8;
    border-bottom: 1px solid #dee2e6;
    gap: 12px;
}

.rt-title {
    margin: 0 0 2px;
    font-size: 13px;
    font-weight: 700;
    color: #1a2533;
}

.rt-subtitle {
    margin: 0;
    font-size: 11px;
    color: #6c757d;
}

.rt-btn-add {
    flex-shrink: 0;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #1976d2;
    background: #fff;
    border: 1px solid #1976d2;
    border-radius: 4px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-add:hover { background: #1976d2; color: #fff; }

.table-wrap { overflow-x: auto; }

.rt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    background: #fff;
}

.rt-table th,
.rt-table td {
    padding: 6px 10px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    white-space: nowrap;
}

.rt-table thead th {
    background: #eef0f3;
    font-weight: 600;
    color: #343a40;
    font-size: 12px;
    text-align: center;
    border-bottom: 2px solid #ced4da;
}

.rt-table tbody tr:hover { background: #f8f9fa; }

.col-n    { width: 50px; }
.col-mark { width: 150px; }
.col-type { min-width: 140px; }
.col-val  { width: 150px; text-align: center; }
.col-del  { width: 32px; }

.td-center { text-align: center; }

.td-computed {
    text-align: center;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #1565c0;
    background: #f0f7ff;
    font-weight: 600;
}

.td-warn {
    background: #fff3cd !important;
    color: #856404 !important;
}

.td-empty {
    text-align: center;
    padding: 14px;
    color: #6c757d;
    font-style: italic;
    font-size: 12px;
}

.rt-input {
    width: 100%;
    padding: 4px 6px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 13px;
    font-family: 'Courier New', monospace;
    color: #212529;
    background: #fff;
    box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }

.rt-input--sm { width: 140px; }

.rt-select {
    width: 100%;
    padding: 4px 6px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 12px;
    color: #212529;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s;
}
.rt-select:focus { outline: none; border-color: #1976d2; }

.rt-btn-del {
    width: 24px;
    height: 24px;
    padding: 0;
    font-size: 14px;
    line-height: 1;
    color: #dc3545;
    background: transparent;
    border: 1px solid #dc3545;
    border-radius: 3px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-del:hover:not(:disabled) { background: #dc3545; color: #fff; }
.rt-btn-del:disabled { opacity: 0.35; cursor: not-allowed; }
</style>
