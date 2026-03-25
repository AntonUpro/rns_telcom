<!-- ElementItem.vue -->
<template>
    <div class="element-item">
        <select v-model="element.type" :class="{'select-error': showErrors && !element.type}">
            <option v-for="option in ELEMENT_TYPES" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <select v-model="element.sectionType" :class="{'select-error': showErrors && !element.sectionType}">
            <option v-for="option in SECTION_TYPES" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <input
            v-model.number="element.widthElement"
            placeholder="Ширина сечения"
            :class="{'input-error': showErrors && !(Number(element.widthElement) > 0)}"
        />
        <input
            v-model.number="element.lengthElement"
            type="number"
            step="0.01"
            placeholder="Длинна"
            :class="{'input-error': showErrors && !(Number(element.lengthElement) > 0)}"
        />
        <input
            v-model.number="element.countElement"
            type="number"
            step="1"
            placeholder="Количество"
            :class="{'input-error': showErrors && !(Number(element.countElement) > 0)}"
        />
        <button class="btn-remove" @click="$emit('remove')">✕</button>
    </div>
</template>

<script setup>

const ELEMENT_TYPES = [
    {value: '', label: '— Тип элемента —'},
    {value: 'Пояс', label: 'Пояс'},
    {value: 'Раскос', label: 'Раскос'},
    {value: 'Распорка', label: 'Распорка'},
    {value: 'Шпренгель', label: 'Шпренгель'},
    {value: 'Трубостойка', label: 'Трубостойка'},
    {value: 'Ограждение', label: 'Ограждение'},
    {value: 'Прочее', label: 'Прочее'}
];

const SECTION_TYPES = [
    {value: '', label: '— Тип сечения —'},
    {value: 'Труба круглая', label: 'Труба круглая'},
    {value: 'Труба квадратная', label: 'Труба квадратная'},
    {value: 'Уголок', label: 'Уголок'},
    {value: 'Швеллер', label: 'Швеллер'},
    {value: 'Парный уголок', label: 'Парный уголок'},
    {value: 'Прочее', label: 'Прочее'}
];

const props = defineProps({
    element: {
        type: Object,
        required: true
    },
    showErrors: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['remove']);
</script>

<style scoped>
.element-item {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 6px;
    background-color: #fff;
    border: 1px solid #eee;
    border-radius: 4px;
    font-size: 13px;
}

.element-item select,
.element-item input {
    padding: 4px;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 13px;
}

.element-item select {
    min-width: 110px;
}

.element-item input {
    flex: 1;
    min-width: 150px;
}

.btn-remove {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffebee;
    border: none;
    border-radius: 50%;
    font-size: 12px;
    cursor: pointer;
}

.btn-remove:hover {
    background-color: #ffcdd2;
}

.input-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}

.select-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}
</style>
