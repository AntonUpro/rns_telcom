<script setup>
import {ref, onMounted} from 'vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

// ─── Состояние ────────────────────────────────────────────────────────────────
const rows = ref(['']);           // массив строк-названий документов
const isLoading = ref(false);
const isSaving = ref(false);
const message = ref(null);       // { type: 'success'|'error', text: string }

// ─── Загрузка ─────────────────────────────────────────────────────────────────
async function fetchDocuments() {
    isLoading.value = true;
    message.value = null;
    try {
        const response = await fetch(`/api/v1/calculation/${props.calculationId}/documents`);
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка загрузки');
        }

        rows.value = data.data.length > 0
            ? data.data.map(doc => doc.name)
            : [''];
    } catch (err) {
        console.error('Ошибка загрузки документов:', err);
        message.value = {type: 'error', text: 'Не удалось загрузить сохранённые документы'};
    } finally {
        isLoading.value = false;
    }
}

// ─── Добавить строку ──────────────────────────────────────────────────────────
function addRow() {
    rows.value.push('');
}

// ─── Удалить строку ───────────────────────────────────────────────────────────
function removeRow(index) {
    rows.value.splice(index, 1);
    if (rows.value.length === 0) {
        rows.value.push('');
    }
}

// ─── Сохранение ───────────────────────────────────────────────────────────────
async function saveDocuments() {
    const names = rows.value.map(r => r.trim()).filter(r => r !== '');

    isSaving.value = true;
    message.value = null;

    try {
        const response = await fetch(`/api/v1/calculation/${props.calculationId}/documents`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({names}),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка сервера');
        }

        rows.value = data.data.length > 0
            ? data.data.map(doc => doc.name)
            : [''];

        message.value = {type: 'success', text: 'Документы успешно сохранены'};
    } catch (err) {
        message.value = {type: 'error', text: err.message};
    } finally {
        isSaving.value = false;
    }
}

onMounted(fetchDocuments);
</script>

<template>
    <div class="docs-form">

        <h3 class="docs-subtitle">Заполните предоставленные документы</h3>

        <!-- Загрузка -->
        <div v-if="isLoading" class="docs-loading">Загрузка сохранённых документов...</div>

        <template v-else>
            <div class="docs-list">
                <div
                    v-for="(row, index) in rows"
                    :key="index"
                    class="docs-row"
                >
                    <span class="docs-row-num">{{ index + 1 }}.</span>
                    <input
                        v-model="rows[index]"
                        type="text"
                        class="docs-input"
                        placeholder="Название документа"
                        maxlength="500"
                    />
                    <button
                        v-if="rows.length > 1"
                        class="docs-btn-remove"
                        title="Удалить строку"
                        @click="removeRow(index)"
                    >
                        &times;
                    </button>
                </div>
            </div>

            <div class="docs-actions">
                <button class="docs-btn-add" @click="addRow">
                    + Добавить документ
                </button>
            </div>

            <!-- Сообщение -->
            <div
                v-if="message"
                :class="['docs-message', message.type === 'success' ? 'docs-message--success' : 'docs-message--error']"
            >
                {{ message.text }}
            </div>

            <!-- Сохранение -->
            <div class="docs-toolbar">
                <button
                    class="docs-btn-save"
                    :disabled="isSaving"
                    @click="saveDocuments"
                >
                    {{ isSaving ? 'Сохранение...' : 'Сохранить' }}
                </button>
            </div>
        </template>

    </div>
</template>

<style scoped>
.docs-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 0 24px 0;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #212529;
}

/* ── Подзаголовок ── */
.docs-subtitle {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1a2533;
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
}

/* ── Загрузка ── */
.docs-loading {
    padding: 16px;
    text-align: center;
    color: #1976d2;
    background: #e3f2fd;
    border: 1px solid #90caf9;
    border-radius: 6px;
}

/* ── Список строк ── */
.docs-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.docs-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.docs-row-num {
    flex-shrink: 0;
    width: 24px;
    text-align: right;
    font-size: 13px;
    color: #6c757d;
}

.docs-input {
    flex: 1;
    padding: 7px 12px;
    font-size: 14px;
    color: #212529;
    background: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 5px;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
}

.docs-input:focus {
    border-color: #1976d2;
    background: #ffffff;
}

.docs-btn-remove {
    flex-shrink: 0;
    width: 28px;
    height: 28px;
    padding: 0;
    font-size: 18px;
    line-height: 1;
    color: #6c757d;
    background: transparent;
    border: 1px solid #ced4da;
    border-radius: 4px;
    cursor: pointer;
    transition: color 0.15s, border-color 0.15s, background 0.15s;
}

.docs-btn-remove:hover {
    color: #c0392b;
    border-color: #c0392b;
    background: #fdecea;
}

/* ── Кнопка добавления ── */
.docs-actions {
    display: flex;
}

.docs-btn-add {
    padding: 6px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #1976d2;
    background: #ffffff;
    border: 1px solid #1976d2;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.docs-btn-add:hover {
    background: #1976d2;
    color: #ffffff;
}

/* ── Сообщение ── */
.docs-message {
    padding: 10px 16px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 500;
}

.docs-message--success {
    background: #d4edda;
    color: #1a6e3c;
    border: 1px solid #c3e6cb;
}

.docs-message--error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ── Тулбар сохранения ── */
.docs-toolbar {
    display: flex;
    justify-content: flex-start;
    padding-top: 4px;
    border-top: 1px solid #dee2e6;
}

.docs-btn-save {
    padding: 8px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
    background: #27ae60;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.15s;
}

.docs-btn-save:hover:not(:disabled) {
    background: #219a52;
}

.docs-btn-save:disabled {
    background: #90cbb0;
    cursor: not-allowed;
}
</style>
