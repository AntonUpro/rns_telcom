<script setup>
/**
 * Таблица «Расчёт значений частот собственных колебаний» (только заказчик NBK).
 *
 * Значения не вычисляются — чистый ввод. «№ формы» проставляется автоматически
 * по порядку строк (только чтение). «№ загружения» редактируется, по умолчанию 3.
 * Поля «Собств. значения», «Круг. частота», «Частота», «Период» — обязательные;
 * их незаполненность подсвечивается через проп `errors` (Set ключей "idx.field").
 *
 * Ctrl+V: в редактируемую ячейку можно вставить прямоугольный блок из Excel /
 * таблицы (значения через табуляцию, строки через перевод строки). Вставка идёт
 * от ячейки с курсором вправо и вниз по редактируемым колонкам; недостающие
 * строки добавляются автоматически.
 */

const props = defineProps({
    tableNumber: { type: Number, required: true },
    rows:        { type: Array,  required: true },
    errors:      { type: Object, default: () => new Set() },
});

const emit = defineEmits(['update:rows']);

// Порядок редактируемых колонок (для Ctrl+V); «№ формы» не редактируется
const EDITABLE_FIELDS = ['loadCase', 'eigenvalue', 'angularFreq', 'frequencyHz', 'period'];

const makeRow = () => ({
    loadCase: 3,
    eigenvalue: null,
    angularFreq: null,
    frequencyHz: null,
    period: null,
});

const updateCell = (idx, field, value) =>
    emit('update:rows', props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r));

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
};

const hasError = (idx, field) => props.errors.has(`${idx}.${field}`);

// «1,23» / «1 234,5» / «2.5e-3» → number | null
const parseNum = (raw) => {
    const s = String(raw).replace(/\s/g, '').replace(',', '.');
    if (s === '') return null;
    const n = Number(s);
    return Number.isFinite(n) ? n : null;
};

const onPaste = (e) => {
    const cell = e.target.closest('input[data-field]');
    if (!cell) return;

    const text = e.clipboardData?.getData('text/plain') ?? '';
    if (!/[\t\n]/.test(text)) return; // одиночное значение — обычная вставка

    e.preventDefault();

    const startRow = Number(cell.dataset.row);
    const startCol = EDITABLE_FIELDS.indexOf(cell.dataset.field);
    if (!Number.isInteger(startRow) || startCol < 0) return;

    const matrix = text
        .replace(/\r/g, '')
        .replace(/\n+$/, '')
        .split('\n')
        .map((line) => line.split('\t'));

    const next = props.rows.map((r) => ({ ...r }));
    matrix.forEach((cols, r) => {
        const ri = startRow + r;
        if (!next[ri]) next[ri] = makeRow();
        cols.forEach((val, c) => {
            const field = EDITABLE_FIELDS[startCol + c];
            if (field) next[ri][field] = parseNum(val);
        });
    });

    emit('update:rows', next);
};
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Расчёт значений частот собственных колебаний</h3>
                <p class="rt-hint">Данные можно вставить из Excel: Ctrl+V в нужную ячейку</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table" @paste="onPaste">
                <thead>
                    <tr>
                        <th rowspan="2" class="col-num">№ загружения</th>
                        <th rowspan="2" class="col-n">№ формы</th>
                        <th rowspan="2" class="col-num">Собств.<br>значения</th>
                        <th colspan="3">Частоты</th>
                        <th rowspan="2" class="col-del"></th>
                    </tr>
                    <tr>
                        <th class="col-num">Круг. частота,<br>рад/с</th>
                        <th class="col-num">Частота,<br>Гц</th>
                        <th class="col-num">Период,<br>с</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td>
                            <input
                                type="number" step="1" class="rt-input rt-input--xs"
                                :data-row="idx" data-field="loadCase"
                                :value="row.loadCase"
                                @change="updateCell(idx, 'loadCase', $event.target.valueAsNumber)"
                                placeholder="3"
                            />
                        </td>

                        <td class="td-center">{{ idx + 1 }}</td>

                        <td>
                            <input
                                type="number" step="0.001"
                                class="rt-input rt-input--xs"
                                :class="{ 'rt-input--error': hasError(idx, 'eigenvalue') }"
                                :data-row="idx" data-field="eigenvalue"
                                :value="row.eigenvalue"
                                @change="updateCell(idx, 'eigenvalue', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <td>
                            <input
                                type="number" step="0.001"
                                class="rt-input rt-input--xs"
                                :class="{ 'rt-input--error': hasError(idx, 'angularFreq') }"
                                :data-row="idx" data-field="angularFreq"
                                :value="row.angularFreq"
                                @change="updateCell(idx, 'angularFreq', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <td>
                            <input
                                type="number" step="0.001"
                                class="rt-input rt-input--xs"
                                :class="{ 'rt-input--error': hasError(idx, 'frequencyHz') }"
                                :data-row="idx" data-field="frequencyHz"
                                :value="row.frequencyHz"
                                @change="updateCell(idx, 'frequencyHz', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <td>
                            <input
                                type="number" step="0.001"
                                class="rt-input rt-input--xs"
                                :class="{ 'rt-input--error': hasError(idx, 'period') }"
                                :data-row="idx" data-field="period"
                                :value="row.period"
                                @change="updateCell(idx, 'period', $event.target.valueAsNumber)"
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
                        <td colspan="7" class="td-empty">
                            Нет строк — нажмите «+ строка»
                        </td>
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
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #1a2533;
}

.rt-hint {
    margin: 2px 0 0;
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
    font-size: 12px;
    background: #fff;
}

.rt-table th,
.rt-table td {
    padding: 5px 8px;
    border: 1px solid #dee2e6;
    vertical-align: middle;
    white-space: nowrap;
}

.rt-table thead th {
    background: #eef0f3;
    font-weight: 600;
    color: #343a40;
    font-size: 11px;
    text-align: center;
    border-bottom: 2px solid #ced4da;
    line-height: 1.3;
}

.rt-table tbody tr:hover { background: #f8f9fa; }

.col-n   { min-width: 60px; }
.col-num { min-width: 90px; text-align: center; }
.col-del { width: 28px; }

.td-center { text-align: center; }

.td-empty {
    text-align: center;
    padding: 14px;
    color: #6c757d;
    font-style: italic;
    font-size: 12px;
}

.rt-input {
    padding: 3px 5px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 12px;
    font-family: 'Courier New', monospace;
    color: #212529;
    background: #fff;
    box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }

.rt-input--xs { width: 90px; }

.rt-input--error {
    border-color: #dc3545;
    background: #fff5f5;
}
.rt-input--error:focus { box-shadow: 0 0 0 2px rgba(220,53,69,0.15); }

.rt-btn-del {
    width: 22px;
    height: 22px;
    padding: 0;
    font-size: 13px;
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
