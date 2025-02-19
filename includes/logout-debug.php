<?php
require_once('../../../../wp-load.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout Debug</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if (is_user_logged_in()): ?>
    <h2>Logout Test Page</h2>
    <p>User ID: <?php echo get_current_user_id(); ?></p>
    <p>Raw Logout URL: <?php echo esc_url(wp_logout_url(home_url())); ?></p>
    <button onclick="testLogout()">Test Logout</button>
    <script>
    function testLogout() {
        Swal.fire({
            title: 'Ready to Leave?',
            text: 'Are you sure you want to log out?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2e08f4',
            cancelButtonColor: '#cf13e4',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo esc_js(wp_logout_url(home_url())); ?>';
            }
        });
    }
    </script>
<?php else: ?>
    <p>Not logged in</p>
<?php endif; ?>
</body>
</html>
