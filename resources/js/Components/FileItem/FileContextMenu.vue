<template>
    <ul tabindex="1" ref="subMenu" class="file-submenu">
        <li v-if="showTrash" @click="handleTrash">Trash</li>
        <li @click="handleRename">Rename</li>
        <li @click="handlePermissions">Permissions</li>
        <li v-if="!showTrash" @click="handleRestore">Restore</li>
    </ul>
</template>
<style scoped>
.file-submenu {
    @apply absolute py-2 shadow bg-base-300 rounded-md w-52 opacity-0 flex flex-col pointer-events-none;
    top: 50%;
    left: 40px;
    li {
        @apply hover:bg-base-100 cursor-pointer p-3;
    }
}
:focus {
    @apply opacity-100 z-10 pointer-events-auto;
}
</style>
<script setup>
import { ref } from "vue";
const props = defineProps({
    handleRename: {
        type: Function,
    },
    handlePermissions: {
        type: Function,
    },
    handleTrash: {
        type: Function,
    },
    handleRestore: {
        type: Function,
    },
    showTrash: {
        type: Boolean,
    },
});

const subMenu = ref(null);
const focus = () => {
    subMenu.value.focus();
};
const blur = () => {
    subMenu.value.blur();
};
defineExpose({ focus, blur });
</script>
