<template>
    <form @submit.prevent="submit">
        <div class="relative form-control" @mouseleave="toggleInput(false)">
            <Combobox
                v-model="selected"
                nullable
                @update:modelValue="handleCombo"
            >
                <div class="input-group">
                    <ComboboxInput
                        type="text"
                        placeholder="Search…"
                        class="input input-bordered search-input"
                        :class="{
                            'invisible w-0': !showInput,
                            'w-56': showInput,
                        }"
                        :value="term"
                        :display-value="() => props.initialValue"
                        @change="handleChange"
                        @focus="showOptions = true"
                        @blur="showOptions = false"
                        id="global-search-input"
                        autocomplete="off"
                    />
                    <Button :ghost="!showInput" @mouseenter="toggleInput(true)">
                        <search-icon />
                    </Button>
                </div>
                <TransitionRoot
                    leave="transition ease-in duration-100"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                    :show="showOptions && term !== ''"
                >
                    <div
                        class="absolute mt-1 max-h-66 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                    >
                        <ComboboxOptions v-if="term !== ''">
                            <template v-if="hints?.data?.length">
                                <ComboboxOption
                                    v-for="hint in hints.data"
                                    as="template"
                                    :key="hint.id"
                                    :value="hint"
                                    v-slot="{ selected, active }"
                                >
                                    <li
                                        class="relative cursor-default select-none py-2 pl-10 pr-4"
                                        :class="{
                                            'bg-teal-600 text-white': active,
                                            'text-gray-900': !active,
                                        }"
                                    >
                                        <span
                                            class="block truncate"
                                            :class="{
                                                'font-medium': selected,
                                                'font-normal': !selected,
                                            }"
                                        >
                                            {{ hint.name }}
                                        </span>
                                        <span
                                            class="block truncate text-base opacity-50 !text-xs"
                                        >
                                            {{ hint.type }}
                                        </span>
                                        <span
                                            v-if="selected"
                                            class="absolute inset-y-0 left-0 flex items-center pl-3"
                                            :class="{
                                                'text-white': active,
                                                'text-teal-600': !active,
                                            }"
                                        >
                                            <font-awesome-icon
                                                :icon="['fas', 'check']"
                                                size="lg"
                                            />
                                        </span>
                                    </li>
                                </ComboboxOption>
                            </template>
                            <div
                                class="relative cursor-default select-none py-2 px-4 text-gray-700"
                                v-else
                            >
                                Nothing found.
                            </div>
                        </ComboboxOptions>
                        <button
                            v-if="
                                hints?.paginatorInfo?.total >
                                hints?.paginatorInfo?.perPage
                            "
                            class="relative select-none py-4 px-4 opacity-50 text-base-300"
                        >
                            View all {{ hints.paginatorInfo.total }} results...
                        </button>
                    </div>
                </TransitionRoot>
            </Combobox>
        </div>
    </form>
</template>
<style scoped>
.search-input {
    transition: width 650ms cubic-bezier(0.18, 0.89, 0.32, 1.28);
}
</style>
<script setup>
import { ref, computed, watch } from "vue";
import {
    Combobox,
    ComboboxInput,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from "@headlessui/vue";
import Button from "@/Components/Button.vue";
import SearchIcon from "./Icons/Search.vue";
import { Inertia } from "@inertiajs/inertia";
import throttle from "lodash/throttle";

import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faCheck } from "@fortawesome/pro-solid-svg-icons";
import queries from "@/gql/queries";
import { useQuery } from "@vue/apollo-composable";
library.add(faCheck);

const props = defineProps({
    size: {
        type: String,
        default: "sm",
    },
    initialValue: {
        type: String,
        default: "",
    },
    animate: {
        type: Boolean,
        default: true,
    },
});

const submit = () => {
    Inertia.get(route("searches"), {
        term: term.value,
    });
};

const showOptions = ref(false);

const search_hints = ref([]);
let term = ref(props.initialValue);
const { result } = useQuery(
    queries["global_search"],
    {
        term: term,
        pagination: {
            limit: 4,
            page: 1,
        },
    },
    {
        throttle: 500,
    }
);
const hints = computed(() => result.value?.globalSearch ?? {});

let selected = ref({});

const showInput = ref(!props.animate);
const toggleInput = (flag) => {
    showInput.value = flag || !props.animate || selected.name || term.value;
};
const handleCombo = (val) => {
    if (val?.link.includes("edit")) {
        Inertia.visit(route(val.link, val.id));
    } else {
        Inertia.visit(route(val.link));
    }
    // selected.value=hint;
    document.activeElement.blur();
    term.value = "";
};

const emit = defineEmits(["change"]);
const handleChange = (e) => {
    term.value = e.target.value;
    emit("change", term.value);
};
</script>
