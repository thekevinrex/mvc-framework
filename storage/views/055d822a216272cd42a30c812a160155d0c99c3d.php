

<?php $_engine->startSection ('content') ?>
<div class="container p">

    <div class="col w-1/4">
        make operations (sell, bay, change)

        <?php echo $_engine->render('hola',[]) ?>
    </div>

    <div class="col w-full border border-gray px items-center">
        display all operations

        <?php $_engine->startSection ('component') ?>
        <?php $component = $_engine->component('containers', 'app\core\view\AnonymosComponent',"vue:msg=hola jijo puta,num=44"); ?>
<?php if (isset($component)){ $component_fde52d22e4fd9dc0e802c3f72b64249383ff1d4f = $component; } ?>
<?php if(isset($component_fde52d22e4fd9dc0e802c3f72b64249383ff1d4f) && $component_fde52d22e4fd9dc0e802c3f72b64249383ff1d4f->shouldRender()): ?>
<?php $_engine->startComponent('containers'); ?>
<?php echo $_engine->renderComponent(); endif; ?>
        <?php $_engine->endSection (); ?>

        <?php $component = $_engine->component('prueba', '\app\App\View\Components\PruebaComponent',""); ?>
<?php if (isset($component)){ $component_5d207dee6e124997ac58b1790acfd89b08fb025d = $component; } ?>
<?php if(isset($component_5d207dee6e124997ac58b1790acfd89b08fb025d) && $component_5d207dee6e124997ac58b1790acfd89b08fb025d->shouldRender()): ?>
<?php $_engine->startComponent('prueba'); ?>
            probando prueba component
            <hr>
            <a href="<?php echo e(route('home')) ?>"><?php echo e(url()->current()) ?></a>

            <?php $name = $_engine->slot('name',""); ?>
<?php $_engine->startSlot('name') ?>probando normal slot
<?php $_engine->endSlot() ?>
        
<?php echo $_engine->renderComponent(); endif; ?>

    </div>

    <div class="col w-1/4">
        show current money in all the containers
    </div>

</div>
<?php $_engine->endSection (); ?>
<?php echo $_engine->render('layaut') ?>
<?php /**PATH C:\xampp\htdocs\mvc-framework/resources/views/index.phtml ENDPATH**/ ?>