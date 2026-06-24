<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <div class="login-wrap">
        <div class="login-card">
            <center><div class="logo-icon">
                <i class="ti ti-car"></i>
            </div></center>
            <div class="login-title">AutoRepair</div>
            <div class="login-subtitle">Management System</div>

            @if(session('error'))
            <div class="alert-error" id="errorAlert">
                <i class="ti ti-circle-x"></i>
                <span id="errorMessage">{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="alert-success">
                <i class="ti ti-circle-check"></i>
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                        placeholder="Enter username"
                        value="{{ old('username') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn-login" id="loginBtn">Login</button>
            </form>

            <div style="text-align:center; margin-top:16px;">
                <a href="{{ route('forgot.password') }}"
                    style="font-size:13px; color:#4f46e5; text-decoration:none;">
                    Forgot password?
                </a>
            </div>
        </div>
    </div>

    <script>
        // auto countdown if locked
        const errorMsg = document.getElementById('errorMessage');
        if (errorMsg) {
            const text = errorMsg.innerText;
            const match = text.match(/wait (\d+) seconds/);
            if (match) {
                let seconds = parseInt(match[1]);
                const loginBtn = document.getElementById('loginBtn');
                loginBtn.disabled = true;
                loginBtn.style.opacity = '0.6';
                loginBtn.style.cursor = 'not-allowed';

                const interval = setInterval(() => {
                    seconds--;
                    errorMsg.innerText = `Too many failed attempts. Please wait ${seconds} seconds before trying again.`;
                    if (seconds <= 0) {
                        clearInterval(interval);
                        loginBtn.disabled = false;
                        loginBtn.style.opacity = '1';
                        loginBtn.style.cursor = 'pointer';
                        document.getElementById('errorAlert').style.display = 'none';
                    }
                }, 1000);
            }
        }
    </script>
</body>

</html>