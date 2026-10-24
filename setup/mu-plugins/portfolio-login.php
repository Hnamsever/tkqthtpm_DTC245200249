<?php
/**
 * Plugin Name: Portfolio Modern Login Theme
 * Description: Clean, perfectly centered, modern login theme for wp-login.php with high-contrast credentials guide.
 * Version: 2.0.0
 * Author: Portfolio Đề 13
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('login_enqueue_scripts', function () {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --bg-dark: #0b0f19;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        /* ----- Body Centering (Column layout fixes alignment) ----- */
        body.login {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            background-color: var(--bg-dark) !important;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(124, 58, 237, 0.2) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(6, 182, 212, 0.12) 0px, transparent 60%) !important;
            background-attachment: fixed !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 0 !important;
            padding: 40px 16px !important;
            box-sizing: border-box !important;
        }

        /* Container #login */
        #login {
            width: 100% !important;
            max-width: 420px !important;
            margin: 0 auto !important;
            padding: 0 !important;
            position: relative !important;
        }

        /* ----- Brand Logo & Header ----- */
        #login h1 {
            margin-bottom: 12px !important;
            text-align: center !important;
        }

        #login h1 a {
            background-image: none !important;
            text-indent: 0 !important;
            width: auto !important;
            height: auto !important;
            display: inline-flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            color: #ffffff !important;
            margin: 0 auto !important;
            transition: transform 0.25s ease;
        }

        #login h1 a:hover {
            transform: translateY(-2px);
        }

        #login h1 a::before {
            content: "\26A1";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: var(--primary-gradient);
            box-shadow: 0 10px 25px -4px rgba(79, 70, 229, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2);
            font-size: 28px;
            margin-bottom: 12px;
        }

        #login h1 a::after {
            content: "PORTFOLIO ADMIN";
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #ffffff;
            text-transform: uppercase;
        }

        .pf-login-sub {
            text-align: center;
            color: #94a3b8;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* ----- Credentials Guide Box (High-Contrast Dark Glass) ----- */
        .pf-login-hint {
            background: rgba(30, 41, 59, 0.85) !important;
            border: 1px solid rgba(99, 102, 241, 0.45) !important;
            border-radius: 14px !important;
            padding: 13px 16px !important;
            margin-bottom: 20px !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25) !important;
            backdrop-filter: blur(10px) !important;
        }

        .pf-hint-header {
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
            color: #a5b4fc;
            margin-bottom: 8px;
        }

        .pf-hint-icon {
            margin-right: 6px;
            font-size: 14px;
        }

        .pf-hint-body {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .pf-hint-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            color: #e2e8f0;
        }

        .pf-hint-label {
            font-weight: 500;
            color: #cbd5e1;
        }

        .pf-hint-code {
            background: #1e1b4b;
            border: 1px solid #6366f1;
            color: #c7d2fe;
            font-family: 'Consolas', 'Fira Code', monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            user-select: all;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        /* ----- Form Card (Solid White with Clean Contrast) ----- */
        #loginform {
            background: #ffffff !important;
            border-radius: 20px !important;
            padding: 32px 28px !important;
            border: 1px solid rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.4) !important;
        }

        #loginform label {
            font-size: 12.5px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            margin-bottom: 6px !important;
            display: block !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
        }

        #loginform input[type="text"],
        #loginform input[type="password"] {
            font-family: inherit !important;
            font-size: 14.5px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 11px 14px !important;
            margin-top: 4px !important;
            margin-bottom: 18px !important;
            width: 100% !important;
            box-sizing: border-box !important;
            transition: all 0.2s ease !important;
        }

        #loginform input[type="text"]:focus,
        #loginform input[type="password"]:focus {
            background: #ffffff !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15) !important;
            outline: none !important;
        }

        /* Remember me */
        .forgetmenot {
            float: none !important;
            margin-bottom: 20px !important;
            display: flex !important;
            align-items: center !important;
        }

        .forgetmenot label {
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #64748b !important;
            text-transform: none !important;
            display: flex !important;
            align-items: center !important;
            cursor: pointer !important;
            margin-bottom: 0 !important;
        }

        .forgetmenot input[type="checkbox"] {
            border-radius: 5px !important;
            border: 1.5px solid #cbd5e1 !important;
            width: 17px !important;
            height: 17px !important;
            margin-right: 8px !important;
            cursor: pointer !important;
        }

        .forgetmenot input[type="checkbox"]:checked {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        /* Submit Button */
        .submit {
            float: none !important;
            margin-top: 6px !important;
        }

        #wp-submit {
            font-family: inherit !important;
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 12px 20px !important;
            font-size: 14.5px !important;
            font-weight: 700 !important;
            letter-spacing: 0.04em !important;
            text-transform: uppercase !important;
            color: #ffffff !important;
            background: var(--primary-gradient) !important;
            border: none !important;
            border-radius: 10px !important;
            box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.45) !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            height: auto !important;
            line-height: normal !important;
        }

        #wp-submit:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 12px 25px -4px rgba(79, 70, 229, 0.6) !important;
            filter: brightness(1.06) !important;
        }

        /* ----- Error & Notice Messages ----- */
        .login #login_error,
        .login .message,
        .login .notice {
            border-radius: 12px !important;
            padding: 12px 16px !important;
            margin-bottom: 18px !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2) !important;
            font-size: 13.5px !important;
            line-height: 1.5 !important;
            box-sizing: border-box !important;
        }

        .login .message {
            background: rgba(34, 197, 94, 0.12) !important;
            border: 1px solid rgba(34, 197, 94, 0.35) !important;
            border-left: 5px solid #22c55e !important;
            color: #86efac !important;
        }

        .login #login_error {
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.35) !important;
            border-left: 5px solid #ef4444 !important;
            color: #fca5a5 !important;
        }

        /* ----- Footer Nav Links ----- */
        #nav, #backtoblog {
            padding: 0 !important;
            margin: 16px 0 0 0 !important;
            text-align: center !important;
        }

        #nav a, #backtoblog a {
            color: #94a3b8 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            transition: color 0.2s ease !important;
            text-decoration: none !important;
        }

        #nav a:hover, #backtoblog a:hover {
            color: #ffffff !important;
            text-decoration: underline !important;
        }

        /* ----- Language Switcher (Centered at Bottom) ----- */
        .language-switcher {
            margin-top: 24px !important;
            text-align: center !important;
            width: 100% !important;
            max-width: 420px !important;
        }

        .language-switcher form {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
        }

        .language-switcher label {
            color: #94a3b8 !important;
            font-size: 12.5px !important;
        }

        .language-switcher select {
            border-radius: 8px !important;
            background: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 5px 10px !important;
            font-size: 12px !important;
        }

        .language-switcher input[type="submit"] {
            border-radius: 8px !important;
            background: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            padding: 5px 12px !important;
            font-size: 12px !important;
            cursor: pointer !important;
        }
    </style>
    <?php
});

add_filter('login_headerurl', function () {
    return home_url('/');
});

add_filter('login_headertext', function () {
    return 'Portfolio Admin';
});

add_filter('login_message', function ($message) {
    $hint = '
    <div class="pf-login-sub">Hệ Thống Quản Trị Nội Dung — Đề Tài 13</div>
    <div class="pf-login-hint">
        <div class="pf-hint-header">
            <span class="pf-hint-icon">🔐</span>
            <span>Thông Tin Đăng Nhập Quản Trị</span>
        </div>
        <div class="pf-hint-body">
            <div class="pf-hint-row">
                <span class="pf-hint-label">Tên đăng nhập:</span>
                <code class="pf-hint-code">admin</code>
            </div>
            <div class="pf-hint-row">
                <span class="pf-hint-label">Mật khẩu:</span>
                <code class="pf-hint-code">Admin@Portfolio2026</code>
            </div>
        </div>
    </div>';

    return $hint . $message;
});
