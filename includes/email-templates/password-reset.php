<?php
// email-templates/password-reset.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function get_password_reset_email_template($user, $reset_link) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset - Biwillz Computers</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
            }
            .header-image {
                width: 100%;
                height: auto;
            }
            .content {
                padding: 20px 0;
                color: #333333;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background-color: #2e08f4;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
            .contact-info {
                margin: 20px 0;
                font-size: 14px;
            }
            .footer-image {
                width: 100%;
                height: auto;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <p>
                <a title="Biwillz Computers" href="https://biwillzcomputers.com" rel="noopener">
                    <img class="header-image" src="https://biwillzcomputers.com/_-Flashware_-/wp-content/uploads/2024/11/EmailH1.png" alt="Biwillz Computers Header" />
                </a>
            </p>
            
            <div class="content">
                <p>Hello ' . esc_html($user->display_name) . ',</p>
                <p>We received a request to reset the password for your account. If you did not make this request, please ignore this email.</p>
                <p>To reset your password, click the button below:</p>
                <p style="text-align: center;">
                    <a href="' . esc_url($reset_link) . '" class="button" style="color: #ffffff;">Reset Password</a>
                </p>
                <p>If the button above doesn\'t work, copy and paste this link into your browser:</p>
                <p style="word-break: break-all;">' . esc_url($reset_link) . '</p>
                <p>This link will expire in 24 hours for security reasons.</p>
                <p>If you need any assistance, please don\'t hesitate to contact our support team.</p>
            </div>

            <div class="contact-info">
                <p style="margin: 0; padding: 0; line-height: 1;"><strong>Support Team</strong></p>
                <p style="margin: 0; padding: 0; line-height: 1;"><em>Biwillz Computers</em></p>
                <p style="margin: 0; padding: 0; line-height: 1;"><em>Dev</em></p>
            </div>

            <p>
                <a title="Biwillz Computers" href="https://biwillzcomputers.com" rel="noopener">
                    <img class="footer-image" src="https://biwillzcomputers.com/_-Flashware_-/wp-content/uploads/2024/11/Email-Footer1.png" alt="Biwillz Computers Footer" />
                </a>
            </p>
        </div>
    </body>
    </html>';
}