<script setup>
import {onMounted, reactive, ref} from "vue";

const props = defineProps({
    calculationId: {
        type: Number,
        required: true
    },
});

const openedSections = reactive({
    general: true,
    climate: true,
    strengthening: true,
    materials: true,
    equipmentList: true,
});

const defaultValues = reactive({
    cableDiameterValues: {
        rrl: 0,
        optical: 0,
        power: 0,
        otherEquipment: 0
    },
    constructionValues: {
        cableTray: 0,
        ladder: 0,
        cableTrayBottom: 0,
        ladderBottom: 0
    },
    shadingCoefficients: {
        rrl: 0,
        panelAntenna: 0,
        radioBlocks: 0,
        cableTray: 0,
        otherEquipment: 0
    }
});

const formData = reactive({
    // Общие данные (новые поля)
    objectCode: '',
    stationNumber: '',
    region: '',
    locality: '',
    customer: '',
    amsType: '',
    amsHeight: 0,
    inspectionDate: new Date().toISOString().split('T')[0],
    latitude: 0,
    longitude: 0,

    // Климатические условия
    windRegion: 'I',
    terrainType: 'A',
    snowRegion: 'II',
    iceRegion: 'I',

    // Данные по столбу
    pillarStamp: 'СК26.1-1',
    bottomMarkPillar: 0,
    strengtheningExist: false,

    strengtheningGeometry: 'square',
    strengtheningWidth: 0,
    strengtheningHeight: 0,
    allowedMoment: 0,

    cableDiameterValues: {
        rrl: 0,
        optical: 0,
        power: 0,
        otherEquipment: 0
    },

    constructionValues: {
        cableTray: 0,
        ladder: 0,
        cableTrayBottom: 0,
        ladderBottom: 0
    },

    shadingCoefficients: {
        rrl: 0,
        panelAntenna: 0,
        radioBlocks: 0,
        cableTray: 0,
        otherEquipment: 0
    },

    // Материалы
    concreteClass: 'B25',
    reinforcementClass: 'A400',
    reinforcementDiameter: 0.1,
    reinforcementCount: 8,
    concreteDensity: 2500,
    workingConditions: 1.0,
});

const isLoading = ref(false);
const isSaving = ref(false);
const savedCalculations = ref([]);

// Dynamic data from API
const windRegions = ref([]);
const terrainTypes = ref([]);
const snowRegions = ref([]);
const icingRegions = ref([]);
const pillarTypes = ref([]);

// Методы
const toggleSection = (section) => {
    openedSections[section] = !openedSections[section];
};

const onStrengtheningToggle = () => {
    // Если усиление отключено, очищаем поля
    if (!formData.strengtheningExist) {
        formData.strengtheningGeometry = 'square';
        formData.strengtheningWidth = 0;
        formData.strengtheningHeight = 0;
        formData.allowedMoment = 0;
    }
};

