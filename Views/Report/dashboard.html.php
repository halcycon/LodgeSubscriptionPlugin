<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'subscriptionDashboard');
$view['slots']->set('headerTitle', 'Subscription Dashboard');
?>

<?php if ($permissions['view']): ?>
    <?php $view['slots']->start('actions'); ?>
    <a href="<?php echo $view['router']->path('mautic_subscription_export', ['year' => $year]); ?>" class="btn btn-default" target="_blank">
        <i class="fa fa-download"></i> <?php echo $view['translator']->trans('Export Report'); ?>
    </a>
    <?php $view['slots']->stop(); ?>
<?php endif; ?>

<div class="panel panel-default bdr-t-wdh-0">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Year Selection</h3>
                    </div>
                    <div class="panel-body">
                        <form method="get" action="<?php echo $view['router']->path('mautic_subscription_dashboard'); ?>">
                            <div class="form-group">
                                <label for="year">Select Year:</label>
                                <select name="year" id="year" class="form-control">
                                    <?php foreach ($years as $yr): ?>
                                        <option value="<?php echo $yr; ?>" <?php echo $yr == $year ? 'selected' : ''; ?>>
                                            <?php echo $yr; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">View</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-md">
            <div class="col-md-12">
                <h3>Subscription Summary for <?php echo $year; ?></h3>
            </div>
        </div>

        <div class="row mt-md">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Member Statistics</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">Total Members:</div>
                            <div class="col-md-6"><?php echo $stats['totalMembers']; ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Paid Members:</div>
                            <div class="col-md-6"><?php echo $stats['paidMembers']; ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Unpaid Members:</div>
                            <div class="col-md-6"><?php echo $stats['unpaidMembers']; ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Payment Percentage:</div>
                            <div class="col-md-6">
                                <?php 
                                    $percentage = $stats['totalMembers'] > 0 
                                        ? round(($stats['paidMembers'] / $stats['totalMembers']) * 100, 1) 
                                        : 0; 
                                    echo $percentage . '%';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Financial Summary</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">Current Year Total:</div>
                            <div class="col-md-6">£<?php echo number_format($stats['currentTotal'], 2); ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Arrears Total:</div>
                            <div class="col-md-6">£<?php echo number_format($stats['arrearsTotal'], 2); ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6"><strong>Total Outstanding:</strong></div>
                            <div class="col-md-6"><strong>£<?php echo number_format($stats['grandTotal'], 2); ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Payment Statistics</h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">Total Payments:</div>
                            <div class="col-md-6"><?php echo $paymentStats['paymentCount']; ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Total Amount:</div>
                            <div class="col-md-6">£<?php echo number_format($paymentStats['totalAmount'], 2); ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Current Year:</div>
                            <div class="col-md-6">£<?php echo number_format($paymentStats['totalAppliedToCurrent'], 2); ?></div>
                        </div>
                        <div class="row mt-sm">
                            <div class="col-md-6">Applied to Arrears:</div>
                            <div class="col-md-6">£<?php echo number_format($paymentStats['totalAppliedToArrears'], 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-md">
            <div class="col-md-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title">Members With Outstanding Balances</h3>
                    </div>
                    <div class="panel-body">
                        <p>Use these links to take action on members with outstanding balances:</p>
                        <ul>
                            <li><a href="#" class="btn btn-default btn-sm" onclick="mQuery('#unpaidSegment').toggle(); return false;">
                                <i class="fa fa-filter"></i> Show/Hide Segment Builder Query
                            </a></li>
                            <li><a href="#" class="btn btn-warning btn-sm">
                                <i class="fa fa-envelope"></i> Send Reminder Emails
                            </a></li>
                            <li><a href="#" class="btn btn-info btn-sm">
                                <i class="fa fa-list"></i> View Unpaid Members Report
                            </a></li>
                        </ul>
                        
                        <div id="unpaidSegment" class="mt-md" style="display:none;">
                            <h4>Segment Builder Query</h4>
                            <pre>craft_paid_current = false AND craft_<?php echo $year; ?>_due = true</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-md">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Year-End Tools</h3>
                    </div>
                    <div class="panel-body">
                        <p>Use these tools to manage year-end processes:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="well">
                                    <h4>Year-End Process</h4>
                                    <p>Run the year-end process to move current year dues to arrears and set up new year dues.</p>
                                    <p class="text-warning">This should be run at the end of <?php echo $year; ?>.</p>
                                    <a href="#" class="btn btn-primary">Run Year-End Process</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="well">
                                    <h4>Set Next Year's Rate</h4>
                                    <p>Make sure to set up the subscription rate for the next year before running the year-end process.</p>
                                    <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-success">Create New Rate</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 