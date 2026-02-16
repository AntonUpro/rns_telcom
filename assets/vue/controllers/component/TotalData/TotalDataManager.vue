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
    strengtheningExist: false,

    strengtheningGeometry: 'square',
    strengtheningWidth: 0,
    strengtheningHeight: 0,
    allowedMoment: 0,

    // Материалы
    concreteClass: 'B25',
    reinforcementClass: 'A400',
    reinforcementDiameter: 0.1,
    reinforcementCount: 8,
    concreteDensity: 2500,
    workingConditions: 1.0,

    // Остальные поля для ветровых нагрузок
    height: 10,
    sectionType: 'rectangle',
    width: 0.4,
    thickness: 0.4,
    diameter: 0.5,
    wallThickness: 0.1,
    windPressure: 300,
    heightAboveGround: 0,
    aerodynamicCoefficient: 1.4,
    gustFactor: 1.4,
    equipmentCount: 0,
    equipmentData: [],
    platformWidth: 0,
    platformLength: 0,
    platformHeight: 0,
    fenceHeight: 0,
});

const isLoading = ref(false);
const isSaving = ref(false);
const savedCalculations = ref([]);

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
        formData.strengtheningExist = data.data.pillarData?.strengtheningExist || false;

        formData.strengtheningGeometry = data.data.pillarData?.strengtheningGeometry || null;
        formData.strengtheningWidth = data.data.pillarData?.strengtheningWidth || null;
        formData.strengtheningHeight = data.data.pillarData?.strengtheningHeight || null;
        formData.allowedMoment = data.data.pillarData?.allowedMoment || null;

    } catch (error) {
        console.error('Ошибка загрузки данных по расчету:', error);
        alert('Не удалось загрузить данные по расчету');
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

onMounted(() => {
    fetchGeneralData();
    // loadSavedCalculations();
});
</script>

<template>
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
                        <option value="I">I (Wo = 230 Па)</option>
                        <option value="II">II (Wo = 300 Па)</option>
                        <option value="III">III (Wo = 380 Па)</option>
                        <option value="IV">IV (Wo = 480 Па)</option>
                        <option value="V">V (Wo = 600 Па)</option>
                        <option value="VI">VI (Wo = 730 Па)</option>
                        <option value="VII">VII (Wo = 850 Па)</option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Тип местности:</label>
                    <select v-model="formData.terrainType" class="form-calculation-control compact-input">
                        <option value="A">A - Открытые побережья, водоемы</option>
                        <option value="B">B - Полевые, сельские местности</option>
                        <option value="C">C - Городские территории</option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Снеговой район:</label>
                    <select v-model="formData.snowRegion" class="form-calculation-control compact-input">
                        <option value="I">I (Sg = 800 Па)</option>
                        <option value="II">II (Sg = 1200 Па)</option>
                        <option value="III">III (Sg = 1800 Па)</option>
                        <option value="IV">IV (Sg = 2400 Па)</option>
                        <option value="V">V (Sg = 3200 Па)</option>
                        <option value="VI">VI (Sg = 4000 Па)</option>
                        <option value="VII">VII (Sg = 5600 Па)</option>
                    </select>
                </div>
                <div class="form-group compact-group">
                    <label>Гололедный район:</label>
                    <select v-model="formData.iceRegion" class="form-calculation-control compact-input">
                        <option value="I">I (b = 5 мм)</option>
                        <option value="II">II (b = 10 мм)</option>
                        <option value="III">III (b = 15 мм)</option>
                        <option value="IV">IV (b = 20 мм)</option>
                        <option value="V">V (b = 25 мм)</option>
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
            <div class="strengthening-row">
                <div class="form-group compact-group">
                    <label>Выберите марку столба:</label>
                    <select v-model="formData.pillarStamp" class="form-calculation-control compact-input">
                        <option value="СК26-1.1">СК26.1-1.1</option>
                        <option value="СК26-1.2">СК26.1-1.2</option>
                        <option value="СК26-1.3">СК26.1-1.3</option>
                        <option value="СК26-5.1">СК26.1-5.1</option>
                        <option value="СК26-6.1">СК26.1-6.1</option>
                    </select>
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
            <div class="form-grid compact-grid">
                <!-- Площадь лестницы-->
                <div class="form-group compact-group">
                    <label>Площадь лестницы на 1 метр погонный:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.reinforcementDiameter"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0"
                            max="1"
                        />
                        <span class="unit">м. кв.</span>
                    </div>
                </div>
                <!-- Отметка низа лестницы-->
                <div class="form-group compact-group">
                    <label>Отметка низа лестницы:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.reinforcementDiameter"
                            class="form-calculation-control compact-input"
                            step="0.1"
                            min="0"
                            max="40"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <!-- Площадь кабельроста-->
                <div class="form-group compact-group">
                    <label>Площадь кабельроста на 1 метр погонный:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.reinforcementDiameter"
                            class="form-calculation-control compact-input"
                            step="0.01"
                            min="0"
                            max="1"
                        />
                        <span class="unit">м. кв.</span>
                    </div>
                </div>
                <!-- Отметка низа кабельроста-->
                <div class="form-group compact-group">
                    <label>Отметка низа кабельроста:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.reinforcementDiameter"
                            class="form-calculation-control compact-input"
                            step="0.1"
                            min="0"
                            max="40"
                        />
                        <span class="unit">м</span>
                    </div>
                </div>
                <!-- Отметка низа кабельной трассы-->
                <div class="form-group compact-group">
                    <label>Отметка низа кабельной трассы:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.reinforcementDiameter"
                            class="form-calculation-control compact-input"
                            step="1"
                            min="6"
                            max="40"
                        />
                        <span class="unit">мм</span>
                    </div>
                </div>
                <!-- Коэффициент затенения РРЛ-->
                <div class="form-group compact-group">
                    <label>Коэффициент затенения РРЛ:</label>
                    <input
                        type="number"
                        v-model.number="formData.reinforcementCount"
                        class="form-calculation-control compact-input"
                        step="0.01"
                        min="0.1"
                        max="1"
                    />
                </div>
                <!-- Коэффициент затенения панельных антенн-->
                <div class="form-group compact-group">
                    <label>Коэффициент затенения панельных антенн:</label>
                    <input
                        type="number"
                        v-model.number="formData.reinforcementCount"
                        class="form-calculation-control compact-input"
                        step="0.01"
                        min="0.1"
                        max="1"
                    />
                </div>
                <!-- Коэффициент затенения радиоблоков антенн-->
                <div class="form-group compact-group">
                    <label>Коэффициент затенения радиоблоков:</label>
                    <input
                        type="number"
                        v-model.number="formData.reinforcementCount"
                        class="form-calculation-control compact-input"
                        step="0.01"
                        min="0.1"
                        max="1"
                    />
                </div>
                <!-- Коэффициент затенения другого оборудования-->
                <div class="form-group compact-group">
                    <label>Коэффициент затенения другого оборудования:</label>
                    <input
                        type="number"
                        v-model.number="formData.reinforcementCount"
                        class="form-calculation-control compact-input"
                        step="0.01"
                        min="0.1"
                        max="1"
                    />
                </div>
                <!-- Коэффициент затенения кабельной трассы-->
                <div class="form-group compact-group">
                    <label>Коэффициент затенения кабельной трассы:</label>
                    <input
                        type="number"
                        v-model.number="formData.reinforcementCount"
                        class="form-calculation-control compact-input"
                        step="0.01"
                        min="0.1"
                        max="1"
                    />
                </div>
                <div class="form-group compact-group">
                    <label>Плотность бетона:</label>
                    <div class="input-with-unit">
                        <input
                            type="number"
                            v-model.number="formData.concreteDensity"
                            class="form-calculation-control compact-input"
                            step="50"
                            min="2200"
                            max="2600"
                        />
                        <span class="unit">кг/м³</span>
                    </div>
                </div>
                <div class="form-group compact-group">
                    <label>Коэф. условий работы:</label>
                    <input
                        type="number"
                        v-model.number="formData.workingConditions"
                        class="form-calculation-control compact-input"
                        step="0.05"
                        min="0.9"
                        max="1.1"
                    />
                </div>
            </div>
        </div>
    </div>
    <div class="section-actions" style="margin-top: 2rem">
        <button @click="saveGeneralData" class="btn-save-small" :disabled="isSaving">
            {{ isSaving ? 'Сохранение...' : 'Сохранить' }}
        </button>
    </div>
</template>

<style scoped>

</style>
