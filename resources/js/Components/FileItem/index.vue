<template>
    <div
        class="relative select-all cursor-pointer"
        :class="{
            'file-desktop': props.mode === 'desktop',
            'file-list': props.mode === 'list',
        }"
        @contextmenu="$event.preventDefault()"
        @dragstart="handleDrag($event)"
    >
        <file-icon
            :icon-size="iconSize"
            :extension="file.extension"
            :showExtension="props.mode === 'desktop'"
            :fileUrl="file.url"
            @mousedown="handleClick($event)"
        />
        <div class="file-name">{{ filename }}</div>
        <file-detail
            v-if="props.mode === 'list'"
            :file="file"
            :handleRename="handleRename"
            :handleTrash="handleTrash"
            :handlePermissions="handlePermissions"
            :showTrash="showTrash"
            :handleRestore="handleRestore"
        />

        <file-context-menu
            ref="subMenu"
            :handleRename="handleRename"
            :handleTrash="handleTrash"
            :handlePermissions="handlePermissions"
            :showTrash="showTrash"
            :handleRestore="handleRestore"
        />
    </div>
</template>
<style scoped>
.file-desktop {
    @apply sm:w-1/12 w-1/3 flex flex-col items-center;
}
.file-list {
    @apply w-full flex flex-row items-center space-x-2;
    .file-name {
        @apply w-6/12;
    }
}
</style>
<script setup>
import { computed, ref } from "vue";
import FileIcon from "./FileIcon.vue";
import FileDetail from "./FileDetail.vue";
import FileContextMenu from "./FileContextMenu.vue";
import { useEventsBus } from "@/utils";

const props = defineProps({
    file: {
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
    handleRestore: {
        type: Function,
    },
});

const handleRename = () => {
    props.handleRename(props.file, "file");
    subMenu.value.blur();
};
const handlePermissions = () => {
    props.handlePermissions(props.file);
    subMenu.value.blur();
};

const handleTrash = () => {
    props.handleTrash(props.file, "file");
    subMenu.value.blur();
};

const handleRestore = () => {
    props.handleRestore(props.file, "file");
    subMenu.value.blur();
};
const subMenu = ref(null);

const handleClick = (event) => {
    if (props.mode !== "desktop" || event.buttons !== 2) {
        return;
    }
    event.preventDefault();
    event.stopPropagation();
    subMenu.value.focus();
};
const iconSize = computed({
    get() {
        return props.mode === "desktop" ? "3x" : "2x";
    },
});

const filename = computed({
    get() {
        let ret = props.file.filename;
        let nameLimit = props.mode === "desktop" ? 10 : 30;
        if (ret.length > nameLimit) {
            ret =
                ret.split(".")[0].substr(0, nameLimit - 4) +
                "~." +
                props.file.extension;
        }
        return ret;
    },
});

const { emit } = useEventsBus();

const handleDrag = (e) => {
    emit("selected_item", { type: "file", data: props.file });
};

const showTrash = props.file.deleted_at ? false : true;
</script>
