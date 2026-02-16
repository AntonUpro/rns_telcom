<script setup>
import {ref, reactive, computed, onMounted} from 'vue';
import EquipmentManager from "./component/Equipment/EquipmentManager.vue";
import TotalDataManager from "./component/TotalData/TotalDataManager.vue";

const props = defineProps({
    user: {
        type: Object,
        required: false,
        default: () => ({fullName: ''}),
    },
    calculationId: {
        type: Number,
        required: true,
    },
});

// Состояние
const activeTab = ref('initial');

const isLoading = ref(false);

const setActiveTab = (tab) => {
    activeTab.value = tab;
};

// Остальные методы из исходного кода остаются без изменений
const calculateAll = async () => {
    isLoading.value = true;
    try {
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const windLoadPillar = calculateWindLoadPillar();
        const windLoadEquipment = calculateWindLoadEquipment();
        const windLoadPlatform = calculateWindLoadPlatform();

        const totalLoad = {
            totalWindForce:
                windLoadPillar.windForce +
                windLoadEquipment.totalForce +
                windLoadPlatform.totalForce,
            totalMoment:
                windLoadPillar.moment +
                windLoadEquipment.totalMoment +
                windLoadPlatform.totalMoment,
            safetyFactor: 1.5,
            isSafe: true,
        };

    } catch (error) {
        console.error('Calculation error:', error);
        alert('Ошибка при расчете');
    } finally {
        isLoading.value = false;
    }
};

const calculateWindLoadPillar = () => {
    const Wm = windPressureCalculated.value;
    const A = sectionPerimeter.value * formData.height;
    const F = Wm * A;
    const M = (F * formData.height) / 2;

    const V = sectionArea.value * formData.height;
    const G = V * formData.concreteDensity * 9.81;

    return {
        windPressure: Wm,
        windForce: F,
        moment: M,
        selfWeight: G,
        area: A,
    };
};

const calculateWindLoadEquipment = () => {
    let totalForce = 0;
    let totalMoment = 0;

    const Wm = windPressureCalculated.value;

    formData.equipmentData.forEach((equipment) => {
        const A = equipment.width * equipment.height;
        const F = Wm * A * equipment.coefficient * (equipment.quantity || 1);
        const M = F * equipment.height;

        totalForce += F;
        totalMoment += M;
    });

    return {
        totalForce,
        totalMoment,
        equipmentCount: formData.equipmentCount,
    };
};

const calculateWindLoadPlatform = () => {
    const Wm = windPressureCalculated.value;
    const platformArea = formData.platformWidth * formData.platformLength;
    const fenceArea = sectionPerimeter.value * formData.fenceHeight;

    const platformForce = Wm * platformArea * 0.8;
    const fenceForce = Wm * fenceArea * 1.2;

    return {
        platformForce,
        fenceForce,
        totalForce: platformForce + fenceForce,
        totalMoment:
            (platformForce * formData.platformHeight) / 2 +
            fenceForce * (formData.platformHeight + formData.fenceHeight / 2),
    };
};

const windPressureCalculated = computed(() => {
    const wo = getWindPressureByRegion(formData.windRegion);
    const k = getTerrainCoefficient(formData.terrainType, formData.heightAboveGround);
    const c = formData.aerodynamicCoefficient;
    const gamma = formData.gustFactor;

    return wo * k * c * gamma;
});

const sectionArea = computed(() => {
    if (formData.sectionType === 'rectangle') {
        return formData.width * formData.thickness;
    } else {
        const R = formData.diameter / 2;
        const r = R - formData.wallThickness;
        return Math.PI * (R * R - r * r);
    }
});

const sectionPerimeter = computed(() => {
    if (formData.sectionType === 'rectangle') {
        return 2 * (formData.width + formData.thickness);
    } else {
        return Math.PI * formData.diameter;
    }
});

const getWindPressureByRegion = (region) => {
    const pressures = {
        I: 230,
        II: 300,
        III: 380,
        IV: 480,
        V: 600,
        VI: 730,
        VII: 850,
    };
    return pressures[region] || 300;
};

const getTerrainCoefficient = (type, height) => {
    const h = Math.max(height, 10);
    const map = {
        A: 0.75 * Math.pow(h / 10, 0.2),
        B: 0.65 * Math.pow(h / 10, 0.2),
        C: 0.4 * Math.pow(h / 10, 0.2),
    };
    return map[type] || map.B;
};

