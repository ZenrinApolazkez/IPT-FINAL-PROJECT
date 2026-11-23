<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0781bd">
    <title>Create an account | Centro Laundry</title>
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
            display: grid;
            min-height: 230px;
            place-items: center;
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
            margin: 0;
            padding: 24px;
            font-size: clamp(38px, 5vw, 64px);
            font-weight: 700;
            letter-spacing: -0.035em;
            text-shadow: 0 2px 18px rgba(0, 0, 0, 0.2);
        }

        .content {
            display: grid;
            grid-template-columns: minmax(220px, 0.7fr) minmax(320px, 1fr);
            align-items: center;
            gap: clamp(36px, 7vw, 96px);
            width: min(100% - 64px, 980px);
            margin: 0 auto;
            padding: 36px 0 48px;
        }

        .shop-brand {
            display: grid;
            justify-items: center;
            gap: 8px;
            text-align: center;
        }

        .washer-logo {
            display: grid;
            width: 160px;
            height: 160px;
            place-items: center;
            border-radius: 50%;
            background: #3998ca;
        }

        .washer-logo svg {
            width: 96px;
            height: 96px;
            color: #fff;
        }

        .shop-wordmark {
            color: #073671;
            font-size: 46px;
            font-weight: 800;
            letter-spacing: -0.07em;
            line-height: 0.92;
        }

        .shop-wordmark span {
            display: block;
            margin-top: 9px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.43em;
        }

        .register-panel {
            width: min(100%, 440px);
        }

        .register-panel h2 {
            margin: 0 0 9px;
            color: #272b2e;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .register-intro {
            margin: 0 0 22px;
            color: #697176;
            font-size: 14px;
            line-height: 1.65;
        }

        .register-form {
            display: grid;
            gap: 13px;
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
            height: 43px;
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

        .create-button {
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

        .create-button:hover {
            background: #066fa4;
            box-shadow: 0 5px 14px rgba(7, 129, 189, 0.2);
        }

        .create-button:focus-visible,
        .login-link:focus-visible {
            outline: 3px solid #79c4e7;
            outline-offset: 3px;
        }

        .login-link {
            display: block;
            margin-top: 19px;
            color: #057bb5;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .form-note {
            margin: 13px 0 0;
            color: #737d82;
            font-size: 11px;
            line-height: 1.6;
            text-align: center;
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
                min-height: 180px;
            }

            .content {
                grid-template-columns: 1fr;
                justify-items: center;
                gap: 25px;
                width: min(100% - 40px, 440px);
                padding: 28px 0 38px;
            }

            .washer-logo {
                width: 112px;
                height: 112px;
            }

            .washer-logo svg {
                width: 68px;
                height: 68px;
            }

            .shop-wordmark {
                font-size: 34px;
            }

            .shop-wordmark span {
                margin-top: 7px;
                font-size: 10px;
            }

            .register-panel {
                width: 100%;
            }
        }

        @media (max-width: 360px) {
            .content {
                width: min(100% - 32px, 440px);
            }

            .register-panel h2 {
                font-size: 22px;
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
        <h1>Create your account</h1>
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

        <section class="register-panel" aria-labelledby="register-heading">
            <h2 id="register-heading">Create an account</h2>
            <p class="register-intro">Enter your details to get started with Centro Laundry.</p>

            @if ($errors->any())
                <div role="alert" style="margin-bottom: 14px; color: #963c3c; font-size: 12px;">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="register-form" method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Your full name" autocomplete="name" required>
                </div>

                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="Choose a username" autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                </div>

                <div class="field">
                    <label for="phone">Mobile number <span style="font-weight: 400;">(optional, for SMS updates)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+63 9XX XXX XXXX" autocomplete="tel">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Create a password" autocomplete="new-password" required>
                </div>

                <div class="field">
                    <label for="password-confirmation">Confirm password</label>
                    <input id="password-confirmation" name="password_confirmation" type="password" placeholder="Enter your password again" autocomplete="new-password" required>
                </div>

                <button class="create-button" type="submit">Create account</button>
            </form>

            <p class="form-note">Customer accounts are created here. Staff access is managed by the laundry administrator.</p>
            <a class="login-link" href="{{ route('login') }}">Already have an account? Sign in</a>
        </section>
    </main>
</body>
</html>
