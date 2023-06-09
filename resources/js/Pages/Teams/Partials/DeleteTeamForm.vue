<template>
    <jet-action-section>
        <template #title> Delete Team </template>

        <template #description> Permanently delete this team. </template>

        <template #content>
            <div class="max-w-xl text-sm">
                Once a team is deleted, all of its resources and data will be
                permanently deleted. Before deleting this team, please download
                any data or information regarding this team that you wish to
                retain.
            </div>

            <div class="mt-5">
                <button class="error" @click="confirmTeamDeletion">
                    Delete Team
                </button>
            </div>

            <!-- Delete Team Confirmation Modal -->
            <jet-confirmation-modal
                :show="confirmingTeamDeletion"
                @close="confirmingTeamDeletion = false"
            >
                <template #title> Delete Team </template>

                <template #content>
                    Are you sure you want to delete this team? Once a team is
                    deleted, all of its resources and data will be permanently
                    deleted.
                </template>

                <template #footer>
                    <jet-secondary-button
                        @click="confirmingTeamDeletion = false"
                    >
                        Cancel
                    </jet-secondary-button>

                    <button
                        class="ml-2 error"
                        @click="deleteTeam"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Delete Team
                    </button>
                </template>
            </jet-confirmation-modal>
        </template>
    </jet-action-section>
</template>

<script setup>
import { ref } from "vue";
import JetActionSection from "@/Jetstream/ActionSection.vue";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal.vue";

import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import { useGymRevForm } from "@/utils";

const confirmingTeamDeletion = ref(false);
const deleting = ref(false);
const form = useGymRevForm();
const props = defineProps({
    team: {
        type: any,
    },
});
function confirmTeamDeletion() {
    confirmingTeamDeletion.value = true;
}

function deleteTeam() {
    form.delete(route("teams.destroy", props.team), {
        errorBag: "deleteTeam",
    });
}
</script>
