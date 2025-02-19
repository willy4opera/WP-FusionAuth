<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
// Prevent WordPress from loading the header and footer
define('SHORTINIT', true);
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<div class="login-container">
 <!-- Login Form -->
<div class="screen-1" id="loginForm">
    <img src="<?php echo plugin_dir_url(__FILE__) . "Biwillz-APP-logo.png"; ?>" alt="Logo" class="auth-logo">
    
    <form method="POST" id="biwillz-login-form">
        <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
        
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
        <span id="login-validation-message" class="validation-message"></span>
        <div class="remember-me">
            <input type="checkbox" name="remember" id="remember" value="1">
            <label for="remember">Remember me</label>
        </div>
        <?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
        <div id="login-recaptcha" class="g-recaptcha" 
             data-sitekey="<?php echo esc_attr(Biwillz_Auth_Settings::get_option('recaptcha_site_key')); ?>"
             data-theme="light"
             data-size="normal"></div>
        <?php endif; ?>
        <button type="submit" class="login">
            <div class="spinner"></div>
            <span>Login</span>
        </button>

        <div class="footer1">
            <span onclick="switchForm('register')">Create Account</span>
            <span onclick="showForgotPassword()">Forgot Password?</span>
        </div>
    </form>
</div>
<!-- Registration Form -->
<div class="screen-1" id="registerForm" style="display: none;">
    <img src="<?php echo plugin_dir_url(__FILE__) . "Biwillz-APP-logo.png"; ?>" alt="Logo" class="auth-logo">

    <form method="POST" id="biwillz-register-form">
        <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
        
        <div class="social-login">
            <a href="<?php echo esc_url((new Biwillz_Google_Auth())->get_auth_url()); ?>" class="social-button">
                <i class="fab fa-google"></i>
            </a>
            <a href="<?php echo esc_url(wp_registration_url() . '?auth=facebook'); ?>" class="social-button">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="<?php echo esc_url(wp_registration_url() . '?auth=linkedin'); ?>" class="social-button">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>

        <div class="divider">
            <span>or register with email</span>
        </div>

        <div id="register-message" class="auth-message" style="display: none;"></div>

        <div class="email">
            <label for="reg-username">Username</label>
            <div class="sec-2">
                <i class="fas fa-user"></i>
                <input type="text" name="username" id="reg-username" placeholder="johndoe" required>
            </div>
        </div>

        <div class="email">
            <label for="reg-email">Email Address</label>
            <div class="sec-2">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="reg-email" placeholder="username@example.com" required>
            </div>
        </div>

        <!-- <div class="email">
            <label for="reg-phone">Phone Number</label>
            <div class="sec-2">
                <i class="fas fa-phone"></i>
                <input type="tel" name="phone" id="reg-phone" placeholder="+1234567890" required 
                       pattern="^\+[0-9]{1,3}[0-9]{4,14}$" 
                       title="Please enter a valid international phone number starting with + and country code">
            </div>
        </div> -->

        <div class="email">
    <label for="reg-phone">Phone Number</label>
    <div class="sec-2">
        <i class="fas fa-phone"></i>
        <div class="phone-input-group">
            <select name="country_code" id="country-code" class="country-select">
                <option value="+1" data-country="US">+1 (US)</option>
                <option value="+44" data-country="GB">+44 (UK)</option>
                <option value="+234" data-country="NG">+234 (NG)</option>
                <option value="+233" data-country="GH">+233 (GH)</option>
                <option value="+254" data-country="KE">+254 (KE)</option>
                <option value="+27" data-country="ZA">+27 (ZA)</option>
                <option value="+91" data-country="IN">+91 (IN)</option>
                <option value="+86" data-country="CN">+86 (CN)</option>
                <option value="+81" data-country="JP">+81 (JP)</option>
                <option value="+49" data-country="DE">+49 (DE)</option>
                <option value="+33" data-country="FR">+33 (FR)</option>
                <option value="+61" data-country="AU">+61 (AU)</option>
                <option value="+971" data-country="AE">+971 (AE)</option>
                <option value="+966" data-country="SA">+966 (SA)</option>
                <option value="+20" data-country="EG">+20 (EG)</option>
            </select>
            <input type="tel" 
                   name="phone" 
                   id="reg-phone" 
                   placeholder="Phone number" 
                   required 
                   pattern="[0-9]{4,14}"
                   title="Please enter a valid phone number">
        </div>
    </div>
    <span id="phone-validation-message" class="validation-message"></span>
