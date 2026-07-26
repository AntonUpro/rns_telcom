<script setup>
/**
 * Максимальные усилия в стволе опоры от расчётных нагрузок.
 *
 * Строки жёстко соответствуют метровым отметкам по высоте столба над землёй
 * (0, +1, +2, ... до последней отметки перед вершиной) — генерируются бэкендом.
 *
 * Поля ввода:  Mрасч, тс·м | Mдоп, тс·м (можно ввести вручную)
 * Вычисляемое: k(max)      — заполняется сервером после расчёта.
 */
import {ref, computed} from 'vue';

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

const VISIBLE_COLLAPSED_COUNT = 2;
const isExpanded = ref(false);

const displayedRows = computed(() => isExpanded.value ? props.rows : props.rows.slice(0, VISIBLE_COLLAPSED_COUNT));
const hasHiddenWarning = computed(() => !isExpanded.value && props.rows
    .slice(VISIBLE_COLLAPSED_COUNT)
    .some((r) => r.kMax !== null && r.kMax !== undefined && r.kMax > 1));

const updateCell = (idx, field, value) => {
    const updated = props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r);
    emit('update:rows', updated);
};

const updateAllowable = (idx, value) => {
    const updated = props.rows.map((r, i) => i === idx ? { ...r, mAllowable: value, mAllowableManual: true } : r);
    emit('update:rows', updated);
};

const resetAllowable = (idx) => {
    const updated = props.rows.map((r, i) => i === idx ? { ...r, mAllowableManual: false } : r);
    emit('update:rows', updated);
};

const fmt2 = (v) => (v !== null && v !== undefined) ? Number(v).toFixed(2) : '';

const fmtKmax = (v) => (v !== null && v !== undefined) ? Number(v).toFixed(2) : '—';

const fmtMark = (mark) => {
    if (mark === null || mark === undefined) return '—';
    const abs = Math.abs(Number(mark)).toFixed(3).replace('.', ',');
    if (mark > 0) return `+${abs}`;
    if (mark < 0) return `-${abs}`;
    return abs;
};
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Максимальные усилия в стволе опоры</h3>
                <p class="rt-subtitle">Усилия от расчётных нагрузок по отметкам высоты столба</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <th class="col-mark">Отметка,<br>м</th>
                        <th class="col-type">Тип опоры</th>
                        <th class="col-val">M<sub>расч</sub>,<br>тс·м</th>
                        <th class="col-val col-comp">M<sub>доп</sub>,<br>тс·м</th>
                        <th class="col-val col-comp">k(max)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in displayedRows" :key="idx">
                        <td class="col-n td-center">{{ idx + 1 }}</td>
                        <td class="td-mark">{{ fmtMark(row.mark) }}</td>
                        <td class="rt-select">{{ row.pillarType }}</td>
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.mCalc"
                                @change="updateCell(idx, 'mCalc', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>
                        <td class="td-computed">
                            <div class="rt-allowable">
                                <input
                                    type="number" step="0.01" class="rt-input rt-input--sm"
                                    :value="fmt2(row.mAllowable)"
                                    @change="updateAllowable(idx, $event.target.valueAsNumber)"
                                    placeholder="0.00"
                                />
                                <button
                                    v-if="row.mAllowableManual"
                                    class="rt-btn-reset"
                                    title="Вернуть автоматический расчёт по высоте"
                                    @click="resetAllowable(idx)"
                                >↺</button>
                                <span
                                    v-if="row.sectionDataAvailable === false"
                                    class="rt-warn-icon"
                                    title="Точный расчёт по высоте недоступен для этой марки опоры — значение приближённое, при необходимости введите вручную"
                                >⚠</span>
                            </div>
                        </td>
                        <td class="td-computed td-kmax" :class="{ 'td-warn': row.kMax !== null && row.kMax > 1 }">
                            {{ fmtKmax(row.kMax) }}
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="6" class="td-empty">Нет данных — заполните высоту столба в исходных данных</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button
            v-if="rows.length > VISIBLE_COLLAPSED_COUNT"
            class="rt-btn-toggle"
            :class="{ 'rt-btn-toggle--warn': hasHiddenWarning }"
            @click="isExpanded = !isExpanded"
        >
            <span>{{ isExpanded ? 'Свернуть' : `Показать все ${rows.length} строк` }}</span>
            <span v-if="hasHiddenWarning" class="rt-toggle-warn-badge" title="Среди скрытых строк есть превышение k(max) > 1">⚠</span>
            <span class="rt-toggle-arrow" :class="{ 'rt-toggle-arrow--up': isExpanded }">▾</span>
        </button>
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

.col-n    { min-width: 50px; }
.col-mark { min-width: 110px; }
.col-type { width: 140px; }
.col-val  { min-width: 150px; text-align: center; }
.col-comp { }

.td-center { text-align: center; }

.td-mark {
    text-align: center;
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: #343a40;
}

.td-computed {
    text-align: center;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #1565c0;
    background: #f0f7ff;
    font-weight: 600;
}

.td-kmax { }

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

.rt-input--sm { width: 110px; }

.rt-select {
    width: 100%;
    padding: 4px 6px;
    font-size: 12px;
    color: #212529;
    text-align: center;
}

.rt-allowable {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.rt-btn-reset {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    padding: 0;
    font-size: 13px;
    line-height: 1;
    color: #1976d2;
    background: #fff;
    border: 1px solid #1976d2;
    border-radius: 3px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-reset:hover { background: #1976d2; color: #fff; }

.rt-warn-icon {
    flex-shrink: 0;
    font-size: 13px;
    cursor: help;
}

.rt-btn-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #495057;
    background: #f8f9fa;
    border: none;
    border-top: 1px solid #dee2e6;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-toggle:hover { background: #eef0f3; color: #1976d2; }
.rt-btn-toggle--warn { color: #856404; background: #fff8e6; }
.rt-btn-toggle--warn:hover { background: #fff3cd; }

.rt-toggle-warn-badge { font-size: 13px; }

.rt-toggle-arrow {
    display: inline-block;
    transition: transform 0.2s ease;
}
.rt-toggle-arrow--up { transform: rotate(180deg); }
</style>
