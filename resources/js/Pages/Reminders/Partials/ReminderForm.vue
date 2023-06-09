<template>
    <jet-form-section @submitted="handleSubmit">
        <template #form>
            <div class="col-span-6">
                <jet-label for="name" value="Name" />
                <input
                    id="name"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.name"
                    autofocus
                />
                <jet-input-error :message="form.errors.name" class="mt-2" />
            </div>
            <div class="col-span-6">
                <jet-label for="description" value="Description" />
                <input
                    id="description"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.description"
                    autofocus
                />
                <jet-input-error
                    :message="form.errors.description"
                    class="mt-2"
                />
            </div>
            <div class="col-span-6">
                <jet-label for="remind_time" value="Reminder time" />
                <input
                    id="remind_time"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.remind_time"
                    autofocus
                />
                <jet-input-error
                    :message="form.errors.remind_time"
                    class="mt-2"
                />
            </div>
        </template>

        <template #actions>
            <Button
                type="button"
                @click="$emit('close')"
                :class="{ 'opacity-25': form.processing }"
                error
                outline
                :disabled="form.processing"
            >
                Cancel
            </Button>
            <div class="flex-grow" />
            <Button
                class="btn-secondary"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing || !form.isDirty"
                :loading="form.processing"
            >
                {{ buttonText }}
            </Button>
        </template>
    </jet-form-section>
</template>

<script setup>
import { usePage } from "@inertiajs/inertia-vue3";
import { computed, ref } from "vue";
import { useGymRevForm } from "@/utils";

import Button from "@/Components/Button.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import { Inertia } from "@inertiajs/inertia";

import mutations from "@/gql/mutations";
import { useMutation } from "@vue/apollo-composable";

const props = defineProps({
    reminder: {
        type: Object,
        default: {
            name: "",
            id: "",
            description: "",
            remind_time: 0,
        },
    },
});

const emit = defineEmits(["close"]);

const { mutate: createReminder } = useMutation(mutations.reminder.create);
const { mutate: updateReminder } = useMutation(mutations.reminder.update);

const operFn = computed(() => {
    return props.reminder.id === "" ? createReminder : updateReminder;
});

const form = useGymRevForm(props.reminder);

const handleSubmit = async () => {
    await operFn.value({
        ...form,
    });

    emit("close");
};
</script>
