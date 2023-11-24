<?php $num = $attributes->prop('num', null) ?>

<?php echo $_engine->defineRef('count', $num) ?>

<?php echo $_engine->method('click') ?>
this.count++;
console.log(this.msg);
<?php echo $_engine->endMethod () ?>

<?php echo $_engine->computed('Product') ?>

return this.count + 10;

<?php echo $_engine->endComputed() ?>

<slot name="juana"></slot>

<h1>{{ msg }}</h1>

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
<?php /**PATH C:\xampp\htdocs\mvc-framework/resources/views//vue/containers.phtml ENDPATH**/ ?>