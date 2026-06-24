<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .login-wrap {
            background:
                linear-gradient(135deg, rgba(8,0,0,0.85) 0%, rgba(80,10,10,0.78) 50%, rgba(8,0,0,0.9) 100%),
                url('{{ asset('Images/Vi.png') }}') center center / cover no-repeat fixed;
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
            box-shadow: 0 24px 64px rgba(0,0,0,0.7);
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
            width: 380px;
            flex-shrink: 0;
            padding: 40px 36px;
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
        .brand-name { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; }
        .brand-name span { color: #e74c3c; }
        .brand-sub { font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 8px; margin-bottom: 28px; text-transform: uppercase; letter-spacing: 0.1em; }
        .brand-feat { display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px; }
        .brand-feat i { color: #e74c3c; font-size: 16px; }
        .form-title { font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 4px; }
        .form-sub { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 20px; }
        .auth-label { display: block; font-size: 10px; font-weight: 600; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 5px; }
        .auth-input {
            width: 100%; padding: 9px 12px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 7px; font-size: 13px; color: #fff;
            outline: none; margin-bottom: 12px;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .auth-input::placeholder { color: rgba(255,255,255,0.25); }
        .auth-input:focus { border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(192,57,43,0.3); }
        .name-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="auth-split">
        {{-- LEFT: Brand --}}
        <div class="auth-left">
            <div class="brand-icon"><i class="ti ti-car"></i></div>
            <div class="brand-name">Auto<span>Repair</span></div>
            <div class="brand-sub">Customer Portal</div>
            <div class="brand-feat"><i class="ti ti-calendar-plus"></i> Book appointments online</div>
            <div class="brand-feat"><i class="ti ti-car"></i> Manage your vehicles</div>
            <div class="brand-feat"><i class="ti ti-history"></i> Track service history</div>
            <div class="brand-feat"><i class="ti ti-bell"></i> Get status updates</div>
        </div>

        {{-- RIGHT: Register form --}}
        <div class="auth-right">
            <div class="form-title">Create Account</div>
            <div class="form-sub">Register as a customer to book appointments</div>

            @if($errors->any())
            <div class="alert-error" style="margin-bottom:14px;">
                <i class="ti ti-circle-x"></i>
                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            </div>
            @endif

            <form method="POST" action="{{ route('customer.register.submit') }}">
                @csrf
                <div class="name-row">
                    <div>
                        <label class="auth-label">First Name</label>
                        <input type="text" name="first_name" class="auth-input"
                               placeholder="Juan" value="{{ old('first_name') }}" required>
                    </div>
                    <div>
                        <label class="auth-label">Last Name</label>
                        <input type="text" name="last_name" class="auth-input"
                               placeholder="Dela Cruz" value="{{ old('last_name') }}" required>
                    </div>
                </div>

                <label class="auth-label">Username</label>
                <input type="text" name="username" class="auth-input"
                       placeholder="Choose a username" value="{{ old('username') }}" required>

                <label class="auth-label">Password</label>
                <input type="password" name="password" class="auth-input"
                       placeholder="At least 6 characters" required>

                <label class="auth-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="auth-input"
                       placeholder="Repeat your password" required>

                <button type="submit" class="btn-login">
                    <i class="ti ti-user-plus" style="margin-right:6px;"></i> Create Account
                </button>
            </form>

            <div style="text-align:center; margin-top:16px; font-size:13px; color:rgba(255,255,255,0.4);">
                Already have an account?
                <a href="{{ route('login') }}"
                   style="color:#e74c3c; font-weight:600; text-decoration:none;">Sign in</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
