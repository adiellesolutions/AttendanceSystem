<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RFID Attendance System Login - Secure access">
    <title>Login - RFID Attendance System</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .login-bg {
            background: linear-gradient(135deg, var(--color-primary-50) 0%, var(--color-background) 100%);
            min-height: 100vh;
        }
        
        .login-card {
            max-width: 400px;
            width: 90%;
            margin: 0 auto;
        }
        
        .remember-checkbox {
            accent-color: var(--color-primary);
        }
        
        .password-toggle {
            cursor: pointer;
            user-select: none;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        .demo-hint {
            background: var(--color-primary-50);
            border: 1px solid var(--color-primary-200);
            border-radius: 8px;
            padding: 12px;
            margin: 16px 0;
        }
        
        .role-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 4px;
            text-transform: uppercase;
        }
        
        .role-student {
            background-color: var(--color-success-100);
            color: var(--color-success-700);
        }
        
        .role-teacher {
            background-color: var(--color-primary-100);
            color: var(--color-primary-700);
        }
        
        .role-admin {
            background-color: var(--color-accent-100);
            color: var(--color-accent-700);
        }
    </style>
</head>
<body class="login-bg">
    <!-- Login Container -->
    <div class="min-h-screen flex items-center justify-center p-4 fade-in">
        <div class="login-card card shadow-card">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="64" height="64" rx="12" fill="#1E3A5F"/>
                        <path d="M24 32L36 42L48 22" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="32" cy="32" r="20" stroke="#E67E22" stroke-width="3" stroke-dasharray="6 6"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-heading font-bold text-text-primary mb-2">RFID Attendance System</h1>
                <p class="text-text-secondary">Sign in to your account</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm">
                <!-- Email/Username Field -->
                <div class="mb-4">
                    <label for="username" class="label">Email or User ID</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username"
                        class="input w-full"
                        placeholder="Enter email or ID"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- Password Field -->
                <div class="mb-6">
                    <div class="relative">
                        <label for="password" class="label">Password</label>

                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            class="input w-full pr-10"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button 
                            type="button" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 password-toggle"
                            onclick="togglePassword()"
                            aria-label="Toggle password visibility"
                        >
                        <span id="passwordIcon" class="w-5 h-5 flex items-center justify-center text-lg">👁️</span>
                        </button>
                        <div class="flex justify-between items-center mb-2">
                        
                    </div>
                    </div>
                </div>

               

                

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-full mb-4">
                    <span id="loginText">Sign In</span>
                    <span id="loadingSpinner" class="hidden">Loading...</span>
                </button>

                <!-- Error Message -->
                <div id="errorMessage" class="hidden p-3 rounded-xl bg-error-50 border border-error-200 text-error text-sm mb-4">
                    Invalid credentials. Please try again.
                </div>
            </form>

            <!-- Links -->
            <div class="text-center pt-4 border-t border-border">
               
                <p class="text-xs text-text-secondary mt-2">
                    © 2026 RFID Attendance System
                </p>
            </div>
        </div>
    </div>

   <script>
/* PASSWORD TOGGLE */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.textContent = '👁️‍🗨️';
    } else {
        passwordInput.type = 'password';
        passwordIcon.textContent = '👁️';
    }
}

/* LOGIN SUBMIT */
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        const loginBtn = form.querySelector('button[type="submit"]');
        const loginText = document.getElementById('loginText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const errorMessage = document.getElementById('errorMessage');

        loginText.classList.add('hidden');
        loadingSpinner.classList.remove('hidden');
        errorMessage.classList.add('hidden');
        loginBtn.disabled = true;

        fetch("../../backend/api/login.php", {
            method: "POST",
            body: formData
        })
        .then(res => {
            if (!res.ok) return res.text().then(t => { throw t; });
            return res.json();
        })
        .then(data => {
            if (data.role === "admin") {
                window.location.href = "admin_dashboard.php";
            } else if (data.role === "teacher") {
                window.location.href = "teacher_dashboard.php";
            } else {
                window.location.href = "student_dashboard.php";
            }
        })
        .catch(err => {
            errorMessage.textContent = err;
            errorMessage.classList.remove('hidden');
            loginText.classList.remove('hidden');
            loadingSpinner.classList.add('hidden');
            loginBtn.disabled = false;
        });
    });
});
</script>

<script>
/* PASSWORD TOGGLE */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        passwordIcon.textContent = '👁️‍🗨️';
    } else {
        passwordInput.type = 'password';
        passwordIcon.textContent = '👁️';
    }
}

/* LOGIN SUBMIT */
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    const loginBtn = form.querySelector('button[type="submit"]');
    const loginText = document.getElementById('loginText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const errorMessage = document.getElementById('errorMessage');

    loginText.classList.add('hidden');
    loadingSpinner.classList.remove('hidden');
    errorMessage.classList.add('hidden');
    loginBtn.disabled = true;

    fetch("../../backend/api/login.php", {
        method: "POST",
        body: formData
    })
    .then(res => {
        if (!res.ok) return res.text().then(t => { throw t; });
        return res.json();
    })
    .then(data => {
        if (data.role === "admin") {
            window.location.href = "admin_dashboard.php";
        } else if (data.role === "teacher") {
            window.location.href = "teacher_dashboard.php";
        } else {
            window.location.href = "student_dashboard.php";
        }
    })
    .catch(err => {
        errorMessage.textContent = err;
        errorMessage.classList.remove('hidden');
        loginText.classList.remove('hidden');
        loadingSpinner.classList.add('hidden');
        loginBtn.disabled = false;
    });
});
</script>

</body>
</html>