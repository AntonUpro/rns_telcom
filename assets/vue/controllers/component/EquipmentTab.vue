<script setup>
import {ref, reactive, computed, onMounted} from 'vue';
import EquipmentManager from "./Equipment/EquipmentManager.vue";

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


const exampleData = {
    existEquipment: {
        rrl: [
            {
                fullName: 'Антенна РРЛ 0,6',
                quantity: 1,
                type: 'rrl',
                diameter: 600,
                weight: 17.0,
                height: 27.500,
                coefficient: 1.0,
                isExisting: true
            },
        ],
        panel: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'panel',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        radio: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'radioBloc',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        other: [
            {
                fullName: '80010426',
                quantity: 3,
                type: 'other',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ]
    },
    plainEquipment: {
        rrl: [
            {
                fullName: 'Антенна РРЛ 0,6',
                quantity: 1,
                type: 'rrl',
                diameter: 600,
                weight: 17.0,
                height: 27.500,
                coefficient: 1.0,
                isExisting: true
            },
        ],
        panel: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'panel',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        radio: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'radioBloc',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        other: [
            {
                fullName: '80010426',
                quantity: 3,
                type: 'other',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ]
    },
    dismantledEquipment: {
        rrl: [
            {
                fullName: 'Антенна РРЛ 0,6',
                quantity: 1,
                type: 'rrl',
                diameter: 600,
                weight: 17.0,
                height: 27.500,
                coefficient: 1.0,
                isExisting: true
            },
        ],
        panel: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'panel',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        radio: [
            {
                fullName: 'Kathrein 80010426',
                quantity: 3,
                type: 'radioBloc',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ],
        other: [
            {
                fullName: '80010426',
                quantity: 3,
                type: 'other',
                width: 1302,
                height: 155,
                depth: 69,
                weight: 6.6,
                coefficient: 0.8,
                isExisting: true
            },
        ]
    },
}

// Инициализация данных
const initializeData = () => {
    if (props.initialData && props.initialData.length > 0) {
        equipmentData.value = [...props.initialData];
    } else {
        equipmentData.value = [...exampleData];
    }
};

// Методы управления таблицей
const addRow = () => {
    equipmentData.value.push({
        fullName: '',
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
            fullName: item.fullName,
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
// initializeData();


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

function handleSave(payload) {
    // payload = { existEquipment, plainEquipment, dismantledEquipment }
    // здесь API-запрос
    // await api.save(payload)
    console.log('SAVE', payload);
}

</script>

<template>
    <EquipmentManager
        :initialData="equipmentData"
        :editable="true"
        @save="handleSave"
    />
</template>

<style scoped>

.table-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
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

</style>
