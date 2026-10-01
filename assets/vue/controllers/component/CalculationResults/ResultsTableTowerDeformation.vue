<script setup>
/**
 * Перемещения верхних узлов башни от нормативных нагрузок.
 *
 * Поля ввода:  линейное перемещение, мм | угловые перемещения относительно осей Y и Z, град
 * Вычисляемые: высота сооружения, м (из исходных данных) | допустимое перемещение = H/100, мм | КИ
 *              — заполняются сервером после расчёта.
 */
import {computed} from 'vue';

const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
    tableNumber: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['update:rows']);

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? {...r, [field]: value} : r));
};

const fmt = (v, d = 3) => (v !== null && v !== undefined) ? Number(v).toFixed(d) : '—';

// Вывод под таблицей — по максимальному КИ
const conclusion = computed(() => {
    const kUses = props.rows.map((r) => r.kUse).filter((k) => k !== null && k !== undefined);
    if (kUses.length === 0) return null;
    const kMax = Math.max(...kUses);
    const kText = kMax.toFixed(2).replace('.', ',');
    return kMax > 1
        ? `Отклонения ствола от вертикали превышают допустимые значения (КИ ${kText}).`
        : `Отклонения ствола от вертикали не превышают допустимые значения (КИ ${kText}).`;
});
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Перемещения верхних узлов опоры от нормативных нагрузок</h3>
                <p class="rt-subtitle">Допустимое линейное перемещение — H/100</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-val">Линейные перемещения<br>верхних узлов, мм</th>
                        <th class="col-val col-comp">Высота<br>сооружения, м</th>
                        <th class="col-val col-comp">Допустимые линейные<br>перемещения, мм</th>
                        <th class="col-val col-comp">КИ</th>
                        <th class="col-val">Угловые перемещения<br>относительно оси Y, град</th>
                        <th class="col-val">Угловые перемещения<br>относительно оси Z, град</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">
                            <input
                                type="number" step="0.1" class="rt-input rt-input--sm"
                                :value="row.displacement"
                                @input="updateCell(idx, 'displacement', $event.target.valueAsNumber)"
                                placeholder="0"
                            />
                        </td>
                        <td class="td-computed">{{ fmt(row.height, 2) }}</td>
                        <td class="td-computed">{{ fmt(row.displacementAllowable, 0) }}</td>
                        <td
                            class="td-computed"
                            :class="{ 'td-warn': row.kUse !== null && row.kUse > 1 }"
                        >
                            {{ fmt(row.kUse, 2) }}
                        </td>
                        <td class="td-center">
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.angleY"
                                @input="updateCell(idx, 'angleY', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.angleZ"
                                @input="updateCell(idx, 'angleZ', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="conclusion" class="rt-conclusion">{{ conclusion }}</p>
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
.col-val  { width: 130px; text-align: center; }
.td-center { text-align: center; }
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
.rt-input--sm { width: 90px; }
.rt-conclusion {
    margin: 0; padding: 8px 16px; font-size: 12px; color: #343a40;
    border-top: 1px solid #dee2e6; background: #fafbfc;
}
</style>