</div>

        <div class="password">
            <label for="reg-password">Password</label>
            <div class="sec-2">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="reg-password" class="pas" placeholder="············" required>
                <i class="fas fa-eye show-hide"></i>
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
        </div>

        <div class="terms-container">
            <label class="terms-label">
                <input type="checkbox" name="terms_accepted" id="terms-checkbox" value="yes" required>
                <span class="checkmark"></span>
                <span class="terms-text">
                    I agree to the <a href="https://biwillzcomputers.com/refund_returns/" target="_blank" class="terms-link">Terms and Conditions</a>
                </span>
            </label>
        </div>
        <?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
        <div id="register-recaptcha" class="g-recaptcha" 
             data-sitekey="<?php echo esc_attr(Biwillz_Auth_Settings::get_option('recaptcha_site_key')); ?>"
             data-theme="light"
             data-size="normal"></div>
        <?php endif; ?>

        <button type="submit" class="login">
            <div class="spinner"></div>
            <span>Create Account</span>
        </button>

        <div class="footer1">
            <span onclick="switchForm('login')">Already have an account? Login</span>
        </div>
    </form>
</div>
</div>
<?php include 'style.php'; ?>
<script>
(function($) {
    'use strict';

    // Store reCAPTCHA widget IDs
    let loginRecaptchaId, registerRecaptchaId;

    document.addEventListener('DOMContentLoaded', function() {
        initializeAuth();
    });

    function initializeAuth() {
        initializeRecaptcha();
        setupLoginForm();
        setupRegisterForm();
        setupPasswordVisibility();
        setupFormSwitching();
    }

    function initializeRecaptcha() {
        if (typeof grecaptcha !== 'undefined') {
            if (document.querySelector('#login-recaptcha')) {
                loginRecaptchaId = grecaptcha.render('login-recaptcha', {
                    'sitekey': '<?php echo esc_attr(Biwillz_Auth_Settings::get_option("recaptcha_site_key")); ?>',
                    'theme': 'light'
                });
            }
            if (document.querySelector('#register-recaptcha')) {
                registerRecaptchaId = grecaptcha.render('register-recaptcha', {
                    'sitekey': '<?php echo esc_attr(Biwillz_Auth_Settings::get_option("recaptcha_site_key")); ?>',
                    'theme': 'light'
                });
            }
        }
    }

    function setupLoginForm() {
        const loginForm = document.getElementById('biwillz-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', handleLoginSubmit);
        }
    }

    function handleLoginSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner');
        const buttonText = submitButton.querySelector('span');

        // Validate form
        if (!validateLoginForm(form)) return;

        // Show loading state
        toggleLoadingState(true, submitButton, spinner, buttonText);

        // Get form data
        const formData = new FormData(form);
        formData.append('action', 'biwillz_modal_login');
        formData.append('current_page', window.location.href);

        // Verify reCAPTCHA if enabled
        if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
            const recaptchaResponse = grecaptcha.getResponse(loginRecaptchaId);
            if (!recaptchaResponse) {
                showError('Please complete the reCAPTCHA verification');
                toggleLoadingState(false, submitButton, spinner, buttonText);
                return;
            }
            formData.append('g-recaptcha-response', recaptchaResponse);
        }

        // Make AJAX request
        $.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
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
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }
                    });
                } else {
                    showError(response.data.message);
                    if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
                        grecaptcha.reset(loginRecaptchaId);
                    }
                }
            },
            error: function() {
                toggleLoadingState(false, submitButton, spinner, buttonText);
                showError('An unexpected error occurred. Please try again.');
                if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
                    grecaptcha.reset(loginRecaptchaId);
                }
            }
        });
    }

    function validateLoginForm(form) {
        const username = form.querySelector('#username').value.trim();
        const password = form.querySelector('#password').value.trim();
        const validationMessage = form.querySelector('#login-validation-message');

        if (username.length < 3) {
            showValidationError(validationMessage, 'Username or email is too short');
            return false;
        }

        if (username.includes('@') && !isValidEmail(username)) {
            showValidationError(validationMessage, 'Please enter a valid email address');
            return false;
        }

        if (password.length < 6) {
            showValidationError(validationMessage, 'Password must be at least 6 characters');
            return false;
        }

        hideValidationError(validationMessage);
        return true;
    }

    function setupRegisterForm() {
        const registerForm = document.getElementById('biwillz-register-form');
        if (registerForm) {
            setupPhoneValidation();
            setupPasswordStrengthMeter();
            registerForm.addEventListener('submit', handleRegisterSubmit);
        }
    }

    // function handleRegisterSubmit(e) {
    //     e.preventDefault();
    //     const form = e.target;
    //     const submitButton = form.querySelector('button[type="submit"]');
    //     const spinner = submitButton.querySelector('.spinner');
    //     const buttonText = submitButton.querySelector('span');

    //     // Validate form
    //     if (!validateRegistrationForm(form)) return;

    //     // Show loading state
    //     toggleLoadingState(true, submitButton, spinner, buttonText);

    //     // Get form data
    //     const formData = new FormData(form);
    //     formData.append('action', 'biwillz_register');

    //     // Verify reCAPTCHA if enabled
    //     if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
    //         const recaptchaResponse = grecaptcha.getResponse(registerRecaptchaId);
    //         if (!recaptchaResponse) {
    //             showError('Please complete the reCAPTCHA verification');
    //             toggleLoadingState(false, submitButton, spinner, buttonText);
    //             return;
    //         }
    //         formData.append('g-recaptcha-response', recaptchaResponse);
    //     }

    //     // Make AJAX request
    //     $.ajax({
    //         url: '<?php echo admin_url("admin-ajax.php"); ?>',
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         success: function(response) {
    //             toggleLoadingState(false, submitButton, spinner, buttonText);

    //             if (response.success) {
    //                 Swal.fire({
    //                     title: 'Success!',
    //                     text: response.data.message,
    //                     icon: 'success',
    //                     timer: 2000,
    //                     showConfirmButton: false
    //                 }).then(() => {
    //                     if (response.data.redirect_url) {
    //                         window.location.href = response.data.redirect_url;
    //                     }
    //                 });
    //             } else {
    //                 showError(response.data.message);
    //                 if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
    //                     grecaptcha.reset(registerRecaptchaId);
    //                 }
    //             }
    //         },
    //         error: function() {
    //             toggleLoadingState(false, submitButton, spinner, buttonText);
    //             showError('An unexpected error occurred. Please try again.');
    //             if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
    //                 grecaptcha.reset(registerRecaptchaId);
    //             }
    //         }
    //     });
    // }

//     function handleRegisterSubmit(e) {
//     e.preventDefault();
//     const form = e.target;
//     const submitButton = form.querySelector('button[type="submit"]');
//     const spinner = submitButton.querySelector('.spinner');
//     const buttonText = submitButton.querySelector('span');

//     // Reset any existing error messages
//     resetValidationMessages();

//     // Validate form
//     if (!validateRegistrationForm(form)) {
//         return;
//     }

//     // Show loading state
//     toggleLoadingState(true, submitButton, spinner, buttonText);

//     // Get form data
//     const formData = new FormData(form);
//     formData.append('action', 'biwillz_register');

//     // First, check if username exists
//     checkUsernameAvailability(
//         formData.get('username'),
//         () => {
//             // Username is available, proceed with registration
//             proceedWithRegistration(formData, submitButton, spinner, buttonText);
//         },
//         (errorMessage) => {
//             // Username is taken or error occurred
//             toggleLoadingState(false, submitButton, spinner, buttonText);
//             showError(errorMessage);
            
//             // Focus on username field
//             const usernameInput = form.querySelector('#reg-username');
//             if (usernameInput) {
//                 usernameInput.focus();
//                 usernameInput.classList.add('invalid');
//             }
//         }
//     );
// }

// function checkUsernameAvailability(username, onSuccess, onError) {
//     $.ajax({
//         url: '<?php echo admin_url("admin-ajax.php"); ?>',
//         type: 'POST',
//         data: {
//             action: 'biwillz_check_username',
//             username: username,
//             nonce: '<?php echo wp_create_nonce("biwillz_auth_nonce"); ?>'
//         },
//         success: function(response) {
//             if (response.success) {
//                 onSuccess();
//             } else {
//                 onError(response.data.message || 'Sorry, that username already exists!');
//             }
//         },
//         error: function() {
//             onError('An error occurred while checking username availability. Please try again.');
//         }
//     });
// }

// function proceedWithRegistration(formData, submitButton, spinner, buttonText) {
//     // Verify reCAPTCHA if enabled
//     if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
//         const recaptchaResponse = grecaptcha.getResponse(registerRecaptchaId);
//         if (!recaptchaResponse) {
//             toggleLoadingState(false, submitButton, spinner, buttonText);
//             showError('Please complete the reCAPTCHA verification');
//             return;
//         }
//         formData.append('g-recaptcha-response', recaptchaResponse);
//     }

//     // Make registration AJAX request
//     $.ajax({
//         url: '<?php echo admin_url("admin-ajax.php"); ?>',
//         type: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false,
//         success: function(response) {
//             toggleLoadingState(false, submitButton, spinner, buttonText);

//             if (response.success) {
//                 Swal.fire({
//                     title: 'Success!',
//                     text: response.data.message,
//                     icon: 'success',
//                     timer: 2000,
//                     showConfirmButton: false
//                 }).then(() => {
//                     if (response.data.redirect_url) {
//                         window.location.href = response.data.redirect_url;
//                     }
//                 });
//             } else {
//                 showError(response.data.message);
//                 if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
//                     grecaptcha.reset(registerRecaptchaId);
//                 }
//             }
//         },
//         error: function(xhr) {
//             toggleLoadingState(false, submitButton, spinner, buttonText);
//             let errorMessage = 'An unexpected error occurred. Please try again.';
            
//             try {
//                 const response = JSON.parse(xhr.responseText);
//                 if (response.data && response.data.message) {
//                     errorMessage = response.data.message;
//                 }
//             } catch(e) {}
            
//             showError(errorMessage);
            
//             if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
//                 grecaptcha.reset(registerRecaptchaId);
//             }
//         }
//     });
// }

// Add this to your existing JavaScript code
function checkEmailAvailability(email, onSuccess, onError) {
    $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        data: {
            action: 'biwillz_check_email',
            email: email,
            nonce: '<?php echo wp_create_nonce("biwillz_auth_nonce"); ?>'
        },
        success: function(response) {
            if (response.success) {
                onSuccess();
            } else {
                onError(response.data.message || 'Sorry, that email address is already registered!');
            }
        },
        error: function(xhr, status, error) {
            console.error('Email check error:', {xhr, status, error});
            let errorMessage = 'An error occurred while checking email availability. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.data?.message) {
                    errorMessage = response.data.message;
                }
            } catch(e) {
                console.error('Error parsing response:', e);
            }
            onError(errorMessage);
        }
    });
}




function handleRegisterSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const spinner = submitButton.querySelector('.spinner');
    const buttonText = submitButton.querySelector('span');
    const username = form.querySelector('#reg-username').value.trim();

    // Reset any existing error messages
    resetValidationMessages();

    // Validate form
    if (!validateRegistrationForm(form)) {
        return;
    }

    // Show loading state
    toggleLoadingState(true, submitButton, spinner, buttonText);

    // Create the nonce from PHP
    const nonce = '<?php echo wp_create_nonce("biwillz_auth_nonce"); ?>';

    // First, check username availability with proper error handling
    $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
        type: 'POST',
        data: {
            action: 'biwillz_check_username',
            username: username,
            nonce: nonce
        },
        success: function(response) {
            if (response.success) {
                // Username is available, proceed with registration
                const formData = new FormData(form);
                formData.append('action', 'biwillz_register');
                formData.append('nonce', nonce);
                proceedWithRegistration(formData, submitButton, spinner, buttonText);
            } else {
                toggleLoadingState(false, submitButton, spinner, buttonText);
                const errorMessage = response.data?.message || 'Sorry, that username already exists!';
                showError(errorMessage);
                
                const usernameInput = form.querySelector('#reg-username');
                if (usernameInput) {
                    usernameInput.focus();
                    usernameInput.classList.add('invalid');
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Username check error:', {xhr, status, error});
            toggleLoadingState(false, submitButton, spinner, buttonText);
            
            let errorMessage = 'An error occurred while checking username availability. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.data?.message) {
                    errorMessage = response.data.message;
                }
            } catch(e) {
                console.error('Error parsing response:', e);
            }
            
            showError(errorMessage);
            
            const usernameInput = form.querySelector('#reg-username');
            if (usernameInput) {
                usernameInput.classList.add('invalid');
            }
        }
    });
}

