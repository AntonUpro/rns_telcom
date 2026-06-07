<script setup>
/**
 * Универсальный компонент для таблиц напряжений (Таблицы 3, 4, 5).
 *
 * Поле «Сечение»:
 *   - поиск по БД: GET /api/v1/gauge/search?type=<GaugeProfileTypeEnum>&q=<строка>
 *   - dropdown отрисовывается через <Teleport to="body"> + position:fixed — не обрезается overflow
 *   - при выборе из БД: A и Wy становятся read-only, ось меняется через select
 *   - ручной ввод доступен пока профиль не выбран (или при изменении обозначения вручную)
 */
import { reactive, Teleport } from 'vue';

const props = defineProps({
    tableNumber:    { type: Number,  required: true },
    tableName:      { type: String,  required: true },
    subtitle:       { type: String,  default: '' },
    rows:           { type: Array,   required: true },
    profileTypes:   { type: Array,   default: () => [] },
    elementOptions: { type: Array,   default: () => [] },
    defaultElement: { type: String,  default: '' },
});

const emit = defineEmits(['update:rows']);

// ─── Row factory ──────────────────────────────────────────────────────────────

const makeRow = () => ({
    element: props.defaultElement,
    mark: null,
    profileType: '',
    sectionDesignation: '',
    area: null,
    momentResistance: null,
    nCalc: null,
    mCalc: null,
    ry: 240,
    sigma: null,
    kUse: null,
});

// ─── Row mutations ────────────────────────────────────────────────────────────

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r));
};

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
    delete ss[idx];
    delete selProfiles[idx];
    delete wyKeys[idx];
    delete inputRefs[idx];
};

// ─── Per-row autocomplete state ───────────────────────────────────────────────

const ss          = reactive({}); // { open, items, loading, query, activeIdx, panelStyle }
const selProfiles = reactive({}); // выбранный профиль из БД | null
const wyKeys      = reactive({}); // выбранный ключ оси

const inputRefs = {}; // DOM-ссылки на input обозначения, не реактивны
const timers    = new Map();
const ctls      = new Map();

function getSS(idx) {
    if (!ss[idx]) {
        ss[idx] = { open: false, items: [], loading: false, query: '', activeIdx: 0, panelStyle: {} };
    }
    return ss[idx];
}

// ─── Позиционирование dropdown ────────────────────────────────────────────────

function recalcPanelPos(idx) {
    const input = inputRefs[idx];
    if (!input) return;
    const r = input.getBoundingClientRect();
    const s = getSS(idx);
    s.panelStyle = {
        position: 'fixed',
        top:      (r.bottom + 2) + 'px',
        left:     r.left + 'px',
        minWidth: Math.max(r.width, 260) + 'px',
        zIndex:   '9999',
    };
}

// ─── Поиск ────────────────────────────────────────────────────────────────────

function onDesignationInput(idx, value) {
    updateCell(idx, 'sectionDesignation', value);
    selProfiles[idx] = null; // ручной ввод снимает блокировку полей

    const type = props.rows[idx]?.profileType ?? '';
    const q    = value.trim();
    const s    = getSS(idx);
    s.query = q;

    if (!type || !q) {
        s.open = false;
        clearTimer(idx);
        abortPrev(idx);
        return;
    }

    recalcPanelPos(idx);
    s.loading = true;
    s.open    = true;
    clearTimer(idx);
    timers.set(idx, setTimeout(() => doSearch(idx, type, q), 300));
}

function onProfileTypeChange(idx, value) {
    updateCell(idx, 'profileType', value);
    selProfiles[idx] = null;
    wyKeys[idx]      = undefined;
    const s = getSS(idx);
    s.open  = false;
    s.items = [];
}

function doSearch(idx, type, q) {
    abortPrev(idx);
    const ctrl = new AbortController();
    ctls.set(idx, ctrl);

    const url = new URL('/api/v1/gauge/search', window.location.origin);
    url.searchParams.set('type', type);
    url.searchParams.set('q', q);

    fetch(url.toString(), { signal: ctrl.signal })
        .then(r => r.json())
        .then(json => {
            const s = getSS(idx);
            if (s.query !== q) return;
            s.items     = json.success ? (Array.isArray(json.data) ? json.data : []) : [];
            s.loading   = false;
            s.activeIdx = 0;
        })
        .catch(err => {
            if (err?.name === 'AbortError') return;
            const s = getSS(idx);
            s.loading = false;
            s.items   = [];
        });
}

// ─── Выбор профиля из результатов ────────────────────────────────────────────

function pickProfile(idx, profile) {
    getSS(idx).open  = false;
    selProfiles[idx] = profile;
    wyKeys[idx]      = profile.defaultMomentResistanceKey;

    const defWy = profile.momentResistances.find(
        m => m.key === profile.defaultMomentResistanceKey,
    );

    emit('update:rows', props.rows.map((r, i) => i !== idx ? r : {
        ...r,
        sectionDesignation: profile.designation,
        area:               profile.area,
        momentResistance:   defWy ? defWy.value : r.momentResistance,
    }));
}

