<template>
    <div class="col-span-6 flex flex-col items-center gap-4 mb-4">
        <div
            class="w-32 h-32 rounded-full overflow-hidden border"
            :style="resolveBorderColorLevel(form['opportunity'])"
        >
            <img
                v-if="fileForm.url"
                :src="fileForm.url"
                alt="member profile picture"
                class="w-full h-full object-cover"
            />
            <img
                v-else-if="member?.profile_picture?.url"
                :src="member.profile_picture.url"
                alt="member profile picture"
                class="w-full h-full object-cover"
            />
            <font-awesome-icon
                v-else
                icon="user-circle"
                size="6x"
                class="opacity-10 !h-full !w-full"
            />
        </div>
        <label class="btn btn-secondary btn-sm btn-outline">
            <span>Upload Image</span>
            <input
                @input="fileForm.file = $event.target.files[0]"
                type="file"
                accept="image/*"
                hidden
                class="hidden"
            />
        </label>
    </div>
    <jet-form-section @submitted="handleSubmit">
        <template #form>
            <div class="form-control col-span-6 md:col-span-2">
                <jet-label for="first_name" value="First Name" />
                <input
                    id="first_name"
                    type="text"
                    v-model="form['first_name']"
                    autofocus
                />
                <jet-input-error
                    :message="form.errors['first_name']"
                    class="mt-2"
                />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="middle_name" value="Middle Name" />
                <input
                    id="middle_name"
                    type="text"
                    v-model="form['middle_name']"
                />
                <jet-input-error
                    :message="form.errors['middle_name']"
                    class="mt-2"
                />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="last_name" value="Last Name" />
                <input id="last_name" type="text" v-model="form['last_name']" />
                <jet-input-error
                    :message="form.errors['last_name']"
                    class="mt-2"
                />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="email" value="Email" />
                <input id="email" type="email" v-model="form.email" />
                <jet-input-error :message="form.errors.email" class="mt-2" />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="phone" value="Primary Phone" />
                <phone-input id="phone" v-model="form.phone" />
                <jet-input-error :message="form.errors.phone" class="mt-2" />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="alternate_phone" value="Alternate Phone" />
                <phone-input
                    id="alternate_phone"
                    v-model="form['alternate_phone']"
                />
                <jet-input-error
                    :message="form.errors.alternate_phone"
                    class="mt-2"
                />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="gender" value="Gender" />
                <select class="" v-model="form['gender']" required id="gender">
                    <option value="">Select a Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <jet-input-error :message="form.errors.gender" class="mt-2" />
            </div>
            <div class="form-control md:col-span-2 col-span-6">
                <jet-label for="date_of_birth" value="Date of Birth" />
                <date-picker
                    v-model="form['date_of_birth']"
                    dark
                    :month-change-on-scroll="false"
                    :auto-apply="true"
                    :close-on-scroll="true"
                />
                <jet-input-error
                    :message="form.errors.date_of_birth"
                    class="mt-2"
                />
            </div>
            <div class="form-divider" />
            <div class="col-span-3 md:col-span-2">
                <jet-label for="city" value="City" />
                <input
                    id="city"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.city"
                />
                <jet-input-error :message="form.errors.city" class="mt-2" />
            </div>
            <div class="col-span-3 md:col-span-2">
                <jet-label for="state" value="State" />
                <multiselect
                    id="state"
                    class="mt-1 multiselect"
                    v-model="form.state"
                    :searchable="true"
                    :create-option="true"
                    :options="validStateSelections"
                    :classes="getDefaultMultiselectTWClasses()"
                />

                <jet-input-error :message="form.errors.state" class="mt-2" />
            </div>
            <div class="col-span-3 md:col-span-2">
                <jet-label for="zip" value="ZIP Code" />
                <input
                    id="zip"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.zip"
                />
                <jet-input-error :message="form.errors.zip" class="mt-2" />
            </div>

            <div class="col-span-6 space-y-2">
                <jet-label for="address1" value="Address" />
                <input
                    id="address1"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.address1"
                />
                <jet-input-error :message="form.errors.address1" class="mt-2" />
                <input
                    id="address2"
                    type="text"
                    class="block w-full mt-1"
                    v-model="form.address2"
                />
                <jet-input-error :message="form.errors.address2" class="mt-2" />
            </div>

            <div
                class="form-control md:col-span-2 col-span-6"
                v-if="member?.agreement_id"
            >
                <jet-label for="agreement_id" value="Agreement Number" />
                <input
                    disabled
                    type="text"
                    v-model="member.agreement_id"
                    class="opacity-70"
                    id="agreement_id"
                />
            </div>

            <div
                class="form-control md:col-span-2 col-span-6"
                v-if="member?.external_id"
            >
                <jet-label for="external_id" value="External ID" />
                <input
                    disabled
                    type="text"
                    v-model="member.external_id"
                    class="opacity-70"
                    id="external_id"
                />
            </div>
            <div
                class="form-control md:col-span-2 col-span-6"
                v-if="member?.misc"
            >
                <jet-label for="json_viewer" value="Additional Data" />
                <vue-json-pretty
                    :data="member.misc"
                    id="json_viewer"
                    class="bg-base-200 border border-2 border-base-content border-opacity-10 rounded-lg p-2"
                />
            </div>

            <div class="form-divider" />
            <div class="form-control md:col-span-2 col-span-6">
                <ClubSelect v-model="form.home_location_id" required />
                <jet-input-error
                    :message="form.errors['home_location_id']"
                    class="mt-2"
                />
            </div>

            <div class="form-divider" />
            <jet-label for="notes" value="Notes" />
            <div class="form-control col-span-6">
                <jet-label for="notes.title" value="Title" />
                <input
                    type="text"
                    v-model="form['notes'].title"
                    id="notes.title"
                />
                <jet-input-error
                    :message="form.errors['notes']?.title"
                    class="mt-2"
                />
            </div>
            <div class="form-control col-span-6">
                <jet-label for="notes" value="Body" />
                <textarea v-model="form['notes'].note" id="notes" />
                <jet-input-error
                    :message="form.errors['notes']?.note"
                    class="mt-2"
                />
            </div>
            <template v-if="member?.all_notes?.length">
                <div class="text-sm font-medium col-span-6">Existing Notes</div>
                <div
                    class="collapse col-span-6"
                    tabindex="0"
                    v-for="(note, ndx) in member.all_notes"
                    :key="note.id"
                >
                    <div
                        class="collapse-title text-sm font-medium"
                        v-on:click="handleNoteExpansion(note)"
                    >
                        <hr
                            v-if="
                                ndx != 0 &&
                                member.all_notes[ndx - 1]['lifecycle'] !=
                                    note['lifecycle']
                            "
                            class="pb-5"
                        />
                        > {{ note.title }}
                        <div
                            v-if="note.read == false"
                            class="badge badge-secondary"
                        >
                            unread
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 collapse-content">
                        <div
                            class="text-sm text-base-content text-opacity-80 bg-base-100 rounded-lg p-2"
                        >
                            {{ note.note }}
                        </div>
                    </div>
                </div>
            </template>
        </template>

        <template #actions>
            <Button
                type="button"
                @click="$emit('close')"
                :class="{ 'opacity-25': form.processing }"
                error
                outline
                :disabled="form.processing"
            >
                Cancel
            </Button>
            <Button
                :class="{ 'opacity-25': form.processing }"
                class="btn-primary"
                :disabled="form.processing || !form.isDirty"
                :loading="form.processing"
            >
                {{ member.id === "" ? "Create" : "Update" }}
            </Button>
        </template>
    </jet-form-section>
