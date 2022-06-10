<template>
    <div>
        <!-- Generate API Token -->
        <jet-form-section @submitted="createApiToken">
            <template #title> Create API Token </template>

            <template #description>
                API tokens allow third-party services to authenticate with our
                application on your behalf.
            </template>

            <template #form>
                <!-- Token Name -->
                <!--                <div class="col-span-6 sm:col-span-4">-->
                <!--                    <jet-label for="name" value="Name" />-->
                <!--                    <input-->
                <!--                        id="name"-->
                <!--                        type="text"-->
                <!--                        class="mt-1 block w-full"-->
                <!--                        v-model="createApiTokenForm.name"-->
                <!--                        autofocus-->
                <!--                    />-->
                <!--                    <jet-input-error-->
                <!--                        :message="createApiTokenForm.errors.name"-->
                <!--                        class="mt-2"-->
                <!--                    />-->
                <!--                </div>-->

                <!-- Token Permissions -->
                <div class="col-span-6" v-if="availablePermissions.length > 0">
                    <jet-label for="permissions" value="Permissions" />

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="permission in availablePermissions"
                            :key="permission"
                        >
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    class="checkbox"
                                    :value="permission"
                                    v-model="createApiTokenForm.permissions"
                                />
                                <span class="ml-2 text-sm">{{
                                    permission
                                }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <jet-action-message
                    :on="createApiTokenForm.recentlySuccessful"
                    class="mr-3"
                >
                    Created.
                </jet-action-message>

                <Button
                    secondary
                    :class="{ 'opacity-25': createApiTokenForm.processing }"
                    :disabled="createApiTokenForm.processing"
                >
                    Create API Token
                </Button>
            </template>
        </jet-form-section>

        <div v-if="tokens.length > 0">
            <jet-section-border />

            <!-- Manage API Tokens -->
            <div class="mt-10 sm:mt-0">
                <jet-action-section>
                    <template #title> Manage API Tokens </template>

                    <template #description>
                        You may delete any of your existing tokens if they are
                        no longer needed.
                    </template>

                    <!-- API Token List -->
                    <template #content>
                        <div class="space-y-6">
                            <div
                                class="flex items-center justify-between"
                                v-for="token in tokens"
                                :key="token.id"
                            >
                                <div>
                                    {{ token.name }}
                                </div>

                                <div class="flex items-center">
                                    <div
                                        class="text-sm"
                                        v-if="token.last_used_ago"
                                    >
                                        Last used {{ token.last_used_ago }}
                                    </div>

                                    <button
                                        class="cursor-pointer ml-6 text-sm underline"
                                        @click="
                                            manageApiTokenPermissions(token)
                                        "
                                        v-if="availablePermissions.length > 0"
                                    >
                                        Permissions
                                    </button>

                                    <button
                                        class="cursor-pointer ml-6 text-sm text-red-500"
                                        @click="confirmApiTokenDeletion(token)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </jet-action-section>
            </div>
        </div>

        <!-- Token Value Modal -->
        <jet-dialog-modal
            :show="displayingToken"
            @close="displayingToken = false"
        >
            <template #title> API Token </template>

            <template #content>
                <div>
                    Please copy your new API token. For your security, it won't
                    be shown again.
                </div>

                <div
                    class="mt-4 bg-base-100 px-4 py-2 rounded font-mono text-sm"
                    v-if="$page.props.jetstream.flash.token"
                >
                    {{ $page.props.jetstream.flash.token }}
                </div>
            </template>

            <template #footer>
                <jet-secondary-button @click="displayingToken = false">
                    Close
                </jet-secondary-button>
            </template>
        </jet-dialog-modal>

        <!-- API Token Permissions Modal -->
        <jet-dialog-modal
            :show="managingPermissionsFor"
            @close="managingPermissionsFor = null"
        >
            <template #title> API Token Permissions </template>

            <template #content>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="permission in availablePermissions"
                        :key="permission"
                    >
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                class="checkbox"
                                :value="permission"
                                v-model="updateApiTokenForm.permissions"
                            />
                            <span class="ml-2 text-sm">{{ permission }}</span>
                        </label>
                    </div>
                </div>
            </template>

            <template #footer>
                <jet-secondary-button @click="managingPermissionsFor = null">
                    Cancel
                </jet-secondary-button>

                <Button
                    class="ml-2"
                    @click="updateApiToken"
                    :class="{ 'opacity-25': updateApiTokenForm.processing }"
                    :disabled="updateApiTokenForm.processing"
                >
                    Save
                </Button>
            </template>
        </jet-dialog-modal>

        <!-- Delete Token Confirmation Modal -->
        <jet-confirmation-modal
            :show="apiTokenBeingDeleted"
            @close="apiTokenBeingDeleted = null"
        >
            <template #title> Delete API Token </template>

            <template #content>
                Are you sure you would like to delete this API token?
            </template>

            <template #footer>
                <jet-secondary-button @click="apiTokenBeingDeleted = null">
                    Cancel
                </jet-secondary-button>

                <button
                    class="error ml-2"
                    @click="deleteApiToken"
                    :class="{ 'opacity-25': deleteApiTokenForm.processing }"
                    :disabled="deleteApiTokenForm.processing"
                >
                    Delete
                </button>
            </template>
        </jet-confirmation-modal>
    </div>
</template>

<script>
import { defineComponent } from "vue";
import JetActionMessage from "@/Jetstream/ActionMessage";
import JetActionSection from "@/Jetstream/ActionSection";
import Button from "@/Components/Button";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal";

import JetDialogModal from "@/Jetstream/DialogModal";
import JetFormSection from "@/Jetstream/FormSection";

import JetInputError from "@/Jetstream/InputError";
import JetLabel from "@/Jetstream/Label";
import JetSecondaryButton from "@/Jetstream/SecondaryButton";
import JetSectionBorder from "@/Jetstream/SectionBorder";

export default defineComponent({
    components: {
        JetActionMessage,
        JetActionSection,
        Button,
        JetConfirmationModal,
        JetDialogModal,
        JetFormSection,
        JetInputError,
        JetLabel,
        JetSecondaryButton,
        JetSectionBorder,
    },

    props: {
        tokens: {
            type: Array,
            default: [],
        },
        availablePermissions: {
            type: Array,
            default: [],
        },
        defaultPermissions: {
            type: Array,
            default: [],
        },
    },

    data() {
        return {
            createApiTokenForm: this.$inertia.form({
                name: "",
                permissions: this.defaultPermissions,
            }),

            updateApiTokenForm: this.$inertia.form({
                permissions: [],
            }),

            deleteApiTokenForm: this.$inertia.form(),

            displayingToken: false,
            managingPermissionsFor: null,
            apiTokenBeingDeleted: null,
        };
    },

    methods: {
        createApiToken() {
            this.createApiTokenForm.post(route("api-tokens.store"), {
                preserveScroll: true,
                onSuccess: () => {
                    this.displayingToken = true;
                    this.createApiTokenForm.reset();
                },
            });
        },

        manageApiTokenPermissions(token) {
            this.updateApiTokenForm.permissions = token.abilities;

            this.managingPermissionsFor = token;
        },

        updateApiToken() {
            this.updateApiTokenForm.put(
                route("api-tokens.update", this.managingPermissionsFor),
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => (this.managingPermissionsFor = null),
                }
            );
        },

        confirmApiTokenDeletion(token) {
            this.apiTokenBeingDeleted = token;
        },

        deleteApiToken() {
            this.deleteApiTokenForm.delete(
                route("api-tokens.destroy", this.apiTokenBeingDeleted),
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => (this.apiTokenBeingDeleted = null),
                }
            );
        },
    },
});
</script>