const fetchGeneralData = async () => {
    try {
        isLoading.value = true;

        const response = await fetch('/api/v1/calculation/' + props.calculationId, {
            method: 'GET',
        });

        if (!response.ok) {
            throw new Error('Ошибка при загрузке данных');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error('Не удалось загрузить общие данные по расчету. Ошибка: ' + data.error);
        }
        // Заполняем поля данными из Bitrix
        formData.objectCode = data.data.totalData?.objectCode || '';
        formData.stationNumber = data.data.totalData?.stationNumber || '';
        formData.region = data.data.totalData?.region || '';
        formData.locality = data.data.totalData?.locality || '';
        formData.customer = data.data.totalData?.customer || '';
        formData.amsType = data.data.totalData?.amsType || '';
        formData.amsHeight = data.data.totalData?.amsHeight || '';
        formData.inspectionDate = data.data.totalData?.inspectionDate || '';
        formData.latitude = data.data.totalData?.latitude || '';
        formData.longitude = data.data.totalData?.longitude || '';

        formData.windRegion = data.data.climateData?.windRegion || '';
        formData.terrainType = data.data.climateData?.terrainType || '';
        formData.snowRegion = data.data.climateData?.snowRegion || '';
        formData.iceRegion = data.data.climateData?.iceRegion || '';

        formData.pillarStamp = data.data.pillarData?.pillarStamp || '';
        formData.bottomMarkPillar = data.data.pillarData?.bottomMarkPillar || 0;
        formData.strengtheningExist = data.data.pillarData?.strengtheningExist || false;

        formData.strengtheningGeometry = data.data.pillarData?.strengtheningGeometry || null;
        formData.strengtheningWidth = data.data.pillarData?.strengtheningWidth || null;
        formData.strengtheningHeight = data.data.pillarData?.strengtheningHeight || null;
        formData.allowedMoment = data.data.pillarData?.allowedMoment || null;

        formData.cableDiameterValues = {
            rrl: data.data.defaultValues.cableDiameterValues?.rrl || defaultValues.cableDiameterValues?.rrl,
            optical: data.data.defaultValues.cableDiameterValues?.optical || defaultValues.cableDiameterValues?.optical,
            power: data.data.defaultValues.cableDiameterValues?.power || defaultValues.cableDiameterValues?.power,
            otherEquipment: data.data.defaultValues.cableDiameterValues?.otherEquipment || defaultValues.cableDiameterValues?.otherEquipment
        };
        formData.constructionValues = {
            cableTray: data.data.defaultValues.constructionValues?.cableTray || defaultValues.constructionValues?.cableTray,
            ladder: data.data.defaultValues.constructionValues?.ladder || defaultValues.constructionValues?.ladder,
            cableTrayBottom: data.data.defaultValues.constructionValues?.cableTrayBottom || defaultValues.constructionValues?.cableTrayBottom,
            ladderBottom: data.data.defaultValues.constructionValues?.ladderBottom || defaultValues.constructionValues?.ladderBottom
        };
        formData.shadingCoefficients = {
            rrl: data.data.defaultValues.shadingCoefficients?.rrl || defaultValues.shadingCoefficients?.rrl,
            panelAntenna: data.data.defaultValues.shadingCoefficients?.panelAntenna || defaultValues.shadingCoefficients?.panelAntenna,
            radioBlocks: data.data.defaultValues.shadingCoefficients?.radioBlocks || defaultValues.shadingCoefficients?.radioBlocks,
            cableTray: data.data.defaultValues.shadingCoefficients?.cableTray || defaultValues.shadingCoefficients?.cableTray,
            otherEquipment: data.data.defaultValues.shadingCoefficients?.otherEquipment || defaultValues.shadingCoefficients?.otherEquipment
        };

    } catch (error) {
        console.error('Ошибка загрузки данных по расчету:', error);
        alert('Не удалось загрузить данные по расчету');
    } finally {
        isLoading.value = false;
    }
};

