<!-- SectionItem.vue -->
<template>
    <tr class="row-table">
        <td class="section-column section-number">
            <div class="form-column">
                <h4 v-if="isStrut">Подкосы</h4>
                <h4 v-else>Секция {{ index + 1 }}</h4>
            </div>
        </td>
        <td class="section-column section-dimensions form-column">
            <input v-model.number="section.height" type="number" step="0.1" :class="{'input-error': showErrors && !(Number(section.height) > 0)}"/>
        </td>
        <td class="section-column section-dimensions form-column">
            <input v-model.number="section.widthBottom" type="number" step="0.01" :class="{'input-error': showErrors && !(Number(section.widthBottom) > 0)}"/>
        </td>
        <td class="section-column section-dimensions form-column">
            <input v-model.number="section.widthTop" type="number" step="0.01" :class="{'input-error': showErrors && !(Number(section.widthTop) > 0)}"/>
        </td>
        <td class="section-column section-elements">
            <div class="elements">
                <div class="elements-header">
                    <span class="eh col-type">Тип элемента</span>
                    <span class="eh col-section-type">Тип сечения</span>
                    <span class="eh col-width-el">Шир. сечения</span>
                    <span class="eh col-length-el">Длина</span>
                    <span class="eh col-count-el">Кол-во</span>
                    <span class="eh eh-action"></span>
                </div>
                <ElementItem
                    v-for="(element, idx) in section.elements"
                    :key="element.id"
                    :element="element"
                    :show-errors="showErrors"
                    :element-types="elementTypes"
                    :section-types="sectionTypes"
                    @remove="() => $emit('remove-element', idx)"
                />
                <button class="btn-add-element" @click="$emit('add-element')">+ Добавить элемент</button>
            </div>
        </td>
        <td class="insert-control after">
            <slot name="insert-after"></slot>
        </td>
    </tr>
</template>

<script setup>
import ElementItem from "./ElementItem.vue";

defineProps({
    section: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        required: true
    },
    isStrut: {
        type: Boolean,
        default: false
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

defineEmits(['remove-section', 'add-element', 'remove-element']);
</script>

<style scoped>
.insert-control {
    vertical-align: top;
    padding: 8px 4px;
    border-left: 1px solid #dee2e6;
    border-top: 1px solid #dee2e6;
}

.after {
    width: 3%;
}

.section-column {
    border-left: 1px solid #dee2e6;
    border-top: 1px solid #dee2e6;
    padding: 8px;
    vertical-align: top;
}

.section-number {
    width: 6%;
    text-align: center;
    vertical-align: middle !important;
    background-color: #f8f9fa;
}

.section-number h4 {
    margin: 0;
    font-size: 13px;
    color: #495057;
    font-weight: 600;
}

.section-dimensions {
    width: 8%;
}

.section-elements {
    width: 67%;
    padding: 6px 8px !important;
}

.form-column input {
    width: 100%;
    padding: 5px 6px;
    border: 1px solid #ced4da;
    border-radius: 3px;
    box-sizing: border-box;
    text-align: center;
    font-size: 13px;
}

/* Заголовки колонок элементов */
.elements-header {
    display: flex;
    gap: 6px;
    align-items: center;
    padding: 2px 4px 4px 4px;
    margin-bottom: 2px;
    border-bottom: 1px solid #dee2e6;
    width: 100%;
    box-sizing: border-box;
}

.eh {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-align: center;
    box-sizing: border-box;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
}

.eh-action { width: 22px; min-width: 22px; flex: none; }

/* Синхронизация пропорций с ElementItem */
.eh.col-type         { flex: 6 1 0; }
.eh.col-section-type { flex: 7 1 0; }
.eh.col-width-el     { flex: 4 1 0; }
.eh.col-length-el    { flex: 4 1 0; }
.eh.col-count-el     { flex: 3 1 0; }

.btn-add-element {
    margin-top: 4px;
    padding: 4px 10px;
    font-size: 12px;
    background-color: #e8f4fd;
    border: 1px solid #9acffa;
    border-radius: 3px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-add-element:hover {
    background-color: #d0e8fb;
}

.input-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}
</style>
