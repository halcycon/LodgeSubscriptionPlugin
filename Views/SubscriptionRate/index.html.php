<?php
/**
 * @package     LodgeSubscriptionPlugin
 * @copyright   2023
 * @author      
 * @license     
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('mauticContent', 'subscriptionRate');
$view['slots']->set('headerTitle', 'Subscription Rates');

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
    <a href="<?php echo $view['router']->path('mautic_subscription_rate_new'); ?>" class="btn btn-default" data-toggle="ajax">
        <i class="fa fa-plus"></i> <?php echo $view['translator']->trans('New Rate'); ?>
    </a>
    <?php $view['slots']->stop(); ?>
<?php endif; ?>

<div class="panel panel-default bdr-t-wdh-0">
    <div class="panel-body">
        <div class="box-layout">
            <div class="col-xs-12 col-md-9">
                <div class="input-group">
                    <input type="text" class="form-control" id="subscription-rate-search" placeholder="Search Rates..." value="<?php echo $view->escape($searchValue); ?>">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="subscription-rate-search-btn">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </div>
            <div class="col-xs-12 col-md-3">
                <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
                    'totalItems' => $totalItems,
                    'page'       => $page,
                    'limit'      => $limit,
                    'baseUrl'    => $view['router']->path('mautic_subscription_rates'),
                    'sessionVar' => 'lodge.subscription.rate',
                ]); ?>
            </div>
        </div>
    </div>
    <div class="panel-heading">
        <h3 class="panel-title">Subscription Rates</h3>
    </div>
    <div class="table-responsive panel-collapse pull out">
        <table class="table table-hover table-striped table-bordered rate-list">
            <thead>
                <tr>
                    <th class="col-rate-year">Year</th>
                    <th class="col-rate-amount">Amount</th>
                    <th class="col-rate-description">Description</th>
                    <th class="col-rate-actions"><?php echo $view['translator']->trans('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($items)): ?>
                <?php foreach ($items as $rate): ?>
                    <tr>
                        <td><?php echo $rate->getYear(); ?></td>
                        <td><?php echo number_format($rate->getAmount(), 2); ?></td>
                        <td><?php echo $view->escape($rate->getDescription()); ?></td>
                        <td>
                            <?php if ($permissions['edit']): ?>
                                <a href="<?php echo $view['router']->path('mautic_subscription_rate_edit', ['id' => $rate->getId()]); ?>" data-toggle="ajax" class="btn btn-default btn-xs">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($permissions['delete']): ?>
                                <a href="<?php echo $view['router']->path('mautic_subscription_rate_delete', ['id' => $rate->getId()]); ?>" data-toggle="confirmation" data-message="<?php echo $view['translator']->trans('Are you sure you want to delete this rate?'); ?>" class="btn btn-danger btn-xs">
                                    <i class="fa fa-trash-o"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">
                        <?php echo $view['translator']->trans('No subscription rates found.'); ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
Mautic.subscriptionRateOnLoad = function() {
    var searchBtn = mQuery('#subscription-rate-search-btn');
    var searchInput = mQuery('#subscription-rate-search');
    
    searchBtn.click(function() {
        var searchValue = searchInput.val().trim();
        var url = '<?php echo $view['router']->path('mautic_subscription_rates'); ?>';
        
        if (searchValue.length) {
            url += '?search=' + encodeURIComponent(searchValue);
        }
        
        window.location = url;
    });
    
    searchInput.keyup(function(e) {
        if (e.keyCode == 13) {
            searchBtn.click();
        }
    });
}</script> 