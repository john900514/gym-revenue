<template>
    <select-box :items="items" label="Month" size="xs" />
</template>
<script setup>
import SelectBox from "@/Components/SelectBox";
const items = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
];
</script>