const fetchPillarTotalInfo = async () => {
    try {
        isLoading.value = true;

        const response = await fetch('/api/v1/information/total-info-pillar', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Ошибка при загрузке справочных данных');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error('Не удалось загрузить справочные данные. Ошибка: ' + data.error);
        }

        // Populate dynamic data
        windRegions.value = Object.entries(data.data.windRegions || {}).map(([value, label]) => ({
            value,
            label
        }));

        terrainTypes.value = Object.entries(data.data.terrainTypes || {}).map(([value, label]) => ({
            value,
            label
        }));

        snowRegions.value = Object.entries(data.data.snowRegions || {}).map(([value, label]) => ({
            value,
            label
        }));

        icingRegions.value = Object.entries(data.data.icingRegions || {}).map(([value, label]) => ({
            value,
            label
        }));

        pillarTypes.value = Object.entries(data.data.pillarTypes || {}).map(([value, label]) => ({
            value,
            label
        }));

        defaultValues.cableDiameterValues.rrl = data.data.defaultValues.cableDiameterValues.rrl;
        defaultValues.cableDiameterValues.optical = data.data.defaultValues.cableDiameterValues.optical;
        defaultValues.cableDiameterValues.power = data.data.defaultValues.cableDiameterValues.power;
        defaultValues.cableDiameterValues.otherEquipment = data.data.defaultValues.cableDiameterValues.otherEquipment;

        defaultValues.constructionValues.cableTray = data.data.defaultValues.constructionValues.cableTray;
        defaultValues.constructionValues.ladder = data.data.defaultValues.constructionValues.ladder;
        defaultValues.constructionValues.cableTrayBottom = data.data.defaultValues.constructionValues.cableTrayBottom;
        defaultValues.constructionValues.ladderBottom = data.data.defaultValues.constructionValues.ladderBottom;


        defaultValues.shadingCoefficients.rrl = data.data.defaultValues.shadingCoefficients.rrl;
        defaultValues.shadingCoefficients.panelAntenna = data.data.defaultValues.shadingCoefficients.panelAntenna;
        defaultValues.shadingCoefficients.radioBlocks = data.data.defaultValues.shadingCoefficients.radioBlocks;
        defaultValues.shadingCoefficients.cableTray = data.data.defaultValues.shadingCoefficients.cableTray;
        defaultValues.shadingCoefficients.otherEquipment = data.data.defaultValues.shadingCoefficients.otherEquipment;
    } catch (error) {
        console.error('Ошибка загрузки справочных данных:', error);
        alert('Не удалось загрузить справочные данные');
    } finally {
        isLoading.value = false;
    }
};

const loadFromBitrix = async () => {
    try {
        isLoading.value = true;

        // Здесь будет асинхронный запрос к вашему API Bitrix
        const response = await fetch('/api/v1/bitrix/load-object-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({objectCode: formData.objectCode}),
        });

        if (!response.ok) {
            throw new Error('Ошибка загрузки данных из Bitrix');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error('Не удалось загрузить данные из Bitrix. Ошибка: ' + data.error);
        }

        // Заполняем поля данными из Bitrix
        formData.stationNumber = data.data.stationNumber || '';
        formData.region = data.data.region || '';
        formData.locality = data.data.locality || '';
        formData.customer = data.data.customer || '';
        formData.amsType = data.data.amsType || '';
        formData.amsHeight = data.data.amsHeight || 0;
        formData.inspectionDate = data.data.inspectionDate || new Date().toISOString().split('T')[0];
        // Широта и долгота НЕ заполняются из Bitrix

    } catch (error) {
        console.error('Ошибка загрузки из Bitrix:', error);
        alert('Не удалось загрузить данные из Bitrix');
    } finally {
        isLoading.value = false;
    }
};

const saveGeneralData = async () => {
    try {
        isSaving.value = true;

        const response = await fetch('/api/v1/save/general-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                calculationId: props.calculationId,
                totalData: {
                    objectCode: formData.objectCode,
                    stationNumber: formData.stationNumber,
                    region: formData.region,
                    locality: formData.locality,
                    customer: formData.customer,
                    amsType: formData.amsType,
                    amsHeight: formData.amsHeight,
                    inspectionDate: formData.inspectionDate,
                    latitude: formData.latitude,
                    longitude: formData.longitude,
                },
                climateData: {
                    windRegion: formData.windRegion,
                    terrainType: formData.terrainType,
                    snowRegion: formData.snowRegion,
                    iceRegion: formData.iceRegion,
                },
                pillarData: {
                    pillarStamp: formData.pillarStamp,
                    strengtheningExist: formData.strengtheningExist,
                    strengtheningGeometry: formData.strengtheningGeometry || null,
                    strengtheningWidth: formData.strengtheningWidth || null,
                    strengtheningHeight: formData.strengtheningHeight || null,
                    allowedMoment: formData.allowedMoment || null,
                },
                defaultValues: {
                    cableDiameterValues: formData.cableDiameterValues,
                    constructionValues: formData.constructionValues,
                    shadingCoefficients: formData.shadingCoefficients,
                },
            }),
        });

        if (!response.ok) {
            throw new Error('Ошибка сохранения данных');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error('Ошибка сохранения данных. Ошибка: ' + data.error);
        }

        alert('Общие данные сохранены');
    } catch (error) {
        console.error('Ошибка сохранения:', error);
        alert('Не удалось сохранить данные');
    } finally {
        isSaving.value = false;
    }
};

