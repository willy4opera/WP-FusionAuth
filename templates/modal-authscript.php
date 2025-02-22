<script>
const biwillzModal = (function($) {
    'use strict';

    // Private variables
    let loginRecaptchaId, registerRecaptchaId;

    // Public methods
    const publicMethods = {
        init: function() {
            initializeRecaptcha();
            setupLoginForm();
            setupRegisterForm();
            setupPasswordVisibility();
        },
        switchForm: switchForm,
        resetForms: resetForms
    };

    // Private methods
    function initializeRecaptcha() {
        if (typeof grecaptcha !== 'undefined') {
            const loginRecaptcha = document.querySelector('#biwillz-modal-login-recaptcha');
            const registerRecaptcha = document.querySelector('#biwillz-modal-register-recaptcha');
            
            if (loginRecaptcha) {
                loginRecaptchaId = grecaptcha.render('biwillz-modal-login-recaptcha', {
                    'sitekey': loginRecaptcha.dataset.sitekey,
                    'theme': 'light'
                });
            }

            if (registerRecaptcha) {
                registerRecaptchaId = grecaptcha.render('biwillz-modal-register-recaptcha', {
                    'sitekey': registerRecaptcha.dataset.sitekey,
                    'theme': 'light'
                });
            }
        }
    }

    function setupLoginForm() {
        const loginForm = document.getElementById('biwillz-modal-login-form');
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

        if (!validateLoginForm(form)) return;

        toggleLoadingState(true, submitButton, spinner, buttonText);

        const formData = new FormData(form);
        formData.append('action', 'biwillz_login');
        formData.append('nonce', biwillzAuth.nonce);
        formData.append('current_page', window.location.href);

        handleRecaptchaAndSubmit(formData, submitButton, spinner, buttonText, loginRecaptchaId);
    }

    function validateLoginForm(form) {
        const username = form.querySelector('#biwillz-modal-login-username').value.trim();
        const password = form.querySelector('#biwillz-modal-login-password').value.trim();
        const validationMessage = form.querySelector('#biwillz-modal-login-message');

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
        const registerForm = document.getElementById('biwillz-modal-register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', handleRegisterSubmit);
            setupPasswordStrengthMeter();
            setupPhoneValidation();
        }
    }






    

    function handleRegisterSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner');
        const buttonText = submitButton.querySelector('span');

        if (!validateRegistrationForm(form)) return;

        toggleLoadingState(true, submitButton, spinner, buttonText);

        const formData = new FormData(form);
        formData.append('action', 'biwillz_register');
        formData.append('nonce', biwillzAuth.nonce);

        handleRecaptchaAndSubmit(formData, submitButton, spinner, buttonText, registerRecaptchaId);
    }

    function validateRegistrationForm(form) {
        resetValidationMessages();

        const username = form.querySelector('#biwillz-modal-register-username').value.trim();
        const email = form.querySelector('#biwillz-modal-register-email').value.trim();
        const phone = form.querySelector('#biwillz-modal-register-phone');
        const countrySelect = document.getElementById('biwillz-modal-country-code');
        const password = form.querySelector('#biwillz-modal-register-password').value.trim();
        const terms = form.querySelector('#biwillz-modal-terms');
        let isValid = true;

        if (!isValidUsername(username)) {
            showError('Username must be 3-20 characters long and can only contain letters, numbers, and underscores');
            isValid = false;
        }

        if (!isValidEmail(email)) {
            showError('Please enter a valid email address');
            isValid = false;
        }

        if (phone && countrySelect) {
            if (!validatePhone(phone, countrySelect)) {
                isValid = false;
            }
        }

        if (!updatePasswordStrength(password)) {
            showError('Please ensure your password meets all requirements');
            isValid = false;
        }

        if (!terms.checked) {
            showError('Please accept the Terms and Conditions');
            isValid = false;
        }

        return isValid;
    }

    function setupPasswordVisibility() {
        document.querySelectorAll('.biwillz-modal-password-toggle').forEach(toggle => {
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
        const passwordInput = document.getElementById('biwillz-modal-register-password');
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
            const element = document.querySelector(`.biwillz-modal-password-meter [data-requirement="${req}"]`);
            if (element) {
                element.classList.toggle('met', requirements[req]);
                const icon = element.querySelector('i');
                icon.classList.toggle('fa-check-circle', requirements[req]);
                icon.classList.toggle('fa-circle', !requirements[req]);
            }
        });

        return Object.values(requirements).every(Boolean);
    }

    function setupPhoneValidation() {
        const phoneInput = document.getElementById('biwillz-modal-register-phone');
        const countrySelect = document.getElementById('biwillz-modal-country-code');
        
        if (phoneInput && countrySelect) {
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
                if (this.value.length > 15) {
                    this.value = this.value.slice(0, 15);
                }
                validatePhone(this, countrySelect);
            });

            countrySelect.addEventListener('change', function() {
                if (phoneInput.value) {
                    validatePhone(phoneInput, countrySelect);
                }
            });
        }
    }

    function validatePhone(phoneInput, countrySelect) {
        const phoneValidation = document.getElementById('biwillz-modal-phone-validation');
        const phoneNumber = phoneInput.value.trim();
        const countryCode = countrySelect.value;
        let isValid = true;
        let errorMessage = '';

        if (!phoneNumber) {
            isValid = false;
            errorMessage = 'Phone number is required';
        } else if (phoneNumber.length < 4) {
            isValid = false;
            errorMessage = 'Phone number is too short';
        } else if (phoneNumber.length > 15) {
            isValid = false;
            errorMessage = 'Phone number is too long';
        }

        if (!isValid) {
            showValidationError(phoneValidation, errorMessage);
            phoneInput.classList.add('invalid');
        } else {
            hideValidationError(phoneValidation);
            phoneInput.classList.remove('invalid');
        }

        return isValid;
    }

    function switchForm(formType) {
        const loginForm = document.getElementById('biwillz-modal-login');
        const registerForm = document.getElementById('biwillz-modal-register');
        
        resetForms();
        resetValidationMessages();

        if (formType === 'register') {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        } else {
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        }

        // Reset reCAPTCHA if enabled
        if (typeof grecaptcha !== 'undefined') {
            if (formType === 'register' && registerRecaptchaId) {
                grecaptcha.reset(registerRecaptchaId);
            } else if (formType === 'login' && loginRecaptchaId) {
                grecaptcha.reset(loginRecaptchaId);
            }
        }
    }


    function resetForms() {
        document.getElementById('biwillz-modal-login-form')?.reset();
        document.getElementById('biwillz-modal-register-form')?.reset();
        
        // Reset password strength indicators
        document.querySelectorAll('.biwillz-modal-password-meter [data-requirement]').forEach(req => {
            const icon = req.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-check-circle');
                icon.classList.add('fa-circle');
            }
            req.classList.remove('met');
        });
    }

    function handleRecaptchaAndSubmit(formData, submitButton, spinner, buttonText, recaptchaId) {
        if (typeof grecaptcha !== 'undefined' && recaptchaId) {
            const recaptchaResponse = grecaptcha.getResponse(recaptchaId);
            if (!recaptchaResponse) {
                showError('Please complete the reCAPTCHA verification');
                toggleLoadingState(false, submitButton, spinner, buttonText);
                return;
            }
            formData.append('g-recaptcha-response', recaptchaResponse);
        }

        $.ajax({
            url: biwillzAuth.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                handleAuthResponse(response, submitButton, spinner, buttonText, recaptchaId);
            },
            error: function() {
                handleAuthError(submitButton, spinner, buttonText, recaptchaId);
            }
        });
    }

    // function handleAuthResponse(response, submitButton, spinner, buttonText, recaptchaId) {
    //     toggleLoadingState(false, submitButton, spinner, buttonText);

    //     if (response.success) {
    //         Swal.fire({
    //             title: 'Success!',
    //             text: response.data.message,
    //             icon: 'success',
    //             timer: 2000,
    //             showConfirmButton: false
    //         }).then(() => {
    //             if (response.data.redirect_url) {
    //                 window.location.href = response.data.redirect_url;
    //             }
    //         });
    //     } else {
    //         showError(response.data.message);
    //         if (typeof grecaptcha !== 'undefined' && recaptchaId) {
    //             grecaptcha.reset(recaptchaId);
    //         }
    //     }
    
    function handleAuthResponse(response, submitButton, spinner, buttonText, recaptchaId) {
    toggleLoadingState(false, submitButton, spinner, buttonText);

    if (response.success) {
        // Close modal first
        if (typeof closeAuthModal === 'function') {
            closeAuthModal();
        }
        if (typeof closeModal === 'function') {
            closeModal();
        }

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
        if (typeof grecaptcha !== 'undefined' && recaptchaId) {
            grecaptcha.reset(recaptchaId);
        }
    }
}

