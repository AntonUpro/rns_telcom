<!-- ElementItem.vue -->
<template>
    <div class="element-item">
        <select v-model="element.type" class="col-type" :class="{'select-error': showErrors && !element.type}">
            <option value="">— Тип элемента —</option>
            <option v-for="option in elementTypes" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <select v-model="element.sectionType" class="col-section-type" :class="{'select-error': showErrors && !element.sectionType}">
            <option value="">— Тип сечения —</option>
            <option v-for="option in sectionTypes" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>

        <input
            v-model.number="element.widthElement"
            type="number"
            step="0.01"
            class="col-width-el"
            :class="{'input-error': showErrors && !(Number(element.widthElement) > 0)}"
        />
        <input
            v-model.number="element.lengthElement"
            type="number"
            step="0.01"
            class="col-length-el"
            :class="{'input-error': showErrors && !(Number(element.lengthElement) > 0)}"
        />
        <input
            v-model.number="element.countElement"
            type="number"
            step="1"
            class="col-count-el"
            :class="{'input-error': showErrors && !(Number(element.countElement) > 0)}"
        />
        <button class="btn-remove" @click="$emit('remove')">✕</button>
    </div>
</template>

<script setup>

const props = defineProps({
    element: {
        type: Object,
        required: true
    },
    showErrors: {
        type: Boolean,
        default: false
    },
    elementTypes: {
        type: Array,
        default: () => []
    },
    sectionTypes: {
        type: Array,
        default: () => []
    },
});

const emit = defineEmits(['remove']);
</script>

<style scoped>
.element-item {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 3px 4px;
    margin-bottom: 4px;
    background-color: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    font-size: 13px;
    width: 100%;
    box-sizing: border-box;
}

.element-item select,
.element-item input {
    padding: 4px 6px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    font-size: 13px;
    box-sizing: border-box;
    text-align: center;
    min-width: 0;
}

.col-type         { flex: 6 1 0; }
.col-section-type { flex: 7 1 0; }
.col-width-el     { flex: 4 1 0; }
.col-length-el    { flex: 4 1 0; }
.col-count-el     { flex: 3 1 0; }

.btn-remove {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffebee;
    border: 1px solid #ffcdd2;
    border-radius: 50%;
    font-size: 11px;
    cursor: pointer;
    flex: none;
    transition: background 0.2s;
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
