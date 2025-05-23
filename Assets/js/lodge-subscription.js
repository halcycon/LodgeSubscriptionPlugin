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

/**
 * Lodge Subscription Plugin JavaScript
 * Removes unwanted date picker buttons from the configuration form
 */

document.addEventListener('DOMContentLoaded', function() {
    // Remove date picker buttons specifically for this plugin
    function removeDatePickers() {
        // Multiple selectors to catch all possible date picker buttons
        const selectors = [
            '.btn-datepicker',
            '.btn.btn-tertiary.btn-icon.btn-nospin.btn-datepicker',
            'label.btn.btn-tertiary.btn-icon.btn-nospin.btn-datepicker',
            'label[size="32"]',
            'div[id="integration_details_apikeys"] .btn-datepicker',
            'form[name="integration_details"] .btn-datepicker'
        ];

        selectors.forEach(function(selector) {
            const elements = document.querySelectorAll(selector);
            elements.forEach(function(element) {
                // Check if this is related to our plugin (has stripe in the for attribute or nearby input)
                const forAttr = element.getAttribute('for') || '';
                const parentDiv = element.closest('div[id="integration_details_apikeys"]');
                
                if (forAttr.includes('stripe') || parentDiv) {
                    element.style.display = 'none';
                    element.style.visibility = 'hidden';
                    element.style.position = 'absolute';
                    element.style.width = '0';
                    element.style.height = '0';
                    element.remove(); // Actually remove the element
                }
            });
        });
    }

    // Run immediately
    removeDatePickers();

    // Run again after a short delay to catch any dynamically added elements
    setTimeout(removeDatePickers, 100);
    setTimeout(removeDatePickers, 500);
    setTimeout(removeDatePickers, 1000);

    // Also run when the form changes
    const form = document.querySelector('form[name="integration_details"]');
    if (form) {
        form.addEventListener('change', function() {
            setTimeout(removeDatePickers, 50);
        });
    }

    // Watch for mutations in the form area
    const targetNode = document.querySelector('div[id="integration_details_apikeys"]');
    if (targetNode) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    removeDatePickers();
                }
            });
        });

        observer.observe(targetNode, {
            childList: true,
            subtree: true
        });
    }
}); 