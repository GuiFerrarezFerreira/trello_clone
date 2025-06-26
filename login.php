<?php
// ===================================
// login.php - Página de Login
// ===================================
session_start();

// Se já estiver logado (via sessão PHP), limpa a sessão para usar apenas JS
if (isset($_SESSION['user_id'])) {
    session_destroy();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trello Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #0079bf 0%, #026aa7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
            color: #0079bf;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .app-title {
            color: white;
            font-size: 32px;
            font-weight: 700;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .login-title {
            font-size: 18px;
            font-weight: 600;
            color: #172b4d;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #5e6c84;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #dfe1e6;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #0079bf;
            box-shadow: 0 0 0 1px #0079bf;
        }

        .form-input.error {
            border-color: #eb5a46;
        }

        .error-message {
            color: #eb5a46;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b778c;
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #172b4d;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #5e6c84;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-password {
            font-size: 14px;
            color: #0079bf;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-password:hover {
            color: #026aa7;
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-primary {
            background: #0079bf;
            color: white;
            margin-bottom: 16px;
        }

        .btn-primary:hover:not(:disabled) {
            background: #026aa7;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-primary:disabled {
            background: #091e420a;
            color: #a5adba;
            cursor: not-allowed;
        }

        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #dfe1e6;
        }

        .divider span {
            background: white;
            padding: 0 16px;
            position: relative;
            color: #6b778c;
            font-size: 14px;
        }

        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn-social {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            border: 1px solid #dfe1e6;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            color: #172b4d;
            text-decoration: none;
        }

        .btn-social:hover {
            background: #f4f5f7;
            border-color: #c1c7d0;
        }

        .signup-link {
            text-align: center;
            font-size: 14px;
            color: #5e6c84;
        }

        .signup-link a {
            color: #0079bf;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .signup-link a:hover {
            color: #026aa7;
            text-decoration: underline;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #0079bf;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #ffeae8;
            color: #c9372c;
            border: 1px solid #f5c6c0;
        }

        .alert-success {
            background: #e3fcef;
            color: #216e4e;
            border: 1px solid #abe2d5;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 24px;
            }
            
            .app-title {
                font-size: 28px;
            }
            
            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <div class="logo">T</div>
                <h1 class="app-title">Trello Clone</h1>
            </div>
            <p class="login-subtitle">Organize seus projetos com facilidade</p>
        </div>

        <div class="login-card">
            <h2 class="login-title">Faça login em sua conta</h2>
            
            <div id="alertContainer"></div>

            <form id="loginForm" novalidate>
                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <input 
                        type="email" 
                        class="form-input" 
                        id="email" 
                        name="email"
                        placeholder="Digite seu e-mail"
                        required
                        autocomplete="email"
                    >
                    <span class="error-message" id="emailError">Por favor, insira um e-mail válido</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Senha</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            class="form-input" 
                            id="password" 
                            name="password"
                            placeholder="Digite sua senha"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            👁️
                        </button>
                    </div>
                    <span class="error-message" id="passwordError">A senha é obrigatória</span>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span>Lembrar de mim</span>
                    </label>
                    <a href="#" class="forgot-password">Esqueceu a senha?</a>
                </div>

                <button type="submit" class="btn btn-primary" id="loginButton">
                    <span id="buttonText">Entrar</span>
                </button>
            </form>

            <div class="divider">
                <span>ou continue com</span>
            </div>

            <div class="social-login">
                <a href="#" class="btn-social" onclick="alert('Login com Google em desenvolvimento'); return false;">
                    <span>🔷</span>
                    <span>Google</span>
                </a>
                <a href="#" class="btn-social" onclick="alert('Login com Microsoft em desenvolvimento'); return false;">
                    <span>🔵</span>
                    <span>Microsoft</span>
                </a>
            </div>

            <div class="signup-link">
                Não tem uma conta? <a href="register.php">Cadastre-se gratuitamente</a>
            </div>
        </div>
    </div>

    <script src="js/services/api.service.js"></script>
    <script>
        // Alert functions
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `;
        }

        function clearAlerts() {
            document.getElementById('alertContainer').innerHTML = '';
        }

        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        // Form validation
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const passwordError = document.getElementById('passwordError');
        const loginButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('buttonText');

        // Email validation
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Show error
        function showError(input, errorElement, message) {
            input.classList.add('error');
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }

        // Hide error
        function hideError(input, errorElement) {
            input.classList.remove('error');
            errorElement.classList.remove('show');
        }

        // Validate on input
        emailInput.addEventListener('input', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this, emailError, 'Por favor, insira um e-mail válido');
            } else {
                hideError(this, emailError);
            }
        });

        passwordInput.addEventListener('input', function() {
            if (this.value) {
                hideError(this, passwordError);
            }
        });

        // Handle form submission
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Clear alerts
            clearAlerts();
            
            // Reset errors
            hideError(emailInput, emailError);
            hideError(passwordInput, passwordError);
            
            // Validate
            let isValid = true;
            
            if (!emailInput.value) {
                showError(emailInput, emailError, 'O e-mail é obrigatório');
                isValid = false;
            } else if (!validateEmail(emailInput.value)) {
                showError(emailInput, emailError, 'Por favor, insira um e-mail válido');
                isValid = false;
            }
            
            if (!passwordInput.value) {
                showError(passwordInput, passwordError, 'A senha é obrigatória');
                isValid = false;
            }
            
            if (!isValid) return;
            
            // Show loading state
            loginButton.disabled = true;
            buttonText.innerHTML = 'Entrando... <span class="loading-spinner"></span>';
            
            try {
                const rememberMe = document.getElementById('rememberMe').checked;
                const response = await apiService.login(
                    emailInput.value,
                    passwordInput.value,
                    rememberMe
                );
                
                if (response.success) {
                    // Save token in localStorage (handled by apiService)
                    // Redirect to index
                    window.location.href = 'index.php';
                } else {
                    showAlert(response.message || 'E-mail ou senha incorretos');
                    loginButton.disabled = false;
                    buttonText.textContent = 'Entrar';
                }
            } catch (error) {
                console.error('Login error:', error);
                showAlert(error.message || 'Erro ao fazer login. Tente novamente.');
                loginButton.disabled = false;
                buttonText.textContent = 'Entrar';
            }
        });

        // Handle forgot password link
        document.querySelector('.forgot-password').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Funcionalidade de recuperação de senha em desenvolvimento');
        });

        // Check if already logged in
        window.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('authToken');
            if (token) {
                // Redirect to index if already has token
                window.location.href = 'index.php';
            }
        });
    </script>
</body>
</html>