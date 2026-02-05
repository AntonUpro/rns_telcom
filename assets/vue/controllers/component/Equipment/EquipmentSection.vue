<script setup>
import {reactive, ref, watch, nextTick} from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    category: { type: String, required: true }, // 'rrl' | 'panel' | 'radio' | 'other'
    rows: { type: Array, default: () => [] },
    editable: { type: Boolean, default: true },

    // Настройки поиска
    minChars: { type: Number, default: 2 },
    debounceMs: { type: Number, default: 300 },
    apiUrl: { type: String, default: '/api/v1/equipment/search' } // поменяйте под ваш бэкенд
});
const emit = defineEmits(['update:rows', 'add-item', 'remove-item']);

const localRows = ref([]);

const clone = (v) => JSON.parse(JSON.stringify(v ?? []));

// Флаг для предотвращения бесконечных обновлений
let isUpdatingFromProps = false;

// Синхронизация пропа и локального состояния
watch(
    () => props.rows,
    (v) => {
        isUpdatingFromProps = true;
        localRows.value = clone(v);
        // Сбрасываем флаг после следующего тика
        nextTick(() => {
            isUpdatingFromProps = false;
        });
    },
    { immediate: true, deep: true }
);

// Эмитим наверх только при изменениях не из пропсов
watch(
    localRows,
    (v) => {
        if (!isUpdatingFromProps) {
            emit('update:rows', clone(v));
        }
    },
    { deep: true }
);

const formatDims = (row) => {
    if (props.category === 'rrl') {
        return `Ø${row?.diameter ?? 0}`;
    }
    const h = row?.height ?? 0;
    const w = row?.width ?? 0;
    const d = row?.depth ?? 0;
    return `${w}×${h}×${d}`;
};

function addIds(arr) {
    return (arr || []).map(r => (r && r.id) ? r : { id: uid(), ...r });
}
const uid = () => Math.random().toString(36).slice(2) + Date.now().toString(36);

// ---------------------- Автокомплит ----------------------
const sMap = reactive({}); // key -> { open, items, loading, error, active, query }
const timers = new Map();   // key -> setTimeout id
const controllers = new Map(); // key -> AbortController

function keyOf(item, idx) { return item?.id ?? idx; }
function getState(key) {
    if (!sMap[key]) {
        sMap[key] = { open: false, items: [], loading: false, error: null, active: 0, query: '' };
    }
    return sMap[key];
}
function stateFor(item, idx) {
    return getState(keyOf(item, idx));
}

async function apiSearch(query, category, signal) {
    const url = new URL(props.apiUrl, window.location.origin);
    url.searchParams.set('query', query);
    url.searchParams.set('type', category);

    const res = await fetch(url.toString(), { signal });
    if (!res.ok) throw new Error('Ошибка поиска');

    const responseData = await res.json();
    if (!responseData || !responseData.success) {
        throw new Error('Ошибка поиска: ' + responseData.message);
    }

    // ожидаем массив объектов
    // [{ id?, fullName, weight, diameter? | width?, height?, depth?, type? }, ...]
    return responseData.data;
}

function calculateDropdownPosition(row, idx) {
    // Get the input element position
    nextTick(() => {
        const inputElements = document.querySelectorAll('input.table-input');
        const targetInput = Array.from(inputElements).find(input =>
            input.value === row.fullName
        );

        if (targetInput) {
            const rect = targetInput.getBoundingClientRect();
            const panel = document.querySelector('.autocomplete-panel[data-visible="true"]');

            if (panel) {
                // Position relative to viewport
                panel.style.position = 'fixed';
                panel.style.top = (rect.bottom + 2) + 'px';
                panel.style.left = rect.left + 'px';
                panel.style.width = rect.width + 'px';
                panel.style.zIndex = '9999';
            }
        }
    });
}

