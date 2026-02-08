<script setup>
import {computed, reactive} from 'vue';
import EquipmentSection from './EquipmentSection.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    },
    title: {
        type: String,
        default: 'Оборудование'
    },
    editable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue']);

const group = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
});

const uid = () => Math.random().toString(36).slice(2) + Date.now().toString(36);

function makeDefaultRow(category) {
    return {
        id: null,
        equipmentId: 0,
        fullName: '',
        type: category,
        diameter: 0,
        height: 0,
        width: 0,
        depth: 0,
        quantity: 1,
        weight: 0,
        mountHeight: 0 // отдельное поле для "Отметка подвеса, м"
    };
}

function updateRows(category, newRows) {
    group.value = {
        ...group.value,
        [category]: Array.isArray(newRows) ? newRows : []
    };
}

function addRow(category) {
    const rows = group.value[category] ?? [];
    group.value = {
        ...group.value,
        [category]: [...rows, makeDefaultRow(category)]
    };
}

function removeRow(category, index) {
    if (confirm('Удалить оборудование?')) {
        const rows = group.value[category] ?? [];
        const next = rows.slice();
        next.splice(index, 1);
        group.value = {
            ...group.value,
            [category]: next
        };
    }
}

// Итоги по таблице
const flatAll = computed(() => {
    const g = group.value || {};
    return ['rrl', 'panel', 'radio', 'other']
        .flatMap(k => Array.isArray(g[k]) ? g[k] : []);
});

const totalQuantity = computed(() =>
    flatAll.value.reduce((s, it) => s + (Number(it.quantity) || 0), 0)
);

const totalWeight = computed(() =>
    flatAll.value.reduce((s, it) => s + (Number(it.weight) || 0) * (Number(it.quantity) || 0), 0)
);

const formatWeight = (n) => {
    const v = Number(n);
    if (Number.isFinite(v)) return v.toFixed(1).replace('.', ',');
    return '0,0';
};

const openedSections = reactive({
    equipmentSection: true,
});


const toggleSection = (section) => {
    openedSections[section] = !openedSections[section];
};

</script>

<template>
    <div class="table-wrapper">
        <div
            class="section-header"
            @click="toggleSection('equipmentSection')"
            :class="{ active: openedSections.equipmentSection }"
        >
            <h3>{{ title }}</h3>
            <span class="toggle-icon">+</span>
        </div>

        <table class="equipment-table" :class="{ active: openedSections.equipmentSection }">
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
            <!-- Антенны РРЛ -->
            <EquipmentSection
                label="Антенны РРЛ"
                category="rrl"
                :rows="group.rrl"
                :editable="editable"
                @update:rows="rows => updateRows('rrl', rows)"
                @add-item="() => addRow('rrl')"
                @remove-item="(idx) => removeRow('rrl', idx)"
            />

            <!-- Панельные антенны -->
            <EquipmentSection
                label="Панельные антенны"
                category="panel"
                :rows="group.panel"
                :editable="editable"
                @update:rows="rows => updateRows('panel', rows)"
                @add-item="() => addRow('panel')"
                @remove-item="(idx) => removeRow('panel', idx)"
            />

            <!-- Радиоблоки -->
            <EquipmentSection
                label="Радиоблоки"
                category="radio"
                :rows="group.radio"
                :editable="editable"
                @update:rows="rows => updateRows('radio', rows)"
                @add-item="() => addRow('radio')"
                @remove-item="(idx) => removeRow('radio', idx)"
            />

            <!-- Прочее -->
            <EquipmentSection
                label="Прочее"
                category="other"
                :rows="group.other"
                :editable="editable"
                @update:rows="rows => updateRows('other', rows)"
                @add-item="() => addRow('other')"
                @remove-item="(idx) => removeRow('other', idx)"
            />

            <!-- Итоги -->
            <tr class="summary-row">
                <td class="summary-label">ИТОГО:</td>
                <td class="summary-value">—</td>
                <td class="summary-value">{{ formatWeight(totalWeight) }}</td>
                <td class="summary-value">{{ totalQuantity }}</td>
                <td class="summary-value">—</td>
                <td class="summary-value">—</td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>

.table-wrapper {
    overflow-x: auto;
    padding: 0;
    margin-bottom: 2rem;
}

.equipment-table {
    padding: 0;
    max-height: 0;
    display: none;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.equipment-table.active {
    padding: 1rem;
    max-height: 2000px;
    display: inline-table;
}

.table-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.equipment-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    margin: 0;
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

/* Колонки */
.column-designation {
    width: 40%;
    text-align: left !important;
}

.column-quantity {
    width: 8%;
}

.column-dimensions {
    width: 20%;
}

.column-weight {
    width: 10%;
}

.column-height {
    width: 12%;
}

.column-actions {
    width: 10%;
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
