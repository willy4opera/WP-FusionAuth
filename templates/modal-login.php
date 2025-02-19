<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// Default values if not set
$modal_id = $template_args['modal_id'] ?? 'loginModal';
$show_button = $template_args['show_button'] ?? true;
$button_text = $template_args['button_text'] ?? 'Login';
$button_class = $template_args['button_class'] ?? '';
$show_icon = $template_args['show_icon'] ?? true;
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<?php if ($show_button): ?>
<!-- Modal Trigger Button -->
<button class="modal-trigger-btn biwillz-modal-trigger <?php echo esc_attr($button_class); ?>" data-modal="<?php echo esc_attr($modal_id); ?>">
    <?php if ($show_icon): ?><i class="fas fa-user"></i><?php endif; ?>
    <?php echo esc_html($button_text); ?>
</button>
<?php endif; ?>

<!-- Modal Container -->
<div id="<?php echo esc_attr($modal_id); ?>" class="modal-container">
    <div class="modal-overlay"></div>
    
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        
        <div class="login-container">
           <!-- Login Form -->
<div class="screen-1" id="loginForm">
    <img src="<?php echo plugin_dir_url(__FILE__) . "Biwillz-APP-logo.png"; ?>" alt="Logo" class="auth-logo">
    
    <form method="POST" id="biwillz-login-form">
        <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
        <input type="hidden" name="action" value="handle_modal_login">  <!-- Updated action name -->
        <input type="hidden" name="is_modal_login" value="1">
        <input type="hidden" name="current_page" value="<?php echo esc_url(get_permalink()); ?>">

        <!-- Rest of the form remains the same -->
        <div class="social-login">
            <a href="<?php echo esc_url((new Biwillz_Google_Auth())->get_auth_url()); ?>" class="social-button">
                <i class="fab fa-google"></i>
            </a>
            <a href="<?php echo esc_url(wp_login_url() . '?auth=facebook'); ?>" class="social-button">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="<?php echo esc_url(wp_login_url() . '?auth=linkedin'); ?>" class="social-button">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>

        <div class="divider">
            <span>or continue with email</span>
        </div>

        <div class="email">
            <label for="username">Email Address</label>
            <div class="sec-2">
                <i class="fas fa-envelope"></i>
                <input type="text" name="username" id="username" placeholder="username@example.com" required>
            </div>
        </div>

        <div class="password">
            <label for="password">Password</label>
            <div class="sec-2">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="pas" placeholder="············" required>
                <i class="fas fa-eye show-hide"></i>
            </div>
        </div>

        <div class="remember-me">
            <input type="checkbox" name="remember" id="remember" value="1">
            <label for="remember">Remember me</label>
        </div>
        <?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
        <div class="g-recaptcha" 
            data-sitekey="<?php echo esc_attr(Biwillz_Auth_Settings::get_option('recaptcha_site_key')); ?>"
            data-theme="light"
            data-size="normal"></div>
        <?php endif; ?>
        
        <button type="submit" class="login">
            <div class="spinner"></div>
            <span>Login</span>
        </button>

        <div class="footer1">
            <span onclick="window.location.href='<?php echo esc_url(home_url('/register/')); ?>'">Create Account</span>
            <span onclick="window.location.href='<?php echo esc_url(home_url('/lost-password/')); ?>'">Forgot Password?</span>
        </div>
    </form>
</div>
        </div>
    </div>
</div>

<style>

.login-container {
    display: flex;
    align-items: center;
    justify-content: center;
    
}
* {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}



body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 15% 15%, rgba(46, 8, 244, 0.05) 0%, transparent 40%),
        radial-gradient(circle at 85% 85%, rgba(207, 19, 228, 0.05) 0%, transparent 40%);
    pointer-events: none;
    z-index: 0;
}

.screen-1 {
    background-color: #ffffff;
    padding: 2em;
    display: flex;
    flex-direction: column;
    border-radius: 30px;
    box-shadow: 
        0 10px 20px rgba(46, 8, 244, 0.05),
        0 6px 6px rgba(46, 8, 244, 0.1),
        0 0 100px rgba(46, 8, 244, 0.1);
    max-width: 400px;
    width: 100%;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.auth-logo {
    width: 80px;
    height: 80px;
    margin: -1.5em auto 2em;
    display: block;
    position: relative;
    z-index: 2;
    padding: 15px;
    background: #ffffff;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(46, 8, 244, 0.1);
    border: 2px solid rgba(46, 8, 244, 0.1);
    object-fit: contain;
    transition: all 0.3s ease;
}

.logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46, 8, 244, 0.15);
    border-color: rgba(46, 8, 244, 0.2);
}