function scheduleSearch(row, idx) {
    if (!props.editable) return;
    const key = keyOf(row, idx);
    const q = (row.fullName || '').trim();
    const st = getState(key);
    st.query = q;

    if (q.length < props.minChars) {
        st.open = false;
        st.items = [];
        st.loading = false;
        st.error = null;
        clearTimer(key);
        abortPrev(key);
        return;
    }

    st.loading = true;
    st.error = null;
    st.open = true;

    // Calculate position when dropdown opens
    // calculateDropdownPosition(row, idx);

    clearTimer(key);
    timers.set(key, setTimeout(() => performSearch(key, q), props.debounceMs));
}

function performSearch(key, q) {
    abortPrev(key);
    const ctrl = new AbortController();
    controllers.set(key, ctrl);

    apiSearch(q, props.category, ctrl.signal)
        .then(list => {
            const st = getState(key);
            // если за время запроса пользователь уже изменил запрос — отбрасываем ответ
            if (st.query !== q) return;
            st.items = normalizeResults(list, props.category);
            st.loading = false;
            st.error = null;
            st.active = 0;
            st.open = true;
        })
        .catch(err => {
            if (err?.name === 'AbortError') return;
            const st = getState(key);
            st.loading = false;
            st.error = err?.message || 'Ошибка поиска';
            st.items = [];
        });
}

function normalizeResults(list, category) {
    return (Array.isArray(list) ? list : []).map((it, i) => ({
        id: it.id ?? `res-${i}-${Math.random().toString(36).slice(2)}`,
        fullName: it.fullName ?? it.name ?? '',
        weight: num(it.weight),
        // размеры
        diameter: num(it.diameter ?? it.diam),
        height: num(it.height),
        width: num(it.width),
        depth: num(it.depth),
        type: it.type ?? category
    }));
}

function num(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
}

function clearTimer(key) {
    const t = timers.get(key);
    if (t) {
        clearTimeout(t);
        timers.delete(key);
    }
}

function abortPrev(key) {
    const c = controllers.get(key);
    if (c) {
        c.abort();
        controllers.delete(key);
    }
}

function closeDropdown(key) {
    const st = getState(key);
    st.open = false;
}

function formatDimsForResult(res, category) {
    if (category === 'rrl') {
        return `Ø${res.diameter || 0} мм`;
    }
    const h = res.height || 0, w =  res.width || 0, d = res.depth || 0;
    return `${h}×${w}×${d} мм`;
}

function formatWeight(n) {
    const v = Number(n);
    return Number.isFinite(v) ? v.toFixed(1).replace('.', ',') : '0,0';
}

function selectResult(row, idx, res) {
    if (props.category === 'rrl') {
        row.diameter = Number(res.diameter) || 0;
    } else {
        row.height = Number(res.height) || 0;
        row.width = Number(res.width) || 0;
        row.depth = Number(res.depth) || 0;
    }
    row.weight = Number(res.weight) || 0;
    row.fullName = res.fullName || row.fullName;

    const key = keyOf(row, idx);
    closeDropdown(key);
}

function onMouseEnter(row, idx, rIdx) {
    // При наведении мышью сбрасываем активный элемент клавиатуры
    const key = keyOf(row, idx);
    const st = getState(key);
    st.active = rIdx;
}

function onKeyDown(row, idx, e) {
    const key = keyOf(row, idx);
    const st = getState(key);
    if (!st.open || !st.items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        st.active = (st.active + 1) % st.items.length;
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        st.active = (st.active - 1 + st.items.length) % st.items.length;
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const item = st.items[st.active];
        if (item) selectResult(row, idx, item);
    } else if (e.key === 'Escape') {
        e.preventDefault();
        closeDropdown(key);
    }
}

function onBlur(row, idx) {
    // даём шанс mousedown по пункту меню сработать раньше blur
    const key = keyOf(row, idx);
    setTimeout(() => closeDropdown(key), 150);
}
</script>

