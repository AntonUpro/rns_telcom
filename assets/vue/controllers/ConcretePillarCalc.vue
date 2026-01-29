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
                    @click="setActiveTab('wind-pillar')"
                    :class="['tab-btn', { active: activeTab === 'wind-pillar' }]"
                >
                    2. Ветер: столб и коммуникации
                </button>
                <button
                    @click="setActiveTab('wind-equipment')"
                    :class="['tab-btn', { active: activeTab === 'wind-equipment' }]"
                >
                    3. Ветер на оборудование
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
                            <div class="form-group compact-group">
                                <label>Широта:</label>
                                <div class="input-with-unit">
                                    <input
                                        type="number"
                                        v-model.number="formData.latitude"
                                        class="form-calculation-control compact-input"
                                        step="0.000001"
                                    />
                                    <span class="unit">°</span>
                                </div>
                            </div>
                            <div class="form-group compact-group">
                                <label>Долгота:</label>
                                <div class="input-with-unit">
                                    <input
                                        type="number"
                                        v-model.number="formData.longitude"
                                        class="form-calculation-control compact-input"
                                        step="0.000001"
                                    />
                                    <span class="unit">°</span>
                                </div>
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

                <!-- Секция 4: Материалы -->
                <div class="data-section compact-section">
                    <div
                        class="section-header"
                        @click="toggleSection('materials')"
                        :class="{ active: openedSections.materials }"
                    >
                        <h3>4. Материалы</h3>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="section-content" :class="{ active: openedSections.materials }">
                        <div class="form-grid compact-grid">
                            <div class="form-group compact-group">
                                <label>Класс бетона:</label>
                                <select v-model="formData.concreteClass" class="form-calculation-control compact-input">
                                    <option value="B15">B15</option>
                                    <option value="B20">B20</option>
                                    <option value="B25">B25</option>
                                    <option value="B30">B30</option>
                                    <option value="B35">B35</option>
                                    <option value="B40">B40</option>
                                </select>
                            </div>
                            <div class="form-group compact-group">
                                <label>Класс арматуры:</label>
                                <select v-model="formData.reinforcementClass" class="form-calculation-control compact-input">
                                    <option value="A240">A240</option>
                                    <option value="A400">A400</option>
                                    <option value="A500">A500</option>
                                    <option value="A600">A600</option>
                                </select>
                            </div>
                            <div class="form-group compact-group">
                                <label>Диаметр арматуры:</label>
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
                            <div class="form-group compact-group">
                                <label>Количество стержней:</label>
                                <input
                                    type="number"
                                    v-model.number="formData.reinforcementCount"
                                    class="form-calculation-control compact-input"
                                    step="1"
                                    min="4"
                                    max="20"
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
            </div>

            <!-- Остальные табы (ветровые нагрузки) остаются без изменений -->
            <div v-if="activeTab === 'wind-equipment'" class="tab-content active">
                <div class="data-section compact-section">
                    <div
                        class="section-header"
                        @click="toggleSection('equipmentList')"
                        :class="{ active: openedSections.equipmentList }"
                    >
                        <h3>Оборудование на столбе</h3>
                        <span class="toggle-icon">+</span>
                    </div>

                    <div class="section-content" :class="{ active: openedSections.equipmentList }">
                        <!-- Форма поиска оборудования -->
                        <div class="equipment-search">
                            <div class="search-container">
                                <input
                                    type="text"
                                    v-model="equipmentSearchQuery"
                                    @input="searchEquipment"
                                    class="form-calculation-control compact-input"
                                    placeholder="Начните вводить марку или модель оборудования..."
                                />
                                <div class="search-loading" v-if="isSearching">
                                    <span>Поиск...</span>
                                </div>
                            </div>

                            <!-- Результаты поиска -->
                            <div v-if="searchResults.length > 0" class="search-results">
                                <div
                                    v-for="equipment in searchResults"
                                    :key="equipment.id"
                                    class="search-result-item"
                                    @click="addEquipmentToList(equipment)"
                                >
                                    <div class="equipment-info">
                                        <strong>{{ equipment.brand }} {{ equipment.model }}</strong>
                                        <span class="equipment-type">{{ getEquipmentTypeName(equipment.type) }}</span>
                                    </div>
                                    <div class="equipment-details">
                                        <span>Габариты: {{ equipment.width }}×{{ equipment.height }}×{{ equipment.depth }} мм</span>
                                        <span>Вес: {{ equipment.weight }} кг</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="equipmentSearchQuery && !isSearching && searchResults.length === 0"
                                 class="search-no-results">
                                Оборудование не найдено
                            </div>
                        </div>

                        <!-- Список добавленного оборудования -->
                        <div class="equipment-list" v-if="formData.equipmentData.length > 0">
                            <h4>Добавленное оборудование:</h4>

                            <div class="equipment-list-items">
                                <div
                                    v-for="(item, index) in formData.equipmentData"
                                    :key="index"
                                    class="equipment-list-item"
                                >
                                    <div class="equipment-item-header">
                                        <div class="equipment-item-info">
                                            <strong>{{ item.brand }} {{ item.model }}</strong>
                                            <span class="equipment-item-type">{{ getEquipmentTypeName(item.type) }}</span>
                                            <span><strong>Габариты:</strong> {{ item.width }}×{{ item.height }}×{{ item.depth }} мм</span>
                                            <span><strong>Вес:</strong> {{ item.weight }} кг</span>
                                            <span><strong>Площадь:</strong> {{ calculateEquipmentArea(item) }} м²</span>
                                        </div>
                                        <button
                                            @click="removeEquipmentFromList(index)"
                                            class="btn-remove-equipment"
                                            title="Удалить оборудование"
                                        >
                                            ×
                                        </button>
                                    </div>

                                    <div class="equipment-item-details">
                                        <div class="form-grid compact-grid">
                                            <div class="form-group compact-group">
                                                <label>Высота установки:</label>
                                                <div class="input-with-unit">
                                                    <input
                                                        type="number"
                                                        v-model.number="item.installationHeight"
                                                        class="form-calculation-control compact-input"
                                                        step="0.1"
                                                        min="0"
                                                    />
                                                    <span class="unit">м</span>
                                                </div>
                                            </div>

                                            <div class="form-group compact-group">
                                                <label>Количество:</label>
                                                <input
                                                    type="number"
                                                    v-model.number="item.quantity"
                                                    class="form-calculation-control compact-input"
                                                    step="1"
                                                    min="1"
                                                />
                                            </div>

                                            <div class="form-group compact-group">
                                                <label>Коэффициент затенения:</label>
                                                <input
                                                    type="number"
                                                    v-model.number="item.shadingCoefficient"
                                                    class="form-calculation-control compact-input"
                                                    step="0.1"
                                                    min="0"
                                                    max="1"
                                                />
                                            </div>
                                        </div>

