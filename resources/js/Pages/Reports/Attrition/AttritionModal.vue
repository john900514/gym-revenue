<template>
    <dashboard-widget-modal
        title="Attrition"
        v-bind="$attrs"
        ref="modal"
        page-route="Attrition"
    >
        <div class="flex md:flex-row flex-col w-full">
            <attrition-running-total
                class="min-w-fit no-favorite no-collapse"
            />
            <attrition-waterline class="min-w-fit no-favorite no-collapse" />
        </div>
    </dashboard-widget-modal>
</template>
<script>
import { defineComponent, ref } from "vue";
import DashboardWidgetModal from "@/Pages/Reports/Partials/DashboardWidgetModal.vue";
import AttritionRunningTotal from "./components/AttritionRunningTotal/AttritionRunningTotal.vue";
import AttritionWaterline from "./components/AttritionWaterline/AttritionWaterline.vue";
export default defineComponent({
    components: {
        DashboardWidgetModal,
        AttritionRunningTotal,
        AttritionWaterline,
    },
    setup() {
        const modal = ref();

        const open = () => {
            if (modal.value?.open) {
                modal.value?.open();
            }
        };
        const close = () => {
            if (modal.value?.close) {
                modal.value?.close();
            }
        };
        return { open, close, modal };
    },
});
</script>
