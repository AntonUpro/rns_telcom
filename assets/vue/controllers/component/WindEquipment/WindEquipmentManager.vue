<script setup>
import {onMounted, reactive, ref} from "../../../../vendor/@vue/runtime-core/runtime-core.index";

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    }
});

const calculationRows = reactive([]);
const loading = ref(false);
const error = ref(null);

const fetchWindLoad = async () => {
    try {
        loading.value = true;
        error.value = null;

        const response = await fetch('/api/v1/calculation/wind-load', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({calculationId: props.calculationId}),
        });

        const responseData = await response.json();

        if (!response.ok || !responseData.success) {
            throw new Error('Ошибка получения данных по оборудованию. Ошибка: ' + (responseData.error || 'Неизвестная ошибка'));
        }

        calculationRows.splice(0, calculationRows.length, ...(responseData.data || []));
        console.log('Calculation rows loaded:', calculationRows);
    } catch (err) {
        error.value = err.message;
        console.error('Error fetching wind load data:', err);
        alert('Ошибка получения данных по оборудованию: ' + err.message);
    } finally {
        loading.value = false;
    }
};

const refreshData = () => {
    fetchWindLoad();
};

onMounted(() => {
    fetchWindLoad();
})
</script>

<template>
    <div class="wind-load-manager">
        <div v-if="loading" class="loading">Загрузка данных...</div>
        <div v-else-if="error" class="error">Ошибка: {{ error }}</div>
        <div v-else class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                <tr>
                    <th rowspan="2">№</th>
                    <th rowspan="2">Z, м</th>
                    <th rowspan="2">K(ze)</th>
                    <th rowspan="2">Wo<br>кг/м²</th>
                    <th rowspan="2">Y</th>
                    <th colspan="3">Ствол опоры</th>
                    <th colspan="3">Кабельная трасса</th>
                    <th colspan="3">Кабельрост</th>
                    <th colspan="3">Лестница</th>
                </tr>
                <tr>
                    <th>А, м²</th>
                    <th>Сх</th>
                    <th>Р, кг</th>
                    <th>А, м²</th>
                    <th>Сх</th>
                    <th>Р, кг</th>
                    <th>А, м²</th>
                    <th>Сх</th>
                    <th>Р, кг</th>
                    <th>А, м²</th>
                    <th>Сх</th>
                    <th>Р, кг</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="(row, index) in calculationRows" :key="index">
                    <td>{{ row.totalCalculationDataDto?.numberSection ?? '-' }}</td>
                    <td>{{ row.totalCalculationDataDto?.topHeight ?? '-' }}</td>
                    <td>{{ row.totalCalculationDataDto?.kze ?? '-' }}</td>
                    <td>{{ row.totalCalculationDataDto?.windPress ?? '-' }}</td>
                    <td>{{ row.totalCalculationDataDto?.shadingCoefficient ?? '-' }}</td>
                    <!-- Ствол опоры -->
                    <td>{{ row.pillarPart?.area ?? '-' }}</td>
                    <td>{{ row.pillarPart?.cx ?? '-' }}</td>
                    <td>{{ row.pillarPart?.press ?? '-' }}</td>
                    <!-- Кабельная трасса -->
                    <td>{{ row.cableChanelPart?.area ?? '-' }}</td>
                    <td>{{ row.cableChanelPart?.cx ?? '-' }}</td>
                    <td>{{ row.cableChanelPart?.press ?? '-' }}</td>
                    <!-- Кабельрост -->
                    <td>{{ row.cablePart?.area ?? '-' }}</td>
                    <td>{{ row.cablePart?.cx ?? '-' }}</td>
                    <td>{{ row.cablePart?.press ?? '-' }}</td>
                    <!-- Лестница -->
                    <td>{{ row.ladderPart?.area ?? '-' }}</td>
                    <td>{{ row.ladderPart?.cx ?? '-' }}</td>
                    <td>{{ row.ladderPart?.press ?? '-' }}</td>
                </tr>
                <tr v-if="calculationRows.length === 0">
                    <td colspan="17" class="text-center">Нет данных для отображения</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="header-actions mb-3">
            <button
                @click="refreshData"
                :disabled="loading"
                class="btn btn-primary btn-sm"
            >
                {{ loading ? 'Загрузка...' : 'Обновить данные' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.header-actions {
    display: flex;
    justify-content: flex-end;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.wind-load-manager {
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.loading, .error {
    padding: 20px;
    text-align: center;
    font-size: 16px;
}

.loading {
    color: #007bff;
}

.error {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
}

.table-responsive {
    overflow-x: auto;
    margin-bottom: 20px;
}

.table {
    width: 100%;
    margin-bottom: 0;
    background-color: #ffffff;
    border-collapse: separate;
    border-spacing: 0;
}

.table th,
.table td {
    padding: 12px 15px;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    text-align: center;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    position: relative;
}

.table-light th {
    background-color: #e9ecef;
    border-bottom: 2px solid #dee2e6;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

/* Header styling for multi-level headers */
.table thead th[rowspan="2"] {
    vertical-align: middle;
}

.table thead th[colspan] {
    border-bottom: 1px solid #dee2e6;
}

/* Business professional styling */
.table th:first-child,
.table td:first-child {
    text-align: center;
    font-weight: 600;
    background-color: #f8f9fa;
}

.table-bordered {
    border: 1px solid #dee2e6;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #dee2e6;
}

.table-bordered thead th {
    border-bottom-width: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .table th,
    .table td {
        padding: 8px 10px;
        font-size: 14px;
    }

    .wind-load-manager {
        padding: 15px;
    }
}

/* Focus states for accessibility */
.table th:focus,
.table td:focus {
    outline: 2px solid #007bff;
    outline-offset: -2px;
}

/* Print styles */
@media print {
    .wind-load-manager {
        box-shadow: none;
        border: 1px solid #000;
    }

    .table th,
    .table td {
        border: 1px solid #000 !important;
    }
}
</style>
