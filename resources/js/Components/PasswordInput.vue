<template>
    <div class="form-control">
        <label class="input-group">
            <input
                :type="type"
                class="input input-bordered"
                ref="input"
                v-bind="$attrs"
                v-model="localValue"
            />
            <span @click="toggleType" class="bg-secondary">
                <font-awesome-icon
                    v-if="type === 'password'"
                    icon="eye"
                    size="sm"
                />
                <font-awesome-icon v-else icon="eye-slash" size="sm" />
            </span>
        </label>
    </div>
</template>
<style scoped></style>
<script setup>
import { ref, defineEmits, computed } from "vue";
import { faEye, faEyeSlash } from "@fortawesome/pro-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";

const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
});

library.add(faEye);
library.add(faEyeSlash);

const type = ref("password");
const toggleType = () => {
    type.value = type.value === "password" ? "text" : "password";
};

const emit = defineEmits(["update:modelValue"]);
const localValue = computed({
    get: () => props.modelValue,
    set: (value) => emit("update:modelValue", value),
});
</script>