.social-login {
    display: flex;
    justify-content: center;
    gap: 1em;
    margin: 1em 0;
}

.social-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid rgba(46, 8, 244, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2e08f4;
    transition: all 0.3s ease;
    background: #ffffff;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(46, 8, 244, 0.1);
}

.social-button:hover {
    background: #2e08f4;
    color: white;
    border-color: #2e08f4;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 8, 244, 0.2);
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 1.5em 0;
    color: rgba(102, 102, 102, 0.8);
    font-size: 0.9em;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid rgba(46, 8, 244, 0.1);
}

.divider span {
    padding: 0 10px;
}

.email, .password {
    background: #f8f9ff;
    box-shadow: 0 2px 10px rgba(46, 8, 244, 0.05);
    padding: 1em;
    display: flex;
    flex-direction: column;
    gap: 0.5em;
    border-radius: 20px;
    color: #333;
    margin-bottom: 1em;
    border: 1px solid rgba(46, 8, 244, 0.1);
    transition: all 0.3s ease;
}

.email:focus-within, 
.password:focus-within {
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(46, 8, 244, 0.1);
    border-color: rgba(46, 8, 244, 0.3);
    transform: translateY(-1px);
}

.email label, .password label {
    font-size: 0.9em;
    color: #333;
    font-weight: 500;
}

.sec-2 {
    display: flex;
    align-items: center;
    gap: 0.5em;
    position: relative;
    width: 100%;
}

.sec-2 i:not(.show-hide) {
    color: rgba(46, 8, 244, 0.6);
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.sec-2 input {
    flex: 1;
    outline: none;
    border: none;
    font-size: 0.9em;
    color: #333;
    background: transparent;
    padding-right: 35px;
    width: 100%;
    min-width: 0;
}

.sec-2 input::placeholder {
    color: #999;
}

.show-hide {
    cursor: pointer;
    color: rgba(46, 8, 244, 0.6);
    transition: color 0.3s ease;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    padding: 5px;
    width: 30px;
    text-align: center;
    z-index: 2;
}

.show-hide:hover {
    color: #2e08f4;
}

.login {
    padding: 1em;
    background: linear-gradient(135deg, #2e08f4 0%, #cf13e4 100%);
    color: white;
    border: none;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin: 1em 0;
    box-shadow: 0 4px 12px rgba(46, 8, 244, 0.2);
}

.login:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(46, 8, 244, 0.3);
}

.login:active {
    transform: translateY(0);
}

.footer1 {
    flex-direction: column;
    gap: 1em;
    text-align: center;
}

.footer1 {
    display: flex;
    justify-content: space-between;
    font-size: 0.8em;
    color: rgba(102, 102, 102, 0.8);
    margin-top: 1em;
}

.footer1 span {
    cursor: pointer;
    color: #2e08f4;
    transition: all 0.3s ease;
}

.footer1 span:hover {
    color: #cf13e4;
    text-decoration: underline;
}


.error-message {
    background-color: #fee2e2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
    padding: 1rem;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    border-radius: 10px;
}

/* Loading spinner */
.spinner {
    display: none;
    width: 20px;
    height: 20px;
    border: 2px solid #ffffff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.login.loading .spinner {
    display: inline-block;
}

.login.loading span {
    display: none;
}

/* Form switching animation */
#loginForm, #registrationForm {
    display: flex;
    opacity: 1;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

#loginForm[style*="none"], #registrationForm[style*="none"] {
    opacity: 0;
    transform: scale(0.95);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .screen-1 {
        padding: 1.5em;
        margin: 1em;
    }

    .auth-logo {
        width: 70px;
        height: 70px;
        margin: -1em auto 1.5em;
        padding: 12px;
    }

 

    .social-login {
        gap: 0.8em;
    }

    .email, .password {
        padding: 0.8em;
    }

    .sec-2 {
        gap: 0.3em;
    }

    .sec-2 input {
        font-size: 16px;
        padding-right: 30px;
    }

    .show-hide {
        width: 24px;
        padding: 3px;
    }
}

@media (max-width: 320px) {
    .screen-1 {
        padding: 1.2em;
    }

    .auth-logo {
        width: 60px;
        height: 60px;
        margin: -0.8em auto 1.2em;
        padding: 10px;
    }

    .sec-2 input {
        padding-right: 25px;
    }

    .show-hide {
        width: 20px;
        padding: 2px;
    }

    .social-button {
        width: 35px;
        height: 35px;
    }
}

