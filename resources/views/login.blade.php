<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0781bd">
    <title>Sign in | Centro Laundry</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #24292c;
            background: #fff;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
        }

        button,
        input {
            font: inherit;
        }

        .topbar {
            display: flex;
            min-height: 56px;
            align-items: center;
            gap: 16px;
            padding: 6px 30px;
            background: #0781bd;
            color: #fff;
        }

        .topbar-logo {
            display: grid;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            place-items: center;
            border: 2px solid rgba(255, 255, 255, 0.9);
            background: #fff;
            color: #0781bd;
        }

        .topbar-logo svg {
            width: 28px;
            height: 28px;
        }

        .topbar-name {
            font-size: 21px;
            font-weight: 400;
            letter-spacing: 0.01em;
        }

        .hero {
            position: relative;
            display: grid;
            min-height: 298px;
            place-items: center;
            overflow: hidden;
            background-color: #48433f;
            background-image:
                linear-gradient(90deg, rgba(20, 25, 28, 0.55), rgba(20, 25, 28, 0.36)),
                url("https://images.unsplash.com/photo-1545173168-9f1947eebb7f?auto=format&fit=crop&w=2000&q=85");
            background-position: center 54%;
            background-size: cover;
            color: #fff;
            text-align: center;
        }

        .hero h1 {
            position: relative;
            margin: 0;
            padding: 24px;
            font-size: clamp(42px, 6vw, 78px);
            font-weight: 700;
            letter-spacing: -0.035em;
            text-shadow: 0 2px 18px rgba(0, 0, 0, 0.2);
        }

        .content {
            display: grid;
            grid-template-columns: minmax(240px, 0.78fr) minmax(320px, 1fr);
            align-items: center;
            gap: clamp(40px, 8vw, 112px);
            width: min(100% - 64px, 1060px);
            margin: 0 auto;
            padding: 42px 0 54px;
        }

        .shop-brand {
            display: grid;
            justify-items: center;
            gap: 8px;
            text-align: center;
        }

        .washer-logo {
            display: grid;
            width: 190px;
            height: 190px;
            place-items: center;
            border-radius: 50%;
            background: #3998ca;
        }

        .washer-logo svg {
            width: 112px;
            height: 112px;
            color: #fff;
        }

        .shop-wordmark {
            color: #073671;
            font-size: clamp(40px, 5vw, 54px);
            font-weight: 800;
            letter-spacing: -0.07em;
            line-height: 0.92;
        }

        .shop-wordmark span {
            display: block;
            margin-top: 9px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.43em;
        }

        .login-panel {
            width: min(100%, 440px);
            padding: 0 0 4px;
        }

        .login-panel h2 {
            margin: 0 0 9px;
            color: #272b2e;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .login-intro {
            margin: 0 0 25px;
            color: #697176;
            font-size: 14px;
            line-height: 1.65;
        }

        .login-form {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 7px;
        }

        .field label {
            color: #30373b;
            font-size: 13px;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            height: 45px;
            padding: 0 13px;
            border: 1px solid #d5dce0;
            border-radius: 4px;
            outline: none;
            background: #fff;
            color: #283237;
            font-size: 14px;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }

        .field input::placeholder {
            color: #9aa3a8;
        }

        .field input:focus {
            border-color: #0781bd;
            box-shadow: 0 0 0 3px rgba(7, 129, 189, 0.14);
        }

        .field-error {
            color: #b43e3e;
            font-size: 11px;
        }

        .human-check {
            display: flex;
            min-height: 66px;
            align-items: center;
            gap: 12px;
            padding: 11px 13px;
            border: 1px solid #d5dce0;
            border-radius: 4px;
            background: #f5f8fa;
            color: #27363c;
            cursor: pointer;
        }

        .human-check input {
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
            margin: 0;
            accent-color: #0781bd;
        }

        .human-check-text {
            font-size: 14px;
            font-weight: 600;
        }

        .verification-status {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-left: auto;
            color: #68757b;
            font-size: 12px;
        }

        .verification-status[data-state="verified"] {
            color: #187447;
            font-weight: 700;
        }

        .verification-spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid #c5d4dc;
            border-top-color: #0781bd;
            border-radius: 50%;
            animation: verification-spin 700ms linear infinite;
        }

        .verification-status[data-state="checking"] .verification-spinner {
            display: inline-block;
        }

        @keyframes verification-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 1px;
        }

        .remember-option {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #505a60;
            font-size: 13px;
            cursor: pointer;
        }

        .remember-option input {
            width: 15px;
            height: 15px;
            margin: 0;
            accent-color: #0781bd;
        }

        .sign-in-button {
            width: 100%;
            min-height: 46px;
            margin-top: 3px;
            border: 0;
            border-radius: 4px;
            background: #0781bd;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 150ms ease, box-shadow 150ms ease;
        }

        .sign-in-button:hover {
            background: #066fa4;
            box-shadow: 0 5px 14px rgba(7, 129, 189, 0.2);
        }

        .sign-in-button:focus-visible,
        .remember-option input:focus-visible {
            outline: 3px solid #79c4e7;
            outline-offset: 3px;
        }

        .help-note {
            margin: 16px 0 0;
            color: #737d82;
            font-size: 12px;
            line-height: 1.6;
        }

        .account-prompt {
            margin-top: 21px;
            padding-top: 18px;
            border-top: 1px solid #e8ecee;
            color: #59646a;
            font-size: 13px;
            text-align: center;
        }

        .account-prompt a {
            color: #057bb5;
            font-weight: 700;
            text-decoration: none;
        }

        .account-prompt a:hover {
            text-decoration: underline;
        }

        @media (max-width: 680px) {
            .topbar {
                min-height: 54px;
                gap: 12px;
                padding: 6px 16px;
            }

            .topbar-logo {
                width: 38px;
                height: 38px;
                flex-basis: 38px;
            }

            .topbar-name {
                font-size: 18px;
            }

            .hero {
                min-height: 230px;
            }

            .content {
                grid-template-columns: 1fr;
                justify-items: center;
                gap: 32px;
                width: min(100% - 40px, 440px);
                padding: 34px 0 42px;
            }

            .washer-logo {
                width: 132px;
                height: 132px;
            }

            .washer-logo svg {
                width: 78px;
                height: 78px;
            }

            .shop-wordmark {
                font-size: 39px;
            }

            .shop-wordmark span {
                font-size: 12px;
            }

            .login-panel {
                width: 100%;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <span class="topbar-logo" aria-hidden="true">
            <svg viewBox="0 0 32 32" fill="none">
                <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="2"/>
                <rect x="10" y="8" width="12" height="17" rx="2" fill="currentColor"/>
                <circle cx="16" cy="17" r="4.3" stroke="#fff" stroke-width="1.7"/>
                <path d="M12.5 11h7M12.5 13h1" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/>
            </svg>
        </span>
        <span class="topbar-name">Centro Laundry</span>
    </header>

    <section class="hero" aria-label="Centro Laundry">
        <h1>Centro Laundry</h1>
    </section>

    <main class="content">
        <section class="shop-brand" aria-label="Centro Laundry staff portal">
            <div class="washer-logo" aria-hidden="true">
                <svg viewBox="0 0 112 112" fill="none">
                    <rect x="14" y="8" width="84" height="96" rx="5" fill="currentColor"/>
                    <path d="M14 20h84" stroke="#3998ca" stroke-width="4"/>
                    <rect x="22" y="27" width="15" height="9" rx="1.5" fill="#3998ca"/>
                    <circle cx="75" cy="31.5" r="3.2" fill="#3998ca"/>
                    <circle cx="84" cy="31.5" r="3.2" fill="#3998ca"/>
                    <circle cx="93" cy="31.5" r="3.2" fill="#3998ca"/>
                    <circle cx="56" cy="66" r="25" stroke="#3998ca" stroke-width="6"/>
                    <circle cx="56" cy="66" r="17" stroke="#3998ca" stroke-width="5"/>
                    <path d="M79 65h9" stroke="#3998ca" stroke-width="5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="shop-wordmark">
                Centro
                <span>LAUNDRY</span>
            </div>
        </section>

        <section class="login-panel" aria-labelledby="login-heading">
            <h2 id="login-heading">Centro Laundry Staff Login</h2>
            <p class="login-intro">Sign in to manage your shop and keep every order moving.</p>

            <form class="login-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Enter your username" autocomplete="username" required>
                    @error('username') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
                </div>

                <div class="field">
                    <label for="human-check-box">Human verification (required for customer accounts)</label>
                    <label class="human-check" for="human-check-box">
                        <input id="human-check-box" type="checkbox" aria-describedby="human-check-status">
                        <span class="human-check-text">I'm not a robot</span>
                        <span id="human-check-status" class="verification-status" aria-live="polite">
                            <span class="verification-spinner" aria-hidden="true"></span>
                            <span data-status-text></span>
                        </span>
                    </label>
                    <input id="human-check" name="human_check" type="hidden" value="">
                    @error('human_check') <span class="field-error" role="alert">{{ $message }}</span> @enderror
                    <span class="help-note" style="margin: 0;">Staff and admin accounts can continue without checking this box.</span>
                </div>

                <div class="form-options">
                    <label class="remember-option" for="remember">
                        <input id="remember" name="remember" type="checkbox">
                        <span>Remember me</span>
                    </label>
                </div>

                <button class="sign-in-button" type="submit">Sign in to your account</button>
            </form>

            <p class="account-prompt">Don't have an account? <a href="{{ route('register') }}">Create an account</a></p>
            <p class="help-note">Need help signing in? Contact your system administrator.</p>
        </section>
    </main>
    <script>
        const verificationBox = document.getElementById('human-check-box');
        const verificationValue = document.getElementById('human-check');
        const verificationStatus = document.getElementById('human-check-status');
        const verificationStatusText = verificationStatus.querySelector('[data-status-text]');
        const loginForm = document.querySelector('.login-form');
        let verificationTimer;

        verificationBox.addEventListener('change', () => {
            window.clearTimeout(verificationTimer);
            verificationValue.value = '';
            verificationStatus.dataset.state = '';

            if (!verificationBox.checked) {
                verificationStatusText.textContent = '';
                return;
            }

            verificationStatus.dataset.state = 'checking';
            verificationStatusText.textContent = 'Verifying...';
            verificationTimer = window.setTimeout(() => {
                verificationValue.value = '1';
                verificationStatus.dataset.state = 'verified';
                verificationStatusText.textContent = 'Verified';
            }, 4000);
        });

        loginForm.addEventListener('submit', (event) => {
            if (verificationBox.checked && verificationValue.value !== '1') {
                event.preventDefault();
                verificationStatusText.textContent = 'Please wait for verification to finish.';
            }
        });
    </script>
</body>
</html>
