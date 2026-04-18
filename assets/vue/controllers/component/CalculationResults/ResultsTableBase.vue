<script setup>
/**
 * Таблица 8. Результаты расчёта основания опоры.
 *
 * Структура шапки (двухуровневая):
 *  ┌─────────────────────┬───────────────────────┬────────────────────────────────────────┐
 *  │    Устойчивость     │      Деформации        │     Коэффициент использования          │
 *  ├──────────┬──────────┼──────────┬─────────────┼──────────────────────┬─────────────────┤
 *  │  Q, тс   │  Qu, тс  │  β, рад. │  βu, рад.   │ Расч. на устойчивость│ Расч. по деформ.│
 *  └──────────┴──────────┴──────────┴─────────────┴──────────────────────┴─────────────────┘
 *
 * Поля ввода:  Q, тс | βu, рад.
 * Вычисляемые: Qu, тс | β, рад. | Кисп устойчивость | Кисп деформации
 *              — заполняются сервером после расчёта.
 *
 * Q    — расчётная поперечная сила у основания (из таблицы 6 или программного ПК).
 * Qu   — предельная поперечная сила (несущая способность основания) — вычисл.
 * β    — расчётное угловое смещение фундамента — вычисл.
 * βu   — предельное угловое смещение (из технического задания / нормативов) — ввод.
 *
 * TODO: уточнить у проектировщика, какие поля вводятся вручную, а какие
 *       берутся из программного расчёта фундамента (КРОСС, Сваи, и др.).
 */
