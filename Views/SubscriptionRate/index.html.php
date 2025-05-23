<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'lodge_subscription_rates');
$view['slots']->set('headerTitle', 'Lodge Subscription Rates');

// Set up security checks
$security = $view['security'];
$permissions = [
    'create' => $security->isGranted('lodge:subscriptions:create'),
    'edit'   => $security->isGranted('lodge:subscriptions:edit'),
    'delete' => $security->isGranted('lodge:subscriptions:delete')
];
?>

<?php if ($permissions['create']): ?>
    <?php $view['slots']->start('actions'); ?>
    <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-default">
        <i class="fa fa-plus"></i> Add New Rate
    </a>
    <?php $view['slots']->stop(); ?>
<?php endif; ?>

<div class="lodge-subscription-rates">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-list"></i>
                Subscription Rates Management
            </h3>
        </div>
        <div class="panel-body">
            <?php if (empty($rates)): ?>
                <div class="alert alert-info">
                    <p><strong>No subscription rates found.</strong></p>
                    <p>You need to create subscription rates for each year to manage lodge subscriptions.</p>
                    <?php if ($permissions['create']): ?>
                        <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create First Rate
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Date Added</th>
                                <th>Last Modified</th>
                                <th class="text-center" width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rates as $rate): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $rate->getYear(); ?></strong>
                                        <?php if ($rate->getYear() == date('Y')): ?>
                                            <span class="label label-primary">Current Year</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            <strong>£<?php echo number_format($rate->getAmount(), 2); ?></strong>
                                        </span>
                                    </td>
                                    <td><?php echo $rate->getDescription() ?: '<em>No description</em>'; ?></td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo $rate->getDateAdded()->format('d M Y'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo $rate->getDateModified() ? $rate->getDateModified()->format('d M Y') : 'Never'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-xs" role="group">
                                            <?php if ($permissions['edit']): ?>
                                                <a href="<?php echo $view['router']->path('mautic_subscription_rate_edit', ['id' => $rate->getId()]); ?>" 
                                                   class="btn btn-primary btn-xs" title="Edit Rate">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($permissions['delete']): ?>
                                                <a href="<?php echo $view['router']->path('mautic_subscription_rate_delete', ['id' => $rate->getId()]); ?>" 
                                                   class="btn btn-danger btn-xs" title="Delete Rate"
                                                   onclick="return confirm('Are you sure you want to delete the rate for year <?php echo $rate->getYear(); ?>?');">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h4><i class="fa fa-info-circle"></i> Rate Management Tips</h4>
                            <ul class="mb-0">
                                <li>Each year should have only one subscription rate</li>
                                <li>Create rates in advance for upcoming years</li>
                                <li>The current year rate is used for new subscription calculations</li>
                                <li>Historical rates are preserved for reporting purposes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-cogs"></i>
                Quick Actions
            </h3>
        </div>
        <div class="panel-body">
            <div class="btn-group" role="group">
                <a href="<?php echo $view['router']->path('mautic_subscription_dashboard'); ?>" class="btn btn-default">
                    <i class="fa fa-dashboard"></i> Back to Dashboard
                </a>
                <?php if ($permissions['create']): ?>
                    <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-success">
                        <i class="fa fa-plus"></i> Add New Rate
                    </a>
                <?php endif; ?>
                <a href="<?php echo $view['router']->path('mautic_subscription_export'); ?>" class="btn btn-primary">
                    <i class="fa fa-download"></i> Export Payments
                </a>
            </div>
        </div>
    </div>
</div> 