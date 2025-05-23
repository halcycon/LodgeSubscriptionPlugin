/**
 * Lodge Subscription form fix
 * 
 * This script prevents CKEditor conflicts on the plugin configuration page
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
            
            /* Prevent CKEditor conflicts */
            .lodge-subscription-form .cke {
                display: none !important;
            }
        `;
        document.head.appendChild(styleEl);
    }
    
    // Add CSS to hide unwanted elements
    addStyles();
    
    // Attempt to fix CKEditor conflicts
    function fixCKEditorConflicts() {
        // If CKEditor is already defined and we're on the right page
        if (typeof CKEDITOR !== 'undefined' && window.location.href.indexOf('plugins/config/LodgeSubscription') !== -1) {
            // Find any instances and remove them from our form fields
            var fieldSelectors = [
                '#integration_details_apikeys_stripe_publishable_key',
                '#integration_details_apikeys_stripe_secret_key',
                '#integration_details_apikeys_stripe_webhook_secret'
            ];
            
            fieldSelectors.forEach(function(selector) {
                var element = document.querySelector(selector);
                if (element && CKEDITOR.instances[element.id]) {
                    CKEDITOR.instances[element.id].destroy(true);
                }
            });
            
            // Also look for elements with our special marker
            var formElement = document.querySelector('[data-editor-prevent="true"]');
            if (formElement) {
                var editorFields = formElement.querySelectorAll('textarea, input[type="text"]');
                editorFields.forEach(function(field) {
                    if (field.id && CKEDITOR.instances[field.id]) {
                        CKEDITOR.instances[field.id].destroy(true);
                    }
                });
            }
        }
    }
    
    // Run the fix after a short delay to ensure CKEditor has initialized
    setTimeout(fixCKEditorConflicts, 500);
    setTimeout(fixCKEditorConflicts, 1500); // Run again to catch late initializations
};

// Initialize on page load
if (typeof Mautic !== 'undefined') {
    // Use Mautic's standard callback
    Mautic.onPageLoad('plugin', function(container) {
        Mautic.lodgeSubscriptionFormSetup();
    });
} else {
    // Fallback if Mautic.onPageLoad is not available
    document.addEventListener('DOMContentLoaded', function() {
        Mautic.lodgeSubscriptionFormSetup();
    });
} 