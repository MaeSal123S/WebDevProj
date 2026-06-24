<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="login-wrap">
    <div class="login-card" style="max-width:440px;">
        <center>
            <div class="logo-icon"><i class="ti ti-car"></i></div>
        </center>
        <div class="login-title">AutoRepair</div>
        <div class="login-subtitle">Create a customer account</div>

        @if($errors->any())
        <div class="alert-error" style="margin-bottom:16px;">
            <i class="ti ti-circle-x"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('customer.register.submit') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" style="padding:0;">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control"
                           placeholder="Juan" value="{{ old('first_name') }}" required>
                </div>
                <div class="form-group" style="padding:0;">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control"
                           placeholder="Dela Cruz" value="{{ old('last_name') }}" required>
                </div>
            </div>
            <div class="form-group" style="padding:0; margin-top:12px;">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                       placeholder="Choose a username" value="{{ old('username') }}" required>
            </div>
            <div class="form-group" style="padding:0; margin-top:12px;">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="At least 6 characters" required>
            </div>
            <div class="form-group" style="padding:0; margin-top:12px;">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                       placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn-login" style="margin-top:20px;">
                Create Account
            </button>
        </form>

        <div style="text-align:center; margin-top:16px; font-size:13px; color:#888;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#4f46e5; text-decoration:none; font-weight:500;">
                Sign in
            </a>
        </div>
    </div>
</div>
</body>
</html>
