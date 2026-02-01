<script setup>
import {ref, reactive, computed, onMounted} from 'vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    },
    initialEquipmentData: {
        type: Array,
        default: () => []
    },
    title: {
        type: String,
        default: 'Оборудование на столбе'
    },
    editable: {
        type: Boolean,
        default: true
    },
    initialData: {
        type: Array,
        default: () => []
    }
});

// const emit = defineEmits(['equipment-updated', 'save-complete']);

// Состояние
const equipmentList = ref(props.initialEquipmentData);

const emit = defineEmits(['save', 'update', 'export']);

// Данные таблицы
const equipmentData = ref([]);
const isSaving = ref(false);

const exampleData = [
    // Антенны РРЛ
    {
        designation: 'Антенна РРЛ 0,6',
        quantity: 1,
        type: 'circular',
        diameter: 600,
        weight: 17.0,
        height: 27.500,
        coefficient: 1.0,
        isExisting: true
    },
    {
        designation: 'Антенна РРЛ 0,6',
        quantity: 1,
        type: 'circular',
        diameter: 600,
        weight: 17.0,
        height: 28.000,
        coefficient: 1.0,
        isExisting: true
    },
    // Панельные антенны
    {
        designation: 'Kathrein 80010426',
        quantity: 3,
        type: 'rectangular',
        width: 1302,
        height: 155,
        depth: 69,
        weight: 6.6,
        coefficient: 0.8,
        isExisting: true
    },
    {
        designation: 'TONGYU TJB-609016/172718/172718DE-65Fv01',
        quantity: 2,
        type: 'rectangular',
        width: 2080,
        height: 355,
        depth: 165,
        weight: 36.4,
        coefficient: 0.8,
        isExisting: true
    },
    {
        designation: 'HUAWEI ATR4517R1',
        quantity: 1,
        type: 'rectangular',
        width: 1499,
        height: 349,
        depth: 166,
        weight: 25.0,
        coefficient: 0.8,
        isExisting: true
    },
    // Радиоблоки
    {
        designation: 'Радиоблок RRUS12B3',
        quantity: 3,
        type: 'rectangular',
        width: 518,
        height: 470,
        depth: 187,
        weight: 26.3,
        coefficient: 0.7,
        isExisting: true
    },
    {
        designation: 'Радиоблок RRUS01B1',
        quantity: 3,
        type: 'rectangular',
        width: 636,
        height: 383,
        depth: 169,
        weight: 20.0,
        coefficient: 0.7,
        isExisting: true
    },
    {
        designation: 'Радиоблок Radio2219B3',
        quantity: 3,
        type: 'rectangular',
        width: 466,
        height: 343,
        depth: 162,
        weight: 21.0,
        coefficient: 0.7,
        isExisting: true
    },
    {
        designation: 'Радиоблок Radio2219B8',
        quantity: 3,
        type: 'rectangular',
        width: 466,
        height: 343,
        depth: 162,
        weight: 21.0,
        coefficient: 0.7,
        isExisting: true
    }
];


// Инициализация данных
const initializeData = () => {
    if (props.initialData && props.initialData.length > 0) {
        equipmentData.value = [...props.initialData];
    } else {
        equipmentData.value = [...exampleData];
    }
};

// Вычисляемые свойства
const totalQuantity = computed(() =>
    equipmentData.value.reduce((sum, item) => sum + item.quantity, 0)
);

const totalWeight = computed(() =>
    equipmentData.value.reduce((sum, item) => sum + (item.weight * item.quantity), 0)
);

// Форматирование чисел
const formatWeight = (weight) => {
    return weight.toFixed(1).replace('.', ',');
};

const formatHeight = (height) => {
    return height.toFixed(3).replace('.', ',');
};

// Методы управления таблицей
const addRow = () => {
    equipmentData.value.push({
        designation: '',
        quantity: 1,
        type: 'rectangular',
        width: 0,
        height: 0,
        depth: 0,
        weight: 0,
        coefficient: 0.8,
        isExisting: false
    });
};

const removeRow = (index) => {
    if (confirm('Удалить эту строку?')) {
        equipmentData.value.splice(index, 1);
    }
};

const toggleExisting = (item) => {
    item.isExisting = !item.isExisting;
};

