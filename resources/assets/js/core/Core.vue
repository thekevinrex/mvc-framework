<template>
    <KeepAlive :max="1">
        <Render :html="html" />
    </KeepAlive>
</template>

<script setup>

import { defineProps, onMounted, h } from "@vue/runtime-core";
import { ref } from "@vue/reactivity";
import { isString } from "@vue/shared";
import Render from "./components/Render.vue";
import Bridge from './core.js';

const props = defineProps({
    el: {
        type: [String, Object],
        default: (props) => {
            return Bridge.defaultElement;
        }
    },

    baseTemplate: {
        type: [Object],
        default: (props) => {
            return null;
        }
    },

    initialHtml: {
        type: [String],
        default: (props) => {
            const $el = isString(props.el) ? document.getElementById(props.el) : props.el;
            return JSON.parse($el.dataset['html']);
        }
    },

    initialComponents: {
        type: [String, Object],
        default: (props) => {
            const $el = isString(props.el) ? document.getElementById(props.el) : props.el;
            return JSON.parse($el.dataset['components']);
        }
    },

    initialOptions: {
        type: [String, Object],
        default: (props) => {
            const $el = isString(props.el) ? document.getElementById(props.el) : props.el;
            return JSON.parse($el.dataset['options']);
        }
    },

    initialEvents: {
        type: [String, Object],
        default: (props) => {
            const $el = isString(props.el) ? document.getElementById(props.el) : props.el;
            return JSON.parse($el.dataset['events']);
        }
    }
});

const html = ref();

Bridge.setElement(props.el);

Bridge.onHtml((newHtml) => {

    html.value = (props.baseTemplate)
        ? h(
            props.baseTemplate,
            {},
            () => [
                h(Render, { html: newHtml })
            ]
        ) : newHtml

});

Bridge.init(
    props.initialHtml,
    props.initialEvents,
    props.initialOptions,
    props.initialComponents
);

onMounted(() => {
    const $el = isString(props.el) ? document.getElementById(props.el) : props.el;

    ["components", "html", "options", "events"].forEach((attribute) => {
        delete $el.dataset[attribute];
    });
});

</script>