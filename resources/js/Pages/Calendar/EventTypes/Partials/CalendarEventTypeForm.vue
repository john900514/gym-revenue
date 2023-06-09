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
                />
                <jet-input-error
                    :message="form.errors.description"
                    class="mt-2"
                />
            </div>
            <div class="col-span-6">
                <jet-label for="type" value="Type" />
                <select
                    id="type"
                    class="block w-full mt-1"
                    @change="checkIfDirty"
                    v-model="form.type"
                >
                    <option>Sales Meeting</option>
                    <option>Training and Development</option>
                    <option>Tour</option>
                    <option>Prospecting</option>
                    <option>Unavailable</option>
                    <option>External Event</option>
                    <option>Task Follow-Up</option>
                </select>
                <jet-input-error :message="form.errors.type" class="mt-2" />
            </div>

            <div class="col-span-6">
                <jet-label for="color" value="Color" />
                <select
                    id="color"
                    class="block w-full mt-1"
                    v-model="form.color"
                >
                    <option>yellow</option>
                    <option>white</option>
                    <option>red</option>
                    <option>grey</option>
                    <option>purple</option>
                    <option>blue</option>
                    <option>green</option>
                </select>
                <jet-input-error :message="form.errors.color" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <Button
                type="button"
                @click="$inertia.visit(route('calendar.event_types'))"
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
import { ref, computed } from "vue";
import { useGymRevForm } from "@/utils";
import Button from "@/Components/Button.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";

const props = defineProps({
    clientId: {
        type: String,
        required: true,
    },
    calendarEventType: {
        type: Object,
        default: {
            id: null,
            name: "",
            description: "",
            type: "",
            color: "",
        },
    },
});

let eventType = ref(props.calendarEventType);

let operation = computed(() => {
    return eventType.value.id === null ? "Create" : "Update";
});

const form = useGymRevForm(eventType.value);

let handleSubmit = () => {
    form.dirty().put(
        route(
            "calendar.event_types." + operation.value.toLowerCase(),
            eventType.id
        )
    );
    if (operation === "Create") {
        handleSubmit = () => form.post(route("calendar.event_types.store"));
    }
};
</script>
