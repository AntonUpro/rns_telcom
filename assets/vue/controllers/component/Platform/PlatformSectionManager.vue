<script setup>
import {ref, onMounted} from 'vue';
import SectionItem2 from "./SectionItem.vue";

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    }
});

const sections = ref([]);
const showErrors = ref(false);
const elementTypes = ref([]);
const sectionTypes = ref([]);
const strut = ref({
    id: null,
    height: 2.5,
    widthBottom: 0.5,
    widthTop: 2,
    elements: [{}, {}]
});

const totalData = ref({
    mountHeightStrut: 21500,
    mountHeightPlatform: 23000,
    facetsCount: 4,
});

// Инициализация пустой секции
const createEmptySection = () => ({
    id: null,
    height: '',
    widthBottom: '',
    widthTop: '',
    elements: [
        {
            type: 'belt',
            sectionType: 'round_pipe',
            widthElement: 0,
            lengthElement: 0,
            countElement: 2
        },
        {
            type: 'belt',
            sectionType: 'round_pipe',
            widthElement: 0,
            lengthElement: 0,
            countElement: 2
        }
    ]
});

// Инициализация пустого элемента
const createEmptyElement = () => ({
    type: 'belt',
    sectionType: 'round_pipe',
    widthElement: 0,
    lengthElement: 0,
    countElement: 2
});

// Загрузка данных при монтировании
onMounted(async () => {
    fetchPlatformData(props.calculationId)
});

// Добавление секций
const addSectionAtStart = () => sections.value.unshift(createEmptySection());

const insertSectionAfter = (index) => sections.value.splice(index + 1, 0, createEmptySection());

// Удаление подкосов
const addStrut = () => {
    strut.value = {
        id: null,
        height: 2.5,
        widthBottom: 0.5,
        widthTop: 2,
        elements: [{}, {}]
    };
};

// Удаление секции
const removeSection = (index) => {
    if (sections.value.length > 1) {
        sections.value.splice(index, 1);
    }
};

// Удаление подкосов
const removeStrut = () => {
    strut.value = {};
};

// Работа с элементами
const addElementToSection = (sectionIndex) => {
    sections.value[sectionIndex].elements.push(createEmptyElement());
};

const removeElementFromSection = (sectionIndex, elementIndex) => {
    sections.value[sectionIndex].elements.splice(elementIndex, 1);
};

const addElementToStrut = () => {
    strut.value.elements.push(createEmptyElement());
};

const removeElementFromStrut = (elementIndex) => {
    strut.value.elements.splice(elementIndex, 1);
};

function validateElement(el) {
    if (!el.type) return false;
    if (!el.sectionType) return false;
    if (!(Number(el.widthElement) > 0)) return false;
    if (!(Number(el.lengthElement) > 0)) return false;
    if (!(Number(el.countElement) > 0)) return false;
    return true;
}

function validateSection(section) {
    if (!(Number(section.height) > 0)) return false;
    if (!(Number(section.widthBottom) > 0)) return false;
    if (!(Number(section.widthTop) > 0)) return false;
    for (const el of section.elements || []) {
        if (!validateElement(el)) return false;
    }
    return true;
}

function validateAllData() {
    if (!(Number(totalData.value.mountHeightStrut) > 0)) return false;
    if (!(Number(totalData.value.mountHeightPlatform) > 0)) return false;
    if (!(Number(totalData.value.facetsCount) > 0)) return false;

    if (strut.value.height !== undefined) {
        if (!validateSection(strut.value)) return false;
    }

    for (const section of sections.value) {
        if (!validateSection(section)) return false;
    }

    return true;
}

const savePlatformData = async () => {
    showErrors.value = true;

    if (!validateAllData()) {
        alert('Пожалуйста, заполните все обязательные поля. Значения не могут быть пустыми или равными 0.');
        return;
    }

    try {
        const response = await fetch('/api/v1/calculation/platform/save', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                calculationId: props.calculationId,
                totalData: totalData.value,
                sections: sections.value,
                strut: strut.value
            })
        });

        const responseData = await response.json();

        if (!response.ok || !responseData.success) {
            throw new Error('Ошибка сохранения данных. Ошибка: ' + responseData.error ? responseData.error : 'Неизвестная ошибка');
        }

        showErrors.value = false;
    } catch (error) {
        console.error('Error get equipment:', error);
        alert('Ошибка получения данных по оборудованию');
    }
};

