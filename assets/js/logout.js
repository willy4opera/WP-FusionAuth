function initLogoutHandlers() {
    // For AJAX logout
    jQuery('.biwillz-logout-ajax').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Logging Out',
            text: 'Please wait...',
            icon: 'info',
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        jQuery.ajax({
            url: biwillz_auth.ajax_url,
            type: 'POST',
            data: {
                action: 'biwillz_logout',
                nonce: biwillz_auth.nonce
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: response.data.title,
                        text: response.data.message,
                        icon: response.data.icon,
                        confirmButtonText: response.data.confirmButtonText,
                        confirmButtonColor: response.data.confirmButtonColor,
                        timer: response.data.timer,
                        timerProgressBar: response.data.timerProgressBar
                    }).then(() => {
                        window.location.href = response.data.redirect_url;
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#2e08f4'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error',
                    text: 'An error occurred. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2e08f4'
                });
            }
        });
    });

    // For regular logout links
    jQuery('.biwillz-logout-regular').on('click', function(e) {
        e.preventDefault();
        const logoutUrl = jQuery(this).attr('href');

        Swal.fire({
            title: 'Confirm Logout',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#2e08f4'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = logoutUrl;
            }
        });
    });
}

// Initialize when document is ready
jQuery(document).ready(function() {
    initLogoutHandlers();
});