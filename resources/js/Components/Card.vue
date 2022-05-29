<template>
    <div :class="derivedClass">
        <div
            v-if="label"
            class="absolute top-[-36px] text-sm font-bold rounded-t px-3 py-2 text-white bg-secondary"
        >
            {{ label }}
        </div>
        <div class="flex flex-row justify-between items-center mb-3">
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
                />
                <favorite-btn
                    v-if="options.favorite"
                    :value="isFavorite"
                    :onChange="toggleFavorite"
                />
                <span class="flex items-center w-4" v-if="options.collapse">
                    <img src="/img/arrow-up.svg" />
                </span>
            </div>
        </div>
        <slot></slot>
    </div>
</template>
<script setup>
import { computed } from "@vue/reactivity";
import { ref } from "vue";
import FavoriteBtn from "@/Components/FavoriteBtn";
import SelectBox from "@/Components/SelectBox";
import SearchInput from "@/Components/SearchInput";
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
        },
    },
});
const derivedClass = computed({
    get() {
        return `px-4 py-3 border border-secondary rounded-lg bg-neutral-900 relative mb-3 ${
            props.class
        } ${props.label ? "mt-9" : ""}`;
    },
});
const isFavorite = ref(false);
const toggleFavorite = (value) => {
    isFavorite.value = value;
};
</script>