<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'lodge_subscription_rate_delete');
$view['slots']->set('headerTitle', 'Delete Subscription Rate');
?>

<div class="lodge-subscription-rate-delete">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-exclamation-triangle"></i>
                Confirm Deletion
            </h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-danger">
                <h4><i class="fa fa-warning"></i> Warning!</h4>
                <p>You are about to delete the subscription rate for <strong><?php echo $rate->getYear(); ?></strong>.</p>
                <p>This action <strong>cannot be undone</strong>.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4>Rate Details</h4>
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Year:</strong></td>
                            <td><?php echo $rate->getYear(); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td>£<?php echo number_format($rate->getAmount(), 2); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td><?php echo $rate->getDescription() ?: '<em>None</em>'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo $rate->getDateAdded()->format('d M Y H:i'); ?></td>
                        </tr>
                        <?php if ($rate->getDateModified()): ?>
                        <tr>
                            <td><strong>Last Modified:</strong></td>
                            <td><?php echo $rate->getDateModified()->format('d M Y H:i'); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <h4><i class="fa fa-info-circle"></i> Important Notes</h4>
                        <ul>
                            <li>Deleting this rate will not affect existing payment records</li>
                            <li>Historical data will remain intact</li>
                            <li>You will need to recreate the rate if needed later</li>
                            <li>Consider editing instead of deleting if you need to make changes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="panel-footer">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" style="display: inline;">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete this rate? This action cannot be undone.');">
                            <i class="fa fa-trash"></i> Yes, Delete Rate
                        </button>
                    </form>
                    <a href="<?php echo $view['router']->path('mautic_subscription_rates'); ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <a href="<?php echo $view['router']->path('mautic_subscription_rate_edit', ['id' => $rate->getId()]); ?>" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Edit Instead
                    </a>
                </div>
            </div>
        </div>
    </div>
</div> 