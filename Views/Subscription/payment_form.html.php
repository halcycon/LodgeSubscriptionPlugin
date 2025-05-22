<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'subscriptionPayment');
$view['slots']->set('headerTitle', 'Record Subscription Payment');
?>

<div class="box-layout">
    <div class="col-md-8 height-auto bg-white">
        <div class="row">
            <div class="col-xs-12">
                <?php echo $view['form']->start($form); ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="pa-md bg-auto">
                            <h3>Contact: <?php echo $view->escape($contact->getName()); ?></h3>
                            
                            <div class="mt-lg mb-lg">
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Outstanding Amounts</h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Current Subscription:</strong>
                                            </div>
                                            <div class="col-md-8">
                                                £<?php echo number_format($currentOwed, 2); ?>
                                            </div>
                                        </div>
                                        <div class="row mt-sm">
                                            <div class="col-md-4">
                                                <strong>Arrears:</strong>
                                            </div>
                                            <div class="col-md-8">
                                                £<?php echo number_format($arrearsOwed, 2); ?>
                                            </div>
                                        </div>
                                        <div class="row mt-sm">
                                            <div class="col-md-4">
                                                <strong>Total Due:</strong>
                                            </div>
                                            <div class="col-md-8">
                                                <strong>£<?php echo number_format($totalOwed, 2); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($stripePaymentLink): ?>
                            <div class="mb-lg">
                                <a href="<?php echo $stripePaymentLink; ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-credit-card"></i> Pay Online via Stripe
                                </a>
                            </div>
                            <?php endif; ?>

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
                                <label class="col-sm-3 control-label"><?php echo $view['form']->label($form['paymentMethod']); ?></label>
                                <div class="col-sm-9">
                                    <?php echo $view['form']->widget($form['paymentMethod']); ?>
                                    <?php echo $view['form']->errors($form['paymentMethod']); ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 control-label"><?php echo $view['form']->label($form['notes']); ?></label>
                                <div class="col-sm-9">
                                    <?php echo $view['form']->widget($form['notes']); ?>
                                    <?php echo $view['form']->errors($form['notes']); ?>
                                </div>
                            </div>

                            <?php echo $view['form']->widget($form['contactId']); ?>
                            <?php echo $view['form']->widget($form['year']); ?>
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
    <div class="col-md-4 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <div class="panel bg-info">
                <div class="panel-body">
                    <h3>Payment Instructions</h3>
                    <p>Record a payment received from a member. The payment will be automatically applied to current dues first, then to any arrears.</p>
                    <p>If you need to generate an online payment link for this member, use the Stripe button.</p>
                </div>
            </div>
            
            <?php if (!empty($totalOwed)): ?>
            <div class="panel bg-warning">
                <div class="panel-body">
                    <h3>Outstanding Balance</h3>
                    <p>This member has an outstanding balance of £<?php echo number_format($totalOwed, 2); ?>.</p>
                </div>
            </div>
            <?php else: ?>
            <div class="panel bg-success">
                <div class="panel-body">
                    <h3>Fully Paid</h3>
                    <p>This member has no outstanding balance.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div> 