<?php
// ===================================
// table.php - Visualização de Tabela
// ===================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabela - RC ADM</title>
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

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
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

        .view-toggle {
            display: flex;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            padding: 4px;
        }

        .view-btn {
            padding: 6px 12px;
            border: none;
            background: transparent;
            color: white;
            cursor: pointer;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .view-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .view-btn.active {
            background: white;
            color: #0079bf;
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

        /* Table Container */
        .table-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            flex-wrap: wrap;
            gap: 16px;
        }

        .table-title {
            font-size: 24px;
            font-weight: 700;
            color: #172b4d;
        }

        .table-controls {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 8px 12px 8px 36px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            width: 250px;
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

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
            background: white;
        }

        .export-btn {
            padding: 8px 16px;
            background: #0079bf;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .export-btn:hover {
            background: #026aa7;
        }

        /* Table */
        .data-table-wrapper {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            overflow: hidden;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: #f4f5f7;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #5e6c84;
            border-bottom: 2px solid #e4e6ea;
            cursor: pointer;
            user-select: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table th:hover {
            background: #ebecf0;
        }

        .sort-arrow {
            display: inline-block;
            margin-left: 4px;
            font-size: 10px;
            opacity: 0.5;
        }

        .sort-arrow.active {
            opacity: 1;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #e4e6ea;
            font-size: 14px;
        }

        .data-table tr:hover {
            background: #f4f5f7;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        /* Cell specific styles */
        .card-title-cell {
            font-weight: 500;
            color: #172b4d;
            cursor: pointer;
            transition: color 0.2s;
        }

        .card-title-cell:hover {
            color: #0079bf;
            text-decoration: underline;
        }

        .board-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
            font-weight: 500;
        }

        .list-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #e4e6ea;
            border-radius: 3px;
            font-size: 12px;
            color: #5e6c84;
        }

        .members-cell {
            display: flex;
            gap: 4px;
        }

        .member-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .member-avatar:hover {
            transform: scale(1.1);
        }

        .labels-cell {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .label {
            width: 40px;
            height: 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .label:hover {
            transform: scale(1.2);
        }

        .label-green { background: #61bd4f; }
        .label-yellow { background: #f2d600; }
        .label-red { background: #eb5a46; }
        .label-blue { background: #0079bf; }
        .label-purple { background: #c377e0; }

        .tags-cell {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .tag {
            background: #e4e6ea;
            color: #5e6c84;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
        }

        .due-date-cell {
            font-size: 13px;
        }

        .due-date-cell.overdue {
            color: #eb5a46;
            font-weight: 500;
        }

        .due-date-cell.due-soon {
            color: #f2d600;
            font-weight: 500;
        }

        .due-date-cell.due-today {
            color: #ff991a;
            font-weight: 500;
        }

        /* Empty state */
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

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
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
            max-height: 80vh;
            overflow-y: auto;
            padding: 24px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #172b4d;
            flex: 1;
            margin-right: 20px;
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
            background: #f4f5f7;
            color: #172b4d;
        }

        .card-info {
            margin-bottom: 16px;
        }

        .card-info-label {
            font-size: 12px;
            font-weight: 600;
            color: #5e6c84;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .card-description {
            background: #f4f5f7;
            padding: 12px;
            border-radius: 3px;
            font-size: 14px;
            line-height: 1.5;
            color: #172b4d;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .table-controls {
                flex-direction: column;
                width: 100%;
            }

            .search-input {
                width: 100%;
            }

            .data-table-wrapper {
                overflow-x: auto;
            }

            .data-table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <h2 style="color: white;">Carregando tabela...</h2>
            <div class="loading-spinner"></div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <h1>
                <div class="logo">RC</div>
                RC ADM
            </h1>
            <div class="view-toggle">
                <button class="view-btn" onclick="location.href='index.php'">📋 Quadros</button>
                <button class="view-btn" onclick="location.href='calendar.php'">📅 Calendário</button>
                <button class="view-btn active">📊 Tabela</button>
            </div>
        </div>
        <div class="navbar-right">
            <div class="current-user">
                <div class="user-avatar-small" id="userAvatar"></div>
                <span id="userName"></span>
            </div>
            <button class="logout-btn" onclick="logout()">Sair</button>
        </div>
    </nav>

    <!-- Table Container -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Todos os Cartões</h2>
            <div class="table-controls">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" id="searchInput" 
                           placeholder="Buscar cartões..." onkeyup="filterTable()">
                </div>
                <select class="filter-select" id="boardFilter" onchange="filterTable()">
                    <option value="all">Todos os quadros</option>
                </select>
                <select class="filter-select" id="memberFilter" onchange="filterTable()">
                    <option value="all">Todos os membros</option>
                </select>
                <select class="filter-select" id="statusFilter" onchange="filterTable()">
                    <option value="all">Todos os status</option>
                    <option value="overdue">Vencidos</option>
                    <option value="due-today">Vence hoje</option>
                    <option value="due-soon">Vence em breve</option>
                    <option value="no-date">Sem data</option>
                </select>
                <button class="export-btn" onclick="exportToCSV()">
                    📥 Exportar CSV
                </button>
            </div>
        </div>

        <div class="data-table-wrapper">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('title')">
                            Título <span class="sort-arrow" data-column="title">↕</span>
                        </th>
                        <th onclick="sortTable('board')">
                            Quadro <span class="sort-arrow" data-column="board">↕</span>
                        </th>
                        <th onclick="sortTable('list')">
                            Lista <span class="sort-arrow" data-column="list">↕</span>
                        </th>
                        <th>Membros</th>
                        <th>Etiquetas</th>
                        <th>Tags</th>
                        <th onclick="sortTable('dueDate')">
                            Vencimento <span class="sort-arrow" data-column="dueDate">↕</span>
                        </th>
                        <th onclick="sortTable('created')">
                            Criado em <span class="sort-arrow" data-column="created">↕</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" class="empty-state">
                            <h3>Carregando dados...</h3>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    <!-- Card Modal -->
    <div class="modal" id="cardModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalCardTitle"></h3>
                <span class="modal-close" onclick="closeModal()">×</span>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <script src="js/services/api.service.js"></script>
    <script src="js/services/auth.guard.js"></script>
    <script src="js/utils/notifications.js"></script>
    <script>
        // State
        let boards = [];
        let allCards = [];
        let filteredCards = [];
        let currentPage = 1;
        const itemsPerPage = 20;
        let sortColumn = 'created';
        let sortDirection = 'desc';
        let allMembers = new Map();

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

                // Update user UI
                updateUserUI(user);

                // Load data
                await loadAllData();

                // Hide loading screen
                hideLoadingScreen();
            } catch (error) {
                console.error('Initialization error:', error);
                notify.error('Erro ao inicializar tabela');
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

        // Load all data
        async function loadAllData() {
            try {
                showLoadingScreen();
                
                // Load boards
                const boardsResponse = await apiService.getBoards();
                if (boardsResponse.success) {
                    boards = boardsResponse.boards;
                    
                    // Load all cards from all boards
                    allCards = [];
                    for (const board of boards) {
                        const boardResponse = await apiService.getBoard(board.id);
                        if (boardResponse.success) {
                            const boardData = boardResponse.board;
                            
                            // Collect all members
                            boardData.members.forEach(member => {
                                allMembers.set(member.userId, member);
                            });
                            
                            // Extract all cards
                            boardData.lists.forEach(list => {
                                list.cards.forEach(card => {
                                    allCards.push({
                                        ...card,
                                        boardId: board.id,
                                        boardTitle: board.title,
                                        boardColor: board.color,
                                        listTitle: list.title,
                                        createdAt: card.createdAt || new Date().toISOString()
                                    });
                                });
                            });
                        }
                    }
                    
                    // Setup filters
                    setupFilters();
                    
                    // Initial filter and render
                    filterTable();
                }
            } catch (error) {
                console.error('Error loading data:', error);
                notify.error('Erro ao carregar dados');
            } finally {
                hideLoadingScreen();
            }
        }

        // Setup filters
        function setupFilters() {
            // Board filter
            const boardFilter = document.getElementById('boardFilter');
            boardFilter.innerHTML = '<option value="all">Todos os quadros</option>';
            boards.forEach(board => {
                const option = document.createElement('option');
                option.value = board.id;
                option.textContent = board.title;
                boardFilter.appendChild(option);
            });
            
            // Member filter
            const memberFilter = document.getElementById('memberFilter');
            memberFilter.innerHTML = '<option value="all">Todos os membros</option>';
            allMembers.forEach((member, userId) => {
                const option = document.createElement('option');
                option.value = userId;
                option.textContent = member.name;
                memberFilter.appendChild(option);
            });
        }

        // Filter table
        function filterTable() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const boardFilter = document.getElementById('boardFilter').value;
            const memberFilter = document.getElementById('memberFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            filteredCards = allCards.filter(card => {
                // Search filter
                if (searchTerm && !card.title.toLowerCase().includes(searchTerm) && 
                    (!card.description || !card.description.toLowerCase().includes(searchTerm))) {
                    return false;
                }
                
                // Board filter
                if (boardFilter !== 'all' && card.boardId !== boardFilter) {
                    return false;
                }
                
                // Member filter
                if (memberFilter !== 'all') {
                    if (!card.members || !card.members.find(m => m.id === memberFilter)) {
                        return false;
                    }
                }
                
                // Status filter
                if (statusFilter !== 'all') {
                    const status = getCardStatus(card);
                    if (status !== statusFilter) {
                        return false;
                    }
                }
                
                return true;
            });
            
            currentPage = 1;
            sortTableData();
            renderTable();
        }

        // Get card status
        function getCardStatus(card) {
            if (!card.dueDate) return 'no-date';
            
            const dueDate = new Date(card.dueDate);
            const now = new Date();
            const diffTime = dueDate - now;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays < 0) return 'overdue';
            if (diffDays === 0) return 'due-today';
            if (diffDays <= 2) return 'due-soon';
            return 'on-track';
        }

        // Sort table
        function sortTable(column) {
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'asc';
            }
            
            // Update sort arrows
            document.querySelectorAll('.sort-arrow').forEach(arrow => {
                arrow.classList.remove('active');
                arrow.textContent = '↕';
            });
            
            const arrow = document.querySelector(`.sort-arrow[data-column="${column}"]`);
            arrow.classList.add('active');
            arrow.textContent = sortDirection === 'asc' ? '↑' : '↓';
            
            sortTableData();
            renderTable();
        }

        // Sort table data
        function sortTableData() {
            filteredCards.sort((a, b) => {
                let aVal, bVal;
                
                switch (sortColumn) {
                    case 'title':
                        aVal = a.title.toLowerCase();
                        bVal = b.title.toLowerCase();
                        break;
                    case 'board':
                        aVal = a.boardTitle.toLowerCase();
                        bVal = b.boardTitle.toLowerCase();
                        break;
                    case 'list':
                        aVal = a.listTitle.toLowerCase();
                        bVal = b.listTitle.toLowerCase();
                        break;
                    case 'dueDate':
                        aVal = a.dueDate ? new Date(a.dueDate).getTime() : 0;
                        bVal = b.dueDate ? new Date(b.dueDate).getTime() : 0;
                        break;
                    case 'created':
                        aVal = new Date(a.createdAt).getTime();
                        bVal = new Date(b.createdAt).getTime();
                        break;
                    default:
                        return 0;
                }
                
                if (sortDirection === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });
        }

        // Render table
        function renderTable() {
            const tbody = document.getElementById('tableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageCards = filteredCards.slice(start, end);
            
            if (pageCards.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state">
                            <h3>Nenhum cartão encontrado</h3>
                            <p>Tente ajustar os filtros</p>
                        </td>
                    </tr>
                `;
                renderPagination();
                return;
            }
            
            tbody.innerHTML = pageCards.map(card => {
                // Members
                const membersHTML = card.members && card.members.length > 0 ? 
                    card.members.map(member => `
                        <div class="member-avatar" style="background-color: ${member.color}" 
                             title="${member.name}">
                            ${member.initials}
                        </div>
                    `).join('') : '-';
                
                // Labels
                const labelsHTML = card.labels && card.labels.length > 0 ?
                    card.labels.map(label => `
                        <span class="label label-${label}" title="${label}"></span>
                    `).join('') : '-';
                
                // Tags
                const tagsHTML = card.tags && card.tags.length > 0 ?
                    card.tags.map(tag => `<span class="tag">#${tag}</span>`).join('') : '-';
                
                // Due date
                let dueDateHTML = '-';
                let dueDateClass = '';
                if (card.dueDate) {
                    const dueDate = new Date(card.dueDate);
                    const status = getCardStatus(card);
                    dueDateClass = status === 'overdue' ? 'overdue' : 
                                  status === 'due-today' ? 'due-today' : 
                                  status === 'due-soon' ? 'due-soon' : '';
                    dueDateHTML = dueDate.toLocaleDateString('pt-BR');
                }
                
                // Created date
                const createdDate = new Date(card.createdAt || new Date());
                const createdHTML = createdDate.toLocaleDateString('pt-BR');
                
                return `
                    <tr>
                        <td>
                            <span class="card-title-cell" onclick="openCardModal('${card.id}', '${card.boardId}')">
                                ${card.title}
                            </span>
                        </td>
                        <td>
                            <span class="board-badge" style="background: ${card.boardColor}">
                                ${card.boardTitle}
                            </span>
                        </td>
                        <td>
                            <span class="list-badge">${card.listTitle}</span>
                        </td>
                        <td>
                            <div class="members-cell">${membersHTML}</div>
                        </td>
                        <td>
                            <div class="labels-cell">${labelsHTML}</div>
                        </td>
                        <td>
                            <div class="tags-cell">${tagsHTML}</div>
                        </td>
                        <td>
                            <span class="due-date-cell ${dueDateClass}">${dueDateHTML}</span>
                        </td>
                        <td>${createdHTML}</td>
                    </tr>
                `;
            }).join('');
            
            renderPagination();
        }

        // Render pagination
        function renderPagination() {
            const totalPages = Math.ceil(filteredCards.length / itemsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '';
            
            // Previous button
            html += `
                <button class="page-btn" onclick="changePage(${currentPage - 1})" 
                        ${currentPage === 1 ? 'disabled' : ''}>
                    ← Anterior
                </button>
            `;
            
            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
                if (startPage > 2) html += `<span class="page-info">...</span>`;
            }
            
            for (let i = startPage; i <= endPage; i++) {
                html += `
                    <button class="page-btn ${i === currentPage ? 'active' : ''}" 
                            onclick="changePage(${i})">
                        ${i}
                    </button>
                `;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span class="page-info">...</span>`;
                html += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
            }
            
            // Next button
            html += `
                <button class="page-btn" onclick="changePage(${currentPage + 1})" 
                        ${currentPage === totalPages ? 'disabled' : ''}>
                    Próximo →
                </button>
            `;
            
            // Info
            html += `
                <span class="page-info">
                    ${filteredCards.length} cartões encontrados
                </span>
            `;
            
            pagination.innerHTML = html;
        }

        // Change page
        function changePage(page) {
            currentPage = page;
            renderTable();
            window.scrollTo(0, 0);
        }

        // Open card modal
        async function openCardModal(cardId, boardId) {
            try {
                const response = await apiService.getCard(cardId);
                if (response.success) {
                    const card = response.card;
                    const board = boards.find(b => b.id === boardId);
                    
                    document.getElementById('modalCardTitle').textContent = card.title;
                    
                    let modalBodyHTML = `
                        <div class="card-info">
                            <div class="card-info-label">Quadro</div>
                            <div style="color: ${board.color}; font-weight: 500;">${board.title}</div>
                        </div>
                    `;
                    
                    if (card.description) {
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Descrição</div>
                                <div class="card-description">${card.description}</div>
                            </div>
                        `;
                    }
                    
                    if (card.dueDate) {
                        const dueDate = new Date(card.dueDate);
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Data de Vencimento</div>
                                <div>${dueDate.toLocaleString('pt-BR')}</div>
                            </div>
                        `;
                    }
                    
                    if (card.members && card.members.length > 0) {
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Membros</div>
                                <div class="members-cell">
                                    ${card.members.map(member => `
                                        <div class="member-avatar" style="background-color: ${member.color}" 
                                             title="${member.name}">
                                            ${member.initials}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }
                    
                    if (card.tags && card.tags.length > 0) {
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Tags</div>
                                <div class="tags-cell">
                                    ${card.tags.map(tag => `<span class="tag">#${tag}</span>`).join('')}
                                </div>
                            </div>
                        `;
                    }
                    
                    document.getElementById('modalBody').innerHTML = modalBodyHTML;
                    document.getElementById('cardModal').classList.add('active');
                }
            } catch (error) {
                console.error('Error loading card:', error);
                notify.error('Erro ao carregar detalhes do cartão');
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('cardModal').classList.remove('active');
        }

        // Export to CSV
        function exportToCSV() {
            const headers = ['Título', 'Quadro', 'Lista', 'Membros', 'Tags', 'Vencimento', 'Status', 'Criado em'];
            const rows = filteredCards.map(card => {
                const members = card.members ? card.members.map(m => m.name).join(', ') : '';
                const tags = card.tags ? card.tags.join(', ') : '';
                const dueDate = card.dueDate ? new Date(card.dueDate).toLocaleDateString('pt-BR') : '';
                const status = card.dueDate ? getCardStatus(card) : '';
                const created = new Date(card.createdAt || new Date()).toLocaleDateString('pt-BR');
                
                return [
                    card.title,
                    card.boardTitle,
                    card.listTitle,
                    members,
                    tags,
                    dueDate,
                    status,
                    created
                ];
            });
            
            // Create CSV content
            const csvContent = [
                headers.join(','),
                ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
            ].join('\n');
            
            // Download file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            
            link.setAttribute('href', url);
            link.setAttribute('download', `rc_cards_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            notify.success('Arquivo CSV exportado com sucesso!');
        }

        // Utilities
        function showLoadingScreen() {
            document.getElementById('loadingScreen').classList.remove('hide');
        }

        function hideLoadingScreen() {
            document.getElementById('loadingScreen').classList.add('hide');
        }

        function logout() {
            apiService.logout();
        }

        // Event listeners
        document.getElementById('cardModal').addEventListener('click', (e) => {
            if (e.target.id === 'cardModal') {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>