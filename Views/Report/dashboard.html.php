<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'lodge_subscription_dashboard');
$view['slots']->set('headerTitle', 'Lodge Subscription Dashboard');
?>

<div class="lodge-subscription-dashboard">
    <!-- Year selector -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-dashboard"></i>
                Lodge Subscription Overview for 
                <select id="year-selector" onchange="changeYear(this.value)" class="form-control" style="display: inline-block; width: auto;">
                    <?php foreach ($years as $yearOption): ?>
                        <option value="<?php echo $yearOption; ?>" <?php echo $yearOption == $year ? 'selected' : ''; ?>>
                            <?php echo $yearOption; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <!-- Member Statistics -->
                <div class="col-sm-6 col-md-3">
                    <div class="widget widget-stat bg-primary">
                        <div class="widget-body">
                            <div class="widget-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="widget-content">
                                <span class="widget-desc">Total Members</span>
                                <span class="widget-title"><?php echo number_format($stats['totalMembers']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Paid Members -->
                <div class="col-sm-6 col-md-3">
                    <div class="widget widget-stat bg-success">
                        <div class="widget-body">
                            <div class="widget-icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="widget-content">
                                <span class="widget-desc">Paid Members</span>
                                <span class="widget-title"><?php echo number_format($stats['paidMembers']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unpaid Members -->
                <div class="col-sm-6 col-md-3">
                    <div class="widget widget-stat bg-warning">
                        <div class="widget-body">
                            <div class="widget-icon">
                                <i class="fa fa-exclamation-triangle"></i>
                            </div>
                            <div class="widget-content">
                                <span class="widget-desc">Unpaid Members</span>
                                <span class="widget-title"><?php echo number_format($stats['unpaidMembers']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Outstanding -->
                <div class="col-sm-6 col-md-3">
                    <div class="widget widget-stat bg-danger">
                        <div class="widget-body">
                            <div class="widget-icon">
                                <i class="fa fa-pound-sign"></i>
                            </div>
                            <div class="widget-content">
                                <span class="widget-desc">Total Outstanding</span>
                                <span class="widget-title">£<?php echo number_format($stats['grandTotal'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Breakdown -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-chart-bar"></i>
                Financial Breakdown
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Outstanding Amounts</h4>
                    <table class="table table-striped">
                        <tr>
                            <td>Current Year Subscriptions:</td>
                            <td class="text-right"><strong>£<?php echo number_format($stats['currentTotal'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Outstanding Arrears:</td>
                            <td class="text-right"><strong>£<?php echo number_format($stats['arrearsTotal'], 2); ?></strong></td>
                        </tr>
                        <tr class="active">
                            <td><strong>Total Outstanding:</strong></td>
                            <td class="text-right"><strong>£<?php echo number_format($stats['grandTotal'], 2); ?></strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h4>Payment Statistics</h4>
                    <table class="table table-striped">
                        <tr>
                            <td>Total Payments Received:</td>
                            <td class="text-right"><strong>£<?php echo number_format($paymentStats['totalAmount'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Applied to Current Year:</td>
                            <td class="text-right">£<?php echo number_format($paymentStats['totalAppliedToCurrent'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Applied to Arrears:</td>
                            <td class="text-right">£<?php echo number_format($paymentStats['totalAppliedToArrears'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Number of Payments:</td>
                            <td class="text-right"><?php echo number_format($paymentStats['paymentCount']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <?php if ($permissions['view']): ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-cogs"></i>
                Quick Actions
            </h3>
        </div>
        <div class="panel-body">
            <div class="btn-group" role="group">
                <a href="<?php echo $view['router']->path('mautic_subscription_rates'); ?>" class="btn btn-default">
                    <i class="fa fa-list"></i> Manage Subscription Rates
                </a>
                <a href="<?php echo $view['router']->path('mautic_subscription_export', ['year' => $year]); ?>" class="btn btn-primary">
                    <i class="fa fa-download"></i> Export Payments (<?php echo $year; ?>)
                </a>
                <?php if ($permissions['create']): ?>
                <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-success">
                    <i class="fa fa-plus"></i> Add New Rate
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeYear(year) {
    window.location.href = '<?php echo $view['router']->path('mautic_subscription_dashboard'); ?>/' + year;
}
</script> 