// ─── Смена оси момента сопротивления ─────────────────────────────────────────

function changeWy(idx, key) {
    wyKeys[idx] = key;
    const p  = selProfiles[idx];
    if (!p) return;
    const mr = p.momentResistances.find(m => m.key === key);
    if (mr) updateCell(idx, 'momentResistance', mr.value);
}

// ─── Клавиатурная навигация и blur ───────────────────────────────────────────

function onDesignationBlur(idx) {
    setTimeout(() => { getSS(idx).open = false; }, 150);
}

function onDesignationKeydown(idx, e) {
    const s = getSS(idx);
    if (!s.open || !s.items.length) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        s.activeIdx = (s.activeIdx + 1) % s.items.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        s.activeIdx = (s.activeIdx - 1 + s.items.length) % s.items.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const p = s.items[s.activeIdx];
        if (p) pickProfile(idx, p);
    } else if (e.key === 'Escape') {
        s.open = false;
    }
}

function clearTimer(idx) {
    const t = timers.get(idx);
    if (t) { clearTimeout(t); timers.delete(idx); }
}

function abortPrev(idx) {
    const c = ctls.get(idx);
    if (c) { c.abort(); ctls.delete(idx); }
}

// ─── Форматирование ───────────────────────────────────────────────────────────

const fmt = (v, d = 2) => (v != null && v !== '') ? Number(v).toFixed(d) : '—';
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. {{ tableName }}</h3>
                <p v-if="subtitle" class="rt-subtitle">{{ subtitle }}</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <thead>
                    <tr>
                        <th class="col-n">№</th>
                        <th class="col-mark">Отметка,<br>м</th>
                        <th class="col-elem">Элемент</th>
                        <th class="col-ptype">Тип<br>сечения</th>
                        <th class="col-sec">Сечение</th>
                        <th class="col-num">A,<br>см²</th>
                        <th class="col-num">W<sub>y</sub>,<br>см³</th>
                        <th class="col-num">N<sub>расч</sub>,<br>тс</th>
                        <th class="col-num">M<sub>расч</sub>,<br>тс·м</th>
                        <th class="col-num">R<sub>y</sub>,<br>Н/мм²</th>
                        <th class="col-num col-comp">σ,<br>Н/мм²</th>
                        <th class="col-num col-comp">К<sub>исп</sub></th>
                        <th class="col-del"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">{{ idx + 1 }}</td>

                        <!-- Отметка -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.mark"
                                @input="updateCell(idx, 'mark', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Тип элемента -->
                        <td>
                            <select
                                class="rt-select rt-select--elem"
                                :value="row.element"
                                @change="updateCell(idx, 'element', $event.target.value)"
                            >
                                <option value="">— выбрать —</option>
                                <option v-for="opt in elementOptions" :key="opt.value" :value="opt.label">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </td>

                        <!-- Тип сечения -->
                        <td>
                            <select
                                class="rt-select rt-select--ptype"
                                :value="row.profileType"
                                @change="onProfileTypeChange(idx, $event.target.value)"
                            >
                                <option value="">— тип —</option>
                                <option v-for="pt in profileTypes" :key="pt.value" :value="pt.value">
                                    {{ pt.label }}
                                </option>
                            </select>
                        </td>

                        <!-- Сечение: input + dropdown через Teleport -->
                        <td class="td-sec-cell">
                            <input
                                :ref="el => { if (el) inputRefs[idx] = el; else delete inputRefs[idx]; }"
                                type="text" class="rt-input rt-input--sec"
                                :value="row.sectionDesignation"
                                @input="onDesignationInput(idx, $event.target.value)"
                                @blur="onDesignationBlur(idx)"
                                @keydown="onDesignationKeydown(idx, $event)"
                                placeholder="напр. 50×4"
                            />
                        </td>

                        <!-- A, см² — readonly когда профиль выбран -->
                        <td class="td-num-cell">
                            <span
                                v-if="selProfiles[idx]"
                                class="td-locked-val"
                                title="Значение из БД сортамента. Измените обозначение вручную для разблокировки."
                            >{{ fmt(row.area, 3) }}</span>
                            <input
                                v-else
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.area"
                                @input="updateCell(idx, 'area', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Wy, см³ — readonly + выбор оси когда профиль выбран -->
                        <td class="td-wy-cell">
                            <template v-if="selProfiles[idx]">
                                <!-- Выбор оси (только если у профиля >1 варианта) -->
                                <select
                                    v-if="selProfiles[idx].momentResistances.length > 1"
                                    class="rt-select rt-select--wy"
                                    :value="wyKeys[idx]"
                                    @change="changeWy(idx, $event.target.value)"
                                >
                                    <option
                                        v-for="mr in selProfiles[idx].momentResistances"
                                        :key="mr.key"
                                        :value="mr.key"
                                    >{{ mr.label }}</option>
                                </select>
                                <span
                                    class="td-locked-val"
                                    title="Значение из БД сортамента. Измените обозначение вручную для разблокировки."
                                >{{ fmt(row.momentResistance, 3) }}</span>
                            </template>
                            <input
                                v-else
                                type="number" step="0.001" class="rt-input rt-input--xs"
                                :value="row.momentResistance"
                                @input="updateCell(idx, 'momentResistance', $event.target.valueAsNumber)"
                                placeholder="0.000"
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
                        <td
                            class="td-computed td-kmax"
                            :class="{ 'td-warn': row.kUse !== null && row.kUse > 1 }"
                        >{{ fmt(row.kUse, 3) }}</td>

                        <td class="td-center">
                            <button
                                class="rt-btn-del" title="Удалить строку"
                                :disabled="rows.length <= 1"
                                @click="removeRow(idx)"
                            >×</button>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="13" class="td-empty">
                            Нет строк — нажмите «+ строка»
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Dropdown-панели через Teleport: вне overflow-контейнера таблицы -->
        <template v-for="(row, idx) in rows" :key="'dd-' + idx">
            <Teleport to="body">
                <div v-if="getSS(idx).open" class="gauge-ac-panel" :style="getSS(idx).panelStyle">
                    <div v-if="getSS(idx).loading" class="gauge-ac-status">Поиск...</div>
                    <template v-else>
                        <div v-if="!getSS(idx).items.length" class="gauge-ac-status">
                            Нет результатов — введите вручную
                        </div>
                        <ul v-else class="gauge-ac-list">
                            <li
                                v-for="(p, pi) in getSS(idx).items"
                                :key="p.id"
                                :class="['gauge-ac-item', { 'gauge-ac-item--active': getSS(idx).activeIdx === pi }]"
                                @mousedown.prevent="pickProfile(idx, p)"
                                @mouseenter="getSS(idx).activeIdx = pi"
                            >
                                <span class="gauge-ac-desig">{{ p.designation }}</span>
                                <span class="gauge-ac-meta">
                                    {{ p.name }}<template v-if="p.standard"> · {{ p.standard }}</template> · A={{ p.area }} см²
                                </span>
                            </li>
                        </ul>
                    </template>
                </div>
            </Teleport>
        </template>
    </section>
