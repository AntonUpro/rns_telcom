<script setup>
import { ref, reactive, computed } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'save']);

const formData = reactive({
    type: 'rrl',
    brand: '',
    model: '',
    diameter: 0,
    width: 0,
    height: 0,
    depth: 0,
    weight: 0
});

const isSaving = ref(false);

const equipmentTypes = [
    { value: 'rrl', label: 'РРЛ' },
    { value: 'panel', label: 'Панельная' },
    { value: 'radio', label: 'Радиоблок' },
    { value: 'other', label: 'Прочее' }
];

const isRRL = computed(() => formData.type === 'rrl');

const resetForm = () => {
    formData.type = 'rrl';
    formData.brand = '';
    formData.model = '';
    formData.diameter = 0;
    formData.width = 0;
    formData.height = 0;
    formData.depth = 0;
    formData.weight = 0;
};

const handleClose = () => {
    resetForm();
    emit('close');
};

const handleSubmit = async () => {
    if (!formData.brand || !formData.model || !formData.weight) {
        alert('Пожалуйста, заполните все обязательные поля');
        return;
    }

    if (isRRL.value && !formData.diameter) {
        alert('Пожалуйста, укажите диаметр для РРЛ оборудования');
        return;
    }

    if (!isRRL.value && (!formData.width || !formData.height || !formData.depth)) {
        alert('Пожалуйста, укажите все габариты для прямоугольного оборудования');
        return;
    }

    try {
        isSaving.value = true;

        const equipmentData = {
            type: formData.type,
            brand: formData.brand,
            model: formData.model,
            weight: Number(formData.weight),
            fromDropdown: false, // Manually added equipment
            ...(isRRL.value
                ? { diameter: Number(formData.diameter) }
                : {
                    width: Number(formData.width),
                    height: Number(formData.height),
                    depth: Number(formData.depth)
                }
            )
        };

        // Here you would make the actual API call
        await fetch('/api/v1/equipment/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(equipmentData)
        });

        emit('save', equipmentData);
        handleClose();

    } catch (error) {
        console.error('Error saving equipment:', error);
        alert('Ошибка при сохранении оборудования');
    } finally {
        isSaving.value = false;
    }
};

const handleBackdropClick = (event) => {
    if (event.target === event.currentTarget) {
        handleClose();
    }
};
</script>

<template>
    <div
        v-if="isOpen"
        class="popup-overlay"
        @click="handleBackdropClick"
    >
        <div class="popup-container">
            <div class="popup-header">
                <h3>Добавить оборудование</h3>
                <button class="popup-close" @click="handleClose">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="popup-content">
                <div class="form-group">
                    <label class="form-label">Тип оборудования *</label>
                    <select v-model="formData.type" class="form-select">
                        <option v-for="type in equipmentTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Марка оборудования *</label>
                    <input
                        v-model="formData.brand"
                        type="text"
                        class="form-input"
                        placeholder="Введите марку оборудования"
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">Модель оборудования *</label>
                    <input
                        v-model="formData.model"
                        type="text"
                        class="form-input"
                        placeholder="Введите модель оборудования"
                    />
                </div>

                <!-- RRL Equipment - Diameter -->
                <div v-if="isRRL" class="form-group">
                    <label class="form-label">Диаметр, мм *</label>
                    <input
                        v-model.number="formData.diameter"
                        type="number"
                        class="form-input"
                        min="0"
                        step="1"
                        placeholder="Введите диаметр"
                    />
                </div>

                <!-- Rectangular Equipment - Dimensions -->
                <div v-else class="dimensions-grid">
                    <div class="form-group">
                        <label class="form-label">Высота, мм *</label>
                        <input
                            v-model.number="formData.height"
                            type="number"
                            class="form-input"
                            min="0"
                            step="1"
                            placeholder="Высота"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ширина, мм *</label>
                        <input
                            v-model.number="formData.width"
                            type="number"
                            class="form-input"
                            min="0"
                            step="1"
                            placeholder="Ширина"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Глубина, мм *</label>
                        <input
                            v-model.number="formData.depth"
                            type="number"
                            class="form-input"
                            min="0"
                            step="1"
                            placeholder="Глубина"
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Вес, кг *</label>
                    <input
                        v-model.number="formData.weight"
                        type="number"
                        class="form-input"
                        min="0"
                        step="0.1"
                        placeholder="Введите вес"
                    />
                </div>
            </div>

            <div class="popup-footer">
                <button
                    @click="handleClose"
                    class="btn-cancel"
                    :disabled="isSaving"
                >
                    Отмена
                </button>
                <button
                    @click="handleSubmit"
                    class="btn-save"
                    :disabled="isSaving"
                >
                    {{ isSaving ? 'Сохранение...' : 'Сохранить' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

.popup-container {
    background: white;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.popup-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 600;
}

.popup-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    color: #6c757d;
    transition: all 0.2s ease;
}

.popup-close:hover {
    background: #f8f9fa;
    color: #2c3e50;
}

.popup-content {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #495057;
}

.form-select,
.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-select:focus,
.form-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.dimensions-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

.popup-footer {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #dee2e6;
    justify-content: flex-end;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-cancel:hover:not(:disabled) {
    background: #5a6268;
}

.btn-cancel:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-save {
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn-save:hover:not(:disabled) {
    background: #27ae60;
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .popup-overlay {
        padding: 0.5rem;
    }

    .popup-container {
        max-width: 100%;
    }

    .dimensions-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .popup-footer {
        flex-direction: column;
    }
}
</style>
