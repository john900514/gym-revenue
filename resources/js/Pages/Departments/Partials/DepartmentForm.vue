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
        </template>

        <template #actions>
            <Button
                type="button"
                @click="handleCancel"
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
                :disabled="form.processing"
                :loading="form.processing"
            >
                {{ buttonText }}
            </Button>
        </template>
    </jet-form-section>
</template>

<script>
import Button from "@/Components/Button.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import { useGymRevForm } from "@/utils";
import { useModal } from "@/Components/InertiaModal";
import { Inertia } from "@inertiajs/inertia";

export default {
    components: {
        Button,
        JetFormSection,
        JetInputError,
        JetLabel,
    },
    props: {
        clientId: {
            type: String,
            required: true,
        },
        department: {
            type: Object,
        },
    },
    setup(props) {
        let department = props.department;
        let operation = "Update";
        if (!department) {
            department = {
                name: "",
                id: null,
                client_id: props.clientId,
            };
            operation = "Create";
        }

        const form = useGymRevForm(department);

        let handleSubmit = () =>
            form.dirty().put(route("departments.update", department.id));
        if (operation === "Create") {
            handleSubmit = () => form.post(route("departments.store"));
        }

        const modal = useModal();
        const handleCancel = () => {
            if (modal?.value?.close) {
                modal.value.close();
                return;
            }
            Inertia.visit(route("departments"));
        };
        return {
            form,
            buttonText: operation,
            handleSubmit,
            handleCancel,
        };
    },
};
</script>