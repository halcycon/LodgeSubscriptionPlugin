// Views/SubscriptionRate/form.html.php
<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$header = $view['form']->vars['data']->getId() 
    ? 'Edit Subscription Rate for ' . $view['form']->vars['data']->getYear()
    : 'New Subscription Rate';
$view['slots']->set('headerTitle', $header);
$view['slots']->set('mauticContent', 'subscriptionRate');
?>

<div class="box-layout">
    <div class="col-md-9 height-auto bg-white">
        <div class="row">
            <div class="col-xs-12">
                <?php echo $view['form']->start($form); ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="pa-md bg-auto">
                            <div class="form-group row">
                                <label class="col-sm-3 control-label"><?php echo $view['form']->label($form['year']); ?></label>
                                <div class="col-sm-9">
                                    <?php echo $view['form']->widget($form['year']); ?>
                                    <?php echo $view['form']->errors($form['year']); ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 control-label"><?php echo $view['form']->label($form['amount']); ?></label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <span class="input-group-addon">£</span>
                                        <?php echo $view['form']->widget($form['amount']); ?>
                                    </div>
                                    <?php echo $view['form']->errors($form['amount']); ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 control-label"><?php echo $view['form']->label($form['description']); ?></label>
                                <div class="col-sm-9">
                                    <?php echo $view['form']->widget($form['description']); ?>
                                    <?php echo $view['form']->errors($form['description']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="pa-md bg-auto">
                    <?php echo $view['form']->widget($form['buttons']); ?>
                </div>
            </div>
        </div>
        <?php echo $view['form']->end($form); ?>
    </div>
    <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <div class="panel bg-info">
                <div class="panel-body">
                    <h3>Tips</h3>
                    <p>Set the annual subscription rate for each year. Rates should be created in advance before running the year-end process.</p>
                    <p>The current year's rate determines how much will be charged to members marked as due to pay.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>