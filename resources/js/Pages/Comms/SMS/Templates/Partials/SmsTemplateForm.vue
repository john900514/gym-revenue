<template>
    <jet-form-section @submitted="handleSubmit">
        <!--        <template #title>-->
        <!--            Location Details-->
        <!--        </template>-->

        <!--        <template #description>-->
        <!--            {{ buttonText }} a location.-->
        <!--        </template>-->
        <template #form>
            <div class="form-control col-span-6">
                <label for="name" class="label">Name</label>
                <input type="text" v-model="form.name" autofocus  id="name"/>
                <jet-input-error :message="form.errors.name" class="mt-2"/>
            </div>
            <div class="col-span-6">
                <sms-form-control v-model="form.markup" id="markup" label="Template"/>
                <jet-input-error :message="form.errors.markup" class="mt-2"/>
            </div>
            <div class="form-control col-span-6 flex flex-row" v-if="canActivate">
                <input type="checkbox" v-model="form.active" autofocus id="active" class="mt-2" :value="true"/>
                <label for="name" class="label ml-4">Activate (allows assigning to Campaigns)</label>
                <jet-input-error :message="form.errors.active" class="mt-2" />
            </div>
<!--                <input id="client_id" type="hidden" v-model="form.client_id"/>-->
<!--                <jet-input-error :message="form.errors.client_id" class="mt-2"/>-->
        </template>

        <template #actions>
<!--            TODO: navigation links should always be Anchors. We need to extract button css so that we can style links as buttons-->
            <Button type="button" @click="$inertia.visit(route('comms.sms-templates'))" :class="{ 'opacity-25': form.processing }" error outline :disabled="form.processing">
                Cancel
            </Button>
            <div class="flex-grow" />
            <Button class="btn-secondary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"  :loading="form.processing">
                {{ buttonText }}
            </Button>
        </template>
    </jet-form-section>
</template>

<script>
import {useForm} from '@inertiajs/inertia-vue3'
import SmsFormControl from "@/Components/SmsFormControl"
import AppLayout from '@/Layouts/AppLayout'
import Button from '@/Components/Button'
import JetFormSection from '@/Jetstream/FormSection'
import JetInputError from '@/Jetstream/InputError'

export default {
    components: {
        AppLayout,
        Button,
        JetFormSection,
        SmsFormControl,
        JetInputError,
    },
    props: ['clientId', 'template', 'canActivate'],
    setup(props, context) {
        let template = props.template;
        let operation = 'Update';
        if (!template) {
            template = {
                markup: null,
                name: null,
                active: false
                // client_id: props.clientId
            }
            operation = 'Create';
        }

        const form = useForm(template)

        let handleSubmit = () => form.put(route('comms.sms-templates.update', template.id));
        if (operation === 'Create') {
            handleSubmit = () => form.post(route('comms.sms-templates.store'));
        }


        return {form, buttonText: operation, handleSubmit}
    },
}
</script>