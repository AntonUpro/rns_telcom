<script setup>
import { ref, reactive, computed, watch } from 'vue';

const props = defineProps({
    editable: {
        type: Boolean,
        default: true
    }
});

// Internal sections state
const sections = ref([]);
const openedSections = reactive({});

// Emit event when sections change (for parent component awareness)
const emit = defineEmits(['sections-changed']);

// Watch for changes and emit event
watch(sections, (newSections) => {
    emit('sections-changed', newSections);
}, { deep: true });

// Element type options
const elementTypeOptions = [
    { value: 'belt', label: 'Пояс' },
    { value: 'brace', label: 'Раскос' },
    { value: 'strut', label: 'Распорка' },
    { value: 'sprengel', label: 'Шпренгель' },
    { value: 'other', label: 'Прочее' }
];

// Cross-section type options
const crossSectionOptions = [
    { value: 'angle', label: 'Уголки' },
    { value: 'pipe', label: 'Трубы' },
    { value: 'channel', label: 'Швеллеры' },
    { value: 'double_angle', label: 'Парные уголки' },
    { value: 'square_pipe', label: 'Квадратные трубы' },
    { value: 'other', label: 'Прочее' }
];

// Generate unique ID
const uid = () => Math.random().toString(36).slice(2) + Date.now().toString(36);

// Create default section
function makeDefaultSection() {
    return {
        id: uid(),
        height: 0,
        bottomWidth: 0,
        topWidth: 0,
        elements: []
    };
}

// Create default element
function makeDefaultElement() {
    return {
        id: uid(),
        type: 'belt',
        crossSection: 'angle',
        profileSize: '',
        profileSearchTerm: ''
    };
}

// Add new section
function addSection() {
    const newSection = makeDefaultSection();
    sections.value.push(newSection);
    openedSections[newSection.id] = true;
}

// Remove section
function removeSection(index) {
    if (confirm('Удалить секцию?')) {
        const removedSection = sections.value.splice(index, 1)[0];
        delete openedSections[removedSection.id];
    }
}

// Add element to specific section
function addElement(sectionIndex) {
    const newElement = makeDefaultElement();
    sections.value[sectionIndex].elements.push(newElement);
}

// Remove element from specific section
function removeElement(sectionIndex, elementIndex) {
    if (confirm('Удалить элемент?')) {
        sections.value[sectionIndex].elements.splice(elementIndex, 1);
    }
}

// Handle profile search
function handleProfileSearch(sectionIndex, elementIndex, searchTerm) {
    console.log('Searching for profile:', searchTerm);
    sections.value[sectionIndex].elements[elementIndex].profileSearchTerm = searchTerm;
}

// Format number input
const formatNumber = (value) => {
    const num = parseFloat(value);
    return isNaN(num) ? '' : num.toFixed(2);
};

// Toggle section visibility
const toggleSection = (sectionId) => {
    if (!(sectionId in openedSections)) {
        openedSections[sectionId] = false;
    }
    openedSections[sectionId] = !openedSections[sectionId];
};
</script>

