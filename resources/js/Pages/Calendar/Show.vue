<template>
    <drawer-layout>
        <template #switch>
            <input
                id="drawerSwitch"
                type="checkbox"
                class="drawer-toggle"
                ref="drawerSwitch"
                @change="handleChange"
            />
        </template>
        <template #pageContent>
            <page-content
                :sessions="sessions"
                :calendar_events="calendar_events"
                :calendar_event_types="calendar_event_types"
                :title="title"
                :isClientUser="isClientUser"
                :filters="filters"
                :client_users="client_users"
                :lead_users="lead_users"
                :client_id="client_id"
                :toggleSwitch="toggleSwitch"
                :resetCalendarEventForm="resetCalendarEventForm"
                :updateCalendarEventForm="updateCalendarEventForm"
                :setSelectedEvent="setSelectedEvent"
            />
        </template>
        <template #drawerContent>
            <drawer-content :toggleSwitch="toggleSwitch">
                <template #eventForm>
                    <calendar-event-form
                        :calendar_event="eventData"
                        :duration="duration"
                        :key="eventData"
                        :client_users="client_users"
                        :lead_users="lead_users"
                        :member_users="members_users"
                        :locations="locations"
                        :client_id="client_id"
                        @submitted="toggleSwitch"
                        ref="eventForm"
                    />
                </template>
            </drawer-content>
        </template>
    </drawer-layout>
</template>
<script setup>
import { ref, computed } from "vue";

import PageContent from "./PageContent.vue";
import CalendarEventForm from "@/Pages/Calendar/Partials/CalendarEventForm.vue";
import DrawerLayout from "./components/DrawerLayout.vue";
import DrawerContent from "./components/DrawerContent.vue";
import { useQuery } from "@vue/apollo-composable";
import queries from "@/gql/queries";

const props = defineProps({
    sessions: {
        type: Array,
        default: null,
    },
    calendar_events: {
        type: Array,
        default: [],
    },
    calendar_event_types: {
        type: Array,
        default: [],
    },
    title: {
        type: String,
        default: "",
    },
    isClientUser: {
        type: Boolean,
        default: true,
    },
    filters: {
        type: Array,
        default: [],
    },
    client_users: {
        type: Array,
        default: [],
    },
    lead_users: {
        type: Array,
        default: [],
    },
    members_users: {
        type: Array,
        default: [],
    },
    client_id: {
        type: String,
        default: "",
    },
    locations: {
        type: Array,
        default: [],
    },
});

const drawerSwitch = ref();
const toggleSwitch = () => {
    drawerSwitch.value.click();
};

const duration = ref({});
const eventForm = ref(null);
const updateCalendarEventForm = (updated) => {
    eventForm.value = {
        ...eventForm.value,
        form: {
            ...eventForm.value.form,
            ...updated,
        },
    };
    duration.value = updated;
};
const resetCalendarEventForm = () => {
    eventForm.value?.form?.reset();
};

const selectedEvent = ref(null);
const setSelectedEvent = (id) => {
    selectedEvent.value = id;
};

const { result } = useQuery(queries["event"]["get"], {
    id: selectedEvent,
});
const eventData = computed(() => result.value?.event ?? null);
const handleChange = (e) => {
    if (!e.target.checked) {
        setSelectedEvent(-1);
        resetCalendarEventForm();
    }
};
</script>
