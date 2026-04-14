<script setup>
/**
 * Таблица 6. Максимальные усилия в основании опоры.
 *
 * Поля ввода:  Тип нагрузки (select) | N, тс | Q, тс | М, тс·м
 * Вычисляемых нет — все значения вводятся вручную или берутся из программного расчёта.
 *
 * Стандартные типы нагрузок:
 *   - Расчётная
 *   - Нормативная
 *   - Нормативная (без пульсации ветровой нагрузки)
 */
const LOAD_TYPE_OPTIONS = [
    'Расчётная',
    'Нормативная',
    'Нормативная (без пульсации ветровой нагрузки)',
];

const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:rows']);

const makeRow = () => ({
    loadType: '',
    n: null,   // N, тс  — нормальная сила
    q: null,   // Q, тс  — поперечная сила
    m: null,   // М, тс·м — изгибающий момент
});

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r));
};

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
};

const fmt = (v) => (v !== null && v !== undefined) ? Number(v).toFixed(3) : '—';
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица 6. Максимальные усилия в основании опоры</h3>
                <p class="rt-subtitle">Расчётные и нормативные усилия</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <th class="col-type">Тип нагрузки</th>
                        <th class="col-val">N,<br>тс</th>
                        <th class="col-val">Q,<br>тс</th>
                        <th class="col-val">М,<br>тс·м</th>
                        <th class="col-del"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">{{ idx + 1 }}</td>
                        <td>
                            <select
                                class="rt-select rt-select--wide"
                                :value="row.loadType"
                                @change="updateCell(idx, 'loadType', $event.target.value)"
                            >
                                <option value="">— выберите тип —</option>
                                <option v-for="opt in LOAD_TYPE_OPTIONS" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.n"
                                @input="updateCell(idx, 'n', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.q"
                                @input="updateCell(idx, 'q', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.m"
                                @input="updateCell(idx, 'm', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
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
                        <td colspan="6" class="td-empty">Нет строк — нажмите «+ строка»</td>
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
.rt-title   { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1a2533; }
.rt-subtitle { margin: 0; font-size: 11px; color: #6c757d; }
.rt-btn-add {
    flex-shrink: 0; padding: 5px 12px; font-size: 12px; font-weight: 600;
    color: #1976d2; background: #fff; border: 1px solid #1976d2;
    border-radius: 4px; cursor: pointer; white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-add:hover { background: #1976d2; color: #fff; }
.table-wrap { overflow-x: auto; }
.rt-table {
    width: 100%; border-collapse: collapse; font-size: 13px; background: #fff;
}
.rt-table th, .rt-table td {
    padding: 6px 10px; border: 1px solid #dee2e6;
    vertical-align: middle; white-space: nowrap;
}
.rt-table thead th {
    background: #eef0f3; font-weight: 600; color: #343a40;
    font-size: 12px; text-align: center; border-bottom: 2px solid #ced4da;
}
.rt-table tbody tr:hover { background: #f8f9fa; }
.col-n    { width: 30px; }
.col-type { min-width: 260px; }
.col-val  { width: 100px; text-align: center; }
.col-del  { width: 32px; }
.td-center { text-align: center; }
.td-empty {
    text-align: center; padding: 14px;
    color: #6c757d; font-style: italic; font-size: 12px;
}
.rt-input {
    padding: 4px 6px; border: 1px solid #ced4da; border-radius: 3px;
    font-size: 13px; font-family: 'Courier New', monospace;
    color: #212529; background: #fff; box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }
.rt-input--sm { width: 90px; }
.rt-select {
    padding: 4px 6px; border: 1px solid #ced4da; border-radius: 3px;
    font-size: 12px; color: #212529; background: #fff;
    cursor: pointer; transition: border-color 0.15s;
}
.rt-select:focus { outline: none; border-color: #1976d2; }
.rt-select--wide { min-width: 240px; }
.rt-btn-del {
    width: 24px; height: 24px; padding: 0; font-size: 14px; line-height: 1;
    color: #dc3545; background: transparent; border: 1px solid #dc3545;
    border-radius: 3px; cursor: pointer; transition: background 0.15s, color 0.15s;
}
.rt-btn-del:hover:not(:disabled) { background: #dc3545; color: #fff; }
.rt-btn-del:disabled { opacity: 0.35; cursor: not-allowed; }
</style>
