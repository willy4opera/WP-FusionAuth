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

    // function setupLoginForm() {
    //     const loginForm = document.getElementById('biwillz-login-form');
    //     if (loginForm) {
    //         loginForm.addEventListener('submit', handleLoginSubmit);
    //     }
    // }

    // function handleLoginSubmit(e) {
    //     e.preventDefault();
    //     const form = e.target;
    //     const submitButton = form.querySelector('button[type="submit"]');
    //     const spinner = submitButton.querySelector('.spinner');
    //     const buttonText = submitButton.querySelector('span');

    //     Validate form
    //     if (!validateLoginForm(form)) return;

    //     Show loading state
    //     toggleLoadingState(true, submitButton, spinner, buttonText);

    //     Get form data
    //     const formData = new FormData(form);
    //     formData.append('action', 'biwillz_modal_login');
    //     formData.append('current_page', window.location.href);

    //     Verify reCAPTCHA if enabled
    //     if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
    //         const recaptchaResponse = grecaptcha.getResponse(loginRecaptchaId);
    //         if (!recaptchaResponse) {
    //             showError('Please complete the reCAPTCHA verification');
    //             toggleLoadingState(false, submitButton, spinner, buttonText);
    //             return;
    //         }
    //         formData.append('g-recaptcha-response', recaptchaResponse);
    //     }

    //     Make AJAX request
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
    //                 if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
    //                     grecaptcha.reset(loginRecaptchaId);
    //                 }
    //             }
    //         },
    //         error: function() {
    //             toggleLoadingState(false, submitButton, spinner, buttonText);
    //             showError('An unexpected error occurred. Please try again.');
    //             if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
    //                 grecaptcha.reset(loginRecaptchaId);
    //             }
    //         }
    //     });
    // }

    // function validateLoginForm(form) {
    //     const username = form.querySelector('#username').value.trim();
    //     const password = form.querySelector('#password').value.trim();
    //     const validationMessage = form.querySelector('#login-validation-message');

    //     if (username.length < 3) {
    //         showValidationError(validationMessage, 'Username or email is too short');
    //         return false;
    //     }

    //     if (username.includes('@') && !isValidEmail(username)) {
    //         showValidationError(validationMessage, 'Please enter a valid email address');
    //         return false;
    //     }

    //     if (password.length < 6) {
    //         showValidationError(validationMessage, 'Password must be at least 6 characters');
    //         return false;
    //     }

    //     hideValidationError(validationMessage);
    //     return true;
    // }

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
    formData.append('action', 'biwillz_login'); // Updated from 'biwillz_modal_login' to 'biwillz_login'
    formData.append('nonce', biwillzAuth.nonce);
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
        url: biwillzAuth.ajaxurl,
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

    if (username.length < biwillzAuth.min_username_length) {
        showValidationError(validationMessage, `Username must be at least ${biwillzAuth.min_username_length} characters`);
        return false;
    }

    if (username.includes('@') && !isValidEmail(username)) {
        showValidationError(validationMessage, 'Please enter a valid email address');
        return false;
    }

    if (password.length < biwillzAuth.min_password_length) {
        showValidationError(validationMessage, `Password must be at least ${biwillzAuth.min_password_length} characters`);
        return false;
    }

    hideValidationError(validationMessage);
    return true;
}

function toggleLoadingState(isLoading, button, spinner, buttonText) {
    button.disabled = isLoading;
    spinner.style.display = isLoading ? 'inline-block' : 'none';
    buttonText.style.display = isLoading ? 'none' : 'inline-block';
}

function showError(message) {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK'
    });
}

function showValidationError(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideValidationError(element) {
    element.style.display = 'none';
}

function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Initialize when document is ready
$(document).ready(function() {
    setupLoginForm();
    if (typeof grecaptcha !== 'undefined' && document.querySelector('#login-recaptcha')) {
        loginRecaptchaId = grecaptcha.render('login-recaptcha', {
            'sitekey': biwillzAuth.recaptcha_site_key
        });
    }
});
    
    function setupRegisterForm() {
        const registerForm = document.getElementById('biwillz-register-form');
        if (registerForm) {
            setupPhoneValidation();
            setupPasswordStrengthMeter();
            registerForm.addEventListener('submit', handleRegisterSubmit);
        }
    }

   

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