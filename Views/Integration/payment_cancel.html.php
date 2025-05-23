<?php $view->extend('LodgeSubscriptionBundle:Themes:lodge/html/page.html.php'); ?>

<?php $view['slots']->set('pageTitle', 'Payment Cancelled'); ?>

<div class="payment-status payment-cancel">
    <h1><i class="fa fa-times-circle"></i> Payment Cancelled</h1>
    <p>Your payment has been cancelled. No charges have been made to your account.</p>
    
    <?php if (isset($contact) && $contact): ?>
    <div class="payment-details">
        <h3>Your Subscription Details</h3>
        <div class="details-table">
            <table>
                <tr>
                    <td><strong>Member:</strong></td>
                    <td><?php echo $view->escape($contact->getName()); ?></td>
                </tr>
                <tr>
                    <td><strong>Current Subscription:</strong></td>
                    <td>£<?php echo number_format($contact->getFieldValue('craft_owed_current'), 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Arrears:</strong></td>
                    <td>£<?php echo number_format($contact->getFieldValue('craft_owed_arrears'), 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Total Outstanding:</strong></td>
                    <td>£<?php echo number_format(
                        $contact->getFieldValue('craft_owed_current') + 
                        $contact->getFieldValue('craft_owed_arrears'), 2); ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="mt-lg">
        <p>If you would like to try again or use a different payment method, please click the button below.</p>
        <?php if (isset($stripePaymentLink) && $stripePaymentLink): ?>
        <a href="<?php echo $stripePaymentLink; ?>" class="btn-return">Try Again</a>
        <?php else: ?>
        <a href="/" class="btn-return">Return to Homepage</a>
        <?php endif; ?>
    </div>
</div> 