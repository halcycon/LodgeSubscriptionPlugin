<!DOCTYPE html>
<html>
    <head>
        <?php $view['slots']->output('head'); ?>
        <title><?php $view['slots']->output('pageTitle', 'Lodge Payment'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $view['assets']->outputSystemStylesheets(); ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getAssetUrl('plugins/LodgeSubscriptionBundle/Assets/css/lodge-theme.css'); ?>" type="text/css" />
        <?php $view['assets']->outputHeadDeclarations(); ?>
    </head>
    <body>
        <div class="lodge-payment-container">
            <div class="lodge-header">
                <img src="<?php echo $view['assets']->getAssetUrl('plugins/LodgeSubscriptionBundle/Assets/img/lodge-logo.png'); ?>" alt="Lodge Logo">
                <h1><?php $view['slots']->output('pageTitle', 'Lodge Payment'); ?></h1>
            </div>
            
            <div class="lodge-content">
                <?php $view['slots']->output('_content'); ?>
            </div>
            
            <div class="lodge-footer">
                <p>&copy; <?php echo date('Y'); ?> Lodge Subscription Service</p>
            </div>
        </div>
        
        <?php echo $view['assets']->outputScripts(); ?>
        <?php $view['assets']->outputScriptDeclarations(); ?>
    </body>
</html> 