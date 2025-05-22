<?php $view->extend('themes/lodge/html/base.html.php'); ?>

<div class="lodge-payment-page">
    <div class="lodge-header">
        <h1>Payment Successful</h1>
    </div>

    <div class="lodge-status success">
        <h2>Thank you, {lodge_member_name}</h2>
        <p>Your payment of <span class="lodge-amount">£{lodge_payment_amount}</span> has been successfully processed.</p>
    </div>

    <div class="lodge-payment-summary">
        <h3>Payment Details</h3>
        <p>Date: <?php echo date('d/m/Y'); ?></p>
        <p>Year: {lodge_payment_year}</p>
        <p>Amount Paid: <span class="lodge-amount">£{lodge_payment_amount}</span></p>
    </div>

    <div class="lodge-footer">
        <p>A receipt has been sent to your email address.</p>
        <p><small>Please retain this confirmation for your records.</small></p>
    </div>
</div>