<template>
    <dashboard-widget
        title="Attrition"
        :modal-component="AttritionModal"
        :amount="18000"
    ></dashboard-widget>
</template>

<script>
import { defineComponent } from "vue";
import DashboardWidget from "@/Pages/Reports/Partials/DashboardWidget.vue";
import AttritionModal from "@/Pages/Reports/Attrition/AttritionModal.vue";

export default defineComponent({
    components: {
        DashboardWidget,
    },
    setup() {
        return { AttritionModal };
    },
});
</script>
