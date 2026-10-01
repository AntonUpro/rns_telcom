<script setup>
/**
 * Заготовка таблицы, расчёт которой ещё не реализован.
 * Выводит только заголовок и пометку — данные не вводятся.
 */
defineProps({
    tableNumber: {
        type: Number,
        required: true,
    },
    tableName: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <h3 class="rt-title">Таблица {{ tableNumber }}. {{ tableName }}</h3>
        </div>
        <p class="rt-placeholder">Таблица и расчет будут добавлены позже.</p>
    </section>
</template>

<style scoped>
.rt-section {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}
.rt-section-header {
    padding: 10px 16px;
    background: #f4f6f8;
    border-bottom: 1px solid #dee2e6;
}
.rt-title { margin: 0; font-size: 13px; font-weight: 700; color: #1a2533; }
.rt-placeholder {
    margin: 0; padding: 14px 16px;
    color: #6c757d; font-style: italic; font-size: 12px; text-align: center;
}
</style>
