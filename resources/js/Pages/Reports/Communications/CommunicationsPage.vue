<template>
    <LayoutHeader title="Communication">
        <reports-title>
            <template #title> Communication </template>
        </reports-title>
    </LayoutHeader>
    <jet-bar-container>
        <div class="flex flex-col md:px-16">
            <div class="text-secondary text-xl font-semibold">
                Call Reporting
            </div>
            <div class="flex flex-row space-x-4 my-4">
                <span>Club:</span>
                <select-box :items="locations" label="Locations" size="xs" />
            </div>
            <call-records class="comm-table"></call-records>
            <div class="text-secondary text-xl font-semibold mt-9 mb-7">
                Mass Communication
            </div>
            <div class="flex flex-row space-x-4 mb-10 pl-1">
                <Button secondary size="xs">SMS</Button>
                <Button secondary outline size="xs">Email</Button>
            </div>
            <div class="flex flex-col md:px-12">
                <div class="flex md:flex-row flex-col flex-wrap">
                    <communication-card
                        title="Deliver Rate"
                        :progress="75"
                        class="md:w-1/2 lg:w-1/4"
                    />
                    <communication-card
                        title="Open Rate"
                        :progress="60"
                        class="md:w-1/2 lg:w-1/4"
                    />
                    <communication-card
                        title="Click Rate"
                        :progress="55"
                        class="md:w-1/2 lg:w-1/4"
                    />
                    <communication-card
                        title="Opt-Out Rate"
                        :progress="25"
                        class="md:w-1/2 lg:w-1/4"
                    />
                </div>
                <mass-call-records class="comm-table" />
            </div>
        </div>
    </jet-bar-container>
</template>
<style scoped>
@media only screen and (max-width: 768px) {
    .comm-table {
        width: calc(100vw - 32px);
    }
}
</style>
<script setup>
import { defineComponent } from "vue";
import LayoutHeader from "@/Layouts/LayoutHeader.vue";
import JetBarContainer from "@/Components/JetBarContainer.vue";
import ReportsTitle from "../components/ReportsTitle.vue";
import SelectBox from "@/Components/SelectBox";
import Button from "@/Components/Button.vue";
import CallRecords from "./components/CallRecords.vue";
import CommunicationCard from "./components/CommunicationCard.vue";
import MassCallRecords from "./components/MassCallRecords.vue";
const locations = [
    "Location 1",
    "Location 2",
    "Location 3",
    "Location 4",
    "Location 5",
    "Location 6",
];
</script>