const props = defineProps({
    rows: {
        type: Array,
        required: true,
    },
    tableNumber: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['update:rows']);

const makeRow = () => ({
    q:                 null,   // Q, тс    — расч. поперечная сила у основания (ввод)
    qU:                null,   // Qu, тс   — предельная поперечная сила (вычисл.)
    beta:              null,   // β, рад.  — расч. угловое смещение (вычисл.)
    betaU:             null,   // βu, рад. — допустимое угловое смещение (ввод)
    kUseStability:     null,   // Кисп по устойчивости (вычисл.)
    kUseDeformation:   null,   // Кисп по деформациям (вычисл.)
});

const updateCell = (idx, field, value) => {
    emit('update:rows', props.rows.map((r, i) => i === idx ? { ...r, [field]: value } : r));
};

const addRow = () => emit('update:rows', [...props.rows, makeRow()]);

const removeRow = (idx) => {
    if (props.rows.length <= 1) return;
    emit('update:rows', props.rows.filter((_, i) => i !== idx));
};

const fmt = (v, d = 3) => (v !== null && v !== undefined) ? Number(v).toFixed(d) : '—';

const isExceeded = (v) => v !== null && v !== undefined && Number(v) > 1;
</script>

<template>
    <section class="rt-section">
        <div class="rt-section-header">
            <div>
                <h3 class="rt-title">Таблица {{ tableNumber }}. Результаты расчёта основания опоры</h3>
                <p class="rt-subtitle">Устойчивость и деформации фундамента</p>
            </div>
            <button class="rt-btn-add" @click="addRow">+ строка</button>
        </div>

        <div class="table-wrap">
            <table class="rt-table">
                <!-- Двухуровневая шапка -->
                <thead>
                    <tr class="thead-group">
                        <th rowspan="2" class="col-n">#</th>
                        <!-- Устойчивость -->
                        <th colspan="2" class="group-stability">Устойчивость</th>
                        <!-- Деформации -->
                        <th colspan="2" class="group-deform">Деформации</th>
                        <!-- Коэффициент использования -->
                        <th colspan="2" class="group-kuse">Коэффициент использования</th>
                        <th rowspan="2" class="col-del"></th>
                    </tr>
                    <tr>
                        <!-- Устойчивость -->
                        <th class="col-val">Q,<br>тс</th>
                        <th class="col-val col-comp">Q<sub>u</sub>,<br>тс</th>
                        <!-- Деформации -->
                        <th class="col-val col-comp">β,<br>рад.</th>
                        <th class="col-val">β<sub>u</sub>,<br>рад.</th>
                        <!-- Кисп -->
                        <th class="col-val col-comp">На устойчивость<br>стойки в грунте</th>
                        <th class="col-val col-comp">По<br>деформациям</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in rows" :key="idx">
                        <td class="td-center">{{ idx + 1 }}</td>

                        <!-- Q — ввод -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.q"
                                @input="updateCell(idx, 'q', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Qu — вычисл. -->
                        <td class="td-computed">{{ fmt(row.qU) }}</td>

                        <!-- β — вычисл. -->
                        <td class="td-computed">{{ fmt(row.beta, 4) }}</td>

                        <!-- βu — ввод -->
                        <td>
                            <input
                                type="number" step="0.001" class="rt-input rt-input--sm"
                                :value="row.betaU"
                                @input="updateCell(idx, 'betaU', $event.target.valueAsNumber)"
                                placeholder="0.000"
                            />
                        </td>

                        <!-- Кисп устойчивость — вычисл. -->
                        <td
                            class="td-computed td-kmax"
                            :class="{ 'td-warn': isExceeded(row.kUseStability) }"
                        >
                            {{ fmt(row.kUseStability) }}
                        </td>

                        <!-- Кисп деформации — вычисл. -->
                        <td
                            class="td-computed td-kmax"
                            :class="{ 'td-warn': isExceeded(row.kUseDeformation) }"
                        >
                            {{ fmt(row.kUseDeformation) }}
                        </td>

                        <td class="td-center">
                            <button
                                class="rt-btn-del" title="Удалить строку"
                                :disabled="rows.length <= 1"
                                @click="removeRow(idx)"
                            >×</button>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="8" class="td-empty">Нет строк — нажмите «+ строка»</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<style scoped>
.rt-section {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    overflow: hidden;
}
.rt-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f4f6f8;
    border-bottom: 1px solid #dee2e6;
    gap: 12px;
}
.rt-title    { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1a2533; }
.rt-subtitle { margin: 0; font-size: 11px; color: #6c757d; }
.rt-btn-add {
    flex-shrink: 0; padding: 5px 12px; font-size: 12px; font-weight: 600;
    color: #1976d2; background: #fff; border: 1px solid #1976d2;
    border-radius: 4px; cursor: pointer; white-space: nowrap;
    transition: background 0.15s, color 0.15s;
}
.rt-btn-add:hover { background: #1976d2; color: #fff; }
.table-wrap { overflow-x: auto; }
.rt-table {
    width: 100%; border-collapse: collapse; font-size: 13px; background: #fff;
}
.rt-table th, .rt-table td {
    padding: 6px 10px; border: 1px solid #dee2e6;
    vertical-align: middle; white-space: nowrap;
}
.rt-table thead th {
    background: #eef0f3; font-weight: 600; color: #343a40;
    font-size: 12px; text-align: center; border-bottom: 2px solid #ced4da;
    line-height: 1.3;
}

/* Групповые заголовки — цветные метки */
.group-stability {
    background: #e8f5e9 !important;
    color: #2e7d32 !important;
    border-bottom: 2px solid #a5d6a7 !important;
    font-size: 11px;
    letter-spacing: 0.02em;
}
.group-deform {
    background: #e3f2fd !important;
    color: #1565c0 !important;
    border-bottom: 2px solid #90caf9 !important;
    font-size: 11px;
    letter-spacing: 0.02em;
}
.group-kuse {
    background: #fce4ec !important;
    color: #880e4f !important;
    border-bottom: 2px solid #f48fb1 !important;
    font-size: 11px;
    letter-spacing: 0.02em;
}

.rt-table tbody tr:hover { background: #f8f9fa; }
.col-n   { width: 30px; }
.col-val { min-width: 90px; text-align: center; }
.col-del { width: 32px; }
.td-center { text-align: center; }
.td-computed {
    text-align: center; font-family: 'Courier New', monospace;
    font-size: 12px; color: #1565c0; background: #f0f7ff; font-weight: 600;
}
.td-warn { background: #fff3cd !important; color: #856404 !important; }
.td-empty {
    text-align: center; padding: 14px;
    color: #6c757d; font-style: italic; font-size: 12px;
}
.rt-input {
    padding: 4px 6px; border: 1px solid #ced4da; border-radius: 3px;
    font-size: 13px; font-family: 'Courier New', monospace;
    color: #212529; background: #fff; box-sizing: border-box;
    transition: border-color 0.15s;
}
.rt-input:focus { outline: none; border-color: #1976d2; box-shadow: 0 0 0 2px rgba(25,118,210,0.15); }
.rt-input--sm { width: 90px; }
.rt-btn-del {
    width: 24px; height: 24px; padding: 0; font-size: 14px; line-height: 1;
    color: #dc3545; background: transparent; border: 1px solid #dc3545;
    border-radius: 3px; cursor: pointer; transition: background 0.15s, color 0.15s;
}
.rt-btn-del:hover:not(:disabled) { background: #dc3545; color: #fff; }
.rt-btn-del:disabled { opacity: 0.35; cursor: not-allowed; }
</style>
