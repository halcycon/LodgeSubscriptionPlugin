/**
 * JavaScript to remove the datepicker button from the form
 */
Mautic.lodgeSubscriptionFormSetup = function() {
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        // Run immediately
        removeDatepickerButtons();
        
        // Also run after a slight delay to catch elements added later
        setTimeout(removeDatepickerButtons, 500);
        setTimeout(removeDatepickerButtons, 1000);
        setTimeout(removeDatepickerButtons, 2000);
    });
    
    // Run when modal is opened
    document.addEventListener('click', function(event) {
        if (event.target && event.target.closest('[data-toggle="ajaxmodal"]')) {
            setTimeout(removeDatepickerButtons, 500);
            setTimeout(removeDatepickerButtons, 1000);
        }
    });

    function removeDatepickerButtons() {
        // Find and remove all btn-datepicker elements 
        var datepickerButtons = document.querySelectorAll('.btn-datepicker, .btn-tertiary.btn-icon');
        
        datepickerButtons.forEach(function(button) {
            // Check if this button is near our fields
            var nearField = button.previousElementSibling && 
                (button.previousElementSibling.id === 'integration_details_apikeys_stripe_publishable_key' ||
                 button.previousElementSibling.classList.contains('lodge-key-field'));
                
            if (nearField || button.closest('#integration_details_apikeys')) {
                button.style.display = 'none';
                button.style.visibility = 'hidden';
                button.style.width = '0';
                button.style.height = '0';
                button.style.position = 'absolute';
                button.style.overflow = 'hidden';
                
                // Adjust field width
                if (button.previousElementSibling) {
                    button.previousElementSibling.style.width = '100%';
                    button.previousElementSibling.style.maxWidth = '100%';
                }
            }
        });
        
        // Make all lodge key fields full width
        var keyFields = document.querySelectorAll('.lodge-key-field');
        keyFields.forEach(function(field) {
            field.style.width = '100%';
            field.style.maxWidth = '100%';
            
            // Get parent flex container
            var flexParent = field.closest('.d-flex');
            if (flexParent) {
                flexParent.style.display = 'block';
            }
        });
    }
};

// Initialize when script loads
Mautic.lodgeSubscriptionFormSetup(); 