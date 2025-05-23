/**
 * Lodge Subscription form fix
 * 
 * This script only runs on the plugin configuration page
 * and removes unwanted datepicker elements
 */
Mautic.lodgeSubscriptionFormSetup = function() {
    // Only run this script on the plugin config page
    if (window.location.href.indexOf('plugins/config/LodgeSubscription') === -1) {
        return;
    }
    
    function addStyles() {
        var styleEl = document.createElement('style');
        styleEl.type = 'text/css';
        styleEl.innerHTML = `
            .btn-datepicker, .btn-tertiary.btn-icon {
                display: none !important;
            }
            #integration_details_apikeys_stripe_publishable_key,
            #integration_details_apikeys_stripe_secret_key,
            #integration_details_apikeys_stripe_webhook_secret {
                width: 100% !important;
            }
        `;
        document.head.appendChild(styleEl);
    }
    
    // Add CSS to hide unwanted elements
    addStyles();
};

// Initialize on page load (if supported by Mautic)
if (typeof Mautic !== 'undefined') {
    Mautic.onPageLoad('plugin', function(container) {
        Mautic.lodgeSubscriptionFormSetup();
    });
} else {
    // Fallback if Mautic.onPageLoad is not available
    document.addEventListener('DOMContentLoaded', function() {
        Mautic.lodgeSubscriptionFormSetup();
    });
} 