const saveTable = async () => {
    try {
        isSaving.value = true;

        // Подготовка данных для сохранения
        const dataToSave = equipmentData.value.map(item => ({
            designation: item.designation,
            quantity: item.quantity,
            dimensions: item.type === 'circular'
                ? { diameter: item.diameter }
                : { width: item.width, height: item.height, depth: item.depth },
            weight: item.weight,
            height: item.height,
            coefficient: item.coefficient,
            isExisting: item.isExisting,
            type: item.type
        }));

        // Эмитим событие сохранения
        emit('save', dataToSave);

        // В реальном приложении здесь будет запрос к API
        // await api.saveEquipmentTable(dataToSave);

        alert('Таблица сохранена успешно');
    } catch (error) {
        console.error('Ошибка сохранения таблицы:', error);
        alert('Не удалось сохранить таблицу');
    } finally {
        isSaving.value = false;
    }
};

const resetTable = () => {
    if (confirm('Сбросить все изменения?')) {
        initializeData();
    }
};

const addExampleData = () => {
    equipmentData.value = [...equipmentData.value, ...exampleData];
};

// // Следим за изменениями данных
// watch(equipmentData, (newValue) => {
//     emit('update', newValue);
// }, { deep: true });

// Инициализация при монтировании
initializeData();


onMounted(() => {
    // Загрузка данных оборудо538я при монтировании компонента
    // apiLoadEquipment(props.calculationId)
    //     .then(data => {
    //         equipmentList.value = data;
    //         emit('equipment-updated', data);
    //     })
    //     .catch(error => {
    //         console.error('Ошибка загрузки данных оборудо538я:', error);
    //     });
});

</script>

<template>
    <div class="equipment-table-container">
        <!-- Заголовок таблицы -->
        <div class="table-header">
            <h4>{{ title }}</h4>
<!--            <div class="table-actions" v-if="editable">-->
<!--                <button @click="addRow" class="btn-add-row" title="Добавить строку">-->
<!--                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">-->
<!--                        <path d="M12 5v14M5 12h14"/>-->
<!--                    </svg>-->
<!--                </button>-->
<!--            </div>-->
        </div>

        <!-- Таблица -->
        <div class="table-wrapper">
            <table class="equipment-table">
                <thead>
                <tr>
                    <th class="column-designation">Обозначение</th>
                    <th class="column-dimensions">Габариты, мм</th>
                    <th class="column-weight">Масса, кг</th>
                    <th class="column-quantity">Кол-во</th>
                    <th class="column-height">Отметка подвеса, м</th>
                    <th class="column-actions">Действия</th>
                </tr>
                </thead>

                <tbody>
                <!-- Раздел: Существующее оборудование -->
                <tr class="section-row">
                    <td colspan="5" class="section-title">
                        <div>Антенны РРЛ</div>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button @click="addRow" class="btn-add-row" title="Добавить строку">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>

                <!-- Строки с оборудованием -->
                <tr
                    v-for="(item, index) in equipmentData"
                    :key="index"
                    class="equipment-row"
                    :class="{ 'editable-row': true, 'existing-equipment': item.isExisting }"
                >
                    <!-- Наименование -->
                    <td class="cell-designation">
                        <div class="editable-cell">
                            <input
                                type="text"
                                v-model="item.designation"
                                class="table-input"
                                placeholder="Введите обозначение"
                            />
                        </div>
                    </td>

                    <!-- Габариты -->
                    <td class="cell-dimensions">
                        <div v-if="true">
                            <template v-if="item.diameter">
                                Ø{{ item.diameter }}
                            </template>
                            <template v-else>
                                {{ item.width }}×{{ item.height }}×{{ item.depth }}
                            </template>
                        </div>
                    </td>

                    <!-- Масса -->
                    <td class="cell-weight">
                        <div>{{ formatWeight(123.45) }}</div>
                    </td>

                    <!-- Количество -->
                    <td class="cell-quantity">
                        <div class="editable-cell">
                            <input
                                type="number"
                                v-model.number="item.quantity"
                                class="table-input number-input"
                                min="1"
                                step="1"
                            />
                        </div>
                    </td>

                    <!-- Отметка подвеса -->
                    <td class="cell-height">
                        <div class="editable-cell">
                            <input
                                type="number"
                                v-model.number="item.height"
                                class="table-input number-input"
                                min="0"
                                step="0.001"
                            />
                        </div>
                    </td>

                    <!-- Действия (только в режиме редактирования) -->
                    <td class="cell-actions">
                        <div class="action-buttons">
                            <button
                                @click="removeRow(index)"
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
                <!-- Раздел: Панельные антенны -->
                <tr class="section-row">
                    <td colspan="6" class="section-title">
                        Панельные антенны
                    </td>
                </tr>

                <!-- Итоговая строка -->
                <tr class="summary-row">
                    <td class="summary-label">ИТОГО:</td>
                    <td class="summary-value">—</td>
                    <td class="summary-value">{{ formatWeight(13245,45) }}</td>
                    <td class="summary-value">{{ totalQuantity }}</td>
                    <td class="summary-value">—</td>
                    <td class="summary-value">—</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Нижние кнопки управления -->
        <div class="table-footer" v-if="editable">
            <div class="footer-actions">
                <button @click="saveTable" class="btn-save-table" :disabled="isSaving">
                    {{ isSaving ? 'Сохранение...' : 'Сохранить таблицу' }}
                </button>
            </div>
        </div>
    </div>
