<template>
    <jet-form-section @submitted="updateTeamName">
        <template #title> Team Name </template>

        <template #description>
            The team's name and owner information.
        </template>

        <template #form>
            <!-- Team Owner Information -->
            <div class="col-span-6">
                <jet-label value="Team Owner" />

                <div class="flex items-center mt-2">
                    <img
                        class="w-12 h-12 rounded-full object-cover"
                        :src="team.owner.profile_photo_url"
                        :alt="team.owner.name"
                    />

                    <div class="ml-4 leading-tight">
                        <div>{{ team.owner.name }}</div>
                        <div class="text-sm">{{ team.owner.email }}</div>
                    </div>
                </div>
            </div>

            <!-- Team Name -->
            <div class="col-span-6 sm:col-span-4">
                <jet-label for="name" value="Team Name" />

                <input
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    :disabled="!abilities.canUpdateTeam"
                />

                <jet-input-error :message="form.errors.name" class="mt-2" />
            </div>
        </template>

        <template #actions v-if="abilities.canUpdateTeam">
            <jet-action-message :on="form.recentlySuccessful" class="mr-3">
                Saved.
            </jet-action-message>

            <Button
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Save
            </Button>
        </template>
    </jet-form-section>
</template>

<script setup>
import JetActionMessage from "@/Jetstream/ActionMessage.vue";
import Button from "@/Components/Button.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import { useGymRevForm } from "@/utils";
const props = defineProps({
    team: {
        type: Object,
    },
    abilities: {
        type: Array,
    },
});

const form = useGymRevForm({
    name: props.team.name,
});
function updateTeamName() {
    form.put(route("team.update", props.team), {
        errorBag: "updateTeamName",
        preserveScroll: true,
    });
}
</script>
