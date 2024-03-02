@extends ([
'layaut',
'asdas'
], (sss))
@section ('content')
<section>
<?php echo expression(str &lt; 5 | uppercase (asda)) ?>
<?php if (isset($component)){ $component_99632d157d272ea2c3763585a8896fc8ede903dd = $component; } ?>
<?php $component = $_engine->component('tuhermana', 'PhpVueBridge\View\AnonymosComponent','[]'); ?>
<?php if(isset($component) && $component->shouldRender()): ?>
<?php $_engine->startComponent('tuhermana'); ?>
<?php echo $_engine->renderComponent(); endif; ?>
<?php if (isset($_component_99632d157d272ea2c3763585a8896fc8ede903dd)){ $_component = $_component_99632d157d272ea2c3763585a8896fc8ede903dd; } ?>
</section>
<div @probadoinnerdirective (5 < 4) style="border:1px solid red;">
dividasd
<?php if (isset($component)){ $component_cd2b063eec7e7a66bdf7660f7ccc4cbb4ff00167 = $component; } ?>
<?php $component = $_engine->component('component-prueba', 'PhpVueBridge\View\AnonymosComponent','pedro={{ juena }}'); ?>
<?php if(isset($component) && $component->shouldRender()): ?>
<?php $_engine->startComponent('component-prueba'); ?>
<?php if (isset($component)){ $component_8616c6b39e13a533cd097151a751e4084ede689a = $component; } ?>
<?php $component = $_engine->component('component-inner', 'PhpVueBridge\View\AnonymosComponent','[]'); ?>
<?php if(isset($component) && $component->shouldRender()): ?>
<?php $_engine->startComponent('component-inner'); ?>
probando inner component
<?php echo $_engine->renderComponent(); endif; ?>
<?php if (isset($_component_8616c6b39e13a533cd097151a751e4084ede689a)){ $_component = $_component_8616c6b39e13a533cd097151a751e4084ede689a; } ?>
<?php echo $_engine->renderComponent(); endif; ?>
<?php if (isset($_component_cd2b063eec7e7a66bdf7660f7ccc4cbb4ff00167)){ $_component = $_component_cd2b063eec7e7a66bdf7660f7ccc4cbb4ff00167; } ?>
<p>
hola
</p>
</div>
@yield ('hola')
hola probando
@endsection
<?php /**PATH C:\xampp\htdocs\mvc-framework\resources\views/hola.phtml ENDPATH**/ ?>