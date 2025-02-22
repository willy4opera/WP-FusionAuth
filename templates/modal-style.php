<style>
/* Modal specific styles */
.login-container {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 999999;
    justify-content: center;
    align-items: center;
    overflow-y: auto;
    padding: 20px;
}

.login-container.active {
    display: flex;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
}

/* Modal content wrapper */
.screen-1 {
    background: #fff;
    border-radius: 10px;
    padding: 30px;
    width: 100%;
    max-width: 500px;
    position: relative;
    margin: auto;
    animation: modalFadeIn 0.3s ease-out;
}

/* Modal animation */
@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Close button */
.modal-close {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 24px;
    color: #666;
    cursor: pointer;
    z-index: 1;
    transition: color 0.3s ease;
}

.biwillz-modal-close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    cursor: pointer;
    z-index: 1000;
}

.modal-close:hover {
    color: #333;
}

/* Modal trigger button */
.biwillz-auth-modal-trigger {
    background: #4CAF50;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s ease;
}

.biwillz-auth-modal-trigger:hover {
    background: #45a049;
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .screen-1 {
        margin: 10px;
        padding: 20px;
    }

    .login-container {
        padding: 10px;
    }
}

/* Make sure modal appears above other elements */
.login-container {
    z-index: 999999 !important;
}

.screen-1 {
    z-index: 1000000 !important;
}

/* Prevent modal content from being cut off */
.login-container {
    align-items: flex-start;
}

.screen-1 {
    margin: 20px auto;
}

/* Add smooth transitions */
.screen-1#loginForm,
.screen-1#registerForm {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Loading state for buttons */
button[type="submit"]:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Ensure proper stacking of reCAPTCHA */
.g-recaptcha {
    margin: 15px 0;
    display: flex;
    justify-content: center;
    z-index: 1000001;
}

/* Ensure proper display of validation messages */
.validation-message {
    margin-top: 5px;
    color: #ff4444;
    font-size: 12px;
}

/* Make modal scrollable on smaller screens */
@media screen and (max-height: 800px) {
    .login-container {
        align-items: flex-start;
        padding-top: 20px;
    }

    .screen-1 {
        margin: 0 auto;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
    }
}


.screen-1 {
    background: #fff;
    border-radius: 10px;
    padding: 30px;
    width: 100%;
    max-width: 500px;
    position: relative;
    margin: auto;
    animation: swipeIn 0.3s ease-out;
}

/* Swipe animation */
@keyframes swipeIn {
    from {
        opacity: 0;
        transform: translateX(-100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes swipeOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

/* Animation classes */
.swipe-enter {
    animation: swipeIn 0.3s ease-out;
}

.swipe-exit {
    animation: swipeOut 0.3s ease-out;
}

/* For form switching animations */
.screen-1#loginForm,
.screen-1#registerForm {
    transition: transform 0.3s ease-out;
}

.screen-1.slide-left {
    transform: translateX(-100%);
}

.screen-1.slide-right {
    transform: translateX(100%);
}






/**Style */
/* Update existing media queries for better responsiveness */
@media screen and (max-width: 768px) {
    .screen-1 {
        padding: 1.5em;
        margin: 10px;
        width: 95%;
        max-width: none;
    }

    .login-container {
        padding: 10px;
        align-items: center;
    }

    /* Improve form transition on mobile */
    #loginForm, #registerForm {
        width: 100%;
        min-height: auto;
    }
}

@media screen and (max-width: 480px) {
    .screen-1 {
        padding: 1.2em;
        margin: 5px;
        border-radius: 15px;
    }

    .sec-2 input {
        font-size: 14px;
    }

    .auth-logo {
        width: 60px;
        height: 60px;
        margin: -0.8em auto 1.2em;
    }

    /* Adjust form elements spacing */
    .email, .password {
        padding: 0.8em;
        margin-bottom: 0.8em;
    }

    .footer1 {
        font-size: 0.75em;
    }
}

/* Improve form switching animation */
.screen-1#loginForm,
.screen-1#registerForm {
    transition: all 0.3s ease-in-out;
    position: relative;
}

/* Optimize for landscape mode on mobile */
@media screen and (max-height: 600px) {
    .login-container {
        align-items: flex-start;
        padding-top: 10px;
    }

    .screen-1 {
        margin: 5px auto;
        max-height: 95vh;
        overflow-y: auto;
    }

    .auth-logo {
        width: 50px;
        height: 50px;
        margin: -0.5em auto 1em;
    }
}

/* Add to handle form content better */
.screen-1 {
    transition: transform 0.3s ease, opacity 0.3s ease;
    backface-visibility: hidden;
}

/* Improve form field layouts */
.email, .password {
    width: 100%;
    max-width: 100%;
}

/* Ensure proper centering and scaling */
.login-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 15px;
    box-sizing: border-box;
}

/* Optimize form transitions */
.screen-1.switch-form {
    opacity: 0;
    transform: scale(0.95);
}

.biwillz-swal-container {
    z-index: 10000 !important;
}



</style>
