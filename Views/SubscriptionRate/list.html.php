// Views/Rate/list.html.php
<?php $view->extend('MauticCoreBundle:Default:content.html.php'); ?>

<div class="box-layout">
    <div class="col-md-9 height-auto">
        <div class="bg-auto">
            <div class="pa-md">
                <div class="row">
                    <div class="col-sm-12">
                        <h3>Subscription Rates</h3>
                        <button class="btn btn-primary" onclick="Mautic.loadNewRateForm();">
                            Add New Rate
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rates as $rate): ?>
                            <tr>
                                <td><?php echo $rate->getYear(); ?></td>
                                <td>£<?php echo number_format($rate->getAmount(), 2); ?></td>
                                <td><?php echo $rate->getDescription(); ?></td>
                                <td>
                                    <button class="btn btn-default btn-xs" onclick="Mautic.loadRateForm(<?php echo $rate->getId(); ?>);">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>