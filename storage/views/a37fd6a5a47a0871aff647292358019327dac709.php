<?php $num = $attributes->prop('num', null) ?>

<?php echo $_engine->defineRef('count', $num) ?>

<?php $_engine->startScript('setup') ?>

    import { computed } from 'vue';

    @bridgeData
    @bridgeProps

    const Product = computed(() => msg);


    const click = () => num.value++;

    console.log('console from component containers');


<?php $_engine->endScript() ?>

<slot name="juana"></slot>

<h1>{{ msg }}</h1>

{{ count }}

<div class="card">
    <button type="button" @click="click">count</button>
    <p>
        {{ Product }}
        Edit
        <code>components/HelloWorld.vue</code> to test HMR
        <hr>
        {{ num }}
    </p>
</div>

<p>
    Check out
    <a href="https://vuejs.org/guide/quick-start.html#local" target="_blank">create-vue</a>, the official Vue + Vite
    starter
</p>
<p>
    Install
    <a href="https://github.com/vuejs/language-tools" target="_blank">Volar</a>
    in your IDE for a better DX
</p>
<p class="read-the-docs">Click on the Vite and Vue logos to learn more</p>

<?php /**PATH C:\xampp\htdocs\mvc-framework/resources/views/components/containers.vue.phtml ENDPATH**/ ?>