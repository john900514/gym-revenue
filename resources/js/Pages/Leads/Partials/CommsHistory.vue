<template>
    <div class="space-y-2">
        <ul class="w-full steps">
            <CommsHistoryStep
                v-for="detail in details"
                :detail="detail"
                @click="selectedDetail = detail"
                :active="selectedDetail?.id === detail.id"
            />
        </ul>
        <CommsHistoryDetail :detail="selectedDetail" v-if="selectedDetail" />
        <span
            v-else
            class="block py-8 w-full text-center self-center m-auto opacity-50 text-xl"
            >Click on an item above to learn more</span
        >
    </div>
</template>
<script>
import { ref } from "vue";
import CommsHistoryStep from "./CommsHistoryStep.vue";
import CommsHistoryDetail from "./CommsHistoryDetail.vue";

export default {
    name: "CommsHistory",
    components: {
        CommsHistoryDetail,
        CommsHistoryStep,
    },
    props: {
        details: {
            type: Array,
            required: true,
        },
        trialMembershipTypes: {
            type: Array,
            default: [],
        },
    },
    setup(props) {
        const selectedDetail = ref(null);

        const goToLeadDetailIndex = (index) => {
            if (index in props.details) {
                selectedDetail.value = props.details[index];
            }
        };

        return { selectedDetail, goToLeadDetailIndex };
    },
};
</script>
