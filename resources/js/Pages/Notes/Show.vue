<template>
    <LayoutHeader title="Notes" />
    <page-toolbar-nav title="Notes" :links="navLinks" />
    <gym-revenue-crud
        base-route="notes"
        model-name="Note"
        model-key="note"
        :edit-component="NoteForm"
        :fields="fields"
        :actions="{
            trash: false,
            restore: false,
            delete: {
                label: 'Delete',
                handler: ({ data }) => handleClickDelete(data),
            },
        }"
    />
    <confirm
        title="Really Trash Note?"
        v-if="confirmDelete"
        @confirm="handleConfirmDelete"
        @cancel="confirmDelete = null"
    >
        Are you sure you want to delete Note '{{ confirmDelete.title }}'
    </confirm>
</template>
<script>
import { defineComponent, ref } from "vue";
import LayoutHeader from "@/Layouts/LayoutHeader.vue";
import GymRevenueCrud from "@/Components/CRUD/GymRevenueCrud.vue";
import { Inertia } from "@inertiajs/inertia";
import Confirm from "@/Components/Confirm.vue";
import Button from "@/Components/Button.vue";
import JetBarContainer from "@/Components/JetBarContainer.vue";
import PageToolbarNav from "@/Components/PageToolbarNav.vue";
import NoteForm from "@/Pages/Notes/Partials/NoteForm.vue";

export default defineComponent({
    components: {
        LayoutHeader,
        GymRevenueCrud,
        Confirm,
        JetBarContainer,
        Button,
        PageToolbarNav,
    },
    props: [],
    setup(props) {
        const confirmDelete = ref(null);
        const handleClickDelete = (item) => {
            confirmDelete.value = item;
        };

        const handleConfirmDelete = () => {
            Inertia.delete(route("notes.delete", confirmDelete.value));
            confirmDelete.value = null;
        };

        const fields = ["title", "note", "active"];

        let navLinks = [
            {
                label: "Users",
                href: route("users"),
                onClick: null,
                active: false,
            },
            {
                label: "Notes",
                href: route("notes"),
                onClick: null,
                active: true,
            },
        ];
        return {
            fields,
            confirmDelete,
            handleConfirmDelete,
            handleClickDelete,
            Inertia,
            navLinks,
            NoteForm,
        };
    },
});
</script>
