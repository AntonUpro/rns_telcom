<script setup>
import {ref} from 'vue';
import EquipmentManager from "./component/Equipment/EquipmentManager.vue";
import TowerTotalDataManager from "./component/TowerTotalData/TowerTotalDataManager.vue";
import PlatformSectionManager from "./component/Platform/PlatformSectionManager.vue";
import UnsavedChangesModal from "./component/shared/UnsavedChangesModal.vue";
import {isTabDirty, clearDirty} from "./component/shared/useUnsavedChanges.js";

const props = defineProps({
    calculationId: {
        type: Number,
        required: true,
    },
});

const TAB_STORAGE_KEY = `rns_active_tower_tab_${props.calculationId}`;
const activeTab = ref(sessionStorage.getItem(TAB_STORAGE_KEY) || 'initial');

const setActiveTab = (tab) => {
    if (tab === activeTab.value) return;

    if (isTabDirty(activeTab.value)) {
        pendingTab.value = tab;
        showUnsavedModal.value = true;
        return;
    }

    switchTab(tab);
};

const switchTab = (tab) => {
    activeTab.value = tab;
    sessionStorage.setItem(TAB_STORAGE_KEY, tab);
};

const handleModalSave = async () => {
    showUnsavedModal.value = false;
    const target = pendingTab.value;
    pendingTab.value = null;

    const tabRef = tabRefMap[activeTab.value];
    if (!tabRef?.value?.save) {
        if (target) switchTab(target);
        return;
    }

    const ok = await tabRef.value.save();
    if (ok && target) {
        switchTab(target);
    }
    // если ok === false — остаёмся на вкладке, дочерний компонент уже показал свой alert()/message об ошибке
};

const handleModalDiscard = () => {
    clearDirty(activeTab.value);
    showUnsavedModal.value = false;
    const target = pendingTab.value;
    pendingTab.value = null;
    if (target) switchTab(target);
};

const handleModalCancel = () => {
    showUnsavedModal.value = false;
    pendingTab.value = null;
};

const totalDataRef = ref(null);
const equipmentRef = ref(null);
const platformRef = ref(null);

const tabRefMap = {
    initial: totalDataRef,
    'wind-equipment': equipmentRef,
    'wind-tower': platformRef,
};

const showUnsavedModal = ref(false);
const pendingTab = ref(null);
</script>

<template>
    <div class="concrete-pillar-calc">
        <div class="page-header">
            <h1>Расчет башни на ветровую нагрузку</h1>
            <p class="page-subtitle">
                Расчет башни по СП 20.13330.2016 "Нагрузки и воздействия"
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
                    @click="setActiveTab('wind-tower')"
                    :class="['tab-btn', { active: activeTab === 'wind-tower' }]"
                >
                    3. Ветер на опору
                </button>
            </div>

            <!-- Таб 1: Исходные данные -->
            <div v-if="activeTab === 'initial'" class="tab-content active">
                <TowerTotalDataManager
                    ref="totalDataRef"
                    :calculation-id="calculationId"
                />
            </div>

            <!-- Таб 2: Ветер на оборудование -->
            <div v-if="activeTab === 'wind-equipment'" class="tab-content active">
                <EquipmentManager
                    ref="equipmentRef"
                    :calculation-id="calculationId"
                    :editable="true"
                />
            </div>

            <!-- Таб 3: Ветер на опору -->
            <div v-if="activeTab === 'wind-tower'" class="tab-content active">
                <PlatformSectionManager
                    ref="platformRef"
                    :calculation-id="calculationId"
                />
            </div>
        </div>

        <UnsavedChangesModal
            :visible="showUnsavedModal"
            @save="handleModalSave"
            @discard="handleModalDiscard"
            @cancel="handleModalCancel"
        />
    </div>
</template>