<template>
    <!-- Заголовок раздела -->
    <tr class="section-row">
        <td colspan="5" class="section-title">
            <div>{{ label }}</div>
        </td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>
            <div class="table-actions" v-if="editable">
                <button @click="emit('add-item')" class="btn-add-row" title="Добавить строку">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
            </div>
        </td>
    </tr>

    <!-- Строки раздела -->
    <tr
        v-for="(item, index) in localRows"
        :key="item.id ?? index"
        class="equipment-row"
    >
        <!-- Наименование -->
        <td class="cell-designation">
            <div class="editable-cell autocomplete-root">
                <input
                    v-if="editable"
                    type="text"
                    v-model="item.fullName"
                    class="table-input"
                    placeholder="Введите обозначение"
                    @input="scheduleSearch(item, index)"
                    @focus="scheduleSearch(item, index)"
                    @keydown="onKeyDown(item, index, $event)"
                    @blur="onBlur(item, index)"
                />
                <span v-else>{{ item.fullName }}</span>
            </div>

            <!-- Выпадающее окно с подсказками -->
            <div
                v-if="stateFor(item, index).open"
                class="autocomplete-panel"
                :data-visible="stateFor(item, index).open"
                style="background: #e3e3c9; border: 3px solid #efc0c0; display: block !important; visibility: visible !important;"
            >
                <div class="autocomplete-status" v-if="stateFor(item, index).loading">
                    Поиск...
                </div>
                <div class="autocomplete-status error" v-else-if="stateFor(item, index).error">
                    {{ stateFor(item, index).error }}
                </div>
                <template v-else>
                    <div class="autocomplete-status" v-if="!stateFor(item, index).items.length">
                        Нет результатов
                    </div>
                    <ul class="autocomplete-list" v-else>
                        <li
                            v-for="(res, rIdx) in stateFor(item, index).items"
                            :key="res.id"
                            :class="['autocomplete-item', { active: stateFor(item, index).active === rIdx }]"
                            @mousedown.prevent="selectResult(item, index, res)"
                            @mouseenter="onMouseEnter(item, index, rIdx)"
                        >
                            <div class="title">{{ res.fullName }}</div>
                            <div class="meta">
                                {{ formatDimsForResult(res, category) }} • {{ formatWeight(res.weight) }} кг
                            </div>
                        </li>
                    </ul>
                </template>
            </div>
        </td>

        <!-- Габариты -->
        <td class="cell-dimensions">
            <div class="editable-cell dimensions-cell">
                <!-- RRL: диаметр -->
                <template v-if="category === 'rrl'">
                    <input
                        v-if="false"
                        type="number"
                        v-model.number="item.diameter"
                        class="table-input small-input number-input"
                        min="0"
                        step="1"
                        placeholder="Диаметр, мм"
                    />
                    <span v-else>{{ formatDims(item) }}</span>
                </template>

                <!-- Прямоугольные: ширина×высота×глубина -->
                <template v-else>
                    <div v-if="false" class="dimension-inputs">
                        <input type="number" v-model.number="item.width"  class="table-input small-input number-input" min="0" step="1" placeholder="Шир" />
                        <span class="dimension-separator">×</span>
                        <input type="number" v-model.number="item.height" class="table-input small-input number-input" min="0" step="1" placeholder="Выс" />
                        <span class="dimension-separator">×</span>
                        <input type="number" v-model.number="item.depth"  class="table-input small-input number-input" min="0" step="1" placeholder="Гл"  />
                    </div>
                    <span v-else>{{ formatDims(item) }}</span>
                </template>
            </div>
        </td>

        <!-- Масса -->
        <td class="cell-weight">
            <div class="editable-cell">
                <input
                    v-if="false"
                    type="number"
                    v-model.number="item.weight"
                    class="table-input number-input"
                    min="0"
                    step="0.1"
                />
                <span v-else>{{ item.weight }}</span>
            </div>
        </td>

        <!-- Количество -->
        <td class="cell-quantity">
            <div class="editable-cell">
                <input
                    v-if="editable"
                    type="number"
                    v-model.number="item.quantity"
                    class="table-input number-input"
                    min="1"
                    step="1"
                />
                <span v-else>{{ item.quantity }}</span>
            </div>
        </td>

        <!-- Отметка подвеса, м -->
        <td class="cell-height">
            <div class="editable-cell">
                <input
                    v-if="editable"
                    type="number"
                    v-model.number="item.mountHeight"
                    class="table-input number-input"
                    min="0"
                    step="0.001"
                    placeholder="м"
                />
                <span v-else>{{ item.mountHeight ?? '—' }}</span>
            </div>
        </td>

        <!-- Действия -->
        <td class="cell-actions">
            <div class="action-buttons" v-if="editable">
                <button
                    @click="emit('remove-item', index)"
                    class="btn-action-icon btn-remove"
                    title="Удалить оборудование"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </td>
    </tr>
