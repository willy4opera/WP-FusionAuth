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



/* Password strength meter styling */
.password-strength-meter {
    margin-top: 10px;
    font-size: 0.8em;
    color: #666;
}

.meter-section {
    margin: 5px 0;
    transition: color 0.3s ease;
}

.meter-section.met {
    color: #2e08f4;
}

.meter-section i {
    margin-right: 5px;
    font-size: 0.9em;
}

/* Form transition animations */
#loginForm, #registerForm {
    transition: transform 0.3s ease, opacity 0.3s ease;
    transform: translateX(0);
    opacity: 1;
}

/* Loading state enhancements */
.login.loading {
    background: linear-gradient(135deg, #2e08f4 0%, #cf13e4 100%);
    opacity: 0.8;
    pointer-events: none;
}

/* Input focus states */
.sec-2 input:focus {
    background: rgba(46, 8, 244, 0.05);
    padding: 8px;
    border-radius: 5px;
}

/* Error message animation */
.error-message {
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}


.phone-input-group {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 1;
}

.country-select {
    background: transparent;
    border: none;
    color: #333;
    font-size: 0.9em;
    padding: 5px;
    outline: none;
    min-width: 80px;
    cursor: pointer;
}

.country-select:focus {
    background: rgba(46, 8, 244, 0.05);
    border-radius: 5px;
}

.validation-message {
    font-size: 0.75em;
    margin-top: 5px;
    display: none;
    color: #ef4444;
}

.validation-message.show {
    display: block;
}

#reg-phone {
    flex: 1;
}

.validation-message {
    display: none;
    color: #ff3e3e;
    font-size: 12px;
    margin: 5px 0;
    padding: 0 5px;
}

.validation-message.show {
    display: block;
}




@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.animated {
    animation-duration: 0.75s;
    animation-fill-mode: both;
}

.shake {
    animation-name: shake;
}

.validation-message.animated {
    animation-duration: 0.5s;
}

/* Forgot password form styling */
.forgot-password-form {
    padding: 10px;
}

.forgot-password-form p {
    color: #666;
    font-size: 14px;
}

.swal2-input {
    margin: 10px auto;
    width: 100%;
    max-width: 300px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.swal2-input:focus {
    border-color: #2e08f4;
    box-shadow: 0 0 0 2px rgba(46, 8, 244, 0.1);
    outline: none;
}


.sec-2 input.invalid {
    border: 1px solid #ff3e3e;
    background-color: rgba(255, 62, 62, 0.05);
}

.phone-input-group {
    position: relative;
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 1;
}

#phone-validation-message {
    position: absolute;
    top: 100%;
    left: 0;
    width: 100%;
    margin-top: 5px;
    font-size: 12px;
    color: #ff3e3e;
    transition: all 0.3s ease;
}

.validation-message.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.validation-message {
    display: none;
    opacity: 0;
    transform: translateY(-10px);
}

/* Input invalid state */
input.invalid {
    border-color: #ff3e3e !important;
    background-color: rgba(255, 62, 62, 0.05) !important;
}

/* SweetAlert2 animations */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

@keyframes fadeOutUp {
    from {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
    to {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
}

.animated {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

.fadeInDown {
    animation-name: fadeInDown;
}

.fadeOutUp {
    animation-name: fadeOutUp;
}

.faster {
    animation-duration: 0.2s;
}

.email-input-container {
    position: relative;
    margin: 10px 0;
}

.email-input-container i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.forgot-password-form {
    text-align: center;
}

.forgot-password-form p {
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.swal2-input {
    margin-top: 10px !important;
}

.animated {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translate3d(0, -20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.fadeInDown {
    animation-name: fadeInDown;
}

.error-message {
    text-align: center;
    padding: 15px;
}

.error-message p {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.register-now-btn {
    background-color: #2e08f4;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.register-now-btn:hover {
    background-color: #cf13e4;
}

.loading-message {
    text-align: center;
    padding: 20px;
}

.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    margin-bottom: 15px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #2e08f4;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.swal2-loading .swal2-content {
    margin-top: 20px;
}

.forgot-password-form {
    margin-bottom: 15px;
}

.forgot-password-form input {
    margin-top: 10px;
}

/* Reset Password Form Specific Styles */
.error-message {
    text-align: center;
    padding: 20px;
}

.error-message i {
    font-size: 48px;
    color: #ff4444;
    margin-bottom: 15px;
}

.error-message p {
    color: #666;
    margin-bottom: 20px;
}

/* Password Strength Meter */
.password-strength-meter {
    margin-top: 10px;
    margin-bottom: 20px;
}

.meter-section {
    font-size: 12px;
    color: #666;
    margin: 5px 0;
}

.meter-section.met {
    color: #28a745;
}

.meter-section i {
    margin-right: 5px;
}

.meter-section.met i {
    color: #28a745;
}
</style>
