<template>
    <LayoutHeader title="Calendar Reporting">
        <calendar-title>
            <template #title>
                <h1 class="mr-8">Calendar Reporting</h1>
            </template>
        </calendar-title>
    </LayoutHeader>
    <jet-bar-container>
        <div class="md:px-10 flex flex-col">
            <div class="text-secondary pl-9 text-xl">Client Activity</div>
            <div
                class="flex flex-row space-x-2 flex-wrap justify-center lg:justify-start"
            >
                <div class="flex w-full lg:w-3/5 flex-wrap">
                    <div class="w-4/5 m-auto md:w-1/3 px-2">
                        <calendar-summary-card
                            title="Confirmed"
                            :progress="[65]"
                        />
                    </div>
                    <div class="w-4/5 m-auto md:w-1/3 px-2">
                        <calendar-summary-card
                            title="Canceled"
                            :progress="[25]"
                        />
                    </div>
                    <div class="w-4/5 m-auto md:w-1/3 px-2">
                        <calendar-summary-card
                            title="Rescheduled"
                            :progress="[10]"
                        />
                    </div>
                </div>
                <calendar-grid />
            </div>

            <calendar-schedule-table :data="schedule" class="schedule-table" />
        </div>
    </jet-bar-container>
</template>
<style scoped>
@media only screen and (max-width: 768px) {
    .schedule-table {
        width: calc(100vw - 48px);
    }
}
.schedule-table {
    @apply overflow-x-scroll;
}
</style>
<script setup>
import { defineComponent } from "vue";
import LayoutHeader from "@/Layouts/LayoutHeader.vue";
import JetBarContainer from "@/Components/JetBarContainer.vue";
import CalendarScheduleTable from "./components/CalendarScheduleTable/CalendarScheduleTable.vue";
import CalendarTitle from "../components/CalendarTitle.vue";
import CalendarGrid from "@/Pages/components/CalendarGrid.vue";
import CalendarSummaryCard from "@/Pages/components/CalendarSummaryCard.vue";

const schedule = [
    {
        id: 1,
        location: "Location ONE",
        event: "Welcome Call",
        employee: "/img/avatars/calendar-avatar.png",
        time: "9.00AM",
    },
    {
        id: 2,
        location: "Location TWO",
        event: "Welcome Email",
        employee: "/img/avatars/calendar-avatar.png",
        time: "10.00AM",
    },
    {
        id: 3,
        location: "Location THREE",
        event: "Text",
        employee: "/img/avatars/calendar-avatar.png",
        time: "10.30AM",
    },
    {
        id: 4,
        location: "Location FOUR",
        event: "Team Meeting",
        employee: "/img/avatars/calendar-avatar.png",
        time: "11.00AM",
    },
];
</script>