<template>
    <div class="platform-section-manager">
        <div class="table-header">
            <h3>Секции площадки/надстройки</h3>
            <button
                v-if="editable"
                @click="addSection"
                class="btn-add-section"
            >
                + Добавить секцию
            </button>
        </div>

        <div class="table-wrapper">
            <table class="platform-table">
                <thead>
                    <tr>
                        <th class="section-col">Секция</th>
                        <th class="dimensions-col">Размеры</th>
                        <th class="element-type-col">Тип элемента</th>
                        <th class="cross-section-col">Сечение</th>
                        <th class="profile-col">Размер профиля</th>
                        <th class="actions-col" v-if="editable">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="(section, sectionIndex) in sections" :key="section.id">
                        <!-- Section header row -->
                        <tr class="section-header-row" @click="toggleSection(section.id)">
                            <td class="section-cell" :rowspan="section.elements.length + 2">
                                <div class="section-info">
                                    <div class="section-number">Секция {{ sectionIndex + 1 }}</div>
                                    <div class="section-toggle">
                                        <span class="toggle-icon">{{ openedSections[section.id] ? '−' : '+' }}</span>
                                    </div>
                                    <div v-if="editable" class="section-actions">
                                        <button
                                            @click.stop="removeSection(sectionIndex)"
                                            class="btn-remove-section"
                                            title="Удалить секцию"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="dimensions-cell" :rowspan="section.elements.length + 2">
                                <div class="dimensions-form">
                                    <div class="dimension-field">
                                        <label>Высота, м:</label>
                                        <input
                                            v-if="editable"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model.number="section.height"
                                            class="dimension-input"
                                        >
                                        <div v-else class="dimension-value">{{ formatNumber(section.height) || '—' }}</div>
                                    </div>
                                    <div class="dimension-field">
                                        <label>Ширина низ, м:</label>
                                        <input
                                            v-if="editable"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model.number="section.bottomWidth"
                                            class="dimension-input"
                                        >
                                        <div v-else class="dimension-value">{{ formatNumber(section.bottomWidth) || '—' }}</div>
                                    </div>
                                    <div class="dimension-field">
                                        <label>Ширина верх, м:</label>
                                        <input
                                            v-if="editable"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model.number="section.topWidth"
                                            class="dimension-input"
                                        >
                                        <div v-else class="dimension-value">{{ formatNumber(section.topWidth) || '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td :rowspan="section.elements.length + 2" class="section-description">
                                <div v-if="section.height && section.bottomWidth && section.topWidth">
                                    {{ section.height }}м × {{ section.bottomWidth }}м (низ) × {{ section.topWidth }}м (верх)
                                </div>
                                <div v-else class="empty-section">
                                    Заполните размеры секции
                                </div>
                            </td>
                        </tr>

                        <!-- Elements rows -->
                        <template v-if="openedSections[section.id]">
                            <tr
                                v-for="(element, elementIndex) in section.elements"
                                :key="element.id"
                                class="element-row"
                            >
                                <td>
                                    <select
                                        v-if="editable"
                                        v-model="element.type"
                                        class="element-select"
                                    >
                                        <option
                                            v-for="option in elementTypeOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div v-else class="element-value">
                                        {{ elementTypeOptions.find(opt => opt.value === element.type)?.label || element.type }}
                                    </div>
                                </td>
                                <td>
                                    <select
                                        v-if="editable"
                                        v-model="element.crossSection"
                                        class="element-select"
                                    >
                                        <option
                                            v-for="option in crossSectionOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div v-else class="element-value">
                                        {{ crossSectionOptions.find(opt => opt.value === element.crossSection)?.label || element.crossSection }}
                                    </div>
                                </td>
                                <td>
                                    <input
                                        v-if="editable"
                                        type="text"
                                        v-model="element.profileSearchTerm"
                                        @input="handleProfileSearch(sectionIndex, elementIndex, $event.target.value)"
                                        class="profile-input"
                                        placeholder="Поиск профиля..."
                                    >
                                    <div v-else class="element-value">
                                        {{ element.profileSize || element.profileSearchTerm || '—' }}
                                    </div>
                                </td>
                                <td v-if="editable" class="actions-cell">
                                    <button
                                        @click="removeElement(sectionIndex, elementIndex)"
                                        class="btn-remove-element"
                                        title="Удалить элемент"
                                    >
                                        ×
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Add element row -->
                        <tr v-if="openedSections[section.id]" class="add-element-row">
                            <td colspan="3"></td>
                            <td>
                                <button
                                    v-if="editable"
                                    @click="addElement(sectionIndex)"
                                    class="btn-add-element"
                                >
                                    + Добавить элемент
                                </button>
                            </td>
                            <td v-if="editable"></td>
                        </tr>

                        <!-- Empty state for section -->
                        <tr v-if="openedSections[section.id] && section.elements.length === 0" class="empty-elements-row">
                            <td colspan="3"></td>
                            <td colspan="2">
                                <div class="empty-message">
                                    Нет элементов в секции
                                </div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty state -->
                    <tr v-if="sections.length === 0" class="empty-state-row">
                        <td colspan="6" class="empty-table-cell">
                            <div class="empty-table-message">
                                Нет секций. Нажмите "Добавить секцию" чтобы начать.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.platform-section-manager {
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
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.btn-add-section {
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-add-section:hover:not(:disabled) {
    background-color: #27ae60;
}

.table-wrapper {
    overflow-x: auto;
}

.platform-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.platform-table th {
    background: #e9ecef;
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.platform-table td {
    padding: 0.75rem;
    border-bottom: 1px solid #dee2e6;
    vertical-align: top;
}

/* Column widths */
.section-col {
    width: 15%;
}

.dimensions-col {
    width: 25%;
}

.element-type-col {
    width: 15%;
}

.cross-section-col {
    width: 15%;
}

.profile-col {
    width: 20%;
}

.actions-col {
    width: 10%;
}

/* Section header styling */
.section-header-row {
    background: #f8f9fa;
    cursor: pointer;
    transition: background 0.2s ease;
}

.section-header-row:hover {
    background: #e9ecef;
}

.section-cell {
    border-right: 2px solid #dee2e6;
    background: #ffffff;
}

.section-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.section-number {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

.section-toggle {
    text-align: center;
}

.toggle-icon {
    font-size: 1.5rem;
    font-weight: bold;
    color: #6c757d;
}

.section-actions {
    text-align: center;
}

.btn-remove-section {
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 4px;
    width: 28px;
    height: 28px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-remove-section:hover {
    background: #c0392b;
}

.dimensions-cell {
    border-right: 1px solid #dee2e6;
}

.dimensions-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.dimension-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.dimension-field label {
    font-weight: 500;
    font-size: 0.85rem;
    color: #495057;
}

.dimension-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.dimension-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.dimension-value {
    padding: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    color: #495057;
    font-size: 0.9rem;
}

.section-description {
    background: #e8f4fc;
    font-weight: 500;
    color: #2c3e50;
    text-align: center;
    vertical-align: middle;
}

.empty-section {
    color: #6c757d;
    font-style: italic;
}

/* Element rows */
.element-row {
    background: #ffffff;
}

.element-row:nth-child(even) {
    background: #f8f9fa;
}

.element-row:hover {
    background: #e9ecef;
}

.element-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
    background-color: white;
}

.element-value {
    padding: 0.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    color: #495057;
    font-size: 0.9rem;
}

.profile-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.profile-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.actions-cell {
    text-align: center;
}

.btn-remove-element {
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 4px;
    width: 28px;
    height: 28px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-remove-element:hover {
    background: #c0392b;
}

/* Add element row */
.add-element-row {
    background: #f8f9fa;
}

.add-element-row td {
    text-align: center;
    padding: 1rem;
}

.btn-add-element {
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-add-element:hover:not(:disabled) {
    background-color: #2980b9;
}

/* Empty states */
.empty-elements-row {
    background: #fff3cd;
}

.empty-elements-row td {
    text-align: center;
    padding: 1rem;
}

.empty-message {
    color: #856404;
    font-style: italic;
}

.empty-state-row {
    background: #f8f9fa;
}

.empty-table-cell {
    text-align: center;
    padding: 3rem;
}

.empty-table-message {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Responsive design */
@media (max-width: 1024px) {
    .platform-table {
        font-size: 0.8rem;
    }

    .platform-table th,
    .platform-table td {
        padding: 0.5rem;
    }

    .section-col {
        width: 12%;
    }

    .dimensions-col {
        width: 22%;
    }

    .element-type-col {
        width: 14%;
    }

    .cross-section-col {
        width: 14%;
    }

    .profile-col {
        width: 28%;
    }

    .actions-col {
        width: 10%;
    }
}

@media (max-width: 768px) {
    .table-header {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
    }

    .table-wrapper {
        padding: 0 0.5rem;
    }

    .platform-table {
        font-size: 0.75rem;
    }

    .dimension-field {
        gap: 0.1rem;
    }

    .dimension-field label {
        font-size: 0.7rem;
    }
}

@media (max-width: 480px) {
    .platform-table {
        font-size: 0.7rem;
    }

    .btn-add-element,
    .btn-remove-section,
    .btn-remove-element {
        width: 24px;
        height: 24px;
        font-size: 0.9rem;
    }

    .section-info {
        gap: 0.25rem;
    }

    .section-number {
        font-size: 0.9rem;
    }
}
</style>
