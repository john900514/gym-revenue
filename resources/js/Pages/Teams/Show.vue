<template>
    <LayoutHeader title="Team Settings">
        <h2 class="font-semibold text-xl leading-tight">Team Settings</h2>
    </LayoutHeader>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <update-team-name-form :team="team" :permissions="permissions" />

            <team-member-manager
                class="mt-10 sm:mt-0"
                :team="team"
                :available-roles="availableRoles"
                :user-permissions="permissions"
            />

            <template v-if="permissions.canDeleteTeam && !team.personal_team">
                <jet-section-border />
                <template v-if="permissions.canDeleteTeam">
                    <jet-section-border />
                </template>

                <delete-team-form class="mt-10 sm:mt-0" :team="team" />
            </template>
        </div>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import LayoutHeader from "@/Layouts/LayoutHeader.vue";
import DeleteTeamForm from "@/Pages/Teams/Partials/DeleteTeamForm.vue";
import JetSectionBorder from "@/Jetstream/SectionBorder.vue";
import TeamMemberManager from "@/Pages/Teams/Partials/TeamMemberManager.vue";
import UpdateTeamNameForm from "@/Pages/Teams/Partials/UpdateTeamNameForm.vue";

export default defineComponent({
    props: ["team", "availableRoles", "permissions"],

    components: {
        LayoutHeader,
        DeleteTeamForm,
        JetSectionBorder,
        TeamMemberManager,
        UpdateTeamNameForm,
    },
});
</script>
