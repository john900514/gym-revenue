<template>
    <button
        :type="type"
        class="btn w-max rounded"
        :class="{
            'btn-outline': outline,
            'btn-ghost': ghost,
            'btn-circle': circle,
            'btn-square': square,
            loading: loading,
            'btn-primary': primary,
            'btn-secondary': secondary,
            'btn-accent': accent,
            'btn-info': info,
            'btn-success': success,
            'btn-warning': warning,
            'btn-error': error,
            'btn-xs': size === 'xs',
            'btn-sm': size === 'sm',
            'btn-lg': size === 'lg',
        }"
    >
        <slot></slot>
    </button>
</template>

<script>
import { defineComponent } from "vue";

export default defineComponent({
    props: {
        type: {
            type: String,
            default: "submit",
        },
        ghost: {
            type: Boolean,
            default: false,
        },
        outline: {
            type: Boolean,
            default: false,
        },
        circle: {
            type: Boolean,
            default: false,
        },
        square: {
            type: Boolean,
            default: false,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        primary: {
            type: Boolean,
            default: false,
        },
        secondary: {
            type: Boolean,
            default: false,
        },
        accent: {
            type: Boolean,
            default: false,
        },
        error: {
            type: Boolean,
            default: false,
        },
        success: {
            type: Boolean,
            default: false,
        },
        warning: {
            type: Boolean,
            default: false,
        },
        info: {
            type: Boolean,
            default: false,
        },
        size: {
            type: String,
        },
    },
});
</script>