<!--                                        <div class="equipment-item-specs">-->
<!--                                            <span><strong>Габариты:</strong> {{ item.width }}×{{ item.height }}×{{ item.depth }} мм</span>-->
<!--                                            <span><strong>Вес:</strong> {{ item.weight }} кг</span>-->
<!--                                            <span><strong>Площадь:</strong> {{ calculateEquipmentArea(item) }} м²</span>-->
<!--                                        </div>-->
                                    </div>
                                </div>
                            </div>

                            <div class="equipment-list-summary">
                                <div class="summary-item">
                                    <span>Всего единиц оборудования:</span>
                                    <strong>{{ formData.equipmentCount }}</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Суммарная площадь:</span>
                                    <strong>{{ totalEquipmentArea.toFixed(2) }} м²</strong>
                                </div>
                                <div class="summary-item">
                                    <span>Суммарный вес:</span>
                                    <strong>{{ totalEquipmentWeight.toFixed(1) }} кг</strong>
                                </div>
                            </div>
                        </div>
                        <div v-else class="equipment-empty-state">
                            <p>Оборудование еще не добавлено. Начните поиск выше.</p>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="section-actions" style="margin-top: 2rem">
                            <button
                                @click="saveEquipmentData"
                                class="btn-save-small"
                                :disabled="isSavingEquipment"
                            >
                                {{ isSavingEquipment ? 'Сохранение...' : 'Сохранить список оборудования' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="activeTab === 'wind-equipment'" class="tab-content active">
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
            <button @click="saveCalculation" class="btn-action btn-save" :disabled="isLoading">
                Сохранить расчет
            </button>
            <button @click="exportToPDF" class="btn-action btn-secondary" :disabled="!calculationResults">
                Экспорт в PDF
            </button>
            <button @click="exportToWord" class="btn-action btn-secondary" :disabled="!calculationResults">
                Экспорт в Word
            </button>
        </div>
    </div>
</template>

<script setup>
import {ref, reactive, computed, onMounted} from 'vue';

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

const openedSections = reactive({
    general: true,
    climate: true,
    strengthening: true,
    materials: true,
    equipmentList: true,
});

// console.log(calculationId)

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
    reinforcementDiameter: 12,
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

const calculationResults = ref(null);
const isLoading = ref(false);
const isSaving = ref(false);
const savedCalculations = ref([]);

// Состояния для оборудования
const equipmentSearchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const isSavingEquipment = ref(false);
const searchTimeout = ref(null);

// Методы
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

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

// Оборудование
// watch(() => formData.equipmentData, (newValue) => {
//     formData.equipmentCount = newValue.reduce((sum, item) => sum + (item.quantity || 1), 0);
// }, { deep: true });

// Методы для работы с оборудованием
const searchEquipment = () => {
    clearTimeout(searchTimeout.value);

    if (!equipmentSearchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;

    searchTimeout.value = setTimeout(async () => {
        try {
            const response = await fetch('/api/v1/equipment/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    query: equipmentSearchQuery.value,
                    limit: 10
                }),
            });

            if (!response.ok) {
                throw new Error('Ошибка поиска оборудования');
            }

            const dataResponse = await response.json();

            if (dataResponse.success) {
                searchResults.value = dataResponse.data;
            } else {
                searchResults.value = [];
                console.error('Ошибка при поиске оборудования:', dataResponse.error);
            }
        } catch (error) {
            console.error('Ошибка поиска оборудования:', error);
            searchResults.value = [];
        } finally {
            isSearching.value = false;
        }
    }, 500);
};

