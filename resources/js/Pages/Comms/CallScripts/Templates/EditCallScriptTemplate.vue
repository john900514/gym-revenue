<template>
    <LayoutHeader title="Edit Call Script Template">
        <h2 class="font-semibold text-xl leading-tight">
            Edit Call Script Template
        </h2>
    </LayoutHeader>

    <daisy-modal ref="modalRef">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <call-script-template-form
                :client-id="$page.props.user.client_id"
                :template="template"
                :can-activate="true"
                @close="closeModal"
            />
        </div>
    </daisy-modal>
</template>

<script setup>
import LayoutHeader from "@/Layouts/LayoutHeader.vue";

import CallScriptTemplateForm from "./Partials/CallScriptTemplateForm.vue";
import DaisyModal from "@/Components/DaisyModal.vue";

const props = defineProps({
    template: {
        required: true,
        type: Object,
    },
});

const modalRef = ref(null);
const closeModal = () => {
    modalRef.value?.close();
};
</script>
