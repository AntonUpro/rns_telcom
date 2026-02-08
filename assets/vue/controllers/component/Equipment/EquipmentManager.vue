<script setup>
import {reactive, toRaw, ref, onMounted} from 'vue';
import EquipmentTable from './EquipmentTable.vue';
import AddEquipmentPopup from './AddEquipmentPopup.vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    },
    editable: {
        type: Boolean,
        default: true
    },
    initialData: {
        type: Object,
        default: () => ({})
    }
});
const emit = defineEmits(['update', 'save']);

const clone = (v) => JSON.parse(JSON.stringify(v ?? {}));
const ensureList = (list) => Array.isArray(list) ? clone(list) : [];

function ensureGroup(group) {
    return {
        rrl: ensureList(group?.rrl),
        panel: ensureList(group?.panel),
        radio: ensureList(group?.radio),
        other: ensureList(group?.other),
    };
}

const allEquipment = reactive({
    existEquipment: ensureGroup(props.initialData?.existEquipment),
    plainEquipment: ensureGroup(props.initialData?.plainEquipment),
    dismantledEquipment: ensureGroup(props.initialData?.dismantledEquipment),
});

const showAddEquipmentPopup = ref(false);

const openAddEquipment = () => {
    showAddEquipmentPopup.value = true;
};

const closeAddEquipment = () => {
    showAddEquipmentPopup.value = false;
};

const handleEquipmentAdded = (equipmentData) => {
    // Here you would typically make an API call to save the equipment
    console.log('Equipment added:', equipmentData);

    // For now, we'll just close the popup
    // In a real implementation, you might want to refresh the equipment list
    // or add the equipment to the current table
    closeAddEquipment();
};

const saveAll  = async () => {
    try {
        // Here you would make the actual API call
        const response = await fetch('/api/v1/save/calculation/equipment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                calculationId: props.calculationId,
                equipment: allEquipment
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error('Ошибка сохранения данных. Ошибка: ' + data.error ? data.error : 'Неизвестная ошибка');
        }

        alert('Оборудование сохранено');
    } catch (error) {
        console.error('Error saving equipment:', error);
        alert('Ошибка при сохранении оборудования');
    } finally {
        isSaving.value = false;
    }
};

const fetchEquipmentData = async () => {
    try {
        const url = new URL('/api/v1/calculation/equipment', window.location.origin);
        url.searchParams.set('calculationId', props.calculationId);
        // Here you would make the actual API call
        const response = await fetch(url.toString());

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error('Ошибка получения данных по оборудованию. Ошибка: ' + data.error ? data.error : 'Неизвестная ошибка');
        }

    } catch (error) {
        console.error('Error saving equipment:', error);
        alert('Ошибка получения данных по оборудованию');
    } finally {
        isSaving.value = false;
    }
};

onMounted(() => {
    fetchEquipmentData(props.calculationId);
});
</script>

<template>
    <div class="equipment-table-container">
        <EquipmentTable
            v-model="allEquipment.existEquipment"
            :editable="editable"
            title="Существующее оборудование"
        />
        <EquipmentTable
            v-model="allEquipment.plainEquipment"
            :editable="editable"
            title="Проектируемое оборудование"
        />
        <EquipmentTable
            v-model="allEquipment.dismantledEquipment"
            :editable="editable"
            title="Демонтируемое оборудование"
        />

        <div class="table-footer">
            <div class="footer-actions">
                <button class="btn-save-table" @click="saveAll">Сохранить все</button>
                <button class="btn-add-equipment" @click="openAddEquipment">Добавить оборудование</button>
            </div>
        </div>

        <AddEquipmentPopup
            :is-open="showAddEquipmentPopup"
            @close="closeAddEquipment"
            @save="handleEquipmentAdded"
        />
    </div>
</template>

<style scoped>

.equipment-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 2rem;
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
    justify-content: space-between;
    align-items: center;
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

.btn-add-equipment {
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-add-equipment:hover:not(:disabled) {
    background-color: #2980b9;
}

.btn-add-equipment:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .footer-actions {
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn-save-table,
    .btn-add-equipment {
        width: 100%;
    }
}

</style>
