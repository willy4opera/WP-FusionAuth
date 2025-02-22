<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
// Prevent WordPress from loading the header and foote
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
<?php include 'authscript.php'; ?>    
<!-- Add this after your other script imports and before your custom script -->
<script src="https://unpkg.com/libphonenumber-js@1.10.51/bundle/libphonenumber-min.js"></script>