<template>
    <LayoutHeader title="Sales Funnel">
        <reports-title>
            <template #title>
                <h1>Sales Funnel</h1>
                <div
                    class="flex flex-row text-base-content space-x-2 text-xs items-center flex-wrap"
                >
                    <span class="cursor-pointer text-secondary">Monthly</span>
                    <span class="cursor-pointer">Quartely</span>
                    <span class="cursor-pointer">Annual</span>
                    <span class="cursor-pointer">YOY Comparisons</span>
                </div>
            </template>
        </reports-title>
    </LayoutHeader>
    <jet-bar-container>
        <div class="flex flex-row mt-[-24px] lg:px-16 mx-2 flex-wrap">
            <div class="lg:w-[37.5%]">
                <status-cards-wrapper
                    title="Actual"
                    :cards="actual_status"
                ></status-cards-wrapper>
            </div>
            <div class="w-full lg:w-[62.5%]">
                <status-cards-wrapper title="Active" :cards="active_status">
                    <template #filter>
                        <status-filter />
                    </template>
                </status-cards-wrapper>
            </div>
        </div>

        <div
            class="flex flex-col lg:flex-row lg:px-20 pt-6 justify-between lg:space-x-8"
        >
            <div class="flex flex-col md:flex-row md:space-x-2">
                <cancel-card progress="25" />
                <completed-card :progress="[34, 54, 16, 23]" />
            </div>
            <div class="md:w-fit">
                <lead-stats-card progress="73" />
            </div>
        </div>
        <attach-rate-membership />
    </jet-bar-container>
</template>

<script setup>
import { defineComponent } from "vue";
import LayoutHeader from "@/Layouts/LayoutHeader.vue";
import JetBarContainer from "@/Components/JetBarContainer.vue";
import ReportsTitle from "../components/ReportsTitle.vue";
import StatusCardsWrapper from "./components/StatusCardsWrapper.vue";
import StatusFilter from "./components/StatusFilter.vue";
import CancelCard from "./components/CancelCard.vue";
import CompletedCard from "./components/CompletedCard/CompletedCard.vue";
import LeadStatsCard from "./components/LeadStatsCard.vue";
import AttachRateMembership from "./components/AttachRateMembership/AttachRateMembership.vue";
const actual_status = [
    { status: "leads", count: 55 },
    { status: "booked", count: 23 },
    { status: "confirmed", count: 14 },
];

const active_status = [
    { status: "referrals", count: 37 },
    { status: "contacts", count: 108 },
    { status: "active_booked", count: 33 },
    { status: "sales", count: 42 },
    { status: "rejections", count: 178 },
];
</script>
