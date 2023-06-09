import gql from "graphql-tag";

import { audience } from "./campaigns/audiences";
import { scheduledCampaign } from "./campaigns/scheduled";
import { dripCampaign } from "./campaigns/drip";
import { smsTemplate } from "./templates/sms";
import { callTemplate } from "./templates/call";

const department = {
    create: gql`
        mutation createDepartment($name: String, $positions: [ID]) {
            createDepartment(name: $name, positions: $positions) {
                id
                name
            }
        }
    `,
    update: gql`
        mutation updateDepartment($id: ID, $name: String, $positions: [ID]) {
            updateDepartment(id: $id, name: $name, positions: $positions) {
                id
                name
            }
        }
    `,
};
const position = {
    create: gql`
        mutation createPosition($input: CreatePositionInput) {
            createPosition(input: $input) {
                id
                name
            }
        }
    `,
    update: gql`
        mutation updatePosition($input: UpdatePositionInput) {
            updatePosition(input: $input) {
                id
                name
            }
        }
    `,
};

const profile = {
    update: gql`
        mutation updateProfile($name: String) {
            updateProfile(name: $name) {
                id
                name
            }
        }
    `,
};

const profile_photo = {
    delete: gql`
        mutation deleteProfilePhoto($id: ID) {
            deleteProfilePhoto(id: $id) {
                id
            }
        }
    `,
};

const role = {
    create: gql`
        mutation createRole($input: CreateRoleInput) {
            createRole(input: $input) {
                id
                name
                created_at
                updated_at
                group
            }
        }
    `,
    update: gql`
        mutation updateRole($input: UpdateRoleInput) {
            updateRole(input: $input) {
                id
                name
                created_at
                updated_at
                group
            }
        }
    `,
    delete: gql`
        mutation deleteRole($id: ID!) {
            deleteRole(id: $id) {
                id
                name
                group
            }
        }
    `,
};

const member = {
    create: gql`
        mutation createMember($first_name: String) {
            createMember(first_name: $first_name) {
                id
            }
        }
    `,
    update: gql`
        mutation updateMember($id: ID, $first_name: String) {
            updateMember(id: $id, first_name: $first_name) {
                id
            }
        }
    `,
};

const user = {
    create: gql`
        mutation createUser($input: UserInput) {
            createUser(input: $input) {
                id
                name
            }
        }
    `,
    update: gql`
        mutation updateUser($input: UserInput) {
            updateUser(input: $input) {
                id
                name
            }
        }
    `,
};

const customer = {
    create: gql`
        mutation createCustomer($input: CustomerInput) {
            createCustomer(input: $input) {
                id
                first_name
                last_name
            }
        }
    `,
    update: gql`
        mutation updateCustomer($input: CustomerInput) {
            updateCustomer(input: $input) {
                id
                first_name
                last_name
            }
        }
    `,
};

const team = {
    create: gql`
        mutation createTeam($name: String, $positions: [ID]) {
            createTeam(name: $name, positions: $positions) {
                id
                name
            }
        }
    `,
    update: gql`
        mutation updateTeam($id: ID, $name: String, $positions: [ID]) {
            updateTeam(id: $id, name: $name, positions: $positions) {
                id
                name
            }
        }
    `,
};

const location = {
    create: gql`
        mutation createLocation($location: CreateLocationInput!) {
            createLocation(location: $location) {
                id
                gymrevenue_id
                location_no
                location_type
                name
                city
                state
                active
                zip
                address1
                address2
                phone
                open_date
                close_date
            }
        }
    `,
    update: gql`
        mutation updateLocation($location: UpdateLocationInput!) {
            updateLocation(location: $location) {
                id
                gymrevenue_id
                location_no
                location_type
                name
                city
                state
                active
                zip
                address1
                address2
                phone
                open_date
                close_date
            }
        }
    `,
};

const task = {
    create: gql`
        mutation createCalendarEvent($input: CreateCalendarEventInput!) {
            createCalendarEvent(input: $input) {
                id
                title
            }
        }
    `,
    update: gql`
        mutation updateCalendarEvent($input: PatchCalendarEventInput!) {
            updateCalendarEvent(input: $input) {
                id
                title
            }
        }
    `,
};

const emailTemplate = {
    create: gql`
        mutation createEmailTemplate($input: CreateEmailTemplateInput!) {
            createEmailTemplate(input: $input) {
                id
                name
                subject
                markup
                json
            }
        }
    `,
    update: gql`
        mutation updateEmailTemplate($input: UpdateEmailTemplateInput) {
            updateEmailTemplate(input: $input) {
                id
                name
                subject
                markup
                json
            }
        }
    `,
};

const note = {
    create: gql`
        mutation createNote($input: CreateNoteInput!) {
            createNote(input: $input) {
                id
                title
                note
                active
            }
        }
    `,
    update: gql`
        mutation updateNote($input: UpdateNoteInput!) {
            updateNote(input: $input) {
                id
                title
                note
                active
            }
        }
    `,
};

const folder = {
    create: gql`
        mutation createFolder($name: String!) {
            createFolder(name: $name) {
                id
                name
                created_at
            }
        }
    `,
    update: gql`
        mutation updateFolder($id: ID!, $name: String!) {
            updateFolder(id: $id, name: $name) {
                id
                name
            }
        }
    `,
    trash: gql`
        mutation trashFolder($id: ID!) {
            trashFolder(id: $id) {
                id
                name
            }
        }
    `,
    restore: gql`
        mutation restoreFolder($id: ID!) {
            restoreFolder(id: $id) {
                id
                name
            }
        }
    `,
    share: gql`
        mutation updateFolderSharing($input: FolderSharingInput!) {
            updateFolderSharing(input: $input) {
                id
                name
            }
        }
    `,
};

const file = {
    create: gql`
        mutation createFile($file: CreateFileInput) {
            createFile(file: $file) {
                id
                filename
            }
        }
    `,
    rename: gql`
        mutation renameFile($id: ID!, $filename: String!) {
            renameFile(id: $id, filename: $filename) {
                id
                filename
            }
        }
    `,
    restore: gql`
        mutation restoreFile($id: ID!) {
            restoreFile(id: $id) {
                id
                filename
            }
        }
    `,
    delete: gql`
        mutation deleteFile($id: ID!) {
            deleteFile(id: $id) {
                id
                filename
            }
        }
    `,
    trash: gql`
        mutation trashFile($id: ID!) {
            trashFile(id: $id) {
                id
                filename
            }
        }
    `,
    updateFileFolder: gql`
        mutation updateFileFolder($id: ID!, $folder: String) {
            updateFileFolder(id: $id, folder: $folder) {
                id
                filename
            }
        }
    `,
    updatePermissions: gql`
        mutation updateFilePermissions($id: ID!, $permissions: String) {
            updateFilePermissions(id: $id, permissions: $permissions) {
                id
                filename
            }
        }
    `,
};
export default {
    audience,
    scheduledCampaign,
    dripCampaign,
    smsTemplate,
    callTemplate,
    department,
    position,
    role,
    user,
    team,
    location,
    task,
    emailTemplate,
    note,
    folder,
    file,
    customer,
    profile,
    profile_photo,
    member,
};
