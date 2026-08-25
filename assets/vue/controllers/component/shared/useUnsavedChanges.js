import {reactive} from 'vue';

const dirtySections = reactive(new Set());

export function hasUnsavedChanges() {
    return dirtySections.size > 0;
}

export function isTabDirty(sectionKey) {
    return dirtySections.has(sectionKey);
}

export function clearDirty(sectionKey) {
    dirtySections.delete(sectionKey);
}

export function useUnsavedChanges(sectionKey) {
    return {
        markDirty: () => dirtySections.add(sectionKey),
        markClean: () => dirtySections.delete(sectionKey),
    };
}