</template>

<script setup>
import * as _ from "lodash";
import "@vuepic/vue-datepicker/dist/main.css";
import { computed, watchEffect, ref } from "vue";
import { useGymRevForm } from "@/utils";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faUserCircle } from "@fortawesome/pro-solid-svg-icons";
import { transformDate } from "@/utils/transformDate";
import { useGoBack } from "@/utils";
import { removeTypename } from "@/utils/formatters/removeTypename.js";
import { getDefaultMultiselectTWClasses } from "@/utils";
import { resolveBorderColorLevel } from "@/utils/resolvers/warningLevelToStyle";
import { preformattedForSelect } from "@/utils/formatters/states";
import { usePage } from "@inertiajs/inertia-vue3";

import Vapor from "laravel-vapor";
import Button from "@/Components/Button.vue";
import JetFormSection from "@/Jetstream/FormSection.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import JetLabel from "@/Jetstream/Label.vue";
import DatePicker from "@vuepic/vue-datepicker";
import PhoneInput from "@/Components/PhoneInput.vue";
import ClubSelect from "@/Pages/Locations/Partials/ClubSelect.vue";

import Multiselect from "@vueform/multiselect";

import mutations from "@/gql/mutations";
import { useMutation } from "@vue/apollo-composable";

library.add(faUserCircle);