const loadSavedCalculations = () => {
    const saved = localStorage.getItem('concretePillarCalculations');
    if (saved) {
        savedCalculations.value = JSON.parse(saved);
    }
};

onMounted(async () => {
    await fetchPillarTotalInfo();
    await fetchGeneralData();
    // loadSavedCalculations();
});
</script>

<template>
    <div v-if="isLoading" class="loading-overlay">
        <div class="loading-spinner"></div>
        <p>Загрузка справочных данных...</p>
    </div>
    <div class="data-section compact-section">
        <div
            class="section-header"
            @click="toggleSection('general')"
            :class="{ active: openedSections.general }"
        >
            <h3>1. Общие данные</h3>
            <span class="toggle-icon">+</span>
        </div>
        <div class="section-content" :class="{ active: openedSections.general }">
            <div class="form-grid compact-grid">
                <div class="form-group compact-group">
                    <label>Шифр объекта:</label>
                    <div class="input-with-button">
                        <input
                            type="text"
                            v-model="formData.objectCode"
                            class="form-calculation-control compact-input"
                            placeholder="Введите шифр объекта"
                        />
                        <button @click="loadFromBitrix" class="btn-bitrix" title="Загрузить из Bitrix">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>№ базовой станции:</label>
                    <input
                        type="text"
                        v-model="formData.stationNumber"
                        class="form-calculation-control compact-input"
                        placeholder="Введите номер"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Регион, область:</label>
                    <input
                        type="text"
                        v-model="formData.region"
                        class="form-calculation-control compact-input"
                        placeholder="Введите регион"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Населённый пункт:</label>
                    <input
                        type="text"
                        v-model="formData.locality"
                        class="form-calculation-control compact-input"
                        placeholder="Введите населенный пункт"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Заказчик:</label>
                    <input
                        type="text"
                        v-model="formData.customer"
                        class="form-calculation-control compact-input"
                        placeholder="Введите заказчика"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Тип АМС:</label>
                    <input
                        type="text"
                        v-model="formData.amsType"
                        class="form-calculation-control compact-input"
                        placeholder="Введите тип АМС"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Высота АМС:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.amsHeight"
                            class="form-calculation-control compact-input"
                            step="0.1"
                            min="0"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Дата обследования:</label>
                    <input
                        type="date"
                        v-model="formData.inspectionDate"
                        class="form-calculation-control compact-input"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Секция 2: Климатические условия -->
    <div class="data-section compact-section">
        <div
            class="section-header"
            @click="toggleSection('climate')"
            :class="{ active: openedSections.climate }"
        >
            <h3>2. Климатические условия</h3>
            <span class="toggle-icon">+</span>
        </div>
        <div class="section-content" :class="{ active: openedSections.climate }">
            <div class="form-grid compact-grid">
                <div class="form-group compact-group">
                    <label>Ветровой район:</label>
                    <select v-model="formData.windRegion" class="form-calculation-control compact-input">
                        <option v-for="region in windRegions" :key="region.value" :value="region.value">
                            {{ region.label }}
                        </option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Тип местности:</label>
                    <select v-model="formData.terrainType" class="form-calculation-control compact-input">
                        <option v-for="type in terrainTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Снеговой район:</label>
                    <select v-model="formData.snowRegion" class="form-calculation-control compact-input">
                        <option v-for="region in snowRegions" :key="region.value" :value="region.value">
                            {{ region.label }}
                        </option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Гололедный район:</label>
                    <select v-model="formData.iceRegion" class="form-calculation-control compact-input">
                        <option v-for="region in icingRegions" :key="region.value" :value="region.value">
                            {{ region.label }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Секция 3: Усиление столба -->
    <div class="data-section compact-section">
        <div
            class="section-header"
            @click="toggleSection('strengthening')"
            :class="{ active: openedSections.strengthening }"
        >
            <h3>3. Данные по столбу</h3>
            <span class="toggle-icon">+</span>
        </div>
        <div class="section-content" :class="{ active: openedSections.strengthening }">
            <div class="form-grid compact-grid">
                <div class="form-group compact-group">
                    <label>Выберите марку столба:</label>
                    <select v-model="formData.pillarStamp" class="form-calculation-control compact-input">
                        <option v-for="pillar in pillarTypes" :key="pillar.value" :value="pillar.value">
                            {{ pillar.label }}
                        </option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Отметка низа столба:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.bottomMarkPillar"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="-3"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group checkbox-group">
                    <div class="checkbox-container">
                        <input
                            type="checkbox"
                            v-model.bool="formData.strengtheningExist"
                            id="strengtheningExist"
                            class="strengthening-checkbox"
                            @change="onStrengtheningToggle"
                        />
                        <label for="strengtheningExist" class="checkbox-label">
                            Есть ли усиление
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-grid compact-grid" style="margin-top: 1.5rem;">
                <div class="form-group compact-group">
                    <label>Геометрия усиления:</label>
                    <select v-model="formData.strengtheningGeometry"
                            class="form-calculation-control compact-input"
                            :disabled="!formData.strengtheningExist"
                    >
                        <option value="square">Квадрат</option>
                        <option value="circle">Круг</option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Ширина:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.strengtheningWidth"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0"
                            :disabled="!formData.strengtheningExist"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Высота:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.strengtheningHeight"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0"
                            :disabled="!formData.strengtheningExist"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Допустимый момент:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.allowedMoment"
                            class="form-calculation-control compact-input"
                            step="0.1"
                            min="0"
                            :disabled="!formData.strengtheningExist"
                        />
                        <span class="unit">кН·м</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Секция 4: Значения по умолчанию -->
    <div class="data-section compact-section">
        <div
            class="section-header"
            @click="toggleSection('materials')"
            :class="{ active: openedSections.materials }"
        >
            <h3>4. Значения по умолчанию</h3>
            <span class="toggle-icon">+</span>
        </div>
        <div class="section-content" :class="{ active: openedSections.materials }">
            <!-- Row 1: Диаметр кабельной трассы -->
            <div class="default-values-row">
                <h4 class="row-title">Диаметр кабельной трассы</h4>
                <div class="dynamic-inputs-grid">
                    <div class="form-group compact-group">
                        <label>РРЛ:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.cableDiameterValues.rrl"
                                class="form-calculation-control compact-input"
                                step="1"
                                min="0"
                                max="100"
                            />
                            <span class="unit">мм</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Оптика:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.cableDiameterValues.optical"
                                class="form-calculation-control compact-input"
                                step="1"
                                min="0"
                                max="100"
                            />
                            <span class="unit">мм</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Питание:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.cableDiameterValues.power"
                                class="form-calculation-control compact-input"
                                step="1"
                                min="0"
                                max="100"
                            />
                            <span class="unit">мм</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Другое оборудование:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.cableDiameterValues.otherEquipment"
                                class="form-calculation-control compact-input"
                                step="1"
                                min="0"
                                max="100"
                            />
                            <span class="unit">мм</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: А, м² на метр погонный -->
            <div class="default-values-row">
                <h4 class="row-title">А, м² на метр погонный</h4>
                <div class="dynamic-inputs-grid">
                    <div class="form-group compact-group">
                        <label>Кабельрост, м²:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.constructionValues.cableTray"
                                class="form-calculation-control compact-input"
                                step="0.01"
                                min="0"
                                max="1"
                            />
                            <span class="unit">м²</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Лестница, м²:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.constructionValues.ladder"
                                class="form-calculation-control compact-input"
                                step="0.01"
                                min="0"
                                max="1"
                            />
                            <span class="unit">м²</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Низ кабельроста, м:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.constructionValues.cableTrayBottom"
                                class="form-calculation-control compact-input"
                                step="0.1"
                                min="0"
                                max="40"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                    <div class="form-group compact-group">
                        <label>Низ лестницы, м:</label>
                        <div class="input-with-unit">
                            <input
                                type="number"
                                v-model.number="formData.constructionValues.ladderBottom"
                                class="form-calculation-control compact-input"
                                step="0.1"
                                min="0"
                                max="40"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Коэффициенты затенения -->
            <div class="default-values-row">
                <h4 class="row-title">Коэффициенты затенения</h4>
                <div class="dynamic-inputs-grid">
                    <div class="form-group compact-group">
                        <label>РРЛ:</label>
                        <input
                            type="number"
                            v-model.number="formData.shadingCoefficients.rrl"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0.1"
                            max="1"
                        />
                    </div>
                    <div class="form-group compact-group">
                        <label>Панельная антенна:</label>
                        <input
                            type="number"
                            v-model.number="formData.shadingCoefficients.panelAntenna"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0.1"
                            max="1"
                        />
                    </div>
                    <div class="form-group compact-group">
                        <label>Радиоблоки:</label>
                        <input
                            type="number"
                            v-model.number="formData.shadingCoefficients.radioBlocks"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0.1"
                            max="1"
                        />
                    </div>
                    <div class="form-group compact-group">
                        <label>Кабельная трасса:</label>
                        <input
                            type="number"
                            v-model.number="formData.shadingCoefficients.cableTray"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0.1"
                            max="1"
                        />
                    </div>
                    <div class="form-group compact-group">
                        <label>Другое оборудование:</label>
                        <input
                            type="number"
                            v-model.number="formData.shadingCoefficients.otherEquipment"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0.1"
                            max="1"
                        />
                    </div>
                </div>
            </div>

            <!-- Additional default values -->
            <!--            <div class="form-grid compact-grid">-->
            <!--                <div class="form-group compact-group">-->
            <!--                    <label>Плотность бетона:</label>-->
            <!--                    <div class="input-with-unit">-->
            <!--                        <input-->
            <!--                            type="number"-->
            <!--                            v-model.number="formData.concreteDensity"-->
            <!--                            class="form-calculation-control compact-input"-->
            <!--                            step="50"-->
            <!--                            min="2200"-->
            <!--                            max="2600"-->
            <!--                        />-->
            <!--                        <span class="unit">кг/м³</span>-->
            <!--                    </div>-->
            <!--                </div>-->
            <!--                <div class="form-group compact-group">-->
            <!--                    <label>Коэф. условий работы:</label>-->
            <!--                    <input-->
            <!--                        type="number"-->
            <!--                        v-model.number="formData.workingConditions"-->
            <!--                        class="form-calculation-control compact-input"-->
            <!--                        step="0.05"-->
            <!--                        min="0.9"-->
            <!--                        max="1.1"-->
            <!--                    />-->
            <!--                </div>-->
            <!--            </div>-->
        </div>
    </div>
    <div class="section-actions" style="margin-top: 2rem">
        <button @click="saveGeneralData" class="btn-save-small" :disabled="isSaving">
            {{ isSaving ? 'Сохранение...' : 'Сохранить' }}
        </button>
    </div>
</template>

<style scoped>
/* Стили для значений по умолчанию */
.default-values-row {
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 6px;
    background-color: #fafafa;
}

.row-title {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #97afbc;
    font-size: 1.1rem;
    font-weight: 600;
}

.dynamic-inputs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loading-overlay p {
    color: #6c757d;
    font-size: 1rem;
    margin: 0;
}

@media (min-width: 1200px) {
    .dynamic-inputs-grid {
        grid-template-columns: repeat(4, 1fr);
    }

    .default-values-row:nth-child(3) .dynamic-inputs-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (max-width: 1199px) and (min-width: 768px) {
    .dynamic-inputs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .dynamic-inputs-grid {
        grid-template-columns: 1fr;
    }

    .default-values-row {
        padding: 0.75rem;
    }

    .row-title {
        font-size: 1rem;
    }
}
</style>
