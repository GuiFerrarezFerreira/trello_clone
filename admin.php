<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração de Usuários - RC ADM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f4f5f7;
            min-height: 100vh;
            color: #172b4d;
        }

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0079bf 0%, #026aa7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            transition: opacity 0.3s ease-out;
        }

        .loading-screen.hide {
            opacity: 0;
            pointer-events: none;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Navbar */
        .navbar {
            background: #026aa7;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 14px;
        }

        .navbar h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            width: 30px;
            height: 30px;
            background: white;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #0079bf;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 3px;
            transition: background 0.2s;
            font-size: 14px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .current-user {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 3px;
        }

        .user-avatar-small {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: white;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Container */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .admin-header {
            margin-bottom: 30px;
        }

        .admin-title {
            font-size: 28px;
            font-weight: 700;
            color: #172b4d;
            margin-bottom: 8px;
        }

        .admin-subtitle {
            color: #5e6c84;
            font-size: 16px;
        }

        /* Controls */
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0079bf;
            box-shadow: 0 0 0 1px #0079bf;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b778c;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: #0079bf;
            color: white;
        }

        .btn-primary:hover {
            background: #026aa7;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -2px rgba(9, 30, 66, 0.15);
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #172b4d;
            margin-bottom: 4px;
        }

        .stat-label {
            color: #5e6c84;
            font-size: 14px;
        }

        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e4e6ea;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            text-align: left;
            padding: 12px 20px;
            font-size: 12px;
            font-weight: 600;
            color: #5e6c84;
            text-transform: uppercase;
            background: #f4f5f7;
            border-bottom: 1px solid #e4e6ea;
        }

        .users-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #e4e6ea;
            font-size: 14px;
        }

        .users-table tr:last-child td {
            border-bottom: none;
        }

        .users-table tr:hover {
            background: #f4f5f7;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            flex-shrink: 0;
        }

        .user-details {
            min-width: 0;
        }

        .user-name {
            font-weight: 500;
            color: #172b4d;
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 12px;
            color: #6b778c;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-admin {
            background: #ff991a;
            color: white;
        }

        .badge-no-create {
            background: #6b778c;
            color: white;
        }

        .boards-preview {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .board-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .board-badge:hover {
            transform: scale(1.05);
        }

        .board-badge-more {
            background: #6b778c;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: #6b778c;
        }

        .action-btn:hover {
            background: #e4e6ea;
            color: #172b4d;
        }

        .action-btn.delete {
            color: #eb5a46;
        }

        .action-btn.delete:hover {
            background: #ffeae8;
            color: #c9372c;
        }

        .action-btn.toggle-permission {
            color: #0079bf;
        }

        .action-btn.toggle-permission:hover {
            background: #e4f0f6;
            color: #026aa7;
        }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            border-top: 1px solid #e4e6ea;
        }

        .page-btn {
            padding: 6px 12px;
            border: 1px solid #dfe1e6;
            background: white;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            color: #5e6c84;
        }

        .page-btn:hover:not(:disabled) {
            border-color: #0079bf;
            color: #0079bf;
        }

        .page-btn.active {
            background: #0079bf;
            color: white;
            border-color: #0079bf;
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-info {
            color: #5e6c84;
            font-size: 14px;
            margin: 0 8px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e4e6ea;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #172b4d;
        }

        .modal-close {
            font-size: 24px;
            cursor: pointer;
            color: #6b778c;
            padding: 4px 8px;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: rgba(0, 0, 0, 0.08);
            color: #172b4d;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 16px;
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
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #0079bf;
            box-shadow: 0 0 0 1px #0079bf;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e4e6ea;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-cancel {
            background: transparent;
            color: #6b778c;
            border: 1px solid #dfe1e6;
        }

        .btn-cancel:hover {
            background: #f4f5f7;
            border-color: #c1c7d0;
        }

        /* User Boards Modal */
        .boards-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .board-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 8px;
            transition: background 0.2s;
        }

        .board-item:hover {
            background: #f4f5f7;
        }

        .board-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .board-color {
            width: 40px;
            height: 32px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .board-details {
            min-width: 0;
        }

        .board-title {
            font-weight: 500;
            color: #172b4d;
            margin-bottom: 2px;
        }

        .board-role {
            font-size: 12px;
            color: #6b778c;
        }

        .board-actions {
            display: flex;
            gap: 8px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #5e6c84;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #172b4d;
            margin-bottom: 8px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                padding: 20px 16px;
            }

            .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .users-table-container {
                overflow-x: auto;
            }

            .users-table {
                min-width: 700px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Loading */
        .table-loading {
            text-align: center;
            padding: 40px;
            color: #6b778c;
        }

        .error-message {
            background: #ffeae8;
            color: #c9372c;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-message {
            background: #e3fcef;
            color: #216e4e;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <h2>Carregando...</h2>
            <div class="loading-spinner"></div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <h1>
                <div class="logo">RC</div>
                Administração
            </h1>
            <a href="index.php" class="nav-link">← Voltar aos Quadros</a>
        </div>
        <div class="navbar-right">
            <div class="current-user">
                <div class="user-avatar-small" id="userAvatar"></div>
                <span id="userName"></span>
            </div>
            <button class="logout-btn" onclick="logout()">Sair</button>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">Gerenciamento de Usuários</h1>
            <p class="admin-subtitle">Gerencie usuários, permissões e acesso aos quadros</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-value" id="totalUsers">0</div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalBoards">0</div>
                <div class="stat-label">Total de Quadros</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="activeUsers">0</div>
                <div class="stat-label">Usuários Ativos (30 dias)</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-input" id="searchInput" 
                       placeholder="Buscar por nome ou email..." 
                       onkeyup="handleSearch()">
            </div>
            <button class="btn btn-primary" onclick="openCreateUserModal()">
                + Novo Usuário
            </button>
        </div>

        <!-- Messages -->
        <div id="messageContainer"></div>

        <!-- Users Table -->
        <div class="users-table-container">
            <div class="table-header">
                <span>Usuários</span>
                <span id="resultsInfo"></span>
            </div>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Quadros</th>
                        <th>Membro desde</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="4" class="table-loading">Carregando usuários...</td>
                    </tr>
                </tbody>
            </table>
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Editar Usuário</h2>
                <span class="modal-close" onclick="closeEditModal()">×</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-input" id="editFirstName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sobrenome</label>
                            <input type="text" class="form-input" id="editLastName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-input" id="editEmail" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nova Senha (deixe em branco para não alterar)</label>
                        <input type="password" class="form-input" id="editPassword" 
                               placeholder="Digite apenas se quiser alterar a senha">
                    </div>
                    <div class="form-group" id="adminCheckboxGroup" style="display: none;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="editIsAdmin" style="width: 16px; height: 16px;">
                            <span>Tornar este usuário um administrador do sistema</span>
                        </label>
                        <p style="font-size: 12px; color: #6b778c; margin-top: 4px;">
                            Administradores têm acesso total ao sistema, incluindo gerenciamento de usuários.
                        </p>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="editCanCreateBoards" style="width: 16px; height: 16px;">
                            <span>Permitir que este usuário crie novos quadros</span>
                        </label>
                        <p style="font-size: 12px; color: #6b778c; margin-top: 4px;">
                            Se desmarcado, o usuário poderá apenas participar de quadros existentes.
                        </p>
                    </div>                    
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeEditModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="saveUserChanges()">Salvar Alterações</button>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal" id="createUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Criar Novo Usuário</h2>
                <span class="modal-close" onclick="closeCreateModal()">×</span>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-input" id="createFirstName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sobrenome</label>
                            <input type="text" class="form-input" id="createLastName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-input" id="createEmail" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Senha</label>
                        <input type="password" class="form-input" id="createPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeCreateModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="createUser()">Criar Usuário</button>
            </div>
        </div>
    </div>

    <!-- User Boards Modal -->
    <div class="modal" id="userBoardsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="userBoardsTitle">Quadros do Usuário</h2>
                <span class="modal-close" onclick="closeUserBoardsModal()">×</span>
            </div>
            <div class="modal-body">
                <div class="boards-list" id="userBoardsList"></div>
            </div>
        </div>
    </div>

    <script src="js/services/api.service.js"></script>
    <script src="js/utils/notifications.js"></script>
    <script>
        // State
        let currentPage = 1;
        let searchQuery = '';
        let searchTimeout;
        let usersData = [];
        let currentUser = null;

        // Initialize
        async function init() {
            try {
                // Check authentication
                const token = localStorage.getItem('authToken');
                if (!token) {
                    window.location.href = 'login.php';
                    return;
                }

                // Validate token and get user info
                const user = await apiService.validateToken();
                if (!user) {
                    apiService.logout();
                    return;
                }

                currentUser = user;
                updateUserUI(user);

                // Load stats and users in parallel
                await Promise.all([
                    loadSystemStats(),
                    loadUsers()
                ]);
                
                // Hide loading screen
                hideLoadingScreen();
            } catch (error) {
                console.error('Initialization error:', error);
                showMessage('Erro ao inicializar. Você não tem permissão para acessar esta área.', 'error');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            }
        }

        // Update user UI
        function updateUserUI(user) {
            const userAvatar = document.getElementById('userAvatar');
            const userName = document.getElementById('userName');
            
            if (userAvatar && userName) {
                userAvatar.textContent = user.initials;
                userAvatar.style.backgroundColor = user.color;
                userName.textContent = `${user.firstName} ${user.lastName}`;
            }
        }

        // Load users
        async function loadUsers() {
            try {
                const response = await fetch(`${apiService.baseURL}/user-management.php?page=${currentPage}&search=${searchQuery}`, {
                    headers: apiService.setAuthHeader()
                });

                const data = await apiService.handleResponse(response);
                
                if (data.success) {
                    usersData = data.users;
                    renderUsers(data.users);
                    renderPagination(data.pagination);
                    
                    // Update results info
                    document.getElementById('resultsInfo').textContent = 
                        `${data.pagination.total} usuário${data.pagination.total !== 1 ? 's' : ''}`;
                }
            } catch (error) {
                console.error('Error loading users:', error);
                showMessage('Erro ao carregar usuários. ' + error.message, 'error');
                renderEmptyState();
            }
        }

        // Render users table
        function renderUsers(users) {
            const tbody = document.getElementById('usersTableBody');
            
            if (users.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">
                            <h3>Nenhum usuário encontrado</h3>
                            <p>Tente ajustar sua busca ou crie um novo usuário</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = users.map(user => {
                const createdDate = new Date(user.createdAt);
                const formattedDate = createdDate.toLocaleDateString('pt-BR');
                
                // Render boards preview (max 3)
                const boardsPreview = user.boards.slice(0, 3).map(board => `
                    <span class="board-badge" style="background: ${board.color}" 
                          title="${board.title} (${board.role})">
                        ${board.title.substring(0, 10)}${board.title.length > 10 ? '...' : ''}
                    </span>
                `).join('');
                
                const moreBoards = user.boardsCount > 3 ? 
                    `<span class="board-badge-more">+${user.boardsCount - 3}</span>` : '';

                return `
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar" style="background-color: ${user.color}">
                                    ${user.initials}
                                </div>
                                <div class="user-details">
                                    <div class="user-name">
                                        ${user.firstName} ${user.lastName}
                                        ${user.isSystemAdmin ? '<span class="badge badge-admin">Admin</span>' : ''}
                                        ${!user.canCreateBoards ? '<span class="badge badge-no-create">Sem permissão para criar quadros</span>' : ''}
                                    </div>
                                    <div class="user-email">${user.email}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="boards-preview" onclick="showUserBoards('${user.id}', '${user.firstName} ${user.lastName}')">
                                ${boardsPreview}
                                ${moreBoards}
                            </div>
                            <div style="font-size: 12px; color: #6b778c; margin-top: 4px;">
                                ${user.boardsCount} quadro${user.boardsCount !== 1 ? 's' : ''} · 
                                ${user.ownedBoardsCount} próprio${user.ownedBoardsCount !== 1 ? 's' : ''}
                            </div>
                        </td>
                        <td>${formattedDate}</td>
                        <td>
                            <div class="actions">
                                <button class="action-btn toggle-permission" onclick="toggleBoardPermission('${user.id}', ${!user.canCreateBoards})" 
                                        title="${user.canCreateBoards ? 'Remover permissão de criar quadros' : 'Conceder permissão de criar quadros'}">
                                    ${user.canCreateBoards ? '🚫' : '✅'} Quadros
                                </button>                    
                                <button class="action-btn" onclick="openEditModal('${user.id}')">
                                    ✏️ Editar
                                </button>
                                ${!user.isSystemAdmin && user.id !== currentUser.id ? `
                                    <button class="action-btn delete" onclick="confirmDeleteUser('${user.id}', '${user.firstName} ${user.lastName}')">
                                        🗑️ Excluir
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Render pagination
        function renderPagination(pagination) {
            const paginationDiv = document.getElementById('pagination');
            
            if (pagination.totalPages <= 1) {
                paginationDiv.innerHTML = '';
                return;
            }

            let html = '';
            
            // Previous button
            html += `
                <button class="page-btn" onclick="changePage(${pagination.page - 1})" 
                        ${pagination.page === 1 ? 'disabled' : ''}>
                    ← Anterior
                </button>
            `;
            
            // Page numbers
            const startPage = Math.max(1, pagination.page - 2);
            const endPage = Math.min(pagination.totalPages, pagination.page + 2);
            
            if (startPage > 1) {
                html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
                if (startPage > 2) html += `<span class="page-info">...</span>`;
            }
            
            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <button class="page-btn ${i === pagination.page ? 'active' : ''}" 
                            onclick="changePage(${i})">
                        ${i}
                    </button>
                `;
            }
            
            if (endPage < pagination.totalPages) {
                if (endPage < pagination.totalPages - 1) html += `<span class="page-info">...</span>`;
                html += `<button class="page-btn" onclick="changePage(${pagination.totalPages})">${pagination.totalPages}</button>`;
            }
            
            // Next button
            html += `
                <button class="page-btn" onclick="changePage(${pagination.page + 1})" 
                        ${pagination.page === pagination.totalPages ? 'disabled' : ''}>
                    Próximo →
                </button>
            `;
            
            paginationDiv.innerHTML = html;
        }

        // Load system stats
        async function loadSystemStats() {
            try {
                const response = await fetch(`${apiService.baseURL}/system-stats.php`, {
                    headers: apiService.setAuthHeader()
                });

                const data = await apiService.handleResponse(response);
                
                if (data.success) {
                    const stats = data.stats;
                    document.getElementById('totalUsers').textContent = stats.totalUsers;
                    document.getElementById('totalBoards').textContent = stats.totalBoards;
                    document.getElementById('activeUsers').textContent = stats.activeUsers30Days;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                // Don't show error for stats, just use defaults
            }
        }

        // Change page
        function changePage(page) {
            currentPage = page;
            loadUsers();
        }

        // Handle search
        function handleSearch() {
            clearTimeout(searchTimeout);
            const query = document.getElementById('searchInput').value.trim();
            
            searchTimeout = setTimeout(() => {
                searchQuery = query;
                currentPage = 1;
                loadUsers();
            }, 300);
        }

        // Show user boards
        async function showUserBoards(userId, userName) {
            const user = usersData.find(u => u.id === userId);
            if (!user) return;
            
            document.getElementById('userBoardsTitle').textContent = `Quadros de ${userName}`;
            
            const boardsList = document.getElementById('userBoardsList');
            
            if (user.boardsCount === 0) {
                boardsList.innerHTML = `
                    <div class="empty-state">
                        <p>Este usuário não participa de nenhum quadro</p>
                    </div>
                `;
            } else {
                // In production, load full boards list from server
                boardsList.innerHTML = user.boards.map(board => `
                    <div class="board-item">
                        <div class="board-info">
                            <div class="board-color" style="background: ${board.color}"></div>
                            <div class="board-details">
                                <div class="board-title">${board.title}</div>
                                <div class="board-role">Função: ${board.role}</div>
                            </div>
                        </div>
                        <div class="board-actions">
                            <a href="index.php?board=${board.id}" class="action-btn" target="_blank">
                                Abrir →
                            </a>
                        </div>
                    </div>
                `).join('');
                
                if (user.boardsCount > user.boards.length) {
                    boardsList.innerHTML += `
                        <div style="text-align: center; padding: 16px; color: #6b778c;">
                            Mostrando ${user.boards.length} de ${user.boardsCount} quadros
                        </div>
                    `;
                }
            }
            
            document.getElementById('userBoardsModal').classList.add('active');
        }

        function closeUserBoardsModal() {
            document.getElementById('userBoardsModal').classList.remove('active');
        }

        // Edit user
        function openEditModal(userId) {
            const user = usersData.find(u => u.id === userId);
            if (!user) return;
            
            document.getElementById('editUserId').value = userId;
            document.getElementById('editFirstName').value = user.firstName;
            document.getElementById('editLastName').value = user.lastName;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editPassword').value = '';
            document.getElementById('editCanCreateBoards').checked = user.canCreateBoards;            
            
            // Show admin checkbox only for current admin editing other users
            const adminCheckboxGroup = document.getElementById('adminCheckboxGroup');
            if (currentUser.isAdmin && user.id !== currentUser.id) {
                adminCheckboxGroup.style.display = 'block';
                document.getElementById('editIsAdmin').checked = user.isSystemAdmin;
            } else {
                adminCheckboxGroup.style.display = 'none';
            }
            
            document.getElementById('editUserModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.remove('active');
            document.getElementById('editUserForm').reset();
        }

        async function saveUserChanges() {
            const userId = document.getElementById('editUserId').value;
            const updates = {
                firstName: document.getElementById('editFirstName').value.trim(),
                lastName: document.getElementById('editLastName').value.trim(),
                email: document.getElementById('editEmail').value.trim(),
                canCreateBoards: document.getElementById('editCanCreateBoards').checked                
            };
            
            const password = document.getElementById('editPassword').value;
            if (password) {
                updates.password = password;
            }
            
            // Add admin status if checkbox is visible
            const adminCheckboxGroup = document.getElementById('adminCheckboxGroup');
            if (adminCheckboxGroup.style.display !== 'none') {
                updates.isAdmin = document.getElementById('editIsAdmin').checked;
            }
            
            if (!updates.firstName || !updates.lastName || !updates.email) {
                showMessage('Por favor, preencha todos os campos obrigatórios', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${apiService.baseURL}/user-management.php?id=${userId}`, {
                    method: 'PUT',
                    headers: apiService.setAuthHeader(),
                    body: JSON.stringify(updates)
                });
                
                const data = await apiService.handleResponse(response);
                
                if (data.success) {
                    showMessage('Usuário atualizado com sucesso!', 'success');
                    closeEditModal();
                    loadUsers();
                }
            } catch (error) {
                console.error('Error updating user:', error);
                showMessage('Erro ao atualizar usuário: ' + error.message, 'error');
            }
        }

        // Create user
        function openCreateUserModal() {
            document.getElementById('createUserModal').classList.add('active');
        }

        function closeCreateModal() {
            document.getElementById('createUserModal').classList.remove('active');
            document.getElementById('createUserForm').reset();
        }

        async function createUser() {
            const userData = {
                firstName: document.getElementById('createFirstName').value.trim(),
                lastName: document.getElementById('createLastName').value.trim(),
                email: document.getElementById('createEmail').value.trim(),
                password: document.getElementById('createPassword').value
            };
            
            if (!userData.firstName || !userData.lastName || !userData.email || !userData.password) {
                showMessage('Por favor, preencha todos os campos', 'error');
                return;
            }
            
            try {
                const response = await apiService.register(
                    userData.firstName,
                    userData.lastName,
                    userData.email,
                    userData.password
                );
                
                if (response.success) {
                    showMessage('Usuário criado com sucesso!', 'success');
                    closeCreateModal();
                    loadUsers();
                }
            } catch (error) {
                console.error('Error creating user:', error);
                showMessage('Erro ao criar usuário: ' + error.message, 'error');
            }
        }

        // Toggle board creation permission
        async function toggleBoardPermission(userId, canCreate) {
            try {
                const response = await fetch(`${apiService.baseURL}/toggle-board-permission.php`, {
                    method: 'POST',
                    headers: apiService.setAuthHeader(),
                    body: JSON.stringify({
                        userId: userId,
                        canCreateBoards: canCreate
                    })
                });
                
                const data = await apiService.handleResponse(response);
                
                if (data.success) {
                    showMessage(data.message, 'success');
                    loadUsers();
                }
            } catch (error) {
                console.error('Error toggling board permission:', error);
                showMessage('Erro ao atualizar permissão: ' + error.message, 'error');
            }
        }

        // Delete user
        async function confirmDeleteUser(userId, userName) {
            if (!confirm(`Tem certeza que deseja excluir o usuário "${userName}"? Esta ação não pode ser desfeita.`)) {
                return;
            }
            
            try {
                const response = await fetch(`${apiService.baseURL}/user-management.php?id=${userId}`, {
                    method: 'DELETE',
                    headers: apiService.setAuthHeader()
                });
                
                const data = await apiService.handleResponse(response);
                
                if (data.success) {
                    showMessage('Usuário excluído com sucesso!', 'success');
                    loadUsers();
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                showMessage('Erro ao excluir usuário: ' + error.message, 'error');
            }
        }

        // Utilities
        function showMessage(message, type = 'info') {
            const container = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `${type}-message`;
            messageDiv.textContent = message;
            
            container.innerHTML = '';
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        function renderEmptyState() {
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="empty-state">
                        <h3>Erro ao carregar usuários</h3>
                        <p>Verifique se você tem permissão para acessar esta área</p>
                    </td>
                </tr>
            `;
        }

        function showLoadingScreen() {
            document.getElementById('loadingScreen').classList.remove('hide');
        }

        function hideLoadingScreen() {
            document.getElementById('loadingScreen').classList.add('hide');
        }

        function logout() {
            apiService.logout();
        }

        // Click outside modals to close
        document.getElementById('editUserModal').addEventListener('click', (e) => {
            if (e.target.id === 'editUserModal') {
                closeEditModal();
            }
        });

        document.getElementById('createUserModal').addEventListener('click', (e) => {
            if (e.target.id === 'createUserModal') {
                closeCreateModal();
            }
        });

        document.getElementById('userBoardsModal').addEventListener('click', (e) => {
            if (e.target.id === 'userBoardsModal') {
                closeUserBoardsModal();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeEditModal();
                closeCreateModal();
                closeUserBoardsModal();
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>