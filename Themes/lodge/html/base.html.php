<!DOCTYPE html>
<html>
    <head>
        <?php $view['assets']->outputSystemStylesheets(); ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getUrl('themes/lodge/css/custom.css'); ?>">
        <style>
            :root {
                --primary-color: <?php echo $config['primary_color']; ?>;
                --secondary-color: <?php echo $config['secondary_color']; ?>;
                --font-family: <?php echo $config['font_family']; ?>;
            }
        </style>
    </head>
    <body>
        <div class="lodge-container">
            <?php $view['slots']->output('_content'); ?>
        </div>
        <?php $view['assets']->outputSystemScripts(); ?>
    </body>
</html>