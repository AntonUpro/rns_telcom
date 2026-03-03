<!-- SectionItem.vue -->
<template>
    <div class="section-item">
        <!-- Вставка до -->
<!--        <div class="insert-control before">-->
<!--            <slot name="insert-before"></slot>-->
<!--        </div>-->

        <!-- Основная секция -->
        <div class="section-content-item">

            <div v-if="index === 0" class="section-header-item">
                <div class="section-column section-number">
                    <label class="section-column-header">№</label>
                </div>
                <div class="section-column section-dimensions">
                    <label class="section-column-header">Габариты секции</label>
                </div>
                <div class="section-column section-elements">
                    <label class="section-column-header">Элементы секции</label>
                </div>
            </div>
            <div class="section-row">
                <div class="section-column section-number">
                    <div class="form-column">
                        <h4>Секция {{ index + 1 }}</h4>
                    </div>
                </div>

                <div class="section-column section-dimensions">
                    <div class="form-column">
                        <label>Высота (м):</label>
                        <input v-model.number="section.height" type="number" step="0.1"/>
                    </div>
                    <div class="form-column">
                        <label>Ширина низа (м):</label>
                        <input v-model.number="section.widthBottom" type="number" step="0.01"/>
                    </div>
                    <div class="form-column">
                        <label>Ширина верха (м):</label>
                        <input v-model.number="section.widthTop" type="number" step="0.01"/>
                    </div>
                </div>

                <!-- Элементы секции -->
                <div class="section-column section-elements">
                    <div class="elements">
                        <ElementItem
                            v-for="(element, idx) in section.elements"
                            :key="element.id"
                            :element="element"
                            @remove="() => $emit('remove-element', idx)"
                        />
                        <button class="btn-add-element" @click="$emit('add-element')">+ Добавить элемент</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Вставка после -->
        <div class="insert-control after">
            <slot name="insert-after"></slot>
        </div>
    </div>
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
    }
});

defineEmits(['remove-section', 'add-element', 'remove-element']);
</script>

<style scoped>
.section-item {
    display: flex;
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    background-color: #fafafa;
}

.insert-control {
    display: flex;
    align-items: center;
    padding: 0 4px;
    background-color: #f5f5f5;
}

.after {
    order: 3;
}

.section-content-item {
    display: block;
    order: 2;
    width: 100%;
}

.section-header-item {
    width: 100%;
    display: flex;
    border-bottom: 1px solid #6f7c8a;
}

.section-row {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.section-column {
    display: flex;
    flex-direction: column; /* Ensures internal items stack vertically if needed */
    gap: 8px; /* Adds spacing between form columns inside */
    vertical-align: top;
    border-left: 1px solid #6f7c8a;
    padding-top: 12px;
}

.section-column-header {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 8px;
    text-align: center;
    color: #333;
}

.section-column.section-number {
    justify-content: center;
}

.section-column.section-elements {
    align-items: normal;
}

.section-number {
    width: 8%;
}

.section-dimensions {
    width: 22%;
}

.section-elements {
    width: 70%;
}

.form-column {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centers label and input horizontally */
    gap: 4px;
    margin-bottom: 8px;
    width: 100%;
}

.form-column label {
    text-align: center;
    font-weight: 500;
}

.form-column input {
    width: 100px;
    padding: 4px;
    border: 1px solid #ccc;
    border-radius: 3px;
    box-sizing: border-box;
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
