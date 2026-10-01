<script setup>
/**
 * Максимальные нагрузки, действующие на фундаменты башни.
 *
 * Строки: каждое направление ветра (W1/W2/W3) × каждый пояс — формируются на бэкенде.
 * Ввод: реакции Rz, Rx, Ry (тс) из ПК. Вычисляемых полей нет.
 */
const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
    tableNumber: {
        type: Number,
        required: true,
    },
    windDirections: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:rows']);

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? {...r, [field]: value} : r));
};

const directionLabel = (value) => props.windDirections.find((d) => d.value === value)?.label ?? value;

// Ячейка направления объединяется на все пояса этого направления
const isFirstOfDirection = (idx) => idx === 0 || props.rows[idx - 1].direction !== props.rows[idx].direction;
const directionRowspan = (direction) => props.rows.filter((r) => r.direction === direction).length;
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Максимальные нагрузки, действующие на фундаменты</h3>
                <p class="rt-subtitle">Реакции опор по поясам для каждого направления ветра</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th>Нагрузка</th>
                        <th>Направление ветра</th>
                        <th>№ пояса</th>
                        <th>R<sub>z</sub>, тс</th>
                        <th>R<sub>x</sub>, тс</th>
                        <th>R<sub>y</sub>, тс</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="`${row.direction}-${row.beltNumber}`">
                        <td v-if="idx === 0" :rowspan="rows.length" class="td-center td-group">Расчетная</td>
                        <td
                            v-if="isFirstOfDirection(idx)"
                            :rowspan="directionRowspan(row.direction)"
                            class="td-center td-group"
                        >
                            {{ directionLabel(row.direction) }}
                        </td>
                        <td class="td-center">{{ row.beltNumber }}</td>
                        <td class="td-center">
                            <input
                                type="number" step="0.01" class="rt-input rt-input--sm"
                                :value="row.rz"
                                @input="updateCell(idx, 'rz', $event.target.valueAsNumber)"
                                placeholder="0.00"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" step="0.01" class="rt-input rt-input--sm"
                                :value="row.rx"
                                @input="updateCell(idx, 'rx', $event.target.valueAsNumber)"
                                placeholder="0.00"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" step="0.01" class="rt-input rt-input--sm"
                                :value="row.ry"
                                @input="updateCell(idx, 'ry', $event.target.valueAsNumber)"
                                placeholder="0.00"
                            />
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="6" class="td-empty">Нет данных</td>
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
.rt-title    { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1a2533; }
.rt-subtitle { margin: 0; font-size: 11px; color: #6c757d; }
.table-wrap { overflow-x: auto; }
.rt-table {
    width: 100%; border-collapse: collapse; font-size: 13px; background: #fff;
    table-layout: fixed; /* все колонки одинаковой ширины */
}
.rt-table th, .rt-table td {
    padding: 6px 10px; border: 1px solid #dee2e6;
    vertical-align: middle; white-space: nowrap;
}
.rt-table thead th {
    background: #eef0f3; font-weight: 600; color: #343a40;
    font-size: 12px; text-align: center; border-bottom: 2px solid #ced4da;
}
.td-center { text-align: center; }
.td-group { background: #f8f9fa; font-weight: 600; }
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
.rt-input--sm { width: 100%; max-width: 110px; }
</style>
