<!DOCTYPE html>
<html>
    <head>
        <?php $view['slots']->output('head'); ?>
        <title><?php $view['slots']->output('pageTitle'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <?php echo $view['assets']->outputSystemStylesheets(); ?>
        <link rel="stylesheet" href="<?php echo $view['assets']->getAssetUrl('plugins/LodgeSubscriptionPlugin/Assets/css/lodge-theme.css'); ?>" type="text/css" />
        <?php $view['assets']->outputHeadDeclarations(); ?>
    </head>
    <body>
        <div class="lodge-container">
            <div class="lodge-header">
                <div class="lodge-logo">
                    <img src="<?php echo $view['assets']->getAssetUrl('plugins/LodgeSubscriptionPlugin/Assets/img/lodge-logo.png'); ?>" alt="Lodge Logo">
                </div>
            </div>
            
            <div class="lodge-content">
                <?php $view['slots']->output('content'); ?>
            </div>
            
            <div class="lodge-footer">
                <p>&copy; <?php echo date('Y'); ?> - Lodge Subscription Manager</p>
            </div>
        </div>
        
        <?php echo $view['assets']->outputScripts(); ?>
        <?php $view['assets']->outputScriptDeclarations(); ?>
    </body>
</html> 