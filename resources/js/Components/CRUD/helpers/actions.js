import { Inertia } from "@inertiajs/inertia";
import { computed } from "vue";
import { merge } from "lodash";
import { preview, edit } from "@/Components/CRUD/helpers/gqlData";

export const defaults = Object.freeze({
    // edit: {
    //     label: "Edit",
    //     handler: ({ baseRoute, data }) =>
    //         Inertia.visitInModal(route(`${baseRoute}.edit`, data.id)),
    // },
    trash: {
        label: "Trash",
        handler: ({ baseRoute, data }) =>
            Inertia.delete(route(`${baseRoute}.trash`, data.id)),
        shouldRender: ({ data }) => data?.deleted_at === null,
    },
    restore: {
        label: "Restore",
        handler: ({ baseRoute, data }) =>
            Inertia.post(route(`${baseRoute}.restore`, data.id)),
        shouldRender: ({ data }) =>
            data && "deleted_at" in data && data.deleted_at !== null,
    },
});

export const getDefaults = ({ previewComponent }) => {
    const hasPreviewComponent = !!previewComponent;

    if (!hasPreviewComponent) {
        return defaults;
    }
    return merge(
        {
            preview: {
                label: "Preview",
                handler: async ({ baseRoute, data }) => {
                    preview(data["id"]);
                },
            },
            edit: {
                label: "Edit",
                handler: async ({ baseRoute, data }) => {
                    console.log("actions.edit", data);
                    edit(data["id"]);
                },
            },
        },
        defaults
    );
};

export const getActions = (props) => {
    return computed(() => {
        if (!props.actions) {
            console.log("getActions returning [],");
            return [];
        }
        if (typeof props.actions === "array" || props.actions[0]) {
            console.log("props.actions is array, returning");
            return props.actions;
        }

        const defaults = getDefaults(props);

        const merged = merge({ ...defaults }, { ...props.actions });

        return merged;
    });
};

export const getRenderableActions = (props) => {
    return Object.values(props.actions)
        .filter((action) => action)
        .filter((action) =>
            action?.shouldRender ? action.shouldRender(props) : true
        );
};
