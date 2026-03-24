<script setup>
import {ref, computed, onMounted} from 'vue';

/**
 * Ожидаемая структура ответа от API GET /api/v1/calculation/total-load/{calculationId}:
 *
 * {
 *   "success": true,
 *   "data": {
 *     "pillarSections": [           // Таблица 1: нагрузка на ствол и коммуникации по секциям
 *       {
 *         "sectionNumber": 1,        // № секции
 *         "topHeight": 10500,        // Высотная отметка верха, мм
 *         "sectionHeight": 3000,     // Высота секции, мм
 *         "totalLoad": 1520.5,       // Общая нагрузка, кг
 *         "loadPerLinearMeter": 506.8 // Нагрузка на 1 п.м., кг/м
 *       }
 *     ],
 *     "platformSections": [         // Таблица 2: нагрузка на площадку и надстройку
 *       {
 *         "label": "1",             // № секции или строка "Подкосы"
 *         "isStrut": false,         // true если это подкосы
 *         "topHeight": 21500,       // Отметка верха, мм
 *         "height": 2500,           // Высота, мм
 *         "totalLoad": 840.0,       // Общая нагрузка, кг
 *         "loadPerLinearMeterPerBelt": 210.0 // Нагрузка на 1 п.м. 1 пояса, кг/м
 *       }
 *     ],
 *     "equipmentHeights": [         // Таблица 3: нагрузка на оборудование по высотным отметкам
 *       {
 *         "heightMark": 15000,      // Высотная отметка, мм
 *         "height": 5000,           // Высота, мм
 *         "totalLoad": 2150.0       // Общая нагрузка на этой отметке, кг
 *       }
 *     ]
 *   }
 * }
 */

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

const loading = ref(false);
const error = ref(null);

const pillarSections = ref([]);
const platformSections = ref([]);

// Таблица 3: equipmentHeights с локальным полем nodesCount для ввода
const equipmentHeights = ref([]);

