<script setup>
import {ref, onMounted} from 'vue';

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

// ─── Описание полей ────────────────────────────────────────────────────────────
const IMAGE_FIELDS = [
    {type: 'scheme', label: 'Схема опоры'},
    {type: 'scheme_pc', label: 'Расчетная схема опоры В ПК'},
    {type: 'sections', label: 'Сечения'},
    {type: 'mosaic_n', label: 'Мозаика усилий N'},
    {type: 'mosaic_m', label: 'Мозаика моментов M'},
    {type: 'mosaic_displacement', label: 'Мозаика перемещений'},
];

// ─── Состояние ────────────────────────────────────────────────────────────────
/** Для каждого imageType хранит { previewUrl, file, saved: { id, version, ... } } */
const images = ref(
    Object.fromEntries(IMAGE_FIELDS.map(f => [f.type, {previewUrl: null, file: null, saved: null}]))
);

const isSaving = ref(false);
const isLoading = ref(false);
const message = ref(null);  // { type: 'success'|'error', text: string }

// ─── Загрузка существующих изображений ────────────────────────────────────────
async function fetchImages() {
    isLoading.value = true;
    try {
        const response = await fetch(`/api/v1/calculation/${props.calculationId}/images`);
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Ошибка загрузки');
        }

        for (const item of data.data) {
            if (images.value[item.imageType]) {
                images.value[item.imageType].saved = item;
                images.value[item.imageType].previewUrl = `/api/v1/calculation/image/${item.id}/file`;
            }
        }
    } catch (err) {
        console.error('Ошибка загрузки изображений:', err);
    } finally {
        isLoading.value = false;
    }
}

// ─── Выбор файла через input ───────────────────────────────────────────────────
function onFileSelected(imageType, event) {
    const file = event.target.files[0];
    if (!file) return;
    setLocalFile(imageType, file);
    // сбросить input, чтобы повторный выбор того же файла тоже срабатывал
    event.target.value = '';
}

// ─── Вставка из буфера ────────────────────────────────────────────────────────
function onPaste(imageType, event) {
    const items = event.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) setLocalFile(imageType, file);
            break;
        }
    }
}

function setLocalFile(imageType, file) {
    const reader = new FileReader();
    reader.onload = (e) => {
        images.value[imageType].previewUrl = e.target.result;
    };
    reader.readAsDataURL(file);
    images.value[imageType].file = file;
    message.value = null;
}

// ─── Сохранение ───────────────────────────────────────────────────────────────
async function saveImages() {
    const pendingTypes = IMAGE_FIELDS
        .map(f => f.type)
        .filter(t => images.value[t].file !== null);

    if (pendingTypes.length === 0) {
        message.value = {type: 'error', text: 'Нет новых изображений для сохранения'};
        return;
    }

    isSaving.value = true;
    message.value = null;

    let successCount = 0;
    const errors = [];

    for (const imageType of pendingTypes) {
        try {
            const formData = new FormData();
            formData.append('imageType', imageType);
            formData.append('file', images.value[imageType].file);

            const response = await fetch(`/api/v1/calculation/${props.calculationId}/images`, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Ошибка сервера');
            }

            images.value[imageType].saved = data.data;
            images.value[imageType].file = null;
            successCount++;
        } catch (err) {
            const label = IMAGE_FIELDS.find(f => f.type === imageType)?.label ?? imageType;
            errors.push(`«${label}»: ${err.message}`);
        }
    }

    isSaving.value = false;

    if (errors.length === 0) {
        message.value = {type: 'success', text: `Сохранено изображений: ${successCount}`};
    } else {
        message.value = {
            type: 'error',
            text: errors.length < pendingTypes.length
                ? `Сохранено: ${successCount}. Ошибки: ${errors.join('; ')}`
                : 'Ошибка сохранения: ' + errors.join('; '),
        };
    }
}

// ─── Сброс выбранного (несохранённого) файла ──────────────────────────────────
function resetFile(imageType) {
    images.value[imageType].file = null;
    // Восстанавливаем URL сохранённого изображения, если оно есть
    if (images.value[imageType].saved) {
        images.value[imageType].previewUrl = `/api/v1/calculation/image/${images.value[imageType].saved.id}/file`;
    } else {
        images.value[imageType].previewUrl = null;
    }
}

