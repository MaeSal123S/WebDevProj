<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .login-wrap {
            background:
                linear-gradient(135deg, rgba(8,0,0,0.85) 0%, rgba(80,10,10,0.78) 50%, rgba(8,0,0,0.9) 100%),
                url('{{ asset('Images/Vi.png') }}') center center / cover no-repeat fixed;
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
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div style="text-align:center; margin-bottom:20px;">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#96281b,#e74c3c);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;box-shadow:0 4px 20px rgba(192,57,43,0.5);">
                <i class="ti ti-lock-open" style="font-size:26px;color:#fff;"></i>
            </div>
            <div class="login-title">Reset Password</div>
            <div class="login-subtitle">Enter your username and new password</div>
        </div>

        @if(session('error'))
        <div class="alert-error" style="margin-bottom:14px;">
            <i class="ti ti-circle-x"></i> {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert-error" style="margin-bottom:14px;">
            <i class="ti ti-circle-x"></i> {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('forgot.password.reset') }}">
            @csrf
            <label class="auth-label">Username</label>
            <input type="text" name="username" class="auth-input" placeholder="Enter your username" required>

            <label class="auth-label">New Password</label>
            <input type="password" name="new_password" class="auth-input" placeholder="Enter new password" required>

            <label class="auth-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="auth-input" placeholder="Confirm new password" required>

            <button type="submit" class="btn-login">
                <i class="ti ti-check" style="margin-right:6px;"></i> Reset Password
            </button>
        </form>

        <div style="text-align:center; margin-top:18px;">
            <a href="{{ route('login') }}"
               style="font-size:13px; color:rgba(255,255,255,0.45); text-decoration:none;">
                <i class="ti ti-arrow-left"></i> Back to login
            </a>
        </div>
    </div>
</div>
</body>
</html>