function closeAuthModal() {
    $('.biwillz-modal-container').removeClass('active');
    $('.login-container').fadeOut();
    $('body').removeClass('modal-open');
    // Reset forms when closing
    biwillzModal.resetForms();
}
window.closeAuthModal = closeAuthModal;

    function handleAuthError(submitButton, spinner, buttonText, recaptchaId) {
        toggleLoadingState(false, submitButton, spinner, buttonText);
        showError('An unexpected error occurred. Please try again.');
        if (typeof grecaptcha !== 'undefined' && recaptchaId) {
            grecaptcha.reset(recaptchaId);
        }
    }

    // Essential utility functions
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

    function showValidationError(element, message) {
        if (element) {
            element.textContent = message;
            element.classList.add('show');
            element.classList.add('animated', 'shake');
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

    function resetValidationMessages() {
        document.querySelectorAll('.validation-message').forEach(msg => {
            hideValidationError(msg);
        });
        document.querySelectorAll('.invalid').forEach(input => {
            input.classList.remove('invalid');
        });
    }



// // Add showForgotPassword to public methods
// publicMethods.showForgotPassword = function() {
//     function isValidEmail(email) {
//         return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
//     }

//     Swal.fire({
//         title: 'Reset Password',
//         html: `
//             <div class="forgot-password-form">
//                 <p style="margin-bottom: 15px;">Enter your email address to reset your password</p>
//                 <input type="email" id="reset-email" class="swal2-input" placeholder="Email address">
//             </div>
//         `,
//         showCancelButton: true,
//         confirmButtonText: 'Reset Password',
//         confirmButtonColor: '#2e08f4',
//         cancelButtonColor: '#cf13e4',
//         showLoaderOnConfirm: true,
//         didOpen: () => {
//             document.getElementById('reset-email').focus();
//         },
//         preConfirm: (email) => {
//             const emailInput = document.getElementById('reset-email').value;
//             if (!isValidEmail(emailInput)) {
//                 Swal.showValidationMessage('Please enter a valid email address');
//                 return false;
//             }
            
//             // Show loading state
//             Swal.update({
//                 title: 'Sending Reset Link',
//                 html: `
//                     <div class="loading-message">
//                         <div class="loading-spinner"></div>
//                         <p>Please wait while we process your request...</p>
//                     </div>
//                 `,
//                 showConfirmButton: false,
//                 showCancelButton: false
//             });

//             // Return a promise for the AJAX request
//             return new Promise((resolve, reject) => {
//                 $.ajax({
//                     url: biwillzAuth.ajaxurl,
//                     type: 'POST',
//                     data: {
//                         action: 'biwillz_reset_password',
//                         email: emailInput,
//                         nonce: biwillzAuth.nonce
//                     },
//                     success: function(response) {
//                         if (response.success) {
//                             resolve(response);
//                         } else {
//                             reject(new Error(response.data.message || 'Failed to send reset link'));
//                         }
//                     },
//                     error: function(xhr) {
//                         let errorMessage = 'An unexpected error occurred. Please try again.';
//                         try {
//                             const response = JSON.parse(xhr.responseText);
//                             if (response.data?.message) {
//                                 errorMessage = response.data.message;
//                             }
//                         } catch(e) {}
//                         reject(new Error(errorMessage));
//                     }
//                 });
//             });
//         },
//         allowOutsideClick: () => !Swal.isLoading()
//     }).then((result) => {
//         if (result.value) {
//             Swal.fire({
//                 title: 'Success!',
//                 text: result.value.data.message,
//                 icon: 'success',
//                 confirmButtonColor: '#2e08f4'
//             });
//         }
//     }).catch((error) => {
//         Swal.fire({
//             title: 'Error',
//             text: error.message,
//             icon: 'error',
//             confirmButtonColor: '#2e08f4'
//         });
//     });
// };

publicMethods.showForgotPassword = function() {
    // First, close the existing modal
    closeAuthModal();

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

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
        customClass: {
            container: 'biwillz-swal-container'
        },
        didOpen: () => {
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
                    url: biwillzAuth.ajaxurl,
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


    

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function isValidUsername(username) {
        return /^[a-zA-Z0-9_]{3,20}$/.test(username);
    }

    // Return public methods
    return publicMethods;
})(jQuery);

// Initialize when document is ready
jQuery(document).ready(function() {
    biwillzModal.init();
});


//Modal switch transition
// Form switching with swipe animation
function switchToRegister() {
    $('#loginForm').addClass('slide-left');
    setTimeout(() => {
        $('#loginForm').hide();
        $('#registerForm').show().addClass('swipe-enter');
    }, 300);
}

function switchToLogin() {
    $('#registerForm').addClass('slide-right');
    setTimeout(() => {
        $('#registerForm').hide();
        $('#loginForm').show().addClass('swipe-enter');
    }, 300);
}

// Remove animation classes after animation completes
$('.screen-1').on('animationend', function() {
    $(this).removeClass('swipe-enter swipe-exit slide-left slide-right');
});



</script>
