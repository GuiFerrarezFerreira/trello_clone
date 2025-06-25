<?php
// ===================================
// register.php - Página de Cadastro
// ===================================
session_start();

// Se já estiver logado, redireciona para index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Processar registro se o formulário foi enviado
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $acceptTerms = isset($_POST['acceptTerms']);
    
    // Validações
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, insira um e-mail válido.';
    } elseif (strlen($password) < 8) {
        $error = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($password !== $confirmPassword) {
        $error = 'As senhas não coincidem.';
    } elseif (!$acceptTerms) {
        $error = 'Você precisa aceitar os termos para continuar.';
    } else {
        // Fazer requisição para a API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/trello_clone/api/register.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'password' => $password
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            
            if ($data['success'] ?? false) {
                $success = 'Conta criada com sucesso! Você será redirecionado para o login...';
                // Redirecionar após 2 segundos
                header("Refresh: 2; url=login.php");
            } else {
                $error = $data['message'] ?? 'Erro ao criar conta. Tente novamente.';
            }
        } else {
            $error = 'Erro ao conectar com o servidor. Tente novamente.';
        }
    }
}

// Função para calcular força da senha
function getPasswordStrength($password) {
    $strength = 0;
    
    if (strlen($password) >= 8) $strength++;
    if (preg_match('/[a-z]/', $password)) $strength++;
    if (preg_match('/[A-Z]/', $password)) $strength++;
    if (preg_match('/[0-9]/', $password)) $strength++;
    if (preg_match('/[$@#&!]/', $password)) $strength++;
    
    return $strength;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Trello Clone</title>
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

        .register-container {
            width: 100%;
            max-width: 480px;
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

        .register-header {
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

        .register-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 16px;
        }

        .register-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .register-title {
            font-size: 18px;
            font-weight: 600;
            color: #172b4d;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row .form-group {
            margin-bottom: 0;
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

        .form-input.success {
            border-color: #61bd4f;
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

        .success-message {
            color: #61bd4f;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .success-message.show {
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

        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #dfe1e6;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #eb5a46;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #f2d600;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #61bd4f;
        }

        .password-strength-text {
            font-size: 12px;
            margin-top: 4px;
            color: #5e6c84;
        }

        .terms-container {
            margin-bottom: 24px;
        }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 14px;
            color: #5e6c84;
            line-height: 1.5;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 2px;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .terms-checkbox a {
            color: #0079bf;
            text-decoration: none;
        }

        .terms-checkbox a:hover {
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

        .login-link {
            text-align: center;
            font-size: 14px;
            color: #5e6c84;
        }

        .login-link a {
            color: #0079bf;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .login-link a:hover {
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

        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 30px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-weight: 500;
        }

        .step.active {
            color: white;
        }

        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.3s;
        }

        .step.active .step-number {
            background: white;
            color: #0079bf;
            border-color: white;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 24px;
            }
            
            .app-title {
                font-size: 28px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .social-login {
                flex-direction: column;
            }

            .progress-steps {
                gap: 15px;
            }

            .step span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo-container">
                <div class="logo">T</div>
                <h1 class="app-title">Trello Clone</h1>
            </div>
            <p class="register-subtitle">Crie sua conta gratuita</p>
            
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span>Dados pessoais</span>
                </div>
                <div class="step <?php echo $success ? 'active' : ''; ?>">
                    <div class="step-number">2</div>
                    <span>Confirmação</span>
                </div>
            </div>
        </div>

        <div class="register-card">
            <h2 class="register-title">Cadastre-se para começar</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm" novalidate>
                <input type="hidden" name="register" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="firstName">Nome</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="firstName" 
                            name="firstName"
                            placeholder="Seu nome"
                            value="<?php echo htmlspecialchars($_POST['firstName'] ?? ''); ?>"
                            required
                            autocomplete="given-name"
                        >
                        <span class="error-message" id="firstNameError">O nome é obrigatório</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lastName">Sobrenome</label>
                        <input 
                            type="text" 
                            class="form-input" 
                            id="lastName" 
                            name="lastName"
                            placeholder="Seu sobrenome"
                            value="<?php echo htmlspecialchars($_POST['lastName'] ?? ''); ?>"
                            required
                            autocomplete="family-name"
                        >
                        <span class="error-message" id="lastNameError">O sobrenome é obrigatório</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">E-mail</label>
                    <input 
                        type="email" 
                        class="form-input" 
                        id="email" 
                        name="email"
                        placeholder="seu@email.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                        autocomplete="email"
                    >
                    <span class="error-message" id="emailError">Por favor, insira um e-mail válido</span>
                    <span class="success-message" id="emailSuccess">E-mail disponível</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Senha</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            class="form-input" 
                            id="password" 
                            name="password"
                            placeholder="Crie uma senha forte"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            👁️
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <span class="password-strength-text" id="passwordStrengthText">Mínimo de 8 caracteres</span>
                    <span class="error-message" id="passwordError">A senha deve ter pelo menos 8 caracteres</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirmar Senha</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            class="form-input" 
                            id="confirmPassword" 
                            name="confirmPassword"
                            placeholder="Digite a senha novamente"
                            required
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">
                            👁️
                        </button>
                    </div>
                    <span class="error-message" id="confirmPasswordError">As senhas não coincidem</span>
                </div>

                <div class="terms-container">
                    <label class="terms-checkbox">
                        <input type="checkbox" id="acceptTerms" name="acceptTerms" <?php echo isset($_POST['acceptTerms']) ? 'checked' : ''; ?> required>
                        <span>Eu concordo com os <a href="#" onclick="alert('Termos de Serviço em desenvolvimento'); return false;">Termos de Serviço</a> e a <a href="#" onclick="alert('Política de Privacidade em desenvolvimento'); return false;">Política de Privacidade</a> do Trello Clone</span>
                    </label>
                    <span class="error-message" id="termsError">Você precisa aceitar os termos para continuar</span>
                </div>

                <button type="submit" class="btn btn-primary" id="registerButton" <?php echo $success ? 'disabled' : ''; ?>>
                    <span id="buttonText"><?php echo $success ? 'Redirecionando...' : 'Criar conta'; ?></span>
                </button>
            </form>

            <div class="divider">
                <span>ou cadastre-se com</span>
            </div>

            <div class="social-login">
                <a href="#" class="btn-social" onclick="alert('Cadastro com Google em desenvolvimento'); return false;">
                    <span>🔷</span>
                    <span>Google</span>
                </a>
                <a href="#" class="btn-social" onclick="alert('Cadastro com Microsoft em desenvolvimento'); return false;">
                    <span>🔵</span>
                    <span>Microsoft</span>
                </a>
            </div>

            <div class="login-link">
                Já tem uma conta? <a href="login.php">Faça login</a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
            });
        }

        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirmPassword');

        // Form elements
        const registerForm = document.getElementById('registerForm');
        const firstNameInput = document.getElementById('firstName');
        const lastNameInput = document.getElementById('lastName');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const acceptTermsInput = document.getElementById('acceptTerms');
        
        const registerButton = document.getElementById('registerButton');
        const buttonText = document.getElementById('buttonText');

        // Validation functions
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            
            return strength;
        }

        function updatePasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');
            const strength = checkPasswordStrength(password);
            
            strengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                strengthText.textContent = 'Mínimo de 8 caracteres';
                strengthText.style.color = '#5e6c84';
            } else if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Senha fraca';
                strengthText.style.color = '#eb5a46';
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Senha média';
                strengthText.style.color = '#f2d600';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Senha forte';
                strengthText.style.color = '#61bd4f';
            }
        }

        // Show/hide error messages
        function showError(input, errorElement, message) {
            input.classList.add('error');
            input.classList.remove('success');
            errorElement.textContent = message;
            errorElement.classList.add('show');
            
            // Hide success message if exists
            const successId = errorElement.id.replace('Error', 'Success');
            const successElement = document.getElementById(successId);
            if (successElement) {
                successElement.classList.remove('show');
            }
        }

        function hideError(input, errorElement) {
            input.classList.remove('error');
            errorElement.classList.remove('show');
        }

        function showSuccess(input, successElement) {
            input.classList.add('success');
            input.classList.remove('error');
            successElement.classList.add('show');
            
            // Hide error message
            const errorId = successElement.id.replace('Success', 'Error');
            const errorElement = document.getElementById(errorId);
            errorElement.classList.remove('show');
        }

        // Real-time validation
        firstNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideError(this, document.getElementById('firstNameError'));
            }
        });

        lastNameInput.addEventListener('input', function() {
            if (this.value.trim()) {
                hideError(this, document.getElementById('lastNameError'));
            }
        });

        emailInput.addEventListener('input', function() {
            if (this.value && !validateEmail(this.value)) {
                showError(this, document.getElementById('emailError'), 'Por favor, insira um e-mail válido');
            } else if (this.value) {
                hideError(this, document.getElementById('emailError'));
                // Here you would normally check if email is already registered
                // For now, we'll simulate it's available
                setTimeout(() => {
                    if (validateEmail(this.value)) {
                        showSuccess(this, document.getElementById('emailSuccess'));
                    }
                }, 500);
            } else {
                hideError(this, document.getElementById('emailError'));
            }
        });

        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value);
            
            if (this.value && this.value.length < 8) {
                showError(this, document.getElementById('passwordError'), 'A senha deve ter pelo menos 8 caracteres');
            } else if (this.value) {
                hideError(this, document.getElementById('passwordError'));
            }
            
            // Check confirm password match
            if (confirmPasswordInput.value) {
                if (this.value !== confirmPasswordInput.value) {
                    showError(confirmPasswordInput, document.getElementById('confirmPasswordError'), 'As senhas não coincidem');
                } else {
                    hideError(confirmPasswordInput, document.getElementById('confirmPasswordError'));
                }
            }
        });

        confirmPasswordInput.addEventListener('input', function() {
            if (this.value && this.value !== passwordInput.value) {
                showError(this, document.getElementById('confirmPasswordError'), 'As senhas não coincidem');
            } else if (this.value) {
                hideError(this, document.getElementById('confirmPasswordError'));
            }
        });

        acceptTermsInput.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('termsError').classList.remove('show');
            }
        });

        // Handle form submission (client-side validation)
        registerForm.addEventListener('submit', function(e) {
            // Reset all errors
            const errorElements = document.querySelectorAll('.error-message');
            errorElements.forEach(el => el.classList.remove('show'));
            document.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('error');
            });
            
            // Validate all fields
            let isValid = true;
            
            if (!firstNameInput.value.trim()) {
                showError(firstNameInput, document.getElementById('firstNameError'), 'O nome é obrigatório');
                isValid = false;
                e.preventDefault();
            }
            
            if (!lastNameInput.value.trim()) {
                showError(lastNameInput, document.getElementById('lastNameError'), 'O sobrenome é obrigatório');
                isValid = false;
                e.preventDefault();
            }
            
            if (!emailInput.value) {
                showError(emailInput, document.getElementById('emailError'), 'O e-mail é obrigatório');
                isValid = false;
                e.preventDefault();
            } else if (!validateEmail(emailInput.value)) {
                showError(emailInput, document.getElementById('emailError'), 'Por favor, insira um e-mail válido');
                isValid = false;
                e.preventDefault();
            }
            
            if (!passwordInput.value) {
                showError(passwordInput, document.getElementById('passwordError'), 'A senha é obrigatória');
                isValid = false;
                e.preventDefault();
            } else if (passwordInput.value.length < 8) {
                showError(passwordInput, document.getElementById('passwordError'), 'A senha deve ter pelo menos 8 caracteres');
                isValid = false;
                e.preventDefault();
            }
            
            if (!confirmPasswordInput.value) {
                showError(confirmPasswordInput, document.getElementById('confirmPasswordError'), 'Por favor, confirme sua senha');
                isValid = false;
                e.preventDefault();
            } else if (passwordInput.value !== confirmPasswordInput.value) {
                showError(confirmPasswordInput, document.getElementById('confirmPasswordError'), 'As senhas não coincidem');
                isValid = false;
                e.preventDefault();
            }
            
            if (!acceptTermsInput.checked) {
                document.getElementById('termsError').classList.add('show');
                isValid = false;
                e.preventDefault();
            }
            
            if (isValid) {
                // Show loading state
                registerButton.disabled = true;
                buttonText.innerHTML = 'Criando conta... <span class="loading-spinner"></span>';
            }
        });
    </script>
</body>
</html>