<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Get the reset key and login from URL parameters
$reset_key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// Check if required parameters are present
$show_form = !empty($reset_key) && !empty($login);
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="login-container">
    <div class="screen-1" id="resetPasswordForm">
        <img src="<?php echo plugin_dir_url(__FILE__) . "Biwillz-APP-logo.png"; ?>" alt="Logo" class="auth-logo">
        
        <?php if ($show_form): ?>
            <form method="POST" id="biwillz-reset-password-form">
                <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
                <input type="hidden" name="key" value="<?php echo esc_attr($reset_key); ?>">
                <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">

                <div class="password">
                    <label for="new-password">New Password</label>
                    <div class="sec-2">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="new_password" 
                               id="new-password" 
                               class="pas" 
                               placeholder="············" 
                               required>
                        <i class="fas fa-eye show-hide"></i>
                    </div>
                </div>

                <div class="password">
                    <label for="confirm-password">Confirm Password</label>
                    <div class="sec-2">
                        <i class="fas fa-lock"></i>
                        <input type="password" 
                               name="confirm_password" 
                               id="confirm-password" 
                               class="pas" 
                               placeholder="············" 
                               required>
                        <i class="fas fa-eye show-hide"></i>
                    </div>
                </div>

                <div class="password-strength-meter">
                    <div class="meter-section" data-requirement="length">
                        <i class="fas fa-circle"></i> 8+ characters
                    </div>
                    <div class="meter-section" data-requirement="uppercase">
                        <i class="fas fa-circle"></i> Uppercase
                    </div>
                    <div class="meter-section" data-requirement="lowercase">
                        <i class="fas fa-circle"></i> Lowercase
                    </div>
                    <div class="meter-section" data-requirement="number">
                        <i class="fas fa-circle"></i> Number
                    </div>
                </div>

                <button type="submit" class="login">
                    <div class="spinner"></div>
                    <span>Reset Password</span>
                </button>

                <div class="footer1">
                    <span onclick="window.location.href='<?php echo esc_url(wp_login_url()); ?>'">Back to Login</span>
                </div>
            </form>
        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>Invalid or expired password reset link.</p>
                <div class="footer1">
                    <span onclick="window.location.href='<?php echo esc_url(wp_login_url()); ?>'">Back to Login</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function($) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        initializeResetForm();
    });

    function initializeResetForm() {
        const form = document.getElementById('biwillz-reset-password-form');
        if (form) {
            setupPasswordVisibility();
            setupPasswordStrengthMeter();
            form.addEventListener('submit', handleResetSubmit);
        }
    }

    function setupPasswordVisibility() {
        document.querySelectorAll('.show-hide').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    }

    function setupPasswordStrengthMeter() {
        const passwordInput = document.getElementById('new-password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                updatePasswordStrength(this.value);
            });
        }
    }

    function updatePasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password)
        };

        Object.keys(requirements).forEach(req => {
            const element = document.querySelector(`[data-requirement="${req}"]`);
            if (element) {
                element.classList.toggle('met', requirements[req]);
                const icon = element.querySelector('i');
                icon.classList.toggle('fa-check-circle', requirements[req]);
                icon.classList.toggle('fa-circle', !requirements[req]);
            }
        });

        return Object.values(requirements).every(Boolean);
    }

    function handleResetSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner');
        const buttonText = submitButton.querySelector('span');

        // Validate form
        if (!validateResetForm(form)) return;

        // Show loading state
        toggleLoadingState(true, submitButton, spinner, buttonText);

        // Get form data
        const formData = new FormData(form);
        formData.append('action', 'biwillz_do_password_reset');

        // Make AJAX request
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
                    }).then(() => {
                        window.location.href = response.data.redirect_url;
                    });
                } else {
                    showError(response.data.message);
                }
            },
            error: function(xhr) {
                toggleLoadingState(false, submitButton, spinner, buttonText);
                let errorMessage = 'An unexpected error occurred. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.data?.message) {
                        errorMessage = response.data.message;
                    }
                } catch(e) {}
                showError(errorMessage);
            }
        });
    }

    function validateResetForm(form) {
        const newPassword = form.querySelector('#new-password').value;
        const confirmPassword = form.querySelector('#confirm-password').value;

        if (!updatePasswordStrength(newPassword)) {
            showError('Please ensure your password meets all requirements');
            return false;
        }

        if (newPassword !== confirmPassword) {
            showError('Passwords do not match');
            return false;
        }

        return true;
    }

    function toggleLoadingState(isLoading, button, spinner, buttonText) {
        button.disabled = isLoading;
        spinner.style.display = isLoading ? 'block' : 'none';
        buttonText.style.opacity = isLoading ? '0' : '1';
    }

    function showError(message) {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonColor: '#2e08f4'
        });
    }
})(jQuery);
</script>

<?php include 'style.php'; ?>