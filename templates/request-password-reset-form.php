<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="login-container">
    <div class="screen-1">
        <img src="<?php echo plugin_dir_url(__FILE__) . "Biwillz-APP-logo.png"; ?>" alt="Logo" class="auth-logo">
        
        <form method="POST" id="biwillz-request-reset-form">
            <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
            
            <div class="email">
                <label for="email">Email Address</label>
                <div class="sec-2">
                    <i class="far fa-envelope"></i>
                    <input type="email" 
                           name="user_email" 
                           id="email" 
                           placeholder="Enter Your Email" 
                           required>
                </div>
            </div>

            <button type="submit" class="login">
                <div class="spinner"></div>
                <span>Request Password Reset</span>
            </button>

            <?php if ($atts['show_login'] === 'true'): ?>
                <div class="footer1">
                    <span onclick="window.location.href='<?php echo esc_url(wp_login_url()); ?>'">Back to Login</span>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
(function($) {
    'use strict';

    $(document).ready(function() {
        const form = $('#biwillz-request-reset-form');
        
        form.on('submit', function(e) {
            e.preventDefault();
            const submitButton = form.find('button[type="submit"]');
            const spinner = submitButton.find('.spinner');
            const buttonText = submitButton.find('span');

            // Show loading state
            toggleLoadingState(true, submitButton, spinner, buttonText);

            const formData = new FormData(this);
            formData.append('action', 'biwillz_request_password_reset');

            $.ajax({
                url: biwillzAuth.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    toggleLoadingState(false, submitButton, spinner, buttonText);

                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.data.message,
                            icon: 'success',
                            confirmButtonColor: '#2e08f4'
                        });
                        form[0].reset();
                    } else {
                        showError(response.data.message);
                    }
                },
                error: function() {
                    toggleLoadingState(false, submitButton, spinner, buttonText);
                    showError('An unexpected error occurred. Please try again.');
                }
            });
        });

        function toggleLoadingState(isLoading, button, spinner, buttonText) {
            button.prop('disabled', isLoading);
            spinner.css('display', isLoading ? 'block' : 'none');
            buttonText.css('opacity', isLoading ? '0' : '1');
        }

        function showError(message) {
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#2e08f4'
            });
        }
    });
})(jQuery);
</script>

<?php include 'style.php'; ?>