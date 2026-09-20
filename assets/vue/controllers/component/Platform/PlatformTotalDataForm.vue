<!-- PlatformTotalDataForm.vue -->
<script setup>
defineProps({
    totalData: {
        type: Object,
        required: true
    },
    showErrors: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
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
                    <span class="unit">мм</span>
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
                    <span class="unit">мм</span>
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
</template>

<style scoped>
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

.input-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
}
</style>
