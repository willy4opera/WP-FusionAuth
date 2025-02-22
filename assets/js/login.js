jQuery(document).ready(function($) {
    // Login form submission handler
    $('#biwillz-login-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const $messageContainer = $('.biwillz-auth-message');
        
        // Get form data
        const formData = {
            action: 'biwillz_login',
            username: $form.find('input[name="username"]').val(),
            password: $form.find('input[name="password"]').val(),
            remember: $form.find('input[name="remember"]').is(':checked'),
            nonce: biwillzAuth.nonce,
            current_page: window.location.href
        };

        // Basic client-side validation
        if (!formData.username || !formData.password) {
            showMessage('Please fill in all required fields.', 'error');
            return;
        }

        // Disable submit button and show loading state
        $submitButton.prop('disabled', true).addClass('loading');
        showMessage('Logging in...', 'info');

        // Send AJAX request
        $.ajax({
            url: biwillzAuth.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    
                    // Handle redirect after successful login
                    if (response.data.redirect_url) {
                        setTimeout(function() {
                            window.location.href = response.data.redirect_url;
                        }, 1000);
                    }
                } else {
                    const message = response.data ? response.data.message : 'Login failed. Please try again.';
                    showMessage(message, 'error');
                    
                    // Handle specific error cases
                    if (response.data && response.data.code === 'locked_out') {
                        $form.find('input').prop('disabled', true);
                        $submitButton.prop('disabled', true);
                    }
                }
            },
            error: function(xhr, status, error) {
                showMessage('An error occurred. Please try again later.', 'error');
                console.error('Login error:', status, error);
            },
            complete: function() {
                // Re-enable submit button and remove loading state
                $submitButton.prop('disabled', false).removeClass('loading');
            }
        });
    });

    // Function to display messages
    function showMessage(message, type = 'info') {
        const $messageContainer = $('.biwillz-auth-message');
        $messageContainer
            .removeClass('success error info')
            .addClass(type)
            .html(message)
            .show();

        if (type === 'success' || type === 'error') {
            setTimeout(function() {
                $messageContainer.fadeOut();
            }, 5000);
        }
    }

    // Optional: Add input validation on typing
    $('#biwillz-login-form input').on('input', function() {
        const $input = $(this);
        const value = $input.val().trim();
        
        if ($input.attr('name') === 'username') {
            if (value.length < biwillzAuth.min_username_length) {
                $input.addClass('invalid');
            } else {
                $input.removeClass('invalid');
            }
        }
        
        if ($input.attr('name') === 'password') {
            if (value.length < biwillzAuth.min_password_length) {
                $input.addClass('invalid');
            } else {
                $input.removeClass('invalid');
            }
        }
    });

    // Optional: Add password visibility toggle
    $('.password-toggle').on('click', function() {
        const $input = $(this).siblings('input');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('show-password');
    });
});
