(function($) {
    'use strict';

    // Initialize admin functionality when document is ready
    $(document).ready(function() {
        const BiWillzAuthAdmin = {
            init: function() {
                this.bindEvents();
                this.initializeTooltips();
                this.handleGoogleAuthToggle();
                this.setupFormValidation();
            },

            bindEvents: function() {
                // Handle Google Auth enable/disable toggle
                $('#enable_google_auth').on('change', this.handleGoogleAuthToggle);

                // Handle form submission
                $('form').on('submit', this.handleFormSubmit);

                // Handle password field visibility toggle
                $('.toggle-password-visibility').on('click', this.togglePasswordVisibility);

                // Handle test connection button
                $('#test_google_connection').on('click', this.testGoogleConnection);
            },

            initializeTooltips: function() {
                // Initialize tooltips for help text
                $('.biwillz-auth-help-tip').tooltipster({
                    position: 'right',
                    theme: 'tooltipster-light',
                    maxWidth: 300
                });
            },

            handleGoogleAuthToggle: function() {
                const isEnabled = $('#enable_google_auth').is(':checked');
                const $googleFields = $('#google_client_id, #google_client_secret').closest('tr');
                
                if (isEnabled) {
                    $googleFields.fadeIn();
                } else {
                    $googleFields.fadeOut();
                }
            },

            setupFormValidation: function() {
                $('form').on('submit', function(e) {
                    let isValid = true;
                    const $form = $(this);

                    // Validate Google OAuth settings if enabled
                    if ($('#enable_google_auth').is(':checked')) {
                        const clientId = $('#google_client_id').val();
                        const clientSecret = $('#google_client_secret').val();
                        
                        if (!clientId || !clientSecret) {
                            isValid = false;
                            BiWillzAuthAdmin.showError('Google OAuth credentials are required when Google Authentication is enabled.');
                        }
                    }

                    // Validate redirect URL if provided
                    const redirectUrl = $('#default_redirect').val();
                    if (redirectUrl && !BiWillzAuthAdmin.isValidUrl(redirectUrl)) {
                        isValid = false;
                        BiWillzAuthAdmin.showError('Please enter a valid redirect URL.');
                    }

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            },

            handleFormSubmit: function(e) {
                const $form = $(this);
                const $submitButton = $form.find(':submit');

                // Show loading state
                $submitButton.prop('disabled', true)
                           .val('Saving...');

                // Clear previous messages
                $('.biwillz-auth-notice').remove();
            },

            testGoogleConnection: function(e) {
                e.preventDefault();
                const $button = $(this);
                const clientId = $('#google_client_id').val();
                const clientSecret = $('#google_client_secret').val();

                if (!clientId || !clientSecret) {
                    BiWillzAuthAdmin.showError('Please enter both Client ID and Client Secret before testing.');
                    return;
                }

                $button.prop('disabled', true)
                      .text('Testing...');

                // AJAX request to test Google connection
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'test_google_connection',
                        nonce: biwillz_auth_admin.nonce,
                        client_id: clientId,
                        client_secret: clientSecret
                    },
                    success: function(response) {
                        if (response.success) {
                            BiWillzAuthAdmin.showSuccess('Google connection test successful!');
                        } else {
                            BiWillzAuthAdmin.showError(response.data.message || 'Connection test failed.');
                        }
                    },
                    error: function() {
                        BiWillzAuthAdmin.showError('Connection test failed. Please check your credentials.');
                    },
                    complete: function() {
                        $button.prop('disabled', false)
                              .text('Test Connection');
                    }
                });
            },

            togglePasswordVisibility: function(e) {
                e.preventDefault();
                const $button = $(this);
                const $input = $button.prev('input');
                const type = $input.attr('type');

                $input.attr('type', type === 'password' ? 'text' : 'password');
                $button.text(type === 'password' ? 'Hide' : 'Show');
            },

            showSuccess: function(message) {
                const $notice = $('<div class="notice notice-success biwillz-auth-notice"><p>' + message + '</p></div>');
                $('.wrap.biwillz-auth-settings h1').after($notice);
                $notice.fadeIn();
            },

            showError: function(message) {
                const $notice = $('<div class="notice notice-error biwillz-auth-notice"><p>' + message + '</p></div>');
                $('.wrap.biwillz-auth-settings h1').after($notice);
                $notice.fadeIn();
            },

            isValidUrl: function(url) {
                try {
                    new URL(url);
                    return true;
                } catch (e) {
                    return false;
                }
            }
        };

        // Initialize the admin functionality
        BiWillzAuthAdmin.init();
    });

})(jQuery);
