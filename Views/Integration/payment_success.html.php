<?php $view->extend('@LodgeSubscriptionBundle/Themes/lodge/html/page.html.php'); ?>

<?php $view['slots']->set('pageTitle', 'Payment Successful'); ?>

<div class="payment-status payment-success">
    <h1><i class="fa fa-check-circle"></i> Payment Successful</h1>
    <p>Thank you for your payment. Your lodge subscription has been processed successfully.</p>
    
    <?php if (isset($contact) && $contact): ?>
    <div class="payment-details">
        <h3>Payment Details</h3>
        <div class="details-table">
            <table>
                <tr>
                    <td><strong>Member:</strong></td>
                    <td><?php echo $view->escape($contact->getName()); ?></td>
                </tr>
                <tr>
                    <td><strong>Amount Paid:</strong></td>
                    <td>£<?php echo number_format($amount, 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td><?php echo date('d/m/Y H:i'); ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td>Stripe Online Payment</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>Completed</td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="mt-lg">
        <p>A receipt has been emailed to you.</p>
        <a href="/" class="btn-return">Return to Homepage</a>
    </div>
</div> 