jQuery(document).ready(function($) {
    // Close button handler
    $(document).on('click', '.biwillz-modal-close', function() {
        closeModal();
    });

    // Close on overlay click
    $(document).on('click', '.biwillz-modal-container', function(e) {
        if ($(e.target).hasClass('biwillz-modal-container')) {
            closeModal();
        }
    });

    // Close on ESC key
    $(document).on('keyup', function(e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });

    function closeModal() {
        $('.biwillz-modal-container').removeClass('active');
        $('body').removeClass('modal-open');
    }

    // Make closeModal available globally
    window.closeAuthModal = closeModal;
});