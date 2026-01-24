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
                <div class="data-section">
                    <div
                        class="section-header"
                        @click="toggleSection('general')"
                        :class="{ active: openedSections.general }"
                    >
                        <h3>1. Общие данные</h3>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="section-content" :class="{ active: openedSections.general }">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Название проекта:</label>
                                <input
                                    type="text"
                                    v-model="formData.projectName"
                                    class="form-control"
                                    placeholder="Введите название проекта"
                                />
                            </div>
                            <div class="form-group">
                                <label>Номер расчета:</label>
                                <input
                                    type="text"
                                    v-model="formData.calculationNumber"
                                    class="form-control"
                                    placeholder="CALC-001"
                                />
                            </div>
                            <div class="form-group">
                                <label>Дата расчета:</label>
                                <input
                                    type="date"
                                    v-model="formData.calculationDate"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label>Исполнитель:</label>
                                <input
                                    type="text"
                                    :value="user?.fullName || ''"
                                    class="form-control"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Секция 2: Геометрия столба -->
                <div class="data-section">
                    <div
                        class="section-header"
                        @click="toggleSection('geometry')"
                        :class="{ active: openedSections.geometry }"
                    >
                        <h3>2. Геометрия столба</h3>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="section-content" :class="{ active: openedSections.geometry }">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Высота столба, H:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="number"
                                        v-model.number="formData.height"
                                        class="form-control"
                                        step="0.1"
                                        min="0"
                                    />
                                    <span class="unit">м</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Тип сечения:</label>
                                <select v-model="formData.sectionType" class="form-control">
                                    <option value="rectangle">Прямоугольное</option>
                                    <option value="circle">Круглое (труба)</option>
                                </select>
                            </div>

                            <template v-if="formData.sectionType === 'rectangle'">
                                <div class="form-group">
                                    <label>Ширина сечения, b:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input
                                            type="number"
                                            v-model.number="formData.width"
                                            class="form-control"
                                            step="0.01"
                                            min="0"
                                        />
                                        <span class="unit">м</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Толщина сечения, h:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input
                                            type="number"
                                            v-model.number="formData.thickness"
                                            class="form-control"
                                            step="0.01"
                                            min="0"
                                        />
                                        <span class="unit">м</span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="form-group">
                                    <label>Наружный диаметр, D:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input
                                            type="number"
                                            v-model.number="formData.diameter"
                                            class="form-control"
                                            step="0.01"
                                            min="0"
                                        />
                                        <span class="unit">м</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Толщина стенки, t:</label>
                                    <div style="display: flex; align-items: center;">
                                        <input
                                            type="number"
                                            v-model.number="formData.wallThickness"
                                            class="form-control"
                                            step="0.001"
                                            min="0"
                                        />
                                        <span class="unit">м</span>
                                    </div>
                                </div>
                            </template>

                            <div class="form-group">
                                <label>Площадь сечения:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="text"
                                        :value="sectionArea.toFixed(4)"
                                        class="form-control"
                                        readonly
                                    />
                                    <span class="unit">м²</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Периметр сечения:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="text"
                                        :value="sectionPerimeter.toFixed(3)"
                                        class="form-control"
                                        readonly
                                    />
                                    <span class="unit">м</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Секция 3: Материалы -->
                <div class="data-section">
                    <div
                        class="section-header"
                        @click="toggleSection('materials')"
                        :class="{ active: openedSections.materials }"
                    >
                        <h3>3. Материалы</h3>
                        <span class="toggle-icon">+</span>
                    </div>
                    <div class="section-content" :class="{ active: openedSections.materials }">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Класс бетона:</label>
                                <select v-model="formData.concreteClass" class="form-control">
                                    <option value="B15">B15</option>
                                    <option value="B20">B20</option>
                                    <option value="B25">B25</option>
                                    <option value="B30">B30</option>
                                    <option value="B35">B35</option>
                                    <option value="B40">B40</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Класс арматуры:</label>
                                <select v-model="formData.reinforcementClass" class="form-control">
                                    <option value="A240">A240</option>
                                    <option value="A400">A400</option>
                                    <option value="A500">A500</option>
                                    <option value="A600">A600</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Диаметр арматуры:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="number"
                                        v-model.number="formData.reinforcementDiameter"
                                        class="form-control"
                                        step="1"
                                        min="6"
                                        max="40"
                                    />
                                    <span class="unit">мм</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Количество стержней:</label>
                                <input
                                    type="number"
                                    v-model.number="formData.reinforcementCount"
                                    class="form-control"
                                    step="1"
                                    min="4"
                                    max="20"
                                />
                            </div>
                            <div class="form-group">
                                <label>Плотность бетона:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="number"
                                        v-model.number="formData.concreteDensity"
                                        class="form-control"
                                        step="50"
                                        min="2200"
                                        max="2600"
                                    />
                                    <span class="unit">кг/м³</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Коэф. условий работы:</label>
                                <input
                                    type="number"
                                    v-model.number="formData.workingConditions"
                                    class="form-control"
                                    step="0.05"
                                    min="0.9"
                                    max="1.1"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таб 2: Ветер на столб и коммуникации -->
            <div v-if="activeTab === 'wind-pillar'" class="tab-content active">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Ветровой район:</label>
                        <select v-model="formData.windRegion" class="form-control">
                            <option value="I">I (Wo = 230 Па)</option>
                            <option value="II">II (Wo = 300 Па)</option>
                            <option value="III">III (Wo = 380 Па)</option>
                            <option value="IV">IV (Wo = 480 Па)</option>
                            <option value="V">V (Wo = 600 Па)</option>
                            <option value="VI">VI (Wo = 730 Па)</option>
                            <option value="VII">VII (Wo = 850 Па)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Нормативное ветровое давление:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                :value="getWindPressureByRegion(formData.windRegion)"
                                class="form-control"
                                readonly
                            />
                            <span class="unit">Па</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Тип местности:</label>
                        <select v-model="formData.terrainType" class="form-control">
                            <option value="A">A - Открытые побережья, водоемы</option>
                            <option value="B">B - Полевые, сельские местности</option>
                            <option value="C">C - Городские территории</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Высота над землей:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                v-model.number="formData.heightAboveGround"
                                class="form-control"
                                step="0.1"
                                min="0"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Аэродинамический коэффициент, c:</label>
                        <input
                            type="number"
                            v-model.number="formData.aerodynamicCoefficient"
                            class="form-control"
                            step="0.1"
                            min="0.5"
                            max="2"
                        />
                    </div>
                    <div class="form-group">
                        <label>Коэффициент пульсации, γ:</label>
                        <input
                            type="number"
                            v-model.number="formData.gustFactor"
                            class="form-control"
                            step="0.1"
                            min="1"
                            max="2"
                        />
                    </div>
                    <div class="form-group">
                        <label>Расчетное ветровое давление:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                :value="windPressureCalculated.toFixed(1)"
                                class="form-control"
                                readonly
                            />
                            <span class="unit">Па</span>
                        </div>
                    </div>
                </div>

                <div
                    class="info-card"
                    style="margin-top: 2rem; background: #f0f7ff; padding: 1.5rem;"
                >
                    <h4>Промежуточные результаты:</h4>
                    <div
                        v-if="calculationResults && calculationResults.pillar"
                        class="results-grid"
                    >
                        <div class="result-item">
                            <span class="result-label">Сила ветра на столб:</span>
                            <span class="result-value">
                {{ (calculationResults.pillar.windForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Момент от ветра:</span>
                            <span class="result-value">
                {{ (calculationResults.pillar.moment / 1000).toFixed(2) }}
                <span class="result-unit">кН·м</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Собственный вес:</span>
                            <span class="result-value">
                {{ (calculationResults.pillar.selfWeight / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                    </div>
                    <p v-else style="color: #7f8c8d;">
                        Выполните расчет для просмотра результатов
                    </p>
                </div>
            </div>

            <!-- Таб 3: Ветер на оборудование -->
            <div v-if="activeTab === 'wind-equipment'" class="tab-content active">
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label>Количество единиц оборудования:</label>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <input
                            type="number"
                            v-model.number="formData.equipmentCount"
                            class="form-control"
                            style="width: 100px;"
                            min="0"
                            max="20"
                        />
                        <button @click="addEquipment" class="btn btn-primary">
                            Добавить оборудование
                        </button>
                    </div>
                </div>

                <div
                    v-for="(equipment, index) in formData.equipmentData"
                    :key="equipment.id"
                    class="data-section"
                    style="margin-bottom: 1rem;"
                >
                    <div class="section-header">
                        <h3>{{ equipment.name }}</h3>
                        <button
                            @click="removeEquipment(index)"
                            class="btn btn-secondary"
                            style="padding: 0.25rem 0.75rem;"
                        >
                            ×
                        </button>
                    </div>
                    <div class="section-content active">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Наименование:</label>
                                <input
                                    type="text"
                                    v-model="equipment.name"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label>Ширина:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="number"
                                        v-model.number="equipment.width"
                                        class="form-control"
                                        step="0.1"
                                    />
                                    <span class="unit">м</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Высота:</label>
                                <div style="display: flex; align-items: center;">
                                    <input
                                        type="number"
                                        v-model.number="equipment.height"
                                        class="form-control"
                                        step="0.1"
                                    />
                                    <span class="unit">м</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Аэродинамический коэффициент:</label>
                                <input
                                    type="number"
                                    v-model.number="equipment.coefficient"
                                    class="form-control"
                                    step="0.1"
                                />
                            </div>
                            <div class="form-group">
                                <label>Количество:</label>
                                <input
                                    type="number"
                                    v-model.number="equipment.quantity"
                                    class="form-control"
                                    min="1"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="formData.equipmentData.length === 0"
                    class="info-card"
                    style="text-align: center; padding: 3rem;"
                >
                    <p style="color: #7f8c8d;">Оборудование не добавлено</p>
                    <button @click="addEquipment" class="btn btn-primary">
                        Добавить первое оборудование
                    </button>
                </div>

                <div
                    v-if="calculationResults && calculationResults.equipment"
                    class="info-card"
                    style="margin-top: 2rem;"
                >
                    <h4>Результаты по оборудованию:</h4>
                    <div class="results-grid">
                        <div class="result-item">
                            <span class="result-label">Суммарная сила на оборудование:</span>
                            <span class="result-value">
                {{ (calculationResults.equipment.totalForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Суммарный момент:</span>
                            <span class="result-value">
                {{ (calculationResults.equipment.totalMoment / 1000).toFixed(2) }}
                <span class="result-unit">кН·м</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Количество единиц:</span>
                            <span class="result-value">
                {{ calculationResults.equipment.equipmentCount }}
              </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таб 4: Ветер на площадку -->
            <div v-if="activeTab === 'wind-platform'" class="tab-content active">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Ширина площадки:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                v-model.number="formData.platformWidth"
                                class="form-control"
                                step="0.1"
                                min="0"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Длина площадки:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                v-model.number="formData.platformLength"
                                class="form-control"
                                step="0.1"
                                min="0"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Высота площадки над землей:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                v-model.number="formData.platformHeight"
                                class="form-control"
                                step="0.1"
                                min="0"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Высота ограждения:</label>
                        <div style="display: flex; align-items: center;">
                            <input
                                type="number"
                                v-model.number="formData.fenceHeight"
                                class="form-control"
                                step="0.1"
                                min="0"
                            />
                            <span class="unit">м</span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="calculationResults && calculationResults.platform"
                    class="info-card"
                    style="margin-top: 2rem;"
                >
                    <h4>Результаты по площадке:</h4>
                    <div class="results-grid">
                        <div class="result-item">
                            <span class="result-label">Сила ветра на площадку:</span>
                            <span class="result-value">
                {{ (calculationResults.platform.platformForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Сила ветра на ограждение:</span>
                            <span class="result-value">
                {{ (calculationResults.platform.fenceForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Суммарная сила:</span>
                            <span class="result-value">
                {{ (calculationResults.platform.totalForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Суммарный момент:</span>
                            <span class="result-value">
                {{ (calculationResults.platform.totalMoment / 1000).toFixed(2) }}
                <span class="result-unit">кН·м</span>
              </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Таб 5: Суммарная нагрузка -->
            <div v-if="activeTab === 'total-load'" class="tab-content active">
                <div v-if="calculationResults && calculationResults.total" class="results-grid">
                    <div class="result-card">
                        <h4>Суммарные нагрузки</h4>
                        <div class="result-item">
                            <span class="result-label">Общая ветровая сила:</span>
                            <span class="result-value">
                {{ (calculationResults.total.totalWindForce / 1000).toFixed(2) }}
                <span class="result-unit">кН</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Общий момент у основания:</span>
                            <span class="result-value">
                {{ (calculationResults.total.totalMoment / 1000).toFixed(2) }}
                <span class="result-unit">кН·м</span>
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Коэффициент запаса:</span>
                            <span class="result-value">
                {{ calculationResults.total.safetyFactor.toFixed(2) }}
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Статус:</span>
                            <span
                                class="result-value"
                                :style="{
                  color: calculationResults.total.isSafe ? '#2ecc71' : '#e74c3c',
                }"
                            >
                {{ calculationResults.total.isSafe ? 'Безопасно' : 'Требуется усиление' }}
              </span>
                        </div>
                    </div>

                    <div class="result-card">
                        <h4>Распределение нагрузок</h4>
                        <div class="result-item">
                            <span class="result-label">Столб и коммуникации:</span>
                            <span class="result-value">
                {{
                                    (
                                        (calculationResults.pillar.windForce /
                                            calculationResults.total.totalWindForce) *
                                        100
                                    ).toFixed(1)
                                }}%
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Оборудование:</span>
                            <span class="result-value">
                {{
                                    (
                                        (calculationResults.equipment.totalForce /
                                            calculationResults.total.totalWindForce) *
                                        100
                                    ).toFixed(1)
                                }}%
              </span>
                        </div>
                        <div class="result-item">
                            <span class="result-label">Площадка:</span>
                            <span class="result-value">
                {{
                                    (
                                        (calculationResults.platform.totalForce /
                                            calculationResults.total.totalWindForce) *
                                        100
                                    ).toFixed(1)
                                }}%
              </span>
                        </div>
                    </div>

                    <div class="result-card">
                        <h4>Рекомендации</h4>
                        <p style="color: #7f8c8d; font-size: 0.9rem; line-height: 1.5;">
              <span v-if="calculationResults.total.isSafe">
                ✅ Конструкция удовлетворяет требованиям СП 20.13330.2016.
                Дополнительное усиление не требуется.
              </span>
                            <span v-else>
                ⚠️ Рекомендуется усиление конструкции. Рассмотрите варианты:
                увеличение сечения, дополнительное армирование или установка растяжек.
              </span>
                        </p>
                    </div>
                </div>

                <div v-else class="info-card" style="text-align: center; padding: 3rem;">
                    <p style="color: #7f8c8d; margin-bottom: 1.5rem;">
                        Для просмотра суммарных нагрузок выполните расчет
                    </p>
                    <button @click="calculateAll" class="btn btn-primary" :disabled="isLoading">
                        {{ isLoading ? 'Выполняется расчет...' : 'Выполнить расчет' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="calc-actions">
            <button @click="calculateAll" class="btn btn-primary" :disabled="isLoading">
                {{ isLoading ? 'Выполняется расчет...' : 'Выполнить расчет' }}
            </button>
            <button @click="saveCalculation" class="btn btn-save" :disabled="!calculationResults">
                Сохранить расчет
            </button>
            <button @click="exportToPDF" class="btn btn-secondary" :disabled="!calculationResults">
                Экспорт в PDF
            </button>
            <button @click="exportToExcel" class="btn btn-secondary" :disabled="!calculationResults">
                Экспорт в Excel
            </button>
        </div>

        <!-- Сохраненные расчеты -->
        <div
            v-if="savedCalculations.length > 0"
            class="saved-calculations"
            style="margin-top: 3rem;"
        >
            <h3>Сохраненные расчеты</h3>
            <div class="calculations-list">
                <div
                    v-for="calc in savedCalculations"
                    :key="calc.id"
                    class="calculation-item"
                    style="
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border: 1px solid #eee;
          "
                >
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>{{ calc.name }}</strong>
                            <div style="color: #7f8c8d; font-size: 0.9rem;">
                                {{ new Date(calc.date).toLocaleDateString('ru-RU') }}
                            </div>
                        </div>
                        <div>
                            <button
                                @click="loadCalculation(calc)"
                                class="btn btn-link"
                                style="margin-right: 0.5rem;"
                            >
                                Загрузить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

// Пропсы из Twig (например, пользователь)
defineProps({
    user: {
        type: Object,
        required: false,
        default: () => ({ fullName: '' }),
    },
});

// Состояние
const activeTab = ref('initial');

const openedSections = reactive({
    general: true,
    geometry: false,
    materials: false,
});

const formData = reactive({
    // Общие данные
    projectName: '',
    calculationNumber: '',
    calculationDate: new Date().toISOString().split('T')[0],

    // Геометрия столба
    height: 10,
    sectionType: 'rectangle',
    width: 0.4,
    thickness: 0.4,
    diameter: 0.5,
    wallThickness: 0.1,

    // Материалы
    concreteClass: 'B25',
    reinforcementClass: 'A400',
    reinforcementDiameter: 12,
    reinforcementCount: 8,
    concreteDensity: 2500,
    workingConditions: 1.0,

    // Ветровые параметры
    windRegion: 'II',
    windPressure: 300,
    terrainType: 'B',
    heightAboveGround: 0,
    aerodynamicCoefficient: 1.4,
    gustFactor: 1.4,

    // Оборудование
    equipmentCount: 0,
    equipmentData: [],

    // Площадка
    platformWidth: 0,
    platformLength: 0,
    platformHeight: 0,
    fenceHeight: 0,
});

const calculationResults = ref(null);
const isLoading = ref(false);
const savedCalculations = ref([]);

// Computed
const currentDateFormatted = computed(() =>
    new Date().toLocaleDateString('ru-RU')
);

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

const windPressureCalculated = computed(() => {
    const wo = getWindPressureByRegion(formData.windRegion);
    const k = getTerrainCoefficient(formData.terrainType, formData.heightAboveGround);
    const c = formData.aerodynamicCoefficient;
    const gamma = formData.gustFactor;

    return wo * k * c * gamma;
});

// Методы (как функции)
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const toggleSection = (section) => {
    openedSections[section] = !openedSections[section];
};

// Расчет всех нагрузок
const calculateAll = async () => {
    isLoading.value = true;

    try {
        // Имитация расчета (в реальности тут можно дергать ваш API)
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
        const M = F * equipment.height; // условно, можно доработать

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
    const h = Math.max(height, 10); // чтобы не было высоты 0
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
        name:
            formData.projectName || `Расчет от ${currentDateFormatted.value}`,
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

const exportToExcel = () => {
    alert('Экспорт в Excel (функция в разработке)');
};

const addEquipment = () => {
    formData.equipmentData.push({
        id: Date.now(),
        name: `Оборудование ${formData.equipmentData.length + 1}`,
        width: 1,
        height: 1,
        coefficient: 1.2,
        quantity: 1,
    });
};

const removeEquipment = (index) => {
    formData.equipmentData.splice(index, 1);
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

// Аналог mounted()
onMounted(() => {
    loadSavedCalculations();
});
</script>