const props = defineProps({
    member: {
        type: Object,
        default: {
            id: "",
            notes: { title: "", note: "" },
        },
    },
    interactionCount: {
        type: [Number, String],
    },
    locations: {
        type: [Array, Object, String],
    },
});

const emit = defineEmits(["close"]);

const page = usePage();

const { mutate: updateNote } = useMutation(mutations.note.update);
const { mutate: createFile } = useMutation(mutations.file.create);
// const { mutate: createMember } = useMutation(mutations.member.create);
const { mutate: updateMember } = useMutation(mutations.user.update);

// notes come back as an array with all the notes the user has, it needs to be an object
// for the form for new note creation
let member = _.cloneDeep({ ...props.member, notes: { title: "", note: "" } });

const form = useGymRevForm({
    ...member,
    home_location_id: member?.home_location?.id,
});
const fileForm = useGymRevForm({ file: null });
const validStateSelections = ref(preformattedForSelect);

const operFn = computed(() => {
    // return props.member.id === "" ? createMember : updateMember;
    return updateMember;
});

/** Update a note to "seen" */
const handleNoteExpansion = async (note) => {
    await updateNote({
        client_id: props.clientId,
        note: note,
    });

    // emit("close");
};

/** File Input event handler */
const handleFileChange = async () => {
    if (!fileForm.file) return;

    const res = await Vapor.store(fileForm.file, {
        visibility: "public-read",
    });

    fileForm.url = `https://${response.bucket}.s3.amazonaws.com/${response.key}`;

    form.profile_picture = {
        uuid: response.uuid,
        key: response.key,
        extension: response.extension,
        bucket: response.bucket,
    };

    await createFile({
        ...res,
        filename: fileForm.file.name,
        original_filename: fileForm.file.name,
        size: fileForm.file.size,
    });
};

/** Form submission */
const handleSubmit = async () => {
    let formData = form.data();
    removeTypename(formData);

    formData["home_location_id"] = formData["home_location"].id;
    delete formData["home_location"];

    // formData["team_id"] = formData["external_id"];
    // delete formData["external_id"];

    // delete formData["middle_name"];
    // delete formData["alternate_phone"];
    delete formData["primary_phone"];
    // delete formData["gender"];
    // delete formData["date_of_birth"];
    // delete formData["agreement_number"];
    delete formData["misc"];
    // delete formData["client"];

    await operFn.value({
        input: formData,
    });

    form.notes = { title: "", note: "" };

    emit("close");
};

// const goBack = useGoBack(route("data.members"));
</script>

<style scoped>
input[type="text"],
input[type="email"],
select {
    @apply w-full;
}

.form-divider {
    @apply col-span-6 border-t-2 border-secondary relative;
}

.form-divider > span {
    @apply absolute inset-0  transform -translate-y-1/2 text-xs text-opacity-30 bg-base-300 block;
}
</style>
