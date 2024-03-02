<template>
    <render v-if="html" />
</template>

<script setup>
import { ref } from "@vue/reactivity";
import { defineProps, h, watch } from "@vue/runtime-core";
import { isString } from "@vue/shared";

const props = defineProps([
    'html',
]);

const render = ref(null);

function updateRender() {
    render.value = (isString(props.html)) ?
        h({
            template: props.html,
            data() {
                return {};
            },
        }) : props.html;
}

watch(() => props.html, updateRender, { immediate: true });
</script>