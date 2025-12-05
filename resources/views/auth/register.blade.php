<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign Up - {{ config('app.name', 'Bingkis Kaca') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #CBA991 0%, #9D6B46 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 480px;
            background: rgba(203, 169, 145, 0.95);
            border-radius: 30px;
            padding: 3rem 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .back-button {
            position: absolute;
            top: 2rem;
            left: 2rem;
            background: rgba(88, 54, 1, 0.5);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: rgba(88, 54, 1, 0.8);
            transform: translateX(-3px);
        }

        .back-button svg {
            width: 24px;
            height: 24px;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
            margin-top: 1rem;
        }

        .auth-tagline {
            font-size: 0.75rem;
            color: white;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .auth-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .auth-logo .italic {
            font-style: italic;
        }

        .auth-subtitle {
            font-size: 0.75rem;
            color: white;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .auth-title {
            font-size: 2rem;
            color: white;
            margin: 2rem 0 2rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 50px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .input-wrapper:focus-within {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .input-icon {
            padding: 0 1.25rem;
            color: #9D6B46;
            display: flex;
            align-items: center;
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .form-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 1rem 1rem 1rem 0;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            color: #333;
        }

        .form-input::placeholder {
            color: #999;
        }

        .password-toggle {
            padding: 0 1.25rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #9D6B46;
            display: flex;
            align-items: center;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            padding-left: 1.25rem;
        }

        .submit-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #9D6B46 0%, #583601 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(88, 54, 1, 0.3);
            margin-top: 1.5rem;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(88, 54, 1, 0.4);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #583601;
        }

        .auth-footer a {
            color: #522504;
            font-weight: 600;
            text-decoration: underline;
            transition: color 0.3s ease;
        }

        .auth-footer a:hover {
            color: #3a1802;
        }

        .character-illustration {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .character-illustration img {
            width: 80px;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        @media (max-width: 576px) {
            .auth-container {
                padding: 2rem 1.5rem;
            }

            .auth-logo {
                font-size: 2rem;
            }

            .auth-title {
                font-size: 1.75rem;
            }

            .character-illustration img {
                width: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <a href="{{ route('home') }}" class="back-button">
            <svg viewBox="0 0 24 24">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
        </a>

        <div class="auth-header">
            <div class="auth-tagline">ARRIVE AT 2025</div>
            <div class="auth-logo">
                <span class="italic">bingkis</span><span>kaca.</span>
            </div>
            <div class="auth-subtitle">SEMANIS SENYUMAN SEINDAH BINGKISAN</div>
        </div>

        <h1 class="auth-title">Create an Account</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <input type="text" name="name" class="form-input" placeholder="Username" 
                           value="{{ old('name') }}" required autofocus>
                </div>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <path d="M22 6l-10 7L2 6"/>
                        </svg>
                    </div>
                    <input type="email" name="email" class="form-input" placeholder="Email" 
                           value="{{ old('email') }}" required>
                </div>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'eyeIcon1')">
                        <svg id="eyeIcon1" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <div class="input-wrapper">
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="form-input" placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'eyeIcon2')">
                        <svg id="eyeIcon2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="submit-button">Sign up</button>
        </form>

        <div class="auth-footer">
            Already Have an Account? <a href="{{ route('login') }}">Sign in</a>
        </div>

        <div class="character-illustration">
            <img src="{{ asset('images/character-left.png') }}" alt="Character" onerror="this.style.display='none'">
            <img src="{{ asset('images/character-left.png') }}" alt="Character" onerror="this.style.display='none'">
            <img src="{{ asset('images/character-right.png') }}" alt="Character" onerror="this.style.display='none'">
            <img src="{{ asset('images/character-right.png') }}" alt="Character" onerror="this.style.display='none'">
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>
</body>
</html>