</style>
<style>
/* Modal specific styles */
.modal-trigger-btn {
    background: linear-gradient(135deg, #2e08f4 0%, #cf13e4 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 30px;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(46, 8, 244, 0.2);
}

.modal-trigger-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(46, 8, 244, 0.3);
}

.modal-container {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 400px;
    width: 90%;
    animation: modalFadeIn 0.3s ease-out;
    background: white;
    border-radius: 15px;
    padding: 20px;
}

.modal-close {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 30px;
    height: 30px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 20px;
    color: #2e08f4;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.modal-close:hover {
    transform: rotate(90deg);
    background: #2e08f4;
    color: white;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

/* Mobile responsiveness for modal */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
    }
    
    .modal-close {
        top: 10px;
        right: 10px;
    }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the specific modal ID
    const modalId = '<?php echo esc_js($modal_id); ?>';
    const modal = document.getElementById(modalId);

    // Handle modal triggers
    document.querySelectorAll(`[data-modal="${modalId}"], .biwillz-modal-trigger[data-modal="${modalId}"]`).forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(modalId);
        });
    });

    // Handle close button
    if (modal) {
        const closeBtn = modal.querySelector('.modal-close');
        const overlay = modal.querySelector('.modal-overlay');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => closeModal(modalId));
        }

        if (overlay) {
            overlay.addEventListener('click', () => closeModal(modalId));
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal(modalId);
        }
    });

    // Prevent modal close when clicking modal content
    const modalContent = modal.querySelector('.modal-content');
    if (modalContent) {
        modalContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }

    // Password visibility toggle
    const passwordField = document.querySelector('#biwillz-login-form input[name="password"]');
    const showHideButton = document.querySelector('#biwillz-login-form .show-hide');

    if (showHideButton && passwordField) {
        showHideButton.addEventListener('click', function() {
            passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
            showHideButton.classList.toggle('fa-eye');
            showHideButton.classList.toggle('fa-eye-slash');
        });
    }

    // Handle login form submission
    const loginForm = document.getElementById('biwillz-login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Get the modal ID from the closest modal container
            const modalContainer = this.closest('.modal-container');
            const modalId = modalContainer ? modalContainer.id : null;

            const form = this;
            const submitButton = form.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner');
            const buttonText = submitButton.querySelector('span');

            // Check for reCAPTCHA if it exists
            const recaptchaElement = document.querySelector('.g-recaptcha');
            if (recaptchaElement) {
                const recaptchaResponse = grecaptcha.getResponse();
                if (!recaptchaResponse) {
                    handleError('Please complete the reCAPTCHA verification');
                    return;
                }
            }

            // Show loading state
            submitButton.disabled = true;
            spinner.style.display = 'inline-block';
            buttonText.style.opacity = '0';

            try {
                // Collect form data
                const formData = new FormData(form);
                
                // Add reCAPTCHA response if exists
                if (recaptchaElement) {
                    const recaptchaResponse = grecaptcha.getResponse();
                    formData.append('g-recaptcha-response', recaptchaResponse);
                }

                // Send AJAX request
                const response = await fetch(biwillzAuth.ajax_url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                // Reset reCAPTCHA if it exists
                if (recaptchaElement) {
                    grecaptcha.reset();
                }

                if (data.success) {
                    // Close modal first
                    if (modalId) {
                        closeModal(modalId);
                    }

                    // Show success message
                    await Swal.fire({
                        title: biwillzAuth.texts.success,
                        text: data.data.message || biwillzAuth.texts.loginSuccess,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal-custom-popup',
                            title: 'swal-custom-title',
                            content: 'swal-custom-content'
                        }
                    });

                    // Handle redirect
                    if (data.data && data.data.redirect_url) {
                        window.location.href = data.data.redirect_url;
                    } else {
                        window.location.reload();
                    }
                } else {
                    handleError(data.data.message || biwillzAuth.texts.invalidCredentials);
                }
            } catch (error) {
                console.error('Login error:', error);
                // Reset reCAPTCHA if it exists
                if (recaptchaElement) {
                    grecaptcha.reset();
                }
                handleError(biwillzAuth.texts.networkError);
            } finally {
                // Reset button state
                submitButton.disabled = false;
                spinner.style.display = 'none';
                buttonText.style.opacity = '1';
            }
        });
    }
});

// Modal helper functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Error handling function
function handleError(message) {
    Swal.fire({
        title: biwillzAuth.texts.loginFailed,
        text: message,
        icon: 'error',
        customClass: {
            popup: 'swal-custom-popup',
            title: 'swal-custom-title',
            content: 'swal-custom-content',
            confirmButton: 'swal-custom-confirm'
        },
        buttonsStyling: false
    });
}
</script>