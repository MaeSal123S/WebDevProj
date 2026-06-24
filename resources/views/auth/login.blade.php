<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .login-wrap {
            background:
                linear-gradient(135deg, rgba(8,0,0,0.85) 0%, rgba(80,10,10,0.78) 50%, rgba(8,0,0,0.9) 100%),
                url('{{ asset('Images/FINALS.png') }}') center center / cover no-repeat fixed;
        }
        .auth-split {
            display: flex;
            width: 820px;
            max-width: 100%;
            background: rgba(15,3,3,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(192,57,43,0.25);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.05);
        }
        .auth-left {
            flex: 1;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid rgba(192,57,43,0.15);
        }
        .auth-right {
            width: 340px;
            flex-shrink: 0;
            padding: 48px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #96281b, #e74c3c);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: #fff;
            box-shadow: 0 4px 20px rgba(192,57,43,0.5);
            margin-bottom: 24px;
        }
        .brand-name {
            font-size: 32px; font-weight: 800;
            color: #fff; line-height: 1;
            letter-spacing: -0.5px;
        }
        .brand-name span { color: #e74c3c; }
        .brand-sub {
            font-size: 13px; color: rgba(255,255,255,0.45);
            margin-top: 8px; margin-bottom: 32px;
            text-transform: uppercase; letter-spacing: 0.1em;
        }
        .brand-features { display: flex; flex-direction: column; gap: 12px; }
        .brand-feat {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; color: rgba(255,255,255,0.6);
        }
        .brand-feat i { color: #e74c3c; font-size: 16px; }
        .form-title {
            font-size: 22px; font-weight: 700; color: #fff;
            margin-bottom: 4px;
        }
        .form-sub {
            font-size: 12px; color: rgba(255,255,255,0.4);
            margin-bottom: 24px;
        }
        .auth-label {
            display: block; font-size: 11px; font-weight: 600;
            color: rgba(255,255,255,0.5); text-transform: uppercase;
            letter-spacing: 0.07em; margin-bottom: 6px;
        }
        .auth-input {
            width: 100%; padding: 10px 14px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px; font-size: 13px; color: #fff;
            outline: none; margin-bottom: 14px;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .auth-input::placeholder { color: rgba(255,255,255,0.25); }
        .auth-input:focus {
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(192,57,43,0.3);
        }
        .auth-links {
            display: flex; justify-content: space-between;
            align-items: center; margin-top: 14px;
            font-size: 12px;
        }
        .auth-link { color: rgba(255,255,255,0.45); text-decoration: none; }
        .auth-link:hover { color: #e74c3c; }
        .auth-link-red { color: #e74c3c; font-weight: 600; text-decoration: none; }
        .auth-link-red:hover { color: #ff6b5a; }
        .divider {
            text-align: center; font-size: 11px;
            color: rgba(255,255,255,0.3); margin: 14px 0 6px;
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="auth-split">
        {{-- LEFT: Brand panel --}}
        <div class="auth-left">
            <div class="brand-icon"><i class="ti ti-car"></i></div>
            <div class="brand-name">Auto<span>Repair</span></div>
            <div class="brand-sub">Management System</div>
            <div class="brand-features">
                <div class="brand-feat"><i class="ti ti-calendar-check"></i> Appointment Booking</div>
                <div class="brand-feat"><i class="ti ti-clipboard-list"></i> Repair Order Tracking</div>
                <div class="brand-feat"><i class="ti ti-package"></i> Inventory Management</div>
                <div class="brand-feat"><i class="ti ti-chart-bar"></i> Dashboard Analytics</div>
            </div>
        </div>

        {{-- RIGHT: Login form --}}
        <div class="auth-right">
            <div class="form-title">Sign In</div>
            <div class="form-sub">Welcome back — enter your credentials</div>

            @if(session('error'))
            <div class="alert-error" id="errorAlert" style="margin-bottom:14px;">
                <i class="ti ti-circle-x"></i>
                <span id="errorMessage">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="alert-success" style="margin-bottom:14px;">
                <i class="ti ti-circle-check"></i>
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <label class="auth-label">Username</label>
                <input type="text" name="username" class="auth-input"
                    placeholder="Enter your username"
                    value="{{ old('username') }}" required autofocus>

                <label class="auth-label">Password</label>
                <input type="password" name="password" class="auth-input"
                    placeholder="Enter your password" required>

                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="ti ti-login" style="margin-right:6px;"></i> Sign In
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('forgot.password') }}" class="auth-link">Forgot password?</a>
            </div>
            <div class="divider">New customer?</div>
            <div style="text-align:center;">
                <a href="{{ route('customer.register') }}" class="auth-link-red">
                    <i class="ti ti-user-plus"></i> Register here
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const errorMsg = document.getElementById('errorMessage');
    if (errorMsg) {
        const match = errorMsg.innerText.match(/wait (\d+) seconds/);
        if (match) {
            let seconds = parseInt(match[1]);
            const btn = document.getElementById('loginBtn');
            btn.disabled = true; btn.style.opacity = '0.5';
            const iv = setInterval(() => {
                seconds--;
                errorMsg.innerText = `Too many failed attempts. Please wait ${seconds} seconds.`;
                if (seconds <= 0) {
                    clearInterval(iv);
                    btn.disabled = false; btn.style.opacity = '1';
                    document.getElementById('errorAlert').style.display = 'none';
                }
            }, 1000);
        }
    }
</script>
</body>
</html>