const saveCalculation = () => {
    const calculation = {
        id: Date.now(),
        name: formData.objectCode || `Расчет от ${new Date().toLocaleDateString('ru-RU')}`,
        date: new Date().toISOString(),
        data: JSON.parse(JSON.stringify(formData)),
    };

    savedCalculations.value.unshift(calculation);
    localStorage.setItem(
        'concretePillarCalculations',
        JSON.stringify(savedCalculations.value)
    );

    alert('Расчет сохранен');
};

const exportToPDF = () => {
    window.print();
};


const loadSavedCalculations = () => {
    const saved = localStorage.getItem('concretePillarCalculations');
    if (saved) {
        savedCalculations.value = JSON.parse(saved);
    }
};
</script>

<template>
    <div class="concrete-pillar-calc">
        <div class="page-header">
            <h1>Расчет ЖБ столба на ветровую нагрузку</h1>
            <p class="page-subtitle">
                Расчет железобетонного столба по СП 20.13330.2016 "Нагрузки и воздействия"
            </p>
        </div>

        <!-- Навигация по разделам -->
        <div class="calc-tabs">
            <div class="tabs-header">
                <button
                    @click="setActiveTab('initial')"
                    :class="['tab-btn', { active: activeTab === 'initial' }]"
                >
                    1. Исходные данные
                </button>
                <button
                    @click="setActiveTab('wind-equipment')"
                    :class="['tab-btn', { active: activeTab === 'wind-equipment' }]"
                >
                    2. Ветер на оборудование
                </button>
                <button
                    @click="setActiveTab('wind-pillar')"
                    :class="['tab-btn', { active: activeTab === 'wind-pillar' }]"
                >
                    3. Ветер: столб и коммуникации
                </button>
                <button
                    @click="setActiveTab('wind-platform')"
                    :class="['tab-btn', { active: activeTab === 'wind-platform' }]"
                >
                    4. Ветер на площадку
                </button>
                <button
                    @click="setActiveTab('total-load')"
                    :class="['tab-btn', { active: activeTab === 'total-load' }]"
                >
                    5. Суммарная нагрузка
                </button>
            </div>

            <!-- Таб 1: Исходные данные -->
            <div v-if="activeTab === 'initial'" class="tab-content active">
                <!-- Секция 1: Общие данные -->
                <TotalDataManager
                    :calculation-id="calculationId"
                />
            </div>
            <!-- Остальные табы (ветровые нагрузки) остаются без изменений -->
            <div v-if="activeTab === 'wind-equipment'" class="tab-content active">
                <EquipmentManager
                    :calculation-id="calculationId"
                    :editable="true"
                />
            </div>

            <div v-if="activeTab === 'wind-pillar'" class="tab-content active">
                <!-- Содержимое таба 3 из исходного кода -->
                <div class="form-grid">
                    <!-- ... ваш существующий код для ветра на оборудование ... -->
                </div>
            </div>

            <div v-if="activeTab === 'wind-platform'" class="tab-content active">
                <!-- Содержимое таба 4 из исходного кода -->
                <div class="form-grid">
                    <!-- ... ваш существующий код для ветра на площадку ... -->
                </div>
            </div>

            <div v-if="activeTab === 'total-load'" class="tab-content active">
                <!-- Содержимое таба 5 из исходного кода -->
                <div class="form-grid">
                    <!-- ... ваш существующий код для суммарной нагрузки ... -->
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="calc-actions">
            <button @click="calculateAll" class="btn-action btn-action-calc" :disabled="isLoading">
                {{ isLoading ? 'Выполняется расчет...' : 'Выполнить расчет' }}
            </button>
<!--            <button @click="saveCalculation" class="btn-action btn-save" :disabled="isLoading">-->
<!--                Сохранить расчет-->
<!--            </button>-->
            <button @click="exportToPDF" class="btn-action btn-secondary">
                Экспорт в PDF
            </button>
<!--            <button @click="exportToWord" class="btn-action btn-secondary" :disabled="!calculationResults">-->
<!--                Экспорт в Word-->
<!--            </button>-->
        </div>
    </div>
</template>
