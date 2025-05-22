// Views/Integration/contact_payments.html.php
<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$payments = $contact->getPayments();
?>

<div class="tab-pane" id="lodge-payments">
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Year</th>
                    <th>Method</th>
                    <th>Current</th>
                    <th>Arrears</th>
                    <th>Status</th>
                    <th>Received By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo $view['date']->toFull($payment->getDateAdded()); ?></td>
                    <td>£<?php echo number_format($payment->getAmount(), 2); ?></td>
                    <td><?php echo $payment->getYear(); ?></td>
                    <td><?php echo $payment->getPaymentMethod(); ?></td>
                    <td>£<?php echo number_format($payment->getAppliedToCurrent(), 2); ?></td>
                    <td>£<?php echo number_format($payment->getAppliedToArrears(), 2); ?></td>
                    <td><?php echo $payment->getStatus(); ?></td>
                    <td><?php echo $payment->getReceivedBy(); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>