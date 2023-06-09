<template>
    <ul class="flex justify-between py-4">
        <button @click="$emit('regress')" class="text-base-100 text-opacity-50">
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
        </button>
        <li
            v-for="day in visibleWeek"
            class="font-bold"
            :class="{ selected_day: isSameDay(selected_date, day) }"
        >
            <button class="align-middle" @click="$emit('changeDate', day)">
                {{ day.getDate() }}
            </button>
        </li>
        <button @click="$emit('advance')" class="text-secondary">
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
        </button>
    </ul>
</template>

<style scoped>
li {
    @apply h-7 w-7 text-center;
}

.selected_day {
    @apply rounded-full bg-secondary text-base-content;
}
</style>

<script setup>
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import {
    faChevronLeft,
    faChevronRight,
} from "@fortawesome/pro-solid-svg-icons";
import { computed } from "vue";
library.add(faChevronLeft);
library.add(faChevronRight);

const props = defineProps({
    selected_date: {
        type: [String, Date],
    },
    visible_date: {
        type: [String, Date],
    },
});

const isSameDay = (d1, d2) => {
    let first = d1 instanceof Date ? d1 : new Date(d1);
    let second = d2 instanceof Date ? d2 : new Date(d2);

    return (
        first.getDate() == second.getDate() &&
        first.getMonth() == second.getMonth() &&
        first.getFullYear() == second.getFullYear()
    );
};

/**
 * Generates an array of days (sun - sat) for the week of the currently
 * visible date. To pan time, we update visible date 7 days in the future/past
 * on the parent component then calculate the dates for that particular week
 */
const visibleWeek = computed(() => {
    let w = [];
    for (let i = 0; i < 7; i++) {
        let cd = new Date(props.visible_date);

        const getPosOffsetDate = (offs) => {
            return new Date(
                cd.getFullYear(),
                cd.getMonth(),
                cd.getDate() + offs
            );
        };

        const getNegOffsetDate = (offs) => {
            return new Date(
                cd.getFullYear(),
                cd.getMonth(),
                cd.getDate() - offs
            );
        };

        if (i === cd.getDay()) {
            w[i] = cd;
        } else if (i < cd.getDay()) {
            w[i] = getNegOffsetDate(cd.getDay() - i);
        } else if (i > cd.getDay()) {
            w[i] = getPosOffsetDate(i - cd.getDay());
        }
    }

    return w;
});
</script>
