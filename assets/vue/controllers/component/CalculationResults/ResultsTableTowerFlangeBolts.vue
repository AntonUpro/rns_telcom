<script setup>
/**
 * Напряжения во фланцевых болтах башни — на растяжение или на срез (проп shear).
 *
 * Строки по умолчанию — стыки по верху секций (кроме верхней), формируются бэкендом.
 * Поля ввода:  номер стыка | отметка, м | количество болтов | максимальная нагрузка, тс | диаметр | класс прочности
 * Из справочника: растяжение — Abn, см² и Rbp, Н/мм²; срез — Ab (брутто), см² и Rbs, Н/мм²
 * Вычисляемые: σ, Н/мм² | Кисп — заполняются сервером после расчёта.
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
    // [{ value, label, netArea, grossArea }]
    diameters: {
        type: Array,
        default: () => [],
    },
    // [{ value, label, tensionResistance, shearResistance }]
    strengthClasses: {
        type: Array,
        default: () => [],
    },
    // false — расчёт на растяжение, true — на срез
    shear: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:rows']);

// Поля строки и справочников, отличающиеся для растяжения и среза
const mode = computed(() => props.shear
    ? {area: 'grossArea', resistance: 'rbs', resistanceOption: 'shearResistance',
        areaLabel: 'Площадь брутто<br>Ab, см²', areaSymbol: 'Ab', resistanceSymbol: 'Rbs', titleSuffix: ' на срез'}
    : {area: 'netArea', resistance: 'rbp', resistanceOption: 'tensionResistance',
        areaLabel: 'Площадь нетто<br>Abn, см²', areaSymbol: 'Abn', resistanceSymbol: 'Rbp', titleSuffix: ''});

const makeRow = () => ({
    jointNumber: props.rows.length + 1, mark: null,
    boltCount: null, maxLoad: null,
    diameter: null, strengthClass: null,
    [mode.value.area]: null, sigma: null, [mode.value.resistance]: null, kUse: null,
});

const updateRow = (idx, patch) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? {...r, ...patch} : r));
};

const updateCell = (idx, field, value) => updateRow(idx, {[field]: value});

// Площадь и расчётное сопротивление проставляются сразу при выборе, σ и Кисп сбрасываются до пересчёта
const updateDiameter = (idx, value) => {
    const diameter = props.diameters.find((d) => d.value === value);
    updateRow(idx, {
        diameter: value, [mode.value.area]: diameter?.[mode.value.area] ?? null, sigma: null, kUse: null,
    });
};

const updateStrengthClass = (idx, value) => {
    const strengthClass = props.strengthClasses.find((c) => c.value === value);
    updateRow(idx, {
        strengthClass: value,
        [mode.value.resistance]: strengthClass?.[mode.value.resistanceOption] ?? null,
        sigma: null, kUse: null,
    });
};

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
};

const fmt = (v, d = 2) => (v !== null && v !== undefined) ? Number(v).toFixed(d) : '—';

// Вывод под таблицей — по максимальному Кисп
const conclusion = computed(() => {
    const kUses = props.rows.map((r) => r.kUse).filter((k) => k !== null && k !== undefined);
    if (kUses.length === 0) return null;
    const kMax = Math.max(...kUses);
    const kText = kMax.toFixed(2).replace('.', ',');
    return kMax > 1
        ? `Прочность фланцевых болтов не обеспечена${mode.value.titleSuffix} (Кисп ${kText}).`
        : `Прочность фланцевых болтов обеспечена${mode.value.titleSuffix} (Кисп ${kText}).`;
});
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Напряжения в фланцевых болтах{{ mode.titleSuffix }}</h3>
                <p class="rt-subtitle">σ = P / (n · {{ mode.areaSymbol }}), Кисп = σ / {{ mode.resistanceSymbol }}</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-val">Номер<br>стыка</th>
                        <th class="col-val">Отметка, м</th>
                        <th class="col-val">Количество<br>болтов, шт</th>
                        <th class="col-val">Максимальная<br>нагрузка, тс</th>
                        <th class="col-val">Диаметр<br>болта, мм</th>
                        <th class="col-val">Класс<br>прочности</th>
                        <th class="col-val col-comp" v-html="mode.areaLabel"></th>
                        <th class="col-val col-comp">σ, Н/мм²</th>
                        <th class="col-val col-comp">{{ mode.resistanceSymbol }}, Н/мм²</th>
                        <th class="col-val col-comp">Кисп</th>
                        <th class="col-del"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">
                            <input
                                type="number" min="1" step="1" class="rt-input rt-input--xs"
                                :value="row.jointNumber"
                                @input="updateCell(idx, 'jointNumber', $event.target.valueAsNumber)"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.mark"
                                @input="updateCell(idx, 'mark', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" min="1" step="1" class="rt-input rt-input--sm"
                                :value="row.boltCount"
                                @input="updateCell(idx, 'boltCount', $event.target.valueAsNumber)"
                                placeholder="0"
                            />
                        </td>
                        <td class="td-center">
                            <input
                                type="number" min="0" step="0.01" class="rt-input rt-input--sm"
                                :value="row.maxLoad"
                                @input="updateCell(idx, 'maxLoad', $event.target.valueAsNumber)"
                                placeholder="0.00"
                            />
                        </td>
                        <td class="td-center">
                            <select
                                class="rt-select"
                                :value="row.diameter ?? ''"
                                @change="updateDiameter(idx, $event.target.value === '' ? null : Number($event.target.value))"
                            >
                                <option value="">—</option>
                                <option v-for="d in diameters" :key="d.value" :value="d.value">{{ d.label }}</option>
                            </select>
                        </td>
                        <td class="td-center">
                            <select
                                class="rt-select"
                                :value="row.strengthClass ?? ''"
                                @change="updateStrengthClass(idx, $event.target.value || null)"
                            >
                                <option value="">—</option>
                                <option v-for="c in strengthClasses" :key="c.value" :value="c.value">{{ c.label }}</option>
                            </select>
                        </td>
                        <td class="td-computed">{{ fmt(row[mode.area], 2) }}</td>
                        <td class="td-computed">{{ fmt(row.sigma, 2) }}</td>
                        <td class="td-computed">{{ fmt(row[mode.resistance], 0) }}</td>
                        <td
                            class="td-computed"
                            :class="{ 'td-warn': row.kUse !== null && row.kUse > 1 }"
                        >
                            {{ fmt(row.kUse, 3) }}
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
                        <td colspan="11" class="td-empty">
                            Нет строк — нажмите «+ строка»
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
.rt-btn-add {
    flex-shrink: 0; padding: 5px 12px; font-size: 12px; font-weight: 600;
    color: #1976d2; background: #fff; border: 1px solid #1976d2; border-radius: 4px;
    cursor: pointer; white-space: nowrap; transition: background 0.15s, color 0.15s;
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
.col-val  { width: 110px; text-align: center; }
.col-del  { width: 36px; }
.td-center { text-align: center; }
.td-computed {
    text-align: center; font-family: 'Courier New', monospace;
    font-size: 12px; color: #1565c0; background: #f0f7ff; font-weight: 600;
}
.td-warn { background: #fff3cd !important; color: #856404 !important; }
.td-empty { text-align: center; color: #6c757d; font-style: italic; font-size: 12px; }
.rt-input, .rt-select {
    padding: 4px 6px; border: 1px solid #ced4da; border-radius: 3px;
    font-size: 13px; font-family: 'Courier New', monospace;
    color: #212529; background: #fff; box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus, .rt-select:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }
.rt-input--sm { width: 90px; }
.rt-input--xs { width: 60px; }
.rt-select { min-width: 80px; cursor: pointer; }
.rt-btn-del {
    width: 22px; height: 22px; padding: 0; font-size: 13px; line-height: 1;
    color: #dc3545; background: transparent; border: 1px solid #dc3545; border-radius: 3px;
    cursor: pointer; transition: background 0.15s, color 0.15s;
}
.rt-btn-del:hover:not(:disabled) { background: #dc3545; color: #fff; }
.rt-btn-del:disabled { opacity: 0.35; cursor: not-allowed; }
.rt-conclusion {
    margin: 0; padding: 8px 16px; font-size: 12px; color: #343a40;
    border-top: 1px solid #dee2e6; background: #fafbfc;
}
</style>
