<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <script src="http://[::1]:5173/@vite/client" type="module"  ></script>
<script src="http://[::1]:5173/resources/assets/js/main.js" type="module"  ></script>
</head>

<body>
    <div id="app" data-html="<?php echo htmlentities(json_encode($bridgeContent), ENT_QUOTES, 'UTF-8') ?>" data-components="<?php echo htmlentities(json_encode($components), ENT_QUOTES, 'UTF-8') ?>" data-events="<?php echo htmlentities(json_encode($events), ENT_QUOTES, 'UTF-8') ?>" data-options="<?php echo htmlentities(json_encode($options), ENT_QUOTES, 'UTF-8') ?>">
<?php echo $bridgeContent ?>
</div>
</body>

</html>

<?php /**PATH C:\xampp\htdocs\mvc-framework/resources/views/root.phtml ENDPATH**/ ?>