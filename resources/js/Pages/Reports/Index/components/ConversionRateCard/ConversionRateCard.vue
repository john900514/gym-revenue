<template>
    <Card
        :options="{
            more: true,
        }"
    >
        <template #cardTitle>
            <div
                class="text-base-content text-xl border-b border-neutral pr-16"
            >
                Conversion Rates and Rolling
            </div>
        </template>
        <div class="flex flex-col">
            <conversion-rate-chart />
            <div class="flex flex-row justify-end pb-2">
                <Button secondary size="xs">View</Button>
            </div>
        </div>
    </Card>
</template>
<script setup>
import Button from "@/Components/Button.vue";
import Card from "@/Components/Card.vue";

import ConversionRateChart from "./ConversionRateChart.vue";
</script>
