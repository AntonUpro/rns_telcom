<script setup>
import {onMounted, onBeforeUnmount} from 'vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save', 'discard', 'cancel']);

const onKeydown = (event) => {
    if (props.visible && event.key === 'Escape') {
        emit('cancel');
    }
};

const onOverlayClick = (event) => {
    if (event.target === event.currentTarget) {
        emit('cancel');
    }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <div v-if="visible" class="ucm-overlay" @click="onOverlayClick">
        <div class="ucm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="ucm-title">
            <h3 id="ucm-title" class="ucm-title">Несохранённые изменения</h3>
            <p class="ucm-text">
                На этой вкладке есть несохранённые изменения. Сохранить их перед переходом?
            </p>
            <div class="ucm-actions">
                <button class="ucm-btn ucm-btn-save" @click="emit('save')">Сохранить и перейти</button>
                <button class="ucm-btn ucm-btn-discard" @click="emit('discard')">Не сохранять и перейти</button>
                <button class="ucm-btn ucm-btn-cancel" @click="emit('cancel')">Отмена</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ucm-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.ucm-dialog {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    padding: 1.5rem;
    max-width: 420px;
    width: 90%;
    font-family: Arial, sans-serif;
}

.ucm-title {
    margin: 0 0 0.75rem;
    color: #2c3e50;
    font-size: 1.1rem;
}

.ucm-text {
    margin: 0 0 1.5rem;
    color: #5d6d7e;
    font-size: 0.9rem;
    line-height: 1.4;
}

.ucm-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: flex-end;
}

.ucm-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.3s ease;
}

.ucm-btn-save { background-color: #2ecc71; color: white; }
.ucm-btn-save:hover { background-color: #27ae60; }

.ucm-btn-discard { background-color: #e74c3c; color: white; }
.ucm-btn-discard:hover { background-color: #c0392b; }

.ucm-btn-cancel { background-color: #95a5a6; color: white; }
.ucm-btn-cancel:hover { background-color: #7f8c8d; }

@media (max-width: 480px) {
    .ucm-actions { flex-direction: column; }
    .ucm-btn { width: 100%; }
}
</style>
