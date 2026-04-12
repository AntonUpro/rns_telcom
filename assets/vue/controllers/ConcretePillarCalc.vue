<script setup>
import {ref, reactive, computed, onMounted} from 'vue';
import EquipmentManager from "./component/Equipment/EquipmentManager.vue";
import TotalDataManager from "./component/TotalData/TotalDataManager.vue";
import WindEquipmentManager from "./component/WindEquipment/WindEquipmentManager.vue";
import PlatformSectionManager from "./component/Platform/PlatformSectionManager.vue";
import TotalLoadManager from "./component/TotalLoad/TotalLoadManager.vue";
import SoftwareCalculationManager from "./component/SoftwareCalculation/SoftwareCalculationManager.vue";
import DocumentsForm from "./component/SoftwareCalculation/DocumentsForm.vue";

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

const isDownloading = ref(false);

const downloadReport = async () => {
    isDownloading.value = true;
    try {
        const response = await fetch(`/api/v1/calculation/${props.calculationId}/report`);

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.error ?? 'Ошибка генерации файла');
        }

        const blob = await response.blob();
        const url  = window.URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = `calculation_${props.calculationId}.docx`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        alert('Ошибка: ' + error.message);
    } finally {
        isDownloading.value = false;
    }
};


const loadSavedCalculations = () => {
    const saved = localStorage.getItem('concretePillarCalculations');
    if (saved) {
        savedCalculations.value = JSON.parse(saved);
    }
};

// Platform sections data
const platformSections = ref([]);

// Handler for platform sections changes
const handlePlatformSectionsChanged = (sections) => {
    platformSections.value = sections;
};

// Computed properties for platform summary
const totalPlatformHeight = computed(() => {
    return platformSections.value.reduce((sum, section) => sum + (parseFloat(section.height) || 0), 0).toFixed(2);
});

const totalElementsCount = computed(() => {
    return platformSections.value.reduce((sum, section) => sum + (section.elements?.length || 0), 0);
});

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
                <button
                    @click="setActiveTab('software-calc')"
                    :class="['tab-btn', { active: activeTab === 'software-calc' }]"
                >
                    6. Программный расчет
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
                <WindEquipmentManager
                    :calculation-id="calculationId"
                    :editable="true"
                />
            </div>

            <div v-if="activeTab === 'wind-platform'" class="tab-content active">
                <PlatformSectionManager
                    :calculation-id="calculationId"
                />

                <div class="platform-summary" v-if="platformSections.length > 0">
                    <h3>Сводка по секциям</h3>
                    <div class="summary-stats">
                        <div class="stat-card">
                            <div class="stat-label">Всего секций</div>
                            <div class="stat-value">{{ platformSections.length }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Общая высота</div>
                            <div class="stat-value">{{ totalPlatformHeight }} м</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Всего элементов</div>
                            <div class="stat-value">{{ totalElementsCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'total-load'" class="tab-content active">
                <TotalLoadManager
                    :calculation-id="calculationId"
                />
            </div>

            <div v-if="activeTab === 'software-calc'" class="tab-content active">
                <!-- Форма документов -->
                <DocumentsForm :calculation-id="calculationId" />
                <!-- Форма скринов из лиры -->
                <SoftwareCalculationManager
                    :calculation-id="calculationId"
                />
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
            <button @click="downloadReport" class="btn-action btn-secondary" :disabled="isDownloading">
                {{ isDownloading ? 'Формируется файл...' : 'Скачать расчет' }}
            </button>
<!--            <button @click="exportToWord" class="btn-action btn-secondary" :disabled="!calculationResults">-->
<!--                Экспорт в Word-->
<!--            </button>-->
        </div>
    </div>
</template>

<style scoped>
/* Existing styles remain unchanged */

/* Platform summary styles */
.platform-summary {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 1.5rem;
    margin-top: 1rem;
}

.platform-summary h3 {
    margin: 0 0 1rem 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 1rem;
    text-align: center;
    border: 1px solid #e9ecef;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .summary-stats {
        grid-template-columns: 1fr;
    }

    .stat-value {
        font-size: 1.2rem;
    }
}
</style>
