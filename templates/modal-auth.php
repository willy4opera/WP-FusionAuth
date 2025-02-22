<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;
?>
<!--- External CSS Dependencies --->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!--- External JS Dependencies --->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/libphonenumber-js@1.10.51/bundle/libphonenumber-min.js"></script>

<?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

<!--- Modal Container --->
<div class="biwillz-modal-container login-container">
    <!--- Login Form --->
    <div class="screen-1" id="biwillz-modal-login">
        <span class="biwillz-modal-close">&times;</span>
        <img src="<?php echo plugin_dir_url(__FILE__) . 'Biwillz-APP-logo.png'; ?>" alt="Logo" class="auth-logo">
        
        <form method="POST" id="biwillz-modal-login-form">
            <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
            
            <div class="biwillz-modal-social-login social-login">
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
                <label for="biwillz-modal-login-username">Email Address</label>
                <div class="sec-2">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="username" id="biwillz-modal-login-username" placeholder="username@example.com" required>
                </div>
            </div>

            <div class="password">
                <label for="biwillz-modal-login-password">Password</label>
                <div class="sec-2">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="biwillz-modal-login-password" class="pas" placeholder="············" required>
                    <i class="fas fa-eye biwillz-modal-password-toggle show-hide"></i>
                </div>
            </div>
            
            <span id="biwillz-modal-login-message" class="validation-message"></span>
            
            <div class="remember-me">
                <input type="checkbox" name="remember" id="biwillz-modal-login-remember" value="1">
                <label for="biwillz-modal-login-remember">Remember me</label>
            </div>

            <?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
                <div id="biwillz-modal-login-recaptcha" class="g-recaptcha" 
                    data-sitekey="<?php echo esc_attr(Biwillz_Auth_Settings::get_option('recaptcha_site_key')); ?>"
                    data-theme="light"
                    data-size="normal"></div>
            <?php endif; ?>

            <button type="submit" class="login">
                <div class="spinner"></div>
                <span>Login</span>
            </button>

            <div class="footer1">
                <span class="biwillz-modal-switch-register" onclick="biwillzModal.switchForm('register')">Create Account</span>
                <span class="forgot-password-link" onclick="biwillzModal.showForgotPassword()">Forgot Password?</span>
            </div>
        </form>
    </div>

    <!--- Registration Form --->
    <div class="screen-1" id="biwillz-modal-register" style="display: none;">
        <span class="biwillz-modal-close">&times;</span>
        <img src="<?php echo plugin_dir_url(__FILE__) . 'Biwillz-APP-logo.png'; ?>" alt="Logo" class="auth-logo">

        <form method="POST" id="biwillz-modal-register-form">
            <?php wp_nonce_field('biwillz_auth_nonce', 'nonce'); ?>
            
            <div class="biwillz-modal-social-login social-login">
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

            <div id="biwillz-modal-register-message" class="auth-message" style="display: none;"></div>

            <div class="email">
                <label for="biwillz-modal-register-username">Username</label>
                <div class="sec-2">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" id="biwillz-modal-register-username" placeholder="johndoe" required>
                </div>
            </div>

            <div class="email">
                <label for="biwillz-modal-register-email">Email Address</label>
                <div class="sec-2">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" id="biwillz-modal-register-email" placeholder="username@example.com" required>
                </div>
            </div>

            <div class="email">
                <label for="biwillz-modal-register-phone">Phone Number</label>
                <div class="sec-2">
                    <i class="fas fa-phone"></i>
                    <div class="phone-input-group">
                        <select name="country_code" id="biwillz-modal-country-code" class="country-select">
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
                            id="biwillz-modal-register-phone" 
                            placeholder="Phone number" 
                            required 
                            pattern="[0-9]{4,14}"
                            title="Please enter a valid phone number">
                    </div>
                </div>
                <span id="biwillz-modal-phone-validation" class="validation-message"></span>
            </div>

            <div class="password">
                <label for="biwillz-modal-register-password">Password</label>
                <div class="sec-2">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="biwillz-modal-register-password" class="pas" placeholder="············" required>
                    <i class="fas fa-eye biwillz-modal-password-toggle show-hide"></i>
                </div>
                <div class="biwillz-modal-password-meter password-strength-meter">
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
                    <input type="checkbox" name="terms_accepted" id="biwillz-modal-terms" value="yes" required>
                    <span class="checkmark"></span>
                    <span class="terms-text">
                        I agree to the <a href="https://biwillzcomputers.com/refund_returns/" target="_blank" class="terms-link">Terms and Conditions</a>
                    </span>
                </label>
            </div>

            <?php if (Biwillz_Auth_Settings::get_option('enable_recaptcha')): ?>
                <div id="biwillz-modal-register-recaptcha" class="g-recaptcha" 
                    data-sitekey="<?php echo esc_attr(Biwillz_Auth_Settings::get_option('recaptcha_site_key')); ?>"
                    data-theme="light"
                    data-size="normal"></div>
            <?php endif; ?>

            <button type="submit" class="login">
                <div class="spinner"></div>
                <span>Create Account</span>
            </button>

            <div class="footer1">
                <span class="biwillz-modal-switch-login" onclick="biwillzModal.switchForm('login')">Already have an account? Login</span>
            </div>
        </form>
    </div>
</div>

<?php
// Include Styles
include_once 'modal-style.php';
include_once 'style.php';
include_once 'modal-authscript.php';
include_once 'modal-behavior.php';

?>
