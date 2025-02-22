<script>
jQuery(document).ready(function($) {
    // Close modal when clicking the close button
    $('.biwillz-modal-close').on('click', function() {
        $(this).closest('.biwillz-modal-container').hide();
    });

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('biwillz-modal-container')) {
            $('.biwillz-modal-container').hide();
        }
    });
});

// Handle forgot password functionality


</script>