const fetchPlatformData = async () => {
    try {
        const url = new URL('/api/v1/calculation/platform/' + props.calculationId, window.location.origin);
        // Here you would make the actual API call
        const response = await fetch(url.toString());

        const responseData = await response.json();

        if (!response.ok || !responseData.success) {
            throw new Error('Ошибка получения данных по оборудованию. Ошибка: ' + responseData.error ? responseData.error : 'Неизвестная ошибка');
        }

        sections.value = responseData.data.sections;
        strut.value = responseData.data.strut;
        totalData.value = responseData.data.totalData;
        elementTypes.value = responseData.data.elementTypes ?? [];
        sectionTypes.value = responseData.data.sectionTypes ?? [];
    } catch (error) {
        console.error('Error get plaform data:', error);
        alert('Ошибка получения данных по площадке');
    }
};

</script>

<!-- TowerWindLoadCalculator.vue -->
<template>
    <div class="calculator">
        <div class="default-values-row">
            <div class="dynamic-inputs-grid">
                <div class="form-group compact-group">
                    <label>Отметка установки подкосов:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="totalData.mountHeightStrut"
                            :class="['form-calculation-control', 'compact-input', {'input-error': showErrors && !(Number(totalData.mountHeightStrut) > 0)}]"
                            step="1"
                            min="0"
                            max="100000"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Отметка установки площадки:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="totalData.mountHeightPlatform"
                            :class="['form-calculation-control', 'compact-input', {'input-error': showErrors && !(Number(totalData.mountHeightPlatform) > 0)}]"
                            step="1"
                            min="0"
                            max="100000"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Количество поясов:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="totalData.facetsCount"
                            :class="['form-calculation-control', 'compact-input', {'input-error': showErrors && !(Number(totalData.facetsCount) > 0)}]"
                            step="1"
                            min="0"
                            max="10"
                        />
                        <span class="unit">шт.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sections">
            <div class="sections-toolbar">
                <button class="btn-add-section" @click="addSectionAtStart">+ Добавить секцию в начало</button>
                <button class="btn-add-section" @click="addStrut">+ Добавить подкосы</button>
            </div>
            <table>
                <thead>
                <tr>
                    <th>№</th>
                    <th>Высота, мм</th>
                    <th>Ширина низа, мм</th>
                    <th>Ширина верха, мм</th>
                    <th>Элементы</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <SectionItem2
                    v-if="Object.keys(strut).length !== 0"
                    :key="0"
                    :section="strut"
                    :index="0"
                    :is-strut="true"
                    :show-errors="showErrors"
                    :element-types="elementTypes"
                    :section-types="sectionTypes"
                    @remove-section="removeStrut(index)"
                    @add-element="addElementToStrut()"
                    @remove-element="removeElementFromStrut($event)"
                >
                    <template #insert-after>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button class="btn-remove" @click="removeStrut(index)">✕</button>
                            <button class="btn-insert" @click="addSectionAtStart()">+</button>
                        </div>
                    </template>
                </SectionItem2>
                <SectionItem2
                    v-for="(section, index) in sections"
                    :key="section.id"
                    :section="section"
                    :index="index"
                    :show-errors="showErrors"
                    :element-types="elementTypes"
                    :section-types="sectionTypes"
                    @remove-section="removeSection(index)"
                    @add-element="addElementToSection(index)"
                    @remove-element="removeElementFromSection(index, $event)"
                >
                    <template #insert-after>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button class="btn-remove" @click="removeSection(index)">✕</button>
                            <button class="btn-insert" @click="insertSectionAfter(index)">+</button>
                        </div>
                    </template>
                </SectionItem2>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="footer-actions">
                <button class="btn-save" @click="savePlatformData">Сохранить</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.calculator {
    font-family: Arial, sans-serif;
    margin: 0 auto;
    font-size: 14px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.default-values-row {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #fafafa;
}

.dynamic-inputs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.sections {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 1rem 1.5rem;
}

.sections-toolbar {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}

.btn-add-section {
    padding: 6px 12px;
    font-size: 13px;
    background-color: #e8f4fd;
    border: 1px solid #9acffa;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-add-section:hover {
    background-color: #d0e8fb;
}

.btn-insert {
    font-size: 12px;
    background-color: #e8f4fd;
    border: 1px solid #9acffa;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-insert:hover {
    background-color: #d0e8fb;
}

.btn-remove {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffebee;
    border: 1px solid #ffcdd2;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-remove:hover {
    background-color: #ffcdd2;
}

table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
}

thead tr {
    background: #e9ecef;
}

th {
    padding: 0.6rem 0.75rem;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    border: 1px solid #dee2e6;
    white-space: nowrap;
}

/* Футер — как в EquipmentManager */
.table-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.footer-actions {
    display: flex;
    justify-content: flex-start;
}

.btn-save {
    background-color: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1.25rem;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-save:hover {
    background-color: #27ae60;
}

.input-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}
</style>