const addEquipmentToList = (equipment) => {
    // Проверяем, нет ли уже такого оборудования в списке
    const existingIndex = formData.equipmentData.findIndex(
        item => item.id === equipment.id
    );

    if (existingIndex === -1) {
        formData.equipmentData.push({
            id: equipment.id,
            brand: equipment.brand,
            model: equipment.model,
            type: equipment.type,
            width: equipment.width,
            height: equipment.height,
            depth: equipment.depth,
            weight: equipment.weight,
            installationHeight: 0,
            quantity: 1,
            shadingCoefficient: 0.8,
            aerodynamicCoefficient: 1.4
        });
    } else {
        // Если оборудование уже есть, увеличиваем количество
        formData.equipmentData[existingIndex].quantity += 1;
    }

    // Очищаем поиск
    equipmentSearchQuery.value = '';
    searchResults.value = [];
};

const removeEquipmentFromList = (index) => {
    formData.equipmentData.splice(index, 1);
};

const getEquipmentTypeName = (type) => {
    const types = {
        'rls': 'РЛС',
        'rrl': 'РРЛ',
        'panel': 'Панельная',
        'dish': 'Тарелка',
        'other': 'Другое'
    };
    return types[type] || type;
};

const calculateEquipmentArea = (equipment) => {
    // Переводим мм в метры и вычисляем площадь
    const widthM = equipment.width / 1000;
    const heightM = equipment.height / 1000;
    return (widthM * heightM).toFixed(2);
};

// Вычисляемые свойства для сводки
const totalEquipmentArea = computed(() => {
    return formData.equipmentData.reduce((sum, item) => {
        const widthM = item.width / 1000;
        const heightM = item.height / 1000;
        return sum + (widthM * heightM * item.quantity);
    }, 0);
});

const totalEquipmentWeight = computed(() => {
    return formData.equipmentData.reduce((sum, item) => {
        return sum + (item.weight * item.quantity);
    }, 0);
});

// Метод сохранения данных оборудования
const saveEquipmentData = async () => {
    if (formData.equipmentData.length === 0) {
        alert('Добавьте оборудование перед сохранением');
        return;
    }

    try {
        isSavingEquipment.value = true;

        const response = await fetch('/api/v1/save/equipment-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                calculationId: props.calculationId,
                equipmentData: formData.equipmentData.map(item => ({
                    equipmentId: item.id,
                    brand: item.brand,
                    model: item.model,
                    type: item.type,
                    width: item.width,
                    height: item.height,
                    depth: item.depth,
                    weight: item.weight,
                    installationHeight: item.installationHeight,
                    quantity: item.quantity,
                    shadingCoefficient: item.shadingCoefficient,
                    aerodynamicCoefficient: item.aerodynamicCoefficient
                }))
            }),
        });

        if (!response.ok) {
            throw new Error('Ошибка сохранения данных оборудования');
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error('Ошибка сохранения данных оборудования: ' + data.error);
        }

        alert('Данные оборудования сохранены');
    } catch (error) {
        console.error('Ошибка сохранения оборудования:', error);
        alert('Не удалось сохранить данные оборудования');
    } finally {
        isSavingEquipment.value = false;
    }
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

        calculationResults.value = {
            pillar: windLoadPillar,
            equipment: windLoadEquipment,
            platform: windLoadPlatform,
            total: totalLoad,
            calculationDate: new Date().toISOString(),
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
        results: calculationResults.value,
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

const exportToWord = () => {
    alert('Экспорт в Word (функция в разработке)');
};

const loadSavedCalculations = () => {
    const saved = localStorage.getItem('concretePillarCalculations');
    if (saved) {
        savedCalculations.value = JSON.parse(saved);
    }
};

const loadCalculation = (calc) => {
    Object.assign(formData, calc.data || {});
    calculationResults.value = calc.results || null;
    alert(`Расчет "${calc.name}" загружен`);
};

onMounted(() => {
    fetchGeneralData();
    loadSavedCalculations();
});
</script>
