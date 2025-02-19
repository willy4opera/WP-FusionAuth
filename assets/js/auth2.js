(function ($) {
    'use strict';

    // Initialize libphonenumber
    const phoneUtil = window.libphonenumber.PhoneNumberUtil.getInstance();

    function initializeAuth() {
        // Set default country based on user's locale
        const userCountry = navigator.language?.split('-')[1] || 'US';
        const countryOption = document.querySelector(`option[data-country="${userCountry}"]`);
        if (countryOption) {
            countryOption.selected = true;
        }

        // Initialize form elements
        setupPasswordStrengthMeter();
        setupPhoneValidation();
        setupFormSubmission();
        setupPasswordToggle();
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

    function setupPhoneValidation() {
        const phoneInput = document.getElementById('reg-phone');
        const countrySelect = document.getElementById('country-code');
        const validationMessage = document.getElementById('phone-validation-message');

        if (phoneInput && countrySelect) {
            phoneInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '');
                validatePhone(this, countrySelect, validationMessage);
            });

            countrySelect.addEventListener('change', function() {
                validatePhone(phoneInput, this, validationMessage);
            });
        }
    }

    function validatePhone(phoneInput, countrySelect, validationMessage) {
        const phoneNumber = phoneInput.value.trim();
        const countryCode = countrySelect.value;
        
        if (!/^[0-9]{4,14}$/.test(phoneNumber)) {
            validationMessage.textContent = 'Please enter a valid phone number';
            validationMessage.classList.add('show');
            return false;
        }
        
        const fullNumber = `${countryCode}${phoneNumber}`;
        phoneInput.dataset.fullNumber = fullNumber;
        
        validationMessage.classList.remove('show');
        return true;
    }

    function setupFormSubmission() {
        // Login form submission
        const loginForm = document.getElementById('biwillz-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', handleLoginSubmit);
        }

        // Registration form submission
        const registerForm = document.getElementById('biwillz-register-form');
        if (registerForm) {
            registerForm.addEventListener('submit', handleRegisterSubmit);
        }
    }

    function handleLoginSubmit(e) {
        e.preventDefault();
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner');
        const buttonText = submitButton.querySelector('span');

        toggleLoadingState(true, submitButton, spinner, buttonText);

        const formData = new FormData(form);
        formData.append('action', 'biwillz_modal_login');
        formData.append('current_page', window.location.href);

        submitForm(formData, submitButton, spinner, buttonText);
    }

    function handleRegisterSubmit(e) {
        e.preventDefault();
        const form = e.target;
        
        // Validate phone
        const phoneInput = form.querySelector('#reg-phone');
        const countrySelect = form.querySelector('#country-code');
        const validationMessage = form.querySelector('#phone-validation-message');
        
        if (!validatePhone(phoneInput, countrySelect, validationMessage)) {
            phoneInput.focus();
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner');
        const buttonText = submitButton.querySelector('span');

        toggleLoadingState(true, submitButton, spinner, buttonText);

        const formData = new FormData(form);
        formData.append('action', 'biwillz_register');

        submitForm(formData, submitButton, spinner, buttonText);
    }

    function submitForm(formData, submitButton, spinner, buttonText) {
        fetch(ajaxurl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            toggleLoadingState(false, submitButton, spinner, buttonText);

            if (data.success) {
                showSuccessMessage(data.data.message, data.data.redirect_url);
            } else {
                showErrorMessage(data.data.message);
            }
        })
        .catch(error => {
            toggleLoadingState(false, submitButton, spinner, buttonText);
            showErrorMessage('An unexpected error occurred. Please try again.');
        });
    }

    function toggleLoadingState(isLoading, submitButton, spinner, buttonText) {
        submitButton.disabled = isLoading;
        spinner.style.display = isLoading ? 'block' : 'none';
        buttonText.style.opacity = isLoading ? '0' : '1';
    }

    function showSuccessMessage(message, redirectUrl) {
        Swal.fire({
            title: 'Success!',
            text: message,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        });
    }

    function showErrorMessage(message) {
        Swal.fire({
            title: 'Error',
            text: message,
            icon: 'error',
            confirmButtonColor: '#2e08f4'
        });
    }

    function setupPasswordToggle() {
        const toggleButtons = document.querySelectorAll('.show-hide');
        toggleButtons.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.closest('.sec-2').querySelector('input');
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    }

    // Form switching with animation
    window.switchForm = function(formType) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        
        if (formType === 'register') {
            loginForm.style.transform = 'translateX(-100%)';
            loginForm.style.opacity = '0';
            setTimeout(() => {
                loginForm.style.display = 'none';
                registerForm.style.display = 'flex';
                setTimeout(() => {
                    registerForm.style.transform = 'translateX(0)';
                    registerForm.style.opacity = '1';
                }, 50);
            }, 300);
        } else {
            registerForm.style.transform = 'translateX(100%)';
            registerForm.style.opacity = '0';
            setTimeout(() => {
                registerForm.style.display = 'none';
                loginForm.style.display = 'flex';
                setTimeout(() => {
                    loginForm.style.transform = 'translateX(0)';
                    loginForm.style.opacity = '1';
                }, 50);
            }, 300);
        }

        // Reset recaptcha if enabled
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    };

    // Initialize on DOM content loaded
    $(document).ready(initializeAuth);

})(jQuery);