</template>

<!-- Глобальные стили для teleport-dropdown (вне scoped, т.к. рендерится в body) -->
<style>
.gauge-ac-panel {
    background: #fff;
    border: 1px solid #1976d2;
    border-radius: 5px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.18);
    font-size: 11px;
    overflow: hidden;
}

.gauge-ac-status {
    padding: 7px 12px;
    color: #6c757d;
    text-align: center;
}

.gauge-ac-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 220px;
    overflow-y: auto;
}

.gauge-ac-item {
    display: flex;
    flex-direction: column;
    gap: 1px;
    padding: 6px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: background 0.1s;
}
.gauge-ac-item:last-child { border-bottom: none; }
.gauge-ac-item:hover,
.gauge-ac-item--active { background: #e8f0fe; }

.gauge-ac-desig {
    font-weight: 600;
    color: #1a2533;
    font-family: 'Courier New', monospace;
    font-size: 12px;
}

.gauge-ac-meta {
    color: #6c757d;
    font-size: 10px;
    white-space: normal;
    line-height: 1.3;
}
</style>

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

.col-n     { min-width: 28px; }
.col-elem  { min-width: 100px; }
.col-mark  { width: 90px; }
.col-ptype { width: 130px; }
.col-sec   { min-width: 120px; }
.col-num   { min-width: 80px; text-align: center; }
.col-del   { width: 28px; }

.td-center { text-align: center; }

.td-num-cell { text-align: center; }

/* Заблокированное значение из БД */
.td-locked-val {
    display: block;
    text-align: center;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #1565c0;
    background: #e8f4fd;
    padding: 3px 6px;
    border-radius: 3px;
    border: 1px solid #90caf9;
    min-width: 60px;
    cursor: default;
}

/* Ячейка Wy: select + locked value */
.td-wy-cell {
    vertical-align: middle;
    text-align: center;
}
.td-wy-cell > * + * { margin-top: 2px; }

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
.rt-input--sec { width: 108px; font-family: Arial, sans-serif; }

.rt-select {
    padding: 3px 4px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 11px;
    color: #212529;
    background: #fff;
    cursor: pointer;
    display: block;
    transition: border-color 0.15s;
}
.rt-select:focus { outline: none; border-color: #1976d2; }

.rt-select--elem  { min-width: 90px; }
.rt-select--ptype { min-width: 120px; }
.rt-select--wy    { width: 100%; max-width: 90px; font-size: 10px; padding: 2px 3px; margin: 0 auto; }

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
