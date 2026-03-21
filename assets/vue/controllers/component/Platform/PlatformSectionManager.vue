<script setup>
import {ref, onMounted} from 'vue';
import SectionItem from './SectionItem.vue';
import SectionItem2 from "./SectionItem2.vue";

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    }
});

const sections = ref([]);
const strut = ref({
    id: null,
    height:  2.5,
    widthBottom:  0.5,
    widthTop: 2,
    elements: [{}, {}]
});

const initData = ref({
    mountHeight: 0,
    typePlatform: '',
    beltCount: 4,
});

// Генерация уникального ID
const generateId = () => Date.now().toString(36) + Math.random().toString(36).substr(2);

// Инициализация пустой секции
const createEmptySection = () => ({
    id: null,
    height: '',
    widthBottom: '',
    widthTop: '',
    elements: [{}, {}]
});

// Инициализация пустого элемента
const createEmptyElement = () => ({
    id: null,
    type: '',
    sectionType: '',
    catalogSearch: ''
});

// Загрузка данных при монтировании
onMounted(async () => {
    // Здесь будет запрос: await fetch(`/api/calculations/${calculationId}/sections`)
    // Пока мокаем
    sections.value = [
        {
            ...createEmptySection(),
            height: 3,
            widthBottom: 2,
            widthTop: 2.0,
            elements: [
                {...createEmptyElement(), type: 'Пояс', sectionType: 'труба круглая'},
                {...createEmptyElement(), type: 'Раскос', sectionType: 'уголок'}
            ]
        }
    ];
});

// Добавление секций
const addSectionAtStart = () => sections.value.unshift(createEmptySection());
const addSectionAtEnd = () => sections.value.push(createEmptySection());

const insertSectionBefore = (index) => sections.value.splice(index, 0, createEmptySection());
const insertSectionAfter = (index) => sections.value.splice(index + 1, 0, createEmptySection());

// Удаление подкосов
const addStrut = () => {
    strut.value = {
        id: null,
        height:  2.5,
        widthBottom:  0.5,
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

// Сохранение (мок)
const saveData = () => {
    console.log('Сохраняем данные:', {calculationId: props.calculationId, sections: sections.value});
    // await post(`/api/calculations/${calculationId}`, { sections })
    alert('Данные сохранены (мок)');
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
                            v-model.number="initData.mountHeightStrut"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="0"
                            max="100"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Отметка установки площадки:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="initData.mountHeightPlatform"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="0"
                            max="100"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Количество поясов:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="initData.facetsCount"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="0"
                            max="100"
                        />
                        <span class="unit">шт.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sections">
            <div style="display: flex; gap: 8px;">
                <button class="btn-add-section" @click="addSectionAtStart">+ Добавить секцию в начало</button>
                <button class="btn-add-section" @click="addStrut">+ Добавить подкосы</button>
            </div>
            <table>
                <thead>
                <tr>
                    <th>№</th>
                    <th>Высота</th>
                    <th>Ширина низа</th>
                    <th>Ширина верха</th>
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

        <div class="actions">
            <button class="btn-save" @click="saveData">Сохранить</button>
        </div>
    </div>
</template>

<style scoped>
.calculator {
    font-family: Arial, sans-serif;
    margin: 0 auto;
    font-size: 14px;
}

.sections {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.btn-add-section {
    align-self: flex-start;
    padding: 6px 12px;
    font-size: 13px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 4px;
    cursor: pointer;
}

.btn-add-section:hover {
    background-color: #e0e0e0;
}

.btn-insert {
    padding: 4px 8px;
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
}

.btn-remove {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffebee;
    border: 1px solid #9acffa;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
}

.btn-insert:hover {
    background-color: #d0e8fb;
}

.actions {
    margin-top: 24px;
    text-align: right;
}

.btn-save {
    padding: 8px 16px;
    background-color: #1976d2;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-save:hover {
    background-color: #1565c0;
}

.default-values-row {
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 6px;
    background-color: #fafafa;
}

.dynamic-inputs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

table {
    border-top: 1px solid #6f7c8a;
    border-bottom: 1px solid #6f7c8a;
}

thead {
    font-size: 16px;
    font-weight: bold;
    border-bottom: 1px solid #6f7c8a;
    margin-bottom: 8px;
}

th {
    border-left: 1px solid #6f7c8a;
}
</style>