</template>

<!--<template>-->
<!--    <div class="data-section compact-section">-->
<!--        <div-->
<!--            class="section-header"-->
<!--            @click="toggleSection('equipmentList')"-->
<!--            :class="{ active: openedSections.equipmentList }"-->
<!--        >-->
<!--            <h3>Существующее оборудование</h3>-->
<!--            <span class="toggle-icon">+</span>-->
<!--        </div>-->

<!--        <div class="section-content" :class="{ active: openedSections.equipmentList }">-->


<!--        </div>-->
<!--    </div>-->
<!--    <div class="section-actions" style="margin-top: 2rem">-->
<!--        <button-->
<!--            @click="saveEquipmentData"-->
<!--            class="btn-save-small"-->
<!--            :disabled="isSavingEquipment"-->
<!--        >-->
<!--            {{ isSavingEquipment ? 'Сохранение...' : 'Сохранить список оборудования' }}-->
<!--        </button>-->
<!--    </div>-->
<!--</template>-->

<style scoped>
.equipment-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
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

.table-wrapper {
    overflow-x: auto;
    padding: 0 1.5rem;
}

.equipment-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin: 1rem 0;
}

.equipment-table th {
    background: #e9ecef;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.equipment-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
    text-align: center;
    vertical-align: middle;
}

/* Секции и подразделы */
.section-row {
    background: #f1f8ff;
}

.section-title {
    font-weight: 600;
    color: #2c3e50;
    text-align: left !important;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.subsection-row {
    background: #f8f9fa;
}

.subsection-title {
    font-weight: 500;
    color: #495057;
    text-align: left !important;
    padding: 0.5rem 1.5rem !important;
    font-size: 0.9rem;
    font-style: italic;
}

/* Стили для строк с оборудованием */
.equipment-row:hover {
    background: #f8f9fa;
}

.existing-equipment {
    background: #f9f9f9;
}

.existing-equipment:hover {
    background: #f0f0f0;
}

/* Колонки */
.column-designation {
    width: 35%;
    text-align: left !important;
}

.column-quantity {
    width: 8%;
}

.column-dimensions {
    width: 15%;
}

.column-weight {
    width: 10%;
}

.column-height {
    width: 15%;
}

/* Ячейки */
.cell-designation {
    text-align: left !important;
}

.editable-cell {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.table-input {
    width: 100%;
    padding: 0.375rem 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.85rem;
    transition: border-color 0.3s ease;
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

.type-select {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: white;
    width: 100%;
    max-width: 150px;
    margin-top: 0.25rem;
}

/* Кнопки действий в строке */
.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
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

.btn-existing {
    background: #2ecc71;
}

.btn-existing:hover {
    background: #27ae60;
}

.btn-remove {
    background: #e74c3c;
}

.btn-remove:hover {
    background: #c0392b;
}

/* Итоговая строка */
.summary-row {
    background: #e8f4fc;
    font-weight: 600;
}

.summary-label {
    text-align: left !important;
    font-weight: 600;
    color: #2c3e50;
}

.summary-value {
    font-weight: 600;
    color: #2c3e50;
}

/* Футер таблицы */
.table-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.footer-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-save-table {
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-save-table:hover:not(:disabled) {
    background-color: #27ae60;
}

.btn-save-table:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


/* Адаптивность */
@media (max-width: 1024px) {
    .table-wrapper {
        padding: 0 0.5rem;
    }

    .equipment-table {
        font-size: 0.85rem;
    }

    .equipment-table th,
    .equipment-table td {
        padding: 0.5rem;
    }
}

@media (max-width: 768px) {
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .table-actions {
        align-self: flex-end;
    }

    .footer-actions {
        flex-direction: column;
    }

    .small-input {
        width: 50px;
    }

    .dimension-inputs {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .table-wrapper {
        padding: 0;
    }

    .equipment-table {
        font-size: 0.8rem;
    }
}

</style>
