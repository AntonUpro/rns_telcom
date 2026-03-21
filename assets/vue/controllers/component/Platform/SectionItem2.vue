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
            <input v-model.number="section.height" type="number" step="0.1"/>
        </td>
        <td class="section-column section-dimensions form-column">
            <input v-model.number="section.widthBottom" type="number" step="0.01"/>
        </td>
        <td class="section-column section-dimensions form-column">
            <input v-model.number="section.widthTop" type="number" step="0.01"/>
        </td>
        <td class="section-column section-elements">
            <div class="elements">
                <ElementItem
                    v-for="(element, idx) in section.elements"
                    :key="element.id"
                    :element="element"
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
    }
});

defineEmits(['remove-section', 'add-element', 'remove-element']);
</script>

<style scoped>

.insert-control {
    align-items: center;
    padding: 0 4px;
    border-left: 1px solid #6f7c8a;
    border-top: 1px solid #6f7c8a;

}

.after {
    order: 3;
    width: 3%;
}

.section-column {
    border-left: 1px solid #6f7c8a;
    border-top: 1px solid #6f7c8a;
    padding-top: 12px;
}

.section-column.section-elements {
    align-items: normal;
}

.section-number {
    width: 6%;
    text-align: center;
}

.section-dimensions {
    width: 8%;
}

.section-elements {
    width: 67%;
}

.form-column {
    align-items: center; /* Centers label and input horizontally */
    margin-bottom: 8px;
    background-color: #fafafa;
}

.form-column label {
    text-align: center;
    font-weight: 500;
}

.form-column input {
    width: 100%;
    padding: 5px 4px;
    border: 1px solid #ccc;
    border-radius: 3px;
    box-sizing: border-box;
    text-align: center;
}

.btn-add-element {
    padding: 4px 10px;
    font-size: 12px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 3px;
    cursor: pointer;
    margin-bottom: 8px;
}
</style>