</template>

<style scoped>
/* Секции и подразделы */
.section-row {
    background: #f1f8ff;
}

.section-title {
    font-weight: 600;
    height: 50px;
    margin-left: 5px;
    color: #2c3e50;
    text-align: left !important;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.table-actions {
    align-items: center;
    justify-content: center;
    display: flex;
}

.btn-add-row {
    background: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    width: 25px;
    height: 25px;
    align-items: center;
    justify-content: center;
    display: flex;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-add-row:hover {
    background: #2980b9;
}

/* Стили для строк с оборудованием */
.equipment-row:hover {
    background: #f8f9fa;
}

/* Ячейки */
.cell-designation {
    text-align: left !important;
    position: relative;
    overflow: visible;
}

.editable-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    position: relative;
    overflow: visible;
}

.table-input {
    width: 100%;
    padding: 0.375rem 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    background: #ffffff;
    position: relative;
    z-index: 1;
}

.table-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.number-input {
    text-align: center;
}

.small-input {
    width: 60px;
}

.dimensions-cell {
    align-items: center;
}

.dimension-inputs {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    justify-content: center;
}

.dimension-separator {
    color: #6c757d;
    padding: 0 0.125rem;
}

.btn-action-icon {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-action-icon:hover {
    background: #5a6268;
}

.btn-remove {
    background: #e74c3c;
}

.btn-remove:hover {
    background: #c0392b;
}

/* Кнопки действий в строке */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

/* Автокомплит */
.autocomplete-root {
    position: relative;
    display: block;
    width: 100%;
}

.autocomplete-panel {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 999999;
    background: #ffffff;
    border: 2px solid #3498db;
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    max-height: 200px;
    overflow-y: auto;
    padding: 0;
    font-size: 0.85rem;
    min-width: 280px;
    width: auto;
    display: block;
    visibility: visible;
}

.autocomplete-panel[data-visible="true"] {
    display: block !important;
    visibility: visible !important;
}

.autocomplete-status {
    padding: 0.5rem 0.75rem;
    color: #6c757d;
    font-size: 0.8rem;
    text-align: center;
}

.autocomplete-status.error {
    color: #dc3545;
    background-color: #fff5f5;
}

.autocomplete-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 160px;
    overflow-y: auto;
}

.autocomplete-item {
    padding: 0.4rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.15s ease;
    display: flex;
    flex-direction: row;
    gap: 1rem;
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item .title {
    font-weight: 500;
    color: #243442;
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.autocomplete-item .meta {
    font-size: 0.9rem;
    color: #6c757d;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.autocomplete-item:hover,
.autocomplete-item.active {
    background: #f8f9fa;
}

.autocomplete-item:hover .title,
.autocomplete-item.active .title {
    color: #3498db;
}

/* Scrollbar styling */
.autocomplete-panel::-webkit-scrollbar {
    width: 6px;
}

.autocomplete-panel::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.autocomplete-panel::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.autocomplete-panel::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

@media (max-width: 768px) {
    .table-actions {
        align-self: flex-end;
    }

    .small-input {
        width: 50px;
    }

    .dimension-inputs {
        flex-wrap: wrap;
    }
}
</style>
