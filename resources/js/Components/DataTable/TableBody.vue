<template>
    <tbody>
        <tr
            v-for="(item, idx) in data"
            :key="item?.id || idx"
            :class="{
                'hover:bg-neutral': interactive,
                'hover:text-primary-900': interactive,
            }"
        >
            <td
                v-for="(column, col_ndx) in columns"
                :key="
                    (column?.field ?? column?.name ?? 'unknown') +
                    (item?.id || col_ndx)
                "
                class="border-b"
                :class="{
                    'border-secondary': border === 'secondary',
                    'border-base-content/60': border !== 'secondary',
                    'border-l': col_ndx === 0 && rowBordered,
                    'border-t': rowBordered,
                    'border-r': col_ndx === columns.length - 1 && rowBordered,
                }"
            >
                <div
                    class="flex items-center justify-center h-full customcell"
                    :class="{
                        'border-r':
                            !collapsed &&
                            !column.noSeparator &&
                            col_ndx !== columns.length - 1,
                        'border-secondary': border === 'secondary',
                        'border-neutral': border !== 'secondary',
                    }"
                >
                    <table-cell
                        :value="item[column?.field]"
                        :data="item"
                        :renderer="column.renderer ? column.renderer : null"
                    />
                </div>
            </td>
        </tr>
        <tr
            v-if="!data?.length"
            :class="{
                'hover:bg-neutral': interactive,
                'hover:text-primary-900': interactive,
            }"
        >
            <td
                :colspan="columns.length"
                :class="{
                    'border-secondary': border === 'secondary',
                    'border-neutral': border !== 'secondary',
                    'border-t': rowBordered,
                }"
            >
                <div
                    class="flex items-center justify-center h-full"
                    :class="{
                        'border-secondary': border === 'secondary',
                        'border-neutral': border !== 'secondary',
                    }"
                >
                    <table-cell value="No campaigns found" :renderer="null" />
                </div>
            </td>
        </tr>
    </tbody>
</template>
<style scoped>
td {
    @apply h-px py-3;
}
td > div {
    @apply text-center;
}
</style>
<script setup>
import { h } from "vue";
import TableCell from "./TableCell.vue";
const props = defineProps({
    border: {
        type: String,
        default: "",
    },
    columns: {
        type: Array,
        default: [],
    },
    data: {
        type: Array,
        default: [],
    },
    interactive: {
        type: Boolean,
        default: false,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    rowBordered: {
        type: Boolean,
        default: false,
    },
});
</script>