const fetchTotalLoad = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await fetch(`/api/v1/calculation/total-load/${props.calculationId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const responseData = await response.json();

        if (!response.ok || !responseData.success) {
            throw new Error(responseData.error || 'Неизвестная ошибка');
        }

        pillarSections.value = responseData.data.pillarSections ?? [];
        platformSections.value = responseData.data.platformSections ?? [];

        // Инициализируем equipmentHeights, добавляя поле nodesCount для ввода
        equipmentHeights.value = (responseData.data.equipmentHeights ?? []).map((row) => ({
            ...row,
            nodesCount: 1,
        }));
    } catch (err) {
        error.value = err.message;
        console.error('Ошибка загрузки суммарной нагрузки:', err);
    } finally {
        loading.value = false;
    }
};

// Нагрузка на один узел считается динамически
const loadPerNode = (row) => {
    const nodes = row.nodesCount;
    if (!nodes || nodes <= 0) return '—';
    return (row.totalLoad / nodes).toFixed(2);
};

const formatNum = (val) => {
    if (val === null || val === undefined) return '—';
    return Number(val).toLocaleString('ru-RU', {maximumFractionDigits: 2});
};

onMounted(() => {
    fetchTotalLoad();
});
</script>

<template>
    <div class="total-load-manager">

        <!-- Состояние загрузки / ошибка -->
        <div v-if="loading" class="state-loading">Загрузка данных...</div>
        <div v-else-if="error" class="state-error">{{ error }}</div>

        <template v-else>

            <!-- ───────────────────────────────────────────────
                 Таблица 1. Общая нагрузка на ствол и коммуникации
            ─────────────────────────────────────────────────── -->
            <section class="load-section">
                <div class="section-header">
                    <h2 class="section-title">Общая нагрузка на ствол опоры и коммуникации</h2>
                    <p class="section-subtitle">Суммарная ветровая нагрузка по секциям опоры</p>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">№<br>секции</th>
                                <th>Высотная отметка<br>верха, мм</th>
                                <th>Высота<br>секции, мм</th>
                                <th>Общая нагрузка,<br>кг</th>
                                <th>Нагрузка на 1<br>пог. метр, кг/м</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in pillarSections" :key="idx">
                                <td class="col-num">{{ row.sectionNumber }}</td>
                                <td>{{ formatNum(row.topHeight) }}</td>
                                <td>{{ formatNum(row.sectionHeight) }}</td>
                                <td class="col-value">{{ formatNum(row.totalLoad) }}</td>
                                <td class="col-value">{{ formatNum(row.loadPerLinearMeter) }}</td>
                            </tr>
                            <tr v-if="pillarSections.length === 0">
                                <td colspan="5" class="no-data">Нет данных для отображения</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ───────────────────────────────────────────────
                 Таблица 2. Ветровая нагрузка на площадку и надстройку
            ─────────────────────────────────────────────────── -->
            <section class="load-section">
                <div class="section-header">
                    <h2 class="section-title">Ветровая нагрузка на площадку и надстройку</h2>
                    <p class="section-subtitle">Распределение ветровой нагрузки по секциям площадки и подкосам</p>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-num">№ секции /<br>элемент</th>
                                <th>Отметка<br>верха, мм</th>
                                <th>Высота,<br>мм</th>
                                <th>Общая<br>нагрузка, кг</th>
                                <th>Нагрузка на 1 п.м.<br>1 пояса, кг/м</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in platformSections"
                                :key="idx"
                                :class="{ 'row-strut': row.isStrut }"
                            >
                                <td class="col-num">
                                    <span v-if="row.isStrut" class="badge-strut">Подкосы</span>
                                    <span v-else>{{ row.label }}</span>
                                </td>
                                <td>{{ formatNum(row.topHeight) }}</td>
                                <td>{{ formatNum(row.height) }}</td>
                                <td class="col-value">{{ formatNum(row.totalLoad) }}</td>
                                <td class="col-value">{{ formatNum(row.loadPerLinearMeterPerBelt) }}</td>
                            </tr>
                            <tr v-if="platformSections.length === 0">
                                <td colspan="5" class="no-data">Нет данных для отображения</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ───────────────────────────────────────────────
                 Таблица 3. Нагрузка на оборудование
            ─────────────────────────────────────────────────── -->
            <section class="load-section">
                <div class="section-header">
                    <h2 class="section-title">Нагрузка на оборудование</h2>
                    <p class="section-subtitle">
                        Укажите количество узлов приложения силы — нагрузка на один узел рассчитывается автоматически
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Высотная<br>отметка, мм</th>
                                <th>Высота,<br>мм</th>
                                <th>Общая нагрузка<br>на отметке, кг</th>
                                <th class="col-input">Кол-во узлов<br>приложения силы</th>
                                <th>Нагрузка на<br>один узел, кг</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in equipmentHeights" :key="idx">
                                <td>{{ formatNum(row.heightMark) }}</td>
                                <td>{{ formatNum(row.height) }}</td>
                                <td class="col-value">{{ formatNum(row.totalLoad) }}</td>
                                <td class="col-input">
                                    <input
                                        type="number"
                                        v-model.number="row.nodesCount"
                                        class="nodes-input"
                                        min="1"
                                        step="1"
                                    />
                                </td>
                                <td class="col-value col-computed">{{ loadPerNode(row) }}</td>
                            </tr>
                            <tr v-if="equipmentHeights.length === 0">
                                <td colspan="5" class="no-data">Нет данных для отображения</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </template>

        <!-- Кнопка обновления -->
        <div class="toolbar">
            <button
                class="btn-refresh"
                :disabled="loading"
                @click="fetchTotalLoad"
            >
                {{ loading ? 'Загрузка...' : 'Обновить данные' }}
            </button>
        </div>

    </div>
</template>

<style scoped>
/* ── Контейнер ── */
.total-load-manager {
    display: flex;
    flex-direction: column;
    gap: 28px;
    padding: 24px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #212529;
}

/* ── Состояния загрузки / ошибки ── */
.state-loading,
.state-error {
    padding: 24px;
    text-align: center;
    font-size: 15px;
    border-radius: 6px;
}

.state-loading {
    color: #1976d2;
    background-color: #e3f2fd;
    border: 1px solid #90caf9;
}

.state-error {
    color: #b71c1c;
    background-color: #ffebee;
    border: 1px solid #ef9a9a;
}

/* ── Секция ── */
.load-section {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}

.section-header {
    padding: 14px 20px 12px;
    background-color: #f4f6f8;
    border-bottom: 1px solid #dee2e6;
}

.section-title {
    margin: 0 0 2px;
    font-size: 15px;
    font-weight: 700;
    color: #1a2533;
    letter-spacing: 0.01em;
}

.section-subtitle {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}

/* ── Таблица ── */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #ffffff;
}

.data-table th,
.data-table td {
    padding: 10px 14px;
    border: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
    line-height: 1.4;
}

.data-table thead th {
    background-color: #eef0f3;
    font-weight: 600;
    color: #343a40;
    font-size: 13px;
    border-bottom: 2px solid #ced4da;
}

.data-table tbody tr:hover {
    background-color: #f8f9fa;
}

/* Подкосы — слегка выделяем строку */
.row-strut td {
    background-color: #fafafa;
    font-style: italic;
}

.badge-strut {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background-color: #e8eaf6;
    color: #3949ab;
    font-size: 12px;
    font-weight: 600;
    font-style: normal;
    white-space: nowrap;
}

/* Первый столбец — номер / метка */
.col-num {
    width: 80px;
    font-weight: 600;
    background-color: #f8f9fa;
}

/* Числовые значения — моноширинный шрифт */
.col-value {
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    color: #1a2533;
}

/* Вычисляемое значение — подсветка */
.col-computed {
    background-color: #f0f7ff;
    font-weight: 600;
    color: #1565c0;
}

/* ── Поле ввода количества узлов ── */
.col-input {
    width: 130px;
}

.nodes-input {
    width: 80px;
    padding: 4px 8px;
    border: 1px solid #adb5bd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
    font-family: 'Courier New', Courier, monospace;
    color: #212529;
    background-color: #ffffff;
    transition: border-color 0.15s ease;
}

.nodes-input:focus {
    outline: none;
    border-color: #1976d2;
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

/* ── Пустые данные ── */
.no-data {
    color: #6c757d;
    font-style: italic;
    padding: 20px;
}

/* ── Тулбар ── */
.toolbar {
    display: flex;
    justify-content: flex-end;
}

.btn-refresh {
    padding: 8px 18px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background-color: #1976d2;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.btn-refresh:hover:not(:disabled) {
    background-color: #1565c0;
}

.btn-refresh:disabled {
    background-color: #90bde8;
    cursor: not-allowed;
}

/* ── Адаптив ── */
@media (max-width: 768px) {
    .total-load-manager {
        padding: 16px;
        gap: 20px;
    }

    .data-table th,
    .data-table td {
        padding: 8px 10px;
        font-size: 13px;
    }

    .section-title {
        font-size: 14px;
    }
}

/* ── Печать ── */
@media print {
    .total-load-manager {
        box-shadow: none;
    }

    .toolbar {
        display: none;
    }

    .data-table th,
    .data-table td {
        border: 1px solid #000 !important;
    }

    .col-computed {
        background-color: transparent !important;
    }
}
</style>
