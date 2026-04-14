<script setup>
/**
 * Универсальный компонент для таблиц напряжений (Таблицы 3, 4, 5).
 *
 * Таблица 3 — Напряжения в подкосах:
 *   hasElement=true, firstCol='element', elementOptions=[...]
 *
 * Таблица 4 — Напряжения в поясах надстройки:
 *   hasElement=true, firstCol='element', elementOptions=[...]
 *
 * Таблица 5 — Усилия в площадке и стойке:
 *   hasElement=false, firstCol='mark'
 *
 * Столбец «Сечение» разбит на два:
 *   - «Тип сечения»  — select из GaugeProfileTypeEnum (бэкенд)
 *   - «Сечение»      — текстовый ввод (TODO: поиск по бэкенду)
 *
 * Поля ввода:  firstCol | Тип сечения | Сечение | A, см² | Wy, см³ | Nрасч | Mрасч | Ry
 * Вычисляемые: σ, Н/мм² | Кисп        — заполняются сервером после расчёта.
 *
 * TODO: при изменении profileType + sectionDesignation — запрашивать с бэкенда A и Wy.
 */
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    rows: {
        type: Array,
        required: true,
    },
    profileTypes: {
        type: Array,
        default: () => [],
    },
    /** true — первая колонка «Элемент» (select), false — «Отметка, м» (number input) */
    hasElement: {
        type: Boolean,
        default: true,
    },
    /** Варианты для select «Элемент». Игнорируется когда hasElement=false. */
    elementOptions: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:rows']);

const makeRow = () => ({
    // первая колонка
    element: '',          // select — если hasElement=true
    mark: null,           // number — если hasElement=false
    // сечение
    profileType: '',      // select GaugeProfileTypeEnum
    sectionDesignation: '', // text input (TODO: search backend)
    // характеристики сечения (TODO: auto-fill from backend by sectionDesignation)
    area: null,           // A, см²
    momentResistance: null, // Wy, см³
    // нагрузки (ввод)
    nCalc: null,          // Nрасч, тс
    mCalc: null,          // Mрасч, тс·м
    // расчётное сопротивление (ввод, по умолчанию для Ст3/С235/С245)
    ry: 240,              // Ry, Н/мм²
    // вычисляемые (заполняет бэкенд)
    sigma: null,          // σ, Н/мм²
    kUse: null,           // Кисп
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

const fmt = (v, decimals = 2) =>
    (v !== null && v !== undefined) ? Number(v).toFixed(decimals) : '—';
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">{{ title }}</h3>
                <p v-if="subtitle" class="rt-subtitle">{{ subtitle }}</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <!-- Первая колонка: Элемент или Отметка -->
                        <th v-if="hasElement" class="col-elem">Элемент</th>
                        <th v-else class="col-mark">Отметка,<br>м</th>
                        <!-- Сечение (разбито на 2) -->
                        <th class="col-ptype">Тип<br>сечения</th>
                        <th class="col-sec">Сечение<br><span class="hint">TODO: поиск</span></th>
                        <!-- Характеристики сечения -->
                        <th class="col-num">A,<br>см²</th>
                        <th class="col-num">W<sub>y</sub>,<br>см³</th>
                        <!-- Нагрузки -->
                        <th class="col-num">N<sub>расч</sub>,<br>тс</th>
                        <th class="col-num">M<sub>расч</sub>,<br>тс·м</th>
                        <!-- Расчётное сопр. -->
                        <th class="col-num">R<sub>y</sub>,<br>Н/мм²</th>
                        <!-- Вычисляемые -->
                        <th class="col-num col-comp">σ,<br>Н/мм²</th>
                        <th class="col-num col-comp">К<sub>исп</sub></th>
                        <th class="col-del"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">{{ idx + 1 }}</td>

                        <!-- Первая колонка -->
                        <td v-if="hasElement">
                            <select
                                class="rt-select rt-select--elem"
                                :value="row.element"
                                @change="updateCell(idx, 'element', $event.target.value)"
                            >
                                <option value="">— выбрать —</option>
                                <option v-for="opt in elementOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                        </td>
                        <td v-else>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.mark"
                                @input="updateCell(idx, 'mark', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Тип сечения -->
                        <td>
                            <select
                                class="rt-select rt-select--ptype"
                                :value="row.profileType"
                                @change="updateCell(idx, 'profileType', $event.target.value)"
                            >
                                <option value="">— тип —</option>
                                <option v-for="pt in profileTypes" :key="pt.value" :value="pt.value">
                                    {{ pt.label }}
                                </option>
                            </select>
                        </td>

                        <!-- Сечение (обозначение) -->
                        <td>
                            <input
                                type="text" class="rt-input rt-input--sec"
                                :value="row.sectionDesignation"
                                @input="updateCell(idx, 'sectionDesignation', $event.target.value)"
                                placeholder="напр. 50×4"
                                title="TODO: поиск по бэкенду для авто-заполнения A и Wy"
                            />
                        </td>

                        <!-- A, см² — TODO: авто-заполнение из БД -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.area"
                                @input="updateCell(idx, 'area', $event.target.valueAsNumber)"
                                placeholder="0.000"
                                title="TODO: авто-заполнение по сечению"
                            />
                        </td>

                        <!-- Wy, см³ — TODO: авто-заполнение из БД -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.momentResistance"
                                @input="updateCell(idx, 'momentResistance', $event.target.valueAsNumber)"
                                placeholder="0.000"
                                title="TODO: авто-заполнение по сечению"
                            />
                        </td>

                        <!-- Nрасч, тс -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.nCalc"
                                @input="updateCell(idx, 'nCalc', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Mрасч, тс·м -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.mCalc"
                                @input="updateCell(idx, 'mCalc', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Ry, Н/мм² -->
                        <td>
                            <input
                                type="number" step="1" class="rt-input rt-input--xs"
                                :value="row.ry"
                                @input="updateCell(idx, 'ry', $event.target.valueAsNumber)"
                                placeholder="240"
                            />
                        </td>

                        <!-- σ — computed -->
                        <td class="td-computed">{{ fmt(row.sigma) }}</td>

                        <!-- Кисп — computed -->
                        <td class="td-computed td-kmax" :class="{ 'td-warn': row.kUse !== null && row.kUse > 1 }">
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
                        <td :colspan="hasElement ? 12 : 12" class="td-empty">
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

.col-n     { width: 28px; }
.col-elem  { min-width: 100px; }
.col-mark  { width: 90px; }
.col-ptype { min-width: 130px; }
.col-sec   { min-width: 110px; }
.col-num   { width: 80px; text-align: center; }
.col-del   { width: 28px; }

.hint {
    font-size: 10px;
    color: #adb5bd;
    font-weight: 400;
}

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

.rt-input--xs  { width: 72px; }
.rt-input--sec { width: 100px; font-family: Arial, sans-serif; }

.rt-select {
    padding: 3px 4px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 11px;
    color: #212529;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.15s;
}
.rt-select:focus { outline: none; border-color: #1976d2; }

.rt-select--elem  { min-width: 90px; }
.rt-select--ptype { min-width: 120px; }

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
