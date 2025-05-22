Mautic.lodgeSubscriptionOnLoad = function() {
    // Initialize any date pickers
    mQuery('.lodge-datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    // Initialize amount formatting
    mQuery('.lodge-amount-input').on('blur', function() {
        var value = parseFloat(mQuery(this).val());
        if (!isNaN(value)) {
            mQuery(this).val(value.toFixed(2));
        }
    });
};

Mautic.processSubscriptionPayment = function(contactId) {
    var amount = mQuery('#payment_amount').val();
    
    Mautic.ajaxActionRequest('plugin:lodgeSubscription:processPayment', {
        contactId: contactId,
        amount: amount
    }, function(response) {
        if (response.success && response.sessionUrl) {
            window.location.href = response.sessionUrl;
        } else {
            Mautic.notificationCenter.addNotification({
                message: response.message || 'An error occurred',
                type: 'error'
            });
        }
    });
};

Mautic.loadNewRateForm = function() {
    Mautic.loadModalForm(
        mauticBaseUrl + 's/lodge/rate/new',
        'new_rate',
        'POST'
    );
};

Mautic.loadRateForm = function(id) {
    Mautic.loadModalForm(
        mauticBaseUrl + 's/lodge/rate/' + id + '/edit',
        'edit_rate',
        'POST'
    );
};

Mautic.submitRateForm = function(form) {
    Mautic.postForm(form, function(response) {
        if (response.success) {
            Mautic.closeModal();
            Mautic.reloadPage();
        }
    });
};