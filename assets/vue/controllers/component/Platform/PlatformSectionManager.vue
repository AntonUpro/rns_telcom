<script setup>
import {ref, onMounted} from 'vue';
import SectionItem from './SectionItem.vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    }
});

const sections = ref([]);

const initData = ref({
    mountHeight: 0,
    typePlatform: '',
    beltCount: 4,
});

// Генерация уникального ID
const generateId = () => Date.now().toString(36) + Math.random().toString(36).substr(2);

// Инициализация пустой секции
const createEmptySection = () => ({
    id: generateId(),
    height: '',
    widthBottom: '',
    widthTop: '',
    elements: []
});

// Инициализация пустого элемента
const createEmptyElement = () => ({
    id: generateId(),
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
            height: 10,
            widthBottom: 2.5,
            widthTop: 2.0,
            elements: [
                {...createEmptyElement(), type: 'пояс', sectionType: 'труба круглая'},
                {...createEmptyElement(), type: 'раскос', sectionType: 'уголок'}
            ]
        }
    ];
});

// Добавление секций
const addSectionAtStart = () => sections.value.unshift(createEmptySection());
const addSectionAtEnd = () => sections.value.push(createEmptySection());

const insertSectionBefore = (index) => sections.value.splice(index, 0, createEmptySection());
const insertSectionAfter = (index) => sections.value.splice(index + 1, 0, createEmptySection());

// Удаление секции
const removeSection = (index) => {
    if (sections.value.length > 1) {
        sections.value.splice(index, 1);
    }
};

// Работа с элементами
const addElementToSection = (sectionIndex) => {
    sections.value[sectionIndex].elements.push(createEmptyElement());
};

const removeElementFromSection = (sectionIndex, elementIndex) => {
    sections.value[sectionIndex].elements.splice(elementIndex, 1);
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
                    <label>Отметка установки:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="initData.mountHeight"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="0"
                            max="100"
                        />
                        <span class="unit">мм</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Тип площадки:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="initData.typePlatform"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="0"
                            max="100"
                        />
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Количество поясов:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="initData.beltCount"
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
                <button class="btn-add-section" @click="addSectionAtStart">+ Добавить подкосы</button>
            </div>

            <SectionItem
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
            </SectionItem>
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
</style>