function proceedWithRegistration(formData, submitButton, spinner, buttonText) {
    // Verify reCAPTCHA if enabled
    if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
        const recaptchaResponse = grecaptcha.getResponse(registerRecaptchaId);
        if (!recaptchaResponse) {
            toggleLoadingState(false, submitButton, spinner, buttonText);
            showError('Please complete the reCAPTCHA verification');
            return;
        }
        formData.append('g-recaptcha-response', recaptchaResponse);
    }

    $.ajax({
        url: '<?php echo admin_url("admin-ajax.php"); ?>',
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
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                    }
                });
            } else {
                showError(response.data.message || 'Registration failed. Please try again.');
                if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
                    grecaptcha.reset(registerRecaptchaId);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Registration error:', {xhr, status, error});
            toggleLoadingState(false, submitButton, spinner, buttonText);
            
            let errorMessage = 'An unexpected error occurred. Please try again.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.data?.message) {
                    errorMessage = response.data.message;
                }
            } catch(e) {
                console.error('Error parsing response:', e);
            }
            
            showError(errorMessage);
            
            if (typeof grecaptcha !== 'undefined' && document.querySelector('#register-recaptcha')) {
                grecaptcha.reset(registerRecaptchaId);
            }
        }
    });
}

// Add this function to validate username format
function isValidUsername(username) {
    // Only allow letters, numbers, and underscores, 3-20 characters
    return /^[a-zA-Z0-9_]{3,20}$/.test(username);
}

