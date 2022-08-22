<template>
    <div
        class="folder"
        :class="{
            'folder-desktop': props.mode === 'desktop',
            'folder-list': props.mode === 'list',
        }"
        @contextmenu="$event.preventDefault()"
        @dragover="$event.preventDefault()"
        @dragenter="$event.preventDefault()"
        @drop="handleDrop()"
        @dblclick="browseFolder()"
    >
        <folder-icon
            :icon-size="iconSize"
            :showExtension="props.mode === 'desktop'"
            :folderUrl="folder.url"
            @mousedown="handleClick($event)"
        />
        <div class="folder-name">{{ foldername }}</div>
        <folder-detail
            v-if="props.mode === 'list'"
            :folder="folder"
            :handleRename="handleRename"
            :handleTrash="handleTrash"
        />

        <folder-context-menu
            ref="subMenu"
            :handleRename="handleRename"
            :handleTrash="handleTrash"
            :handlePermissions="handlePermissions"
        />
    </div>
</template>
<style scoped>
.folder {
    @apply relative cursor-pointer;
}
.folder:-moz-drag-over {
    @apply bg-primary/50;
}
.folder-desktop {
    @apply sm:w-1/12 w-1/3 flex flex-col items-center;
}
.folder-list {
    @apply w-full flex flex-row items-center space-x-2;
    .folder-name {
        @apply w-6/12;
    }
}
</style>
<script setup>
import { computed, ref, watch } from "vue";
import FolderIcon from "./Folder.vue";
import FolderDetail from "./FolderDetail.vue";
import FolderContextMenu from "./FolderContextMenu.vue";
import { useEventsBus } from "@/utils";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    folder: {
        type: Object,
        required: true,
    },
    mode: {
        type: String,
        default: "desktop",
    },
    handleRename: {
        type: Function,
    },
    handlePermissions: {
        type: Function,
    },
    handleTrash: {
        type: Function,
    },
    moveFileToFolder: {
        type: Function,
    },
});

const handleRename = () => {
    props.handleRename(props.folder, "folder");
    subMenu.value.blur();
};

const handleTrash = () => {
    props.handleTrash(props.folder, "folder");
    subMenu.value.blur();
};

const subMenu = ref(null);

const handleClick = (event) => {
    if (props.mode !== "desktop") {
        return;
    }
    if (event.which == 3) {
        event.preventDefault();
        event.stopPropagation();
    }
    subMenu.value.focus();
};
const iconSize = computed({
    get() {
        return props.mode === "desktop" ? "3x" : "2x";
    },
});

const foldername = computed({
    get() {
        let ret = props.folder.name;
        let nameLimit = props.mode === "desktop" ? 14 : 30;
        if (ret.length > nameLimit) {
            ret = ret.split(".")[0].substr(0, nameLimit - 1) + "~";
        }
        return ret;
    },
});

const selectedFile = ref(null);
const handleDrop = () => {
    props.moveFileToFolder(selectedFile.value, props.folder.id);
};

const setFile = (data) => {
    selectedFile.value = data[0];
};

const { bus } = useEventsBus();
watch(() => bus.value.get("select_file"), setFile);

const browseFolder = () => {
    // TODO browse to the folder
    Inertia.get(route("folders.viewFiles", props.folder.id));
};
</script>