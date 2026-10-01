<script setup>
/**
 * Сравнение расчётных нагрузок на фундаменты башни с проектными.
 *
 * Ввод: проектные вертикальная и сдвигающая нагрузки, тс.
 * Вычисляемые (сервер): расчётные значения из таблицы нагрузок на фундаменты
 *   (прижимающая — max Rz > 0, выдергивающая — |min Rz < 0|, сдвигающая — |Rx| той же строки)
 *   и КИ = расчётная / проектная.
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
    loadKinds: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:rows']);

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? {...r, [field]: value} : r));
};

const loadKindLabel = (value) => props.loadKinds.find((k) => k.value === value)?.label ?? value;

const fmt = (v, d = 2) => (v !== null && v !== undefined) ? Number(v).toFixed(d) : '—';
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Сравнение расчетных нагрузок с проектными</h3>
                <p class="rt-subtitle">
                    Расчетные нагрузки берутся из таблицы нагрузок на фундаменты после выполнения расчета
                </p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th rowspan="2"></th>
                        <th colspan="2">Нагрузки на фундаменты</th>
                        <th rowspan="2">КИ</th>
                    </tr>
                    <tr>
                        <th>Расчетные</th>
                        <th>Проектные</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(row, idx) in rows" :key="row.loadKind">
                        <tr>
                            <td class="td-label">{{ loadKindLabel(row.loadKind) }}, тс</td>
                            <td class="td-computed">{{ fmt(row.calcVertical) }}</td>
                            <td class="td-center">
                                <input
                                    type="number" step="0.01" class="rt-input rt-input--sm"
                                    :value="row.projectVertical"
                                    @input="updateCell(idx, 'projectVertical', $event.target.valueAsNumber)"
                                    placeholder="0.00"
                                />
                            </td>
                            <td
                                class="td-computed"
                                :class="{ 'td-warn': row.kUseVertical !== null && row.kUseVertical > 1 }"
                            >
                                {{ fmt(row.kUseVertical) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="td-label">Сдвигающая, тс</td>
                            <td class="td-computed">{{ fmt(row.calcShear) }}</td>
                            <td class="td-center">
                                <input
                                    type="number" step="0.01" class="rt-input rt-input--sm"
                                    :value="row.projectShear"
                                    @input="updateCell(idx, 'projectShear', $event.target.valueAsNumber)"
                                    placeholder="0.00"
                                />
                            </td>
                            <td
                                class="td-computed"
                                :class="{ 'td-warn': row.kUseShear !== null && row.kUseShear > 1 }"
                            >
                                {{ fmt(row.kUseShear) }}
                            </td>
                        </tr>
                    </template>
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
.td-label { font-weight: 600; background: #f8f9fa; }
.td-computed {
    text-align: center; font-family: 'Courier New', monospace;
    font-size: 12px; color: #1565c0; background: #f0f7ff; font-weight: 600;
}
.td-warn { background: #fff3cd !important; color: #856404 !important; }
.rt-input {
    padding: 4px 6px; border: 1px solid #ced4da; border-radius: 3px;
    font-size: 13px; font-family: 'Courier New', monospace;
    color: #212529; background: #fff; box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }
.rt-input--sm { width: 100%; max-width: 110px; }
</style>
