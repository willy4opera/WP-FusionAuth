<div align="center">
    <h1>🔐 Biwillz-Auth Plugin</h1>
    <p>Advanced WordPress Authentication and User Management System</p>
    <p>
        <img src="https://img.shields.io/badge/PHP-7.4%2B-blue" alt="PHP Version">
        <img src="https://img.shields.io/badge/WordPress-5.8%2B-green" alt="WordPress Version">
        <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License">
        <img src="https://img.shields.io/badge/Version-1.0.0-red" alt="Version">
    </p>
</div>

## 🌟 Features

### 👤 User Registration System
- ✅ Real-time username availability checking
- 📧 Email validation and verification
- 🔒 Strong password enforcement
- 📱 Phone number validation
- 📜 Terms and conditions integration

### 🛡️ Security Features
- 🔐 WordPress nonce implementation
- 🤖 reCAPTCHA integration
- 💪 Advanced password requirements
- 🧹 Input sanitization
- 🔒 Secure session handling

### ⚡ Enhanced User Experience
- 🔄 Real-time form validation
- ⌛ Loading states and indicators
- ❗ Clear error messaging
- ✅ Success notifications
- 🔑 Automatic login after registration

### 🎨 Frontend Features
- ⚡ AJAX-powered form submission
- 🔄 Dynamic validation
- 📱 Responsive design
- 🎨 Customizable templates
- 💫 Modern UI components

## 🔧 Installation

1. **Download the Plugin**
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/username/Biwillz-Auth.git
   ```

2. **Activate the Plugin**
   - Navigate to WordPress Admin Panel
   - Go to Plugins > Installed Plugins
   - Find "Biwillz-Auth"
   - Click "Activate"

3. **Configure Settings**
   - Go to Settings > Biwillz-Auth
   - Configure your reCAPTCHA keys
   - Customize validation rules
   - Set up email templates
   - Save your settings

## ⚙️ System Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.8 or higher
- **jQuery**: 1.12 or higher
- **Bootstrap**: Compatible with 4.x and 5.x
- **Browser Support**: Modern browsers (IE11 not supported)

## 📖 Documentation

- [Complete Documentation](docs/README.md)
- [Installation Guide](docs/installation.md)
- [Configuration Guide](docs/configuration.md)
- [API Reference](docs/api.md)
- [Changelog](CHANGELOG.md)

## 🚀 Usage

### Basic Implementation
```php
// Initialize Biwillz-Auth
add_action('init', 'biwillz_auth_init');

// Custom registration form
[biwillz_registration_form template="custom"]

// Custom login form
[biwillz_login_form redirect="dashboard"]
```

### Advanced Features
```php
// Custom validation rules
add_filter('biwillz_password_rules', 'custom_password_rules');

// Custom success handling
add_action('biwillz_after_registration', 'custom_registration_handler');
```

## 🔧 Development

### Build Setup
```bash
# Install dependencies
npm install

# Build for production
npm run build

# Run development server
npm run dev
```

### Testing
```bash
# Run PHP unit tests
composer test

# Run JavaScript tests
npm test
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please read our [Contributing Guidelines](CONTRIBUTING.md) for details.

## 👨‍💻 Developer

<div align="center">
    <h3>Williams OBI</h3>
    <p>Full Stack Developer & WordPress Expert</p>
</div>

### 🌐 Connect with me:

[![GitHub](https://img.shields.io/badge/GitHub-100000?style=for-the-badge&logo=github&logoColor=white)](https://github.com/willy4opera)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-0077B5?style=for-the-badge&logo=linkedin&logoColor=white)](https://linkedin.com/in/williamssobi)
[![Facebook](https://img.shields.io/badge/Facebook-1877F2?style=for-the-badge&logo=facebook&logoColor=white)](https://facebook.com/williamsobi)
[![Website](https://img.shields.io/badge/Website-FF7139?style=for-the-badge&logo=Firefox-Browser&logoColor=white)](https://williamsobi.com.ng)
[![Email](https://img.shields.io/badge/Email-D14836?style=for-the-badge&logo=gmail&logoColor=white)](mailto:icare@williamsobi.com.ng)

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📊 Stats

- ⭐ Stars: ![GitHub stars](https://img.shields.io/github/stars/username/Biwillz-Auth?style=social)
- 🔄 Forks: ![GitHub forks](https://img.shields.io/github/forks/username/Biwillz-Auth?style=social)
- 🐛 Issues: ![GitHub issues](https://img.shields.io/github/issues/username/Biwillz-Auth)
- 📦 Downloads: ![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/Biwillz-Auth)

## 🙏 Acknowledgments

- WordPress Plugin Development Team
- All our contributors and supporters
- The amazing WordPress community

---

<div align="center">
    <p>⭐ If you find this plugin helpful, please star it on GitHub! ⭐</p>
    <p>Made with ❤️ by Williams OBI</p>
</div>