// ─── Активация зоны для paste ─────────────────────────────────────────────────
function focusZone(el) {
    el?.focus();
}

onMounted(fetchImages);
</script>

<template>
    <div class="sw-calc-manager">

        <!--        <div class="sc-section-header">-->
        <!--            <h2 class="sc-title">6. Программный расчет опоры</h2>-->
        <!--            <p class="sc-subtitle">-->
        <!--                Загрузите скриншоты из программы расчёта: схему, сечения и мозаики усилий/перемещений-->
        <!--            </p>-->
        <!--        </div>-->

        <!-- Загрузка -->
        <div v-if="isLoading" class="sc-state-loading">Загрузка сохранённых изображений...</div>

        <template v-else>
            <div class="sc-grid">
                <div
                    v-for="field in IMAGE_FIELDS"
                    :key="field.type"
                    class="sc-image-card"
                >
                    <div class="sc-card-header">
                        <span class="sc-field-label">{{ field.label }}</span>
                        <span
                            v-if="images[field.type].saved && !images[field.type].file"
                            class="sc-badge-saved"
                            :title="`v${images[field.type].saved.version} · ${images[field.type].saved.updatedAt ?? images[field.type].saved.createdAt}`"
                        >
                            сохранено
                        </span>
                        <span v-if="images[field.type].file" class="sc-badge-pending">
                            не сохранено
                        </span>
                    </div>

                    <!-- Превью -->
                    <div
                        v-if="images[field.type].previewUrl"
                        class="sc-preview-wrap"
                        tabindex="0"
                        @paste="onPaste(field.type, $event)"
                        @focus="() => {}"
                    >
                        <img
                            :src="images[field.type].previewUrl"
                            class="sc-preview-img"
                            alt="Изображение"
                        />
                        <div class="sc-preview-overlay">
                            <label class="sc-btn-icon" :title="'Заменить изображение'">
                                <input
                                    type="file"
                                    accept="image/*"
                                    class="sc-file-input"
                                    @change="onFileSelected(field.type, $event)"
                                />
                                Заменить
                            </label>
                            <button
                                v-if="images[field.type].file"
                                class="sc-btn-icon sc-btn-cancel"
                                @click="resetFile(field.type)"
                                title="Отменить изменение"
                            >
                                Отменить
                            </button>
                        </div>
                    </div>

                    <!-- Зона загрузки (нет изображения) -->
                    <div
                        v-else
                        class="sc-drop-zone"
                        tabindex="0"
                        @paste="onPaste(field.type, $event)"
                        @click="focusZone($event.currentTarget)"
                    >
                        <svg class="sc-drop-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.5">
                            <path d="M4 16l4-4 4 4 4-6 4 6" stroke-linecap="round" stroke-linejoin="round"/>
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                        </svg>
                        <p class="sc-drop-hint">Нажмите для выбора или вставьте из буфера</p>
                        <label class="sc-btn-upload">
                            <input
                                type="file"
                                accept="image/*"
                                class="sc-file-input"
                                @change="onFileSelected(field.type, $event)"
                            />
                            Выбрать файл
                        </label>
                    </div>

                    <!-- Подсказка paste для зоны с превью -->
                    <p v-if="images[field.type].previewUrl" class="sc-paste-hint">
                        Нажмите на изображение и вставьте из буфера (Ctrl+V)
                    </p>
                </div>
            </div>

            <!-- Сообщение -->
            <div
                v-if="message"
                :class="['sc-message', message.type === 'success' ? 'sc-message--success' : 'sc-message--error']"
            >
                {{ message.text }}
            </div>

            <!-- Тулбар -->
            <div class="sc-toolbar">
                <button
                    class="sc-btn-save"
                    :disabled="isSaving"
                    @click="saveImages"
                >
                    {{ isSaving ? 'Сохранение...' : 'Сохранить' }}
                </button>
            </div>
        </template>

    </div>
</template>

<style scoped>
/* ── Контейнер ── */
.sw-calc-manager {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 24px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #212529;
}

/* ── Заголовок раздела ── */
.sc-section-header {
    padding-bottom: 12px;
    border-bottom: 1px solid #dee2e6;
}

