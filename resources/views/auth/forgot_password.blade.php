<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="login-wrap">
        <div class="login-card">
            <center><div class="logo-icon">
                <i class="ti ti-lock-open"></i>
            </div></center>
            <div class="login-title">Reset Password</div>
            <div class="login-subtitle">Enter your username and new password</div>

            @if(session('error'))
            <div class="alert-error">
                <i class="ti ti-circle-x"></i>
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert-error">
                <i class="ti ti-circle-x"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('forgot.password.reset') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                        placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New password</label>
                    <input type="password" name="new_password" class="form-control"
                        placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="confirm_password" class="form-control"
                        placeholder="Confirm new password" required>
                </div>
                <button type="submit" class="btn-login">Reset Password</button>
            </form>

            <div style="text-align:center; margin-top:16px;">
                <a href="{{ route('login') }}"
                    style="font-size:13px; color:#4f46e5; text-decoration:none;">
                    <i class="ti ti-arrow-left"></i> Back to login
                </a>
            </div>
        </div>
    </div>
</body>

</html>