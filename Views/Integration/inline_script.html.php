<?php if (strpos($app->getRequest()->getRequestUri(), 'plugins/config/LodgeSubscription') !== false): ?>
<script>
// Immediately executing script to hide datepicker buttons
(function() {
    // Insert CSS styles
    var style = document.createElement('style');
    style.innerHTML = `
        .btn-datepicker, 
        .btn-tertiary.btn-icon, 
        [size="32"],
        label.btn.btn-tertiary,
        *[class*="btn-datepicker"],
        [for*="stripe_publishable_key"],
        #integration_details_apikeys .btn-tertiary {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden !important;
            position: absolute !important;
            clip: rect(0, 0, 0, 0) !important;
            border: 0 !important;
        }

        /* Make form fields full width */
        input[id*="stripe_publishable_key"],
        input[id*="stripe_secret_key"], 
        input[id*="stripe_webhook_secret"] {
            width: 100% !important;
            display: block !important;
            margin-right: 0 !important;
        }
    `;
    document.head.appendChild(style);

    // Function to forcefully remove elements
    function removeElements() {
        var buttons = document.querySelectorAll(
            '.btn-datepicker, .btn-tertiary.btn-icon, [size="32"], label.btn.btn-tertiary'
        );
        
        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i] && buttons[i].parentNode) {
                buttons[i].parentNode.removeChild(buttons[i]);
            }
        }
    }

    // Run immediately and on intervals
    removeElements();
    setInterval(removeElements, 200);
    
    // Run when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        removeElements();
        setTimeout(removeElements, 500);
    });
    
    // Run on all user interactions
    document.addEventListener('click', removeElements);
    document.addEventListener('mouseover', removeElements);
})();
</script>
<?php endif; ?> 