// Update the validateRegistrationForm function
function validateRegistrationForm(form) {
    const username = form.querySelector('#reg-username').value.trim();
    const email = form.querySelector('#reg-email').value.trim();
    const phone = form.querySelector('#reg-phone');
    const countrySelect = document.getElementById('country-code');
    const phoneValidationMessage = document.getElementById('phone-validation-message');
    const password = form.querySelector('#reg-password').value.trim();
    const terms = form.querySelector('#terms-checkbox');
    let isValid = true;

    // Reset previous validation states
    resetValidationMessages();

    // Username validation
    if (!isValidUsername(username)) {
        showError('Username must be 3-20 characters long and can only contain letters, numbers, and underscores');
        form.querySelector('#reg-username').classList.add('invalid');
        isValid = false;
    }

    // Email validation
    if (!isValidEmail(email)) {
        showError('Please enter a valid email address');
        form.querySelector('#reg-email').classList.add('invalid');
        isValid = false;
    }

    // Phone validation
    if (phone && countrySelect) {
        if (!validatePhone(phone, countrySelect, phoneValidationMessage)) {
            isValid = false;
        }
    }

    // Password validation
    if (!updatePasswordStrength(password)) {
        showError('Please ensure your password meets all requirements');
        form.querySelector('#reg-password').classList.add('invalid');
        isValid = false;
    }

    // Terms validation
    if (!terms.checked) {
        showError('Please accept the Terms and Conditions');
        terms.classList.add('invalid');
        isValid = false;
    }

    return isValid;
}






function resetValidationMessages() {
    document.querySelectorAll('.validation-message').forEach(msg => {
        hideValidationError(msg);
    });
    document.querySelectorAll('.invalid').forEach(input => {
        input.classList.remove('invalid');
    });
}


    function validateRegistrationForm(form) {
    const username = form.querySelector('#reg-username').value.trim();
    const email = form.querySelector('#reg-email').value.trim();
    const phone = form.querySelector('#reg-phone');
    const countrySelect = document.getElementById('country-code');
    const phoneValidationMessage = document.getElementById('phone-validation-message');
    const password = form.querySelector('#reg-password').value.trim();
    const terms = form.querySelector('#terms-checkbox');
    let isValid = true;

    // Clear previous validation messages
    form.querySelectorAll('.validation-message').forEach(msg => {
        hideValidationError(msg);
    });

    // Username validation
    if (username.length < 3) {
        showError('Username must be at least 3 characters long');
        isValid = false;
    }

    // Email validation
    if (!isValidEmail(email)) {
        showError('Please enter a valid email address');
        isValid = false;
    }

    // Phone validation
    if (phone && countrySelect) {
        if (!validatePhone(phone, countrySelect, phoneValidationMessage)) {
            isValid = false;
        }
    }

    // Password validation
    if (!updatePasswordStrength(password)) {
        showError('Please ensure your password meets all requirements');
        isValid = false;
    }

    // Terms validation
    if (!terms.checked) {
        showError('Please accept the Terms and Conditions');
        isValid = false;
    }

    return isValid;
}

    function setupPhoneValidation() {
    const phoneInput = document.getElementById('reg-phone');
    const countrySelect = document.getElementById('country-code');
    const validationMessage = document.getElementById('phone-validation-message');

    if (phoneInput && countrySelect) {
        // Set default country based on user's locale
        const userCountry = navigator.language?.split('-')[1] || 'US';
        const countryOption = document.querySelector(`option[data-country="${userCountry}"]`);
        if (countryOption) {
            countryOption.selected = true;
        }

        // Real-time validation
        phoneInput.addEventListener('input', function(e) {
            // Remove non-numeric characters
            this.value = this.value.replace(/\D/g, '');
            
            // Limit length to 15 digits (international standard)
            if (this.value.length > 15) {
                this.value = this.value.slice(0, 15);
            }

            // Validate on each input
            validatePhone(this, countrySelect, validationMessage);
        });

        // Validate when country changes
        countrySelect.addEventListener('change', function() {
            if (phoneInput.value) {
                validatePhone(phoneInput, countrySelect, validationMessage);
            }
        });
    }
}


