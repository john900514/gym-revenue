<template>
    <div :class="derivedClass">
        <div
            v-if="label"
            class="absolute top-[-36px] text-sm font-bold rounded-t px-3 py-2 text-base-content bg-secondary"
        >
            {{ label }}
        </div>
        <div
            class="flex flex-row justify-between items-start mb-3"
            :class="{
                'border-b border-neutral': options.borderedTitle,
            }"
        >
            <!-- Can use one of them to display header -->
            <div class="text-secondary text-lg text-bold" v-if="header">
                {{ header }}
            </div>
            <slot v-else name="cardTitle"></slot>

            <div class="flex flex-row space-x-2 cursor-pointer items-center">
                <search-input v-if="options.search" />
                <select-box
                    v-if="options.filter && options.filter.length"
                    :items="options.filter"
                    :label="options.filterLabel"
                    size="xs"
                    class="select-box"
                />
                <favorite-btn
                    v-if="options.favorite"
                    :value="isFavorite"
                    :onChange="toggleFavorite"
                />
                <more-icon v-if="options.more" class="more-btn" />
                <arrow-icon
                    v-if="options.collapse"
                    direction="up"
                    size="xs"
                    class="collapse-btn"
                />
            </div>
        </div>
        <slot></slot>
    </div>
</template>
<style scoped>
.no-favorite .favorite-btn,
.no-collapse .collapse-btn {
    @apply hidden;
}
.no-collapse .collapse-btn {
    @apply hidden;
}
.no-more .more-btn {
    @apply hidden;
}
.no-select .select-box {
    @apply hidden;
}
</style>
<script setup>
import { computed } from "@vue/reactivity";
import { ref } from "vue";
import FavoriteBtn from "@/Components/FavoriteBtn.vue";
import SelectBox from "@/Components/SelectBox";
import SearchInput from "@/Components/SearchInput.vue";
import MoreIcon from "@/Components/Icons/More.vue";
import ArrowIcon from "@/Components/Icons/Arrow.vue";

const props = defineProps({
    class: {
        type: String,
        default: "",
    },
    label: {
        type: String,
        default: "",
    },
    header: {
        type: String,
    },
    options: {
        type: Object,
        default: {
            collapse: false,
            favorite: false,
            filter: [],
            filterLabel: "",
            borderedTitle: false,
        },
    },
});
const derivedClass = computed({
    get() {
        return `px-4 py-3 border border-secondary rounded bg-neutral relative mb-3 ${
            props.class
        } ${props.label ? "mt-9" : ""}`;
    },
});
const isFavorite = ref(false);
const toggleFavorite = (value) => {
    isFavorite.value = value;
};
</script>
