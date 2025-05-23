// Views/SubscriptionRate/form.html.php
<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'lodge_subscription_rate_' . $action);
$view['slots']->set('headerTitle', ($action == 'new') ? 'New Subscription Rate' : 'Edit Subscription Rate');
?>

<div class="lodge-subscription-rate-form">
    <?php echo $view['form']->start($form); ?>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-<?php echo ($action == 'new') ? 'plus' : 'edit'; ?>"></i>
                <?php echo ($action == 'new') ? 'Create New Subscription Rate' : 'Edit Subscription Rate for ' . $rate->getYear(); ?>
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo $view['form']->label($form['year']); ?>
                                <?php echo $view['form']->widget($form['year'], [
                                    'attr' => [
                                        'class' => 'form-control',
                                        'placeholder' => 'e.g. ' . date('Y')
                                    ]
                                ]); ?>
                                <?php echo $view['form']->errors($form['year']); ?>
                                <div class="help-block">
                                    <small>The year this subscription rate applies to</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo $view['form']->label($form['amount']); ?>
                                <div class="input-group">
                                    <span class="input-group-addon">£</span>
                                    <?php echo $view['form']->widget($form['amount'], [
                                        'attr' => [
                                            'class' => 'form-control',
                                            'placeholder' => '0.00',
                                            'step' => '0.01'
                                        ]
                                    ]); ?>
                                </div>
                                <?php echo $view['form']->errors($form['amount']); ?>
                                <div class="help-block">
                                    <small>Annual subscription amount in GBP</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <?php echo $view['form']->label($form['description']); ?>
                        <?php echo $view['form']->widget($form['description'], [
                            'attr' => [
                                'class' => 'form-control',
                                'placeholder' => 'Optional description for this rate...',
                                'rows' => 3
                            ]
                        ]); ?>
                        <?php echo $view['form']->errors($form['description']); ?>
                        <div class="help-block">
                            <small>Optional description or notes about this subscription rate</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <i class="fa fa-info-circle"></i> Rate Guidelines
                            </h4>
                        </div>
                        <div class="panel-body">
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check text-success"></i> One rate per year</li>
                                <li><i class="fa fa-check text-success"></i> Set rates in advance</li>
                                <li><i class="fa fa-check text-success"></i> Include any applicable fees</li>
                                <li><i class="fa fa-check text-success"></i> Consider inflation adjustments</li>
                            </ul>
                            
                            <?php if ($action == 'edit'): ?>
                                <hr>
                                <div class="small text-muted">
                                    <strong>Created:</strong><br>
                                    <?php echo $rate->getDateAdded()->format('d M Y H:i'); ?><br><br>
                                    
                                    <?php if ($rate->getDateModified()): ?>
                                        <strong>Last Modified:</strong><br>
                                        <?php echo $rate->getDateModified()->format('d M Y H:i'); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="panel-footer">
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> 
                        <?php echo ($action == 'new') ? 'Create Rate' : 'Update Rate'; ?>
                    </button>
                    <a href="<?php echo $view['router']->path('mautic_subscription_rates'); ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <?php if ($action == 'edit'): ?>
                        <a href="<?php echo $view['router']->path('mautic_subscription_rate_delete', ['id' => $rate->getId()]); ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete the rate for year <?php echo $rate->getYear(); ?>?');">
                            <i class="fa fa-trash"></i> Delete Rate
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo $view['form']->end($form); ?>
</div>

<script>
// Auto-focus the year field for new rates
<?php if ($action == 'new'): ?>
document.addEventListener('DOMContentLoaded', function() {
    var yearField = document.getElementById('<?php echo $form['year']->vars['id']; ?>');
    if (yearField) {
        yearField.focus();
    }
});
<?php endif; ?>

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.lodge-subscription-rate-form form');
    if (form) {
        form.addEventListener('submit', function(e) {
            var yearField = document.getElementById('<?php echo $form['year']->vars['id']; ?>');
            var amountField = document.getElementById('<?php echo $form['amount']->vars['id']; ?>');
            
            var year = parseInt(yearField.value);
            var amount = parseFloat(amountField.value);
            
            if (isNaN(year) || year < 1900 || year > 2100) {
                alert('Please enter a valid year between 1900 and 2100.');
                yearField.focus();
                e.preventDefault();
                return false;
            }
            
            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid amount greater than 0.');
                amountField.focus();
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>