function validatePhone(phoneInput, countrySelect, validationMessage) {
    const phoneNumber = phoneInput.value.trim();
    const countryCode = countrySelect.value;
    let isValid = true;
    let errorMessage = '';

    // Basic validation rules
    if (phoneNumber.length === 0) {
        isValid = false;
        errorMessage = 'Phone number is required';
    } else if (phoneNumber.length < 4) {
        isValid = false;
        errorMessage = 'Phone number is too short';
    } else if (phoneNumber.length > 15) {
        isValid = false;
        errorMessage = 'Phone number is too long';
    } else if (!/^[0-9]+$/.test(phoneNumber)) {
        isValid = false;
        errorMessage = 'Phone number can only contain digits';
    }

    // Country-specific validation
    if (isValid) {
        switch(countryCode) {
            case '+1': // US/Canada
                if (phoneNumber.length !== 10) {
                    isValid = false;
                    errorMessage = 'US/Canada numbers must be 10 digits';
                }
                break;
            case '+44': // UK
                if (phoneNumber.length < 10 || phoneNumber.length > 11) {
                    isValid = false;
                    errorMessage = 'UK numbers must be 10-11 digits';
                }
                break;
            case '+234': // Nigeria
                if (phoneNumber.length !== 10) {
                    isValid = false;
                    errorMessage = 'Nigerian numbers must be 10 digits';
                }
                break;
            // Add more country-specific rules as needed
        }
    }

    // Update UI
    if (!isValid) {
        showValidationError(validationMessage, errorMessage);
        phoneInput.classList.add('invalid');
    } else {
        hideValidationError(validationMessage);
        phoneInput.classList.remove('invalid');
        // Store the full number with country code
        phoneInput.dataset.fullNumber = `${countryCode}${phoneNumber}`;
    }

    return isValid;
}

    function setupPasswordStrengthMeter() {
        const passwordInput = document.getElementById('reg-password');
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

    function setupFormSwitching() {
        window.switchForm = function(formType) {
            const loginContainer = document.querySelector('.login-container');
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            
            if (!loginForm || !registerForm) return;

            // Reset forms and validation states
            resetForms();

            // Handle form switching animation
            if (formType === 'register') {
                // Switch to register form
                loginForm.style.transform = 'translateX(-100%)';
                loginForm.style.opacity = '0';
                
                setTimeout(() => {
                    loginForm.style.display = 'none';
                    registerForm.style.display = 'flex';
                    
                    // Trigger reflow
                    registerForm.offsetHeight;
                    
                    registerForm.style.transform = 'translateX(0)';
                    registerForm.style.opacity = '1';
                }, 300);
            } else {
                // Switch to login form
                registerForm.style.transform = 'translateX(100%)';
                registerForm.style.opacity = '0';
                
                setTimeout(() => {
                    registerForm.style.display = 'none';
                    loginForm.style.display = 'flex';
                    
                    // Trigger reflow
                    loginForm.offsetHeight;
                    
                    loginForm.style.transform = 'translateX(0)';
                    loginForm.style.opacity = '1';
                }, 300);
            }

            // Reset reCAPTCHA if enabled
            if (typeof grecaptcha !== 'undefined') {
                if (formType === 'register' && registerRecaptchaId) {
                    grecaptcha.reset(registerRecaptchaId);
                } else if (formType === 'login' && loginRecaptchaId) {
                    grecaptcha.reset(loginRecaptchaId);
                }
            }
        };
    }

    function resetForms() {
        // Reset form inputs
        document.getElementById('biwillz-login-form')?.reset();
        document.getElementById('biwillz-register-form')?.reset();

        // Hide all validation messages
        document.querySelectorAll('.validation-message').forEach(msg => {
            msg.classList.remove('show');
            msg.textContent = '';
        });

        // Reset password strength indicators
        document.querySelectorAll('.meter-section').forEach(section => {
            section.classList.remove('met');
            const icon = section.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
        });

        // Reset password visibility
        document.querySelectorAll('.show-hide').forEach(toggle => {
            const passwordInput = toggle.previousElementSibling;
            if (passwordInput) {
                passwordInput.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        });
    }

    function toggleLoadingState(isLoading, button, spinner, buttonText) {
        button.disabled = isLoading;
        spinner.style.display = isLoading ? 'block' : 'none';
        buttonText.style.opacity = isLoading ? '0' : '1';
    }

    // function showError(message) {
    //     Swal.fire({
    //         title: 'Error',
    //         text: message,
    //         icon: 'error',
    //         confirmButtonColor: '#2e08f4',
    //         customClass: {
    //             popup: 'animated shake'
    //         }
    //     });
    // }

   // Update the showError function to handle errors more gracefully
function showError(message) {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonColor: '#2e08f4',
        customClass: {
            popup: 'animated fadeInDown'
        },
        showClass: {
            popup: 'animated fadeInDown faster'
        },
        hideClass: {
            popup: 'animated fadeOutUp faster'
        }
    });
} 

    function showValidationError(element, message) {
        if (element) {
            element.textContent = message;
            element.classList.add('show');
            // Add shake animation
            element.classList.add('animated', 'shake');
            // Remove animation classes after animation ends
            setTimeout(() => {
                element.classList.remove('animated', 'shake');
            }, 1000);
        }
    }

    function hideValidationError(element) {
        if (element) {
            element.classList.remove('show');
            element.textContent = '';
        }
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Handle forgot password functionality
    window.showForgotPassword = function() {
    Swal.fire({
        title: 'Reset Password',
        html: `
            <div class="forgot-password-form">
                <p style="margin-bottom: 15px;">Enter your email address to reset your password</p>
                <input type="email" id="reset-email" class="swal2-input" placeholder="Email address">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Reset Password',
        confirmButtonColor: '#2e08f4',
        cancelButtonColor: '#cf13e4',
        showLoaderOnConfirm: true,
        didOpen: () => {
            // Focus on email input when modal opens
            document.getElementById('reset-email').focus();
        },
        preConfirm: (email) => {
            const emailInput = document.getElementById('reset-email').value;
            if (!isValidEmail(emailInput)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }
            
            // Show loading state
            Swal.update({
                title: 'Sending Reset Link',
                html: `
                    <div class="loading-message">
                        <div class="loading-spinner"></div>
                        <p>Please wait while we process your request...</p>
                    </div>
                `,
                showConfirmButton: false,
                showCancelButton: false
            });

            // Return a promise for the AJAX request
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: biwillzAuth.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'biwillz_reset_password',
                        email: emailInput,
                        nonce: biwillzAuth.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response);
                        } else {
                            reject(new Error(response.data.message || 'Failed to send reset link'));
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'An unexpected error occurred. Please try again.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.data?.message) {
                                errorMessage = response.data.message;
                            }
                        } catch(e) {}
                        reject(new Error(errorMessage));
                    }
                });
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.value) {
            Swal.fire({
                title: 'Success!',
                text: result.value.data.message,
                icon: 'success',
                confirmButtonColor: '#2e08f4'
            });
        }
    }).catch((error) => {
        Swal.fire({
            title: 'Error',
            text: error.message,
            icon: 'error',
            confirmButtonColor: '#2e08f4'
        });
    });
};

})(jQuery);
</script>
    
<!-- Add this after your other script imports and before your custom script -->
<script src="https://unpkg.com/libphonenumber-js@1.10.51/bundle/libphonenumber-min.js"></script>