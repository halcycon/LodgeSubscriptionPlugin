<?php $view->extend('themes/lodge/html/base.html.php'); ?>

<div class="lodge-payment-page">
    <div class="lodge-header">
        <h1>Lodge Subscription Payment</h1>
    </div>

    <div class="lodge-payment-summary">
        <h2>Payment Details for {lodge_member_name}</h2>
        <div class="lodge-amount-details">
            <p>Current Year Subscription: <span class="lodge-amount">£{lodge_payment_amount}</span></p>
            
            <?php if ({lodge_arrears_amount} > 0): ?>
            <p>Outstanding Arrears: <span class="lodge-amount">£{lodge_arrears_amount}</span></p>
            <p>Total Amount Due: <span class="lodge-amount">£{lodge_total_outstanding}</span></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="lodge-action">
        <p>Please click below to make your secure payment:</p>
        <a href="{stripe_payment_link}" class="lodge-button">Pay Now</a>
    </div>

    <div class="lodge-footer">
        <p><small>This is a secure payment processed by Stripe. For any queries, please contact the Lodge Secretary.</small></p>
    </div>
</div>