.sc-title {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 700;
    color: #1a2533;
}

.sc-subtitle {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
}

/* ── Состояние загрузки ── */
.sc-state-loading {
    padding: 20px;
    text-align: center;
    color: #1976d2;
    background: #e3f2fd;
    border: 1px solid #90caf9;
    border-radius: 6px;
}

/* ── Сетка карточек ── */
.sc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
}

/* ── Карточка изображения ── */
.sc-image-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
    background: #fafbfc;
}

/* ── Заголовок карточки ── */
.sc-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    height: 50px;
    padding: 10px 12px 8px;
    background: #f4f6f8;
    border-bottom: 1px solid #dee2e6;
}

.sc-field-label {
    font-size: 12px;
    font-weight: 600;
    color: #343a40;
    line-height: 1.3;
}

.sc-badge-saved {
    flex-shrink: 0;
    padding: 2px 7px;
    font-size: 11px;
    font-weight: 600;
    color: #1a6e3c;
    background: #d4edda;
    border-radius: 10px;
    cursor: default;
}

.sc-badge-pending {
    flex-shrink: 0;
    padding: 2px 7px;
    font-size: 11px;
    font-weight: 600;
    color: #856404;
    background: #fff3cd;
    border-radius: 10px;
}

/* ── Зона загрузки (без изображения) ── */
.sc-drop-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 24px 16px;
    min-height: 220px;
    border: 2px dashed #ced4da;
    border-radius: 0;
    background: #f8f9fa;
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
}

.sc-drop-zone:focus,
.sc-drop-zone:hover {
    border-color: #1976d2;
    background: #f0f7ff;
}

.sc-drop-icon {
    width: 36px;
    height: 36px;
    color: #adb5bd;
}

.sc-drop-hint {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
    text-align: center;
}

/* ── Превью ── */
.sc-preview-wrap {
    position: relative;
    width: 100%;
    min-height: 120px;
    overflow: hidden;
    cursor: pointer;
    outline: none;
}

.sc-preview-img {
    display: block;
    width: 100%;
    max-height: 220px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 8px;
}

.sc-preview-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: rgba(0, 0, 0, 0);
    opacity: 0;
    transition: opacity 0.15s, background 0.15s;
}

.sc-preview-wrap:hover .sc-preview-overlay,
.sc-preview-wrap:focus .sc-preview-overlay {
    background: rgba(0, 0, 0, 0.45);
    opacity: 1;
}

/* ── Кнопки внутри превью ── */
.sc-btn-icon {
    display: inline-flex;
    align-items: center;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #ffffff;
    background: #1976d2;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s;
}

.sc-btn-icon:hover {
    background: #1565c0;
}

.sc-btn-cancel {
    background: #6c757d;
}

.sc-btn-cancel:hover {
    background: #495057;
}

/* ── Кнопка «Выбрать файл» ── */
.sc-btn-upload {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    color: #1976d2;
    background: #ffffff;
    border: 1px solid #1976d2;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.sc-btn-upload:hover {
    background: #1976d2;
    color: #ffffff;
}

/* ── Скрытый input ── */
.sc-file-input {
    display: none;
}

/* ── Подсказка paste ── */
.sc-paste-hint {
    margin: 0;
    padding: 4px 12px 8px;
    font-size: 11px;
    color: #adb5bd;
    text-align: center;
}

/* ── Сообщение ── */
.sc-message {
    padding: 10px 16px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 500;
}

.sc-message--success {
    background: #d4edda;
    color: #1a6e3c;
    border: 1px solid #c3e6cb;
}

.sc-message--error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ── Тулбар ── */
.sc-toolbar {
    display: flex;
    justify-content: flex-start;
    padding-top: 4px;
    border-top: 1px solid #dee2e6;
}

.sc-btn-save {
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

.sc-btn-save:hover:not(:disabled) {
    background: #378541;
}

.sc-btn-save:disabled {
    background: #90bde8;
    cursor: not-allowed;
}

/* ── Адаптив ── */
@media (max-width: 768px) {
    .sw-calc-manager {
        padding: 16px;
    }

    .sc-grid {
        grid-template-columns: 1fr;
    }
}
</style>
