<template>
    <div>
        <h2>Шаг 1: Общие данные</h2>

        <form @submit.prevent="handleSubmit">
            <div>
                <label>Ветровой район</label>
                <input v-model="form.windRegion" type="text">
            </div>

            <div>
                <label>Адрес</label>
                <input v-model="form.address" type="text">
            </div>

            <div>
                <label>Тип опоры</label>
                <select v-model="form.poleType">
                    <option value="">Выберите...</option>
                    <option value="metal">Металлическая</option>
                    <option value="concrete">Железобетонная</option>
                </select>
            </div>

            <button type="submit" :disabled="loading || !isFormValid">
                {{ loading ? 'Считаем...' : 'Предварительный расчёт' }}
            </button>
        </form>

        <div v-if="error" style="color: red; margin-top: 1rem;">
            {{ error }}
        </div>

        <div v-if="previewResult" style="margin-top: 1rem;">
            <h3>Предварительные результаты</h3>
            <!-- пример простой таблицы -->
            <table border="1">
                <thead>
                <tr>
                    <th>Участок</th>
                    <th>Нагрузка, кН</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="row in previewResult.rows" :key="row.id">
                    <td>{{ row.segment }}</td>
                    <td>{{ row.load }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import {ref, reactive, computed, onMounted, watch} from 'vue';


// 1. Входные параметры компонента
const props = defineProps({
    name: {
        type: String,
        default: 'World',
    },
});

// 2. Локальное состояние (форма, загрузка, ошибки)
const form = reactive({
    windRegion: '',
    address: '',
    poleType: '',
});

const loading = ref(false);
const error = ref(null);
const previewResult = ref(null);

// 3. Пример вычисляемого свойства
const isFormValid = computed(() => {
    return form.windRegion !== '' && form.poleType !== '';
});

// 4. Асинхронный запрос к API Symfony
const fetchPreview = async () => {
    error.value = null;
    loading.value = true;

    try {
        const response = await fetch('/api/calculations/preview/step/1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form),
        });

        if (!response.ok) {
            throw new Error(`Ошибка ${response.status}`);
        }

        previewResult.value = await response.json();
    } catch (e) {
        error.value = e.message || 'Не удалось выполнить предварительный расчёт';
    } finally {
        loading.value = false;
    }
};

// 5. Обработчик отправки формы (например, по кнопке "Сохранить шаг")
const handleSubmit = async () => {
    if (!isFormValid.value) {
        error.value = 'Заполните обязательные поля';
        return;
    }

    // здесь можешь отправлять данные на другой эндпоинт, сохранять шаг и т.п.
    await fetchPreview();
};

// 6. Если нужно что-то делать при монтировании (например, загрузить начальные данные)
onMounted(() => {
    // можно подгрузить существующий черновик, если нужен
});

</script>
