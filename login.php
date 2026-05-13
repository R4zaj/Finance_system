<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance System | Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        /* Exact color matching from your image */
        :root {
            --brand-green: #144d32; 
            --brand-green-hover: #0e3b25;
            --bg-green: #114227; 
        }

        body {
            background-color: var(--bg-green);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        /* Bank Icon Circle */
        .icon-circle {
            width: 60px;
            height: 60px;
            background-color: var(--brand-green);
            color: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        /* Text Styling */
        .text-brand {
            color: var(--brand-green) !important;
        }

        /* Custom Input Group Styling to match the picture */
        .custom-input-group {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            background: #fff;
            overflow: hidden;
        }
        .custom-input-group:focus-within {
            border-color: var(--brand-green);
            box-shadow: 0 0 0 0.25rem rgba(20, 77, 50, 0.25);
        }
        .custom-input-group .input-icon {
            padding: 0.75rem 1rem;
            color: #6c757d;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        .custom-input-group input {
            border: none;
            box-shadow: none;
            flex: 1;
            padding: 0.75rem;
        }
        .custom-input-group input:focus {
            outline: none;
        }
        .custom-input-group .eye-icon {
            padding: 0.75rem 1rem;
            color: #6c757d;
            cursor: pointer;
        }

        /* Button Styling */
        .btn-brand {
            background-color: var(--brand-green);
            color: white;
            border: none;
            font-weight: 500;
        }
        .btn-brand:hover {
            background-color: var(--brand-green-hover);
            color: white;
        }

        /* Demo Credentials Area */
        .demo-area {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
        }
        .demo-badge {
            cursor: pointer;
            transition: transform 0.1s;
        }
        .demo-badge:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="d-flex flex-column align-items-center justify-content-center vh-100">

    <div class="card login-card border-0 bg-white p-4 p-md-5">
        
        <!-- Header & Icon -->
        <div class="text-center">
            <div class="icon-circle">
                <i class="fas fa-university"></i>
            </div>
            <h4 class="fw-bold text-brand mb-1">Finance Management System</h4>
            <p class="text-muted small mb-4">Sign in to your account</p>
        </div>

        <!-- Hidden Alert Box for Errors -->
        <div id="alert-box" class="alert d-none small py-2" role="alert"></div>

        <!-- Form -->
        <form id="loginForm">
            
            <!-- Username Input -->
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-dark">Username</label>
                <div class="custom-input-group">
                    <div class="input-icon"><i class="fas fa-user"></i></div>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                </div>
            </div>

            <!-- Password Input -->
            <div class="mb-4">
                <label for="password" class="form-label small fw-semibold text-dark">Password</label>
                <div class="custom-input-group">
                    <div class="input-icon"><i class="fas fa-lock"></i></div>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <div class="eye-icon" id="togglePassword"><i class="fas fa-eye-slash"></i></div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-brand w-100 py-2 mb-4" id="submitBtn">
                <span id="btnText"><i class="fas fa-sign-in-alt me-2"></i> Sign In</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>

      

    </div>

    <!-- Footer -->
    <div class="text-center text-white mt-4 small opacity-75">
        <i class="fas fa-shield-alt me-1"></i> © 2026 Finance Management System
    </div>

    <!-- Scripts -->
    <script>
        // 1. Password Visibility Toggle
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });

        // 2. Demo Credentials Auto-Fill
        document.querySelectorAll('.demo-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                const username = this.textContent.trim();
                document.getElementById('username').value = username;
                // Based on your database hash, the default password is 'password'
                document.getElementById('password').value = 'password'; 
            });
        });

        // 3. AJAX Login Logic (Connected to your existing API)
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); 

            const usernameInput = document.getElementById('username').value;
            const passwordInput = document.getElementById('password').value;
            const alertBox = document.getElementById('alert-box');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            // Reset UI
            alertBox.classList.add('d-none');
            alertBox.classList.remove('alert-success', 'alert-danger');
            submitBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');

            // Note: points to 'api/login_api.php' based on your folder structure
            fetch('api/login_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ username: usernameInput, password: passwordInput })
            })
            .then(response => response.json())
            .then(data => {
                alertBox.classList.remove('d-none');
                
                if (data.status === 'success') {
                    alertBox.classList.add('alert-success');
                    alertBox.textContent = 'Success! Redirecting...';
                    setTimeout(() => { window.location.href = data.redirect; }, 1000);
                } else {
                    alertBox.classList.add('alert-danger');
                    alertBox.textContent = data.message;
                    submitBtn.disabled = false;
                    btnText.classList.remove('d-none');
                    btnSpinner.classList.add('d-none');
                }
            })
            .catch(error => {
                alertBox.classList.remove('d-none');
                alertBox.classList.add('alert-danger');
                alertBox.textContent = 'Network error. Please try again.';
                submitBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
            });
        });
    </script>
</body>
</html>
