<?php
// ===================================
// calendar.php - Visualização de Calendário
// ===================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário - RC ADM</title>
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

        /* Calendar Container */
        .calendar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
        }

        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .month-year {
            font-size: 24px;
            font-weight: 700;
            color: #172b4d;
        }

        .nav-button {
            background: #0079bf;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-button:hover {
            background: #026aa7;
        }

        .today-button {
            background: transparent;
            color: #0079bf;
            border: 1px solid #0079bf;
        }

        .today-button:hover {
            background: #e4f0f6;
        }

        .board-selector {
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
        }

        /* Calendar Grid */
        .calendar-grid {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            overflow: hidden;
        }

        .weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f4f5f7;
            border-bottom: 1px solid #e4e6ea;
        }

        .weekday {
            padding: 15px;
            text-align: center;
            font-weight: 600;
            color: #5e6c84;
            font-size: 14px;
            text-transform: uppercase;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-auto-rows: minmax(120px, auto);
        }

        .day-cell {
            border-right: 1px solid #e4e6ea;
            border-bottom: 1px solid #e4e6ea;
            padding: 8px;
            position: relative;
            background: white;
            transition: background 0.2s;
        }

        .day-cell:nth-child(7n) {
            border-right: none;
        }

        .day-cell.other-month {
            background: #f4f5f7;
        }

        .day-cell.today {
            background: #e4f0f6;
        }

        .day-cell:hover {
            background: #f4f5f7;
        }

        .day-number {
            font-weight: 600;
            font-size: 14px;
            color: #172b4d;
            margin-bottom: 8px;
        }

        .day-cell.other-month .day-number {
            color: #6b778c;
        }

        .day-cell.today .day-number {
            color: #0079bf;
            background: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .day-cards {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .calendar-card {
            background: white;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calendar-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendar-card.overdue {
            background: #eb5a46;
            color: white;
            border-color: #eb5a46;
        }

        .calendar-card.due-today {
            background: #f2d600;
            border-color: #f2d600;
        }

        .calendar-card.due-soon {
            background: #ff991a;
            color: white;
            border-color: #ff991a;
        }

        .card-board-color {
            width: 4px;
            height: 12px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .more-cards {
            font-size: 11px;
            color: #6b778c;
            cursor: pointer;
            padding: 2px 8px;
            text-align: center;
            transition: all 0.2s;
        }

        .more-cards:hover {
            background: #e4e6ea;
            border-radius: 3px;
        }

        /* Card Modal */
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

        .card-info-value {
            font-size: 14px;
            color: #172b4d;
        }

        .card-description {
            background: #f4f5f7;
            padding: 12px;
            border-radius: 3px;
            font-size: 14px;
            line-height: 1.5;
            color: #172b4d;
        }

        .card-members {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }

        .card-tags {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .tag {
            background: #e4e6ea;
            color: #5e6c84;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
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
            .calendar-header {
                flex-direction: column;
                gap: 16px;
            }

            .calendar-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .weekday {
                font-size: 12px;
                padding: 10px 5px;
            }

            .day-cell {
                min-height: 100px;
            }

            .calendar-card {
                font-size: 11px;
                padding: 2px 4px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <h2 style="color: white;">Carregando calendário...</h2>
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
                <button class="view-btn" onclick="switchToBoard()">📋 Quadros</button>
                <button class="view-btn active">📅 Calendário</button>
                <button class="view-btn" onclick="location.href='table.php'">📊 Tabela</button>
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

    <!-- Calendar Container -->
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="calendar-nav">
                <button class="nav-button" onclick="previousMonth()">← Anterior</button>
                <h2 class="month-year" id="monthYear"></h2>
                <button class="nav-button" onclick="nextMonth()">Próximo →</button>
                <button class="nav-button today-button" onclick="goToToday()">Hoje</button>
            </div>
            <select class="board-selector" id="boardSelector" onchange="loadBoardCards()">
                <option value="all">Todos os quadros</option>
            </select>
        </div>

        <div class="calendar-grid">
            <div class="weekdays">
                <div class="weekday">Dom</div>
                <div class="weekday">Seg</div>
                <div class="weekday">Ter</div>
                <div class="weekday">Qua</div>
                <div class="weekday">Qui</div>
                <div class="weekday">Sex</div>
                <div class="weekday">Sáb</div>
            </div>
            <div class="days-grid" id="daysGrid"></div>
        </div>
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
        let currentDate = new Date();
        let boards = [];
        let allCards = [];
        let selectedBoardId = 'all';

        // Portuguese month names
        const monthNames = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
        ];

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

                // Load boards
                await loadBoards();

                // Render calendar
                renderCalendar();

                // Hide loading screen
                hideLoadingScreen();
            } catch (error) {
                console.error('Initialization error:', error);
                notify.error('Erro ao inicializar calendário');
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

        // Load boards
        async function loadBoards() {
            try {
                const response = await apiService.getBoards();
                if (response.success) {
                    boards = response.boards;
                    renderBoardSelector();
                    await loadBoardCards();
                }
            } catch (error) {
                console.error('Error loading boards:', error);
                notify.error('Erro ao carregar quadros');
            }
        }

        // Render board selector
        function renderBoardSelector() {
            const selector = document.getElementById('boardSelector');
            selector.innerHTML = '<option value="all">Todos os quadros</option>';
            
            boards.forEach(board => {
                const option = document.createElement('option');
                option.value = board.id;
                option.textContent = board.title;
                option.style.color = board.color;
                selector.appendChild(option);
            });
        }

        // Load cards from boards
        async function loadBoardCards() {
            showLoadingScreen();
            allCards = [];
            selectedBoardId = document.getElementById('boardSelector').value;
            
            try {
                const boardsToLoad = selectedBoardId === 'all' ? boards : boards.filter(b => b.id === selectedBoardId);
                
                for (const board of boardsToLoad) {
                    const response = await apiService.getBoard(board.id);
                    if (response.success) {
                        const boardData = response.board;
                        // Extract all cards with due dates
                        boardData.lists.forEach(list => {
                            list.cards.forEach(card => {
                                if (card.dueDate) {
                                    allCards.push({
                                        ...card,
                                        boardId: board.id,
                                        boardTitle: board.title,
                                        boardColor: board.color,
                                        listTitle: list.title
                                    });
                                }
                            });
                        });
                    }
                }
                
                renderCalendar();
            } catch (error) {
                console.error('Error loading cards:', error);
                notify.error('Erro ao carregar cartões');
            } finally {
                hideLoadingScreen();
            }
        }

        // Calendar navigation
        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }

        function goToToday() {
            currentDate = new Date();
            renderCalendar();
        }

        // Render calendar
        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update header
            document.getElementById('monthYear').textContent = `${monthNames[month]} ${year}`;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            // Get days from previous month
            const prevLastDay = new Date(year, month, 0).getDate();
            
            // Create days array
            const days = [];
            
            // Previous month days
            for (let i = startingDayOfWeek - 1; i >= 0; i--) {
                days.push({
                    day: prevLastDay - i,
                    month: month - 1,
                    year: month === 0 ? year - 1 : year,
                    otherMonth: true
                });
            }
            
            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                days.push({
                    day,
                    month,
                    year,
                    otherMonth: false
                });
            }
            
            // Next month days
            const remainingDays = 42 - days.length; // 6 weeks * 7 days
            for (let day = 1; day <= remainingDays; day++) {
                days.push({
                    day,
                    month: month + 1,
                    year: month === 11 ? year + 1 : year,
                    otherMonth: true
                });
            }
            
            // Render days grid
            const daysGrid = document.getElementById('daysGrid');
            const today = new Date();
            
            daysGrid.innerHTML = days.map(dayInfo => {
                const isToday = !dayInfo.otherMonth && 
                               dayInfo.day === today.getDate() && 
                               dayInfo.month === today.getMonth() && 
                               dayInfo.year === today.getFullYear();
                
                const dayDate = new Date(dayInfo.year, dayInfo.month, dayInfo.day);
                const dayCards = getCardsForDate(dayDate);
                
                return `
                    <div class="day-cell ${dayInfo.otherMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}">
                        <div class="day-number">${dayInfo.day}</div>
                        <div class="day-cards">
                            ${renderDayCards(dayCards)}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Get cards for specific date
        function getCardsForDate(date) {
            return allCards.filter(card => {
                const cardDate = new Date(card.dueDate);
                return cardDate.getDate() === date.getDate() &&
                       cardDate.getMonth() === date.getMonth() &&
                       cardDate.getFullYear() === date.getFullYear();
            });
        }

        // Render cards for a day
        function renderDayCards(cards) {
            if (cards.length === 0) return '';
            
            const maxVisible = 3;
            const visibleCards = cards.slice(0, maxVisible);
            const hiddenCount = cards.length - maxVisible;
            
            let html = visibleCards.map(card => {
                const dueDate = new Date(card.dueDate);
                const now = new Date();
                const diffTime = dueDate - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                let cardClass = '';
                if (diffDays < 0) cardClass = 'overdue';
                else if (diffDays === 0) cardClass = 'due-today';
                else if (diffDays === 1) cardClass = 'due-soon';
                
                return `
                    <div class="calendar-card ${cardClass}" onclick="openCardModal('${card.id}', '${card.boardId}')">
                        <div class="card-board-color" style="background: ${card.boardColor}"></div>
                        ${card.title}
                    </div>
                `;
            }).join('');
            
            if (hiddenCount > 0) {
                html += `<div class="more-cards">+${hiddenCount} mais</div>`;
            }
            
            return html;
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
                            <div class="card-info-value" style="color: ${board.color}">${board.title}</div>
                        </div>
                    `;
                    
                    if (card.dueDate) {
                        const dueDate = new Date(card.dueDate);
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Data de Vencimento</div>
                                <div class="card-info-value">${dueDate.toLocaleString('pt-BR')}</div>
                            </div>
                        `;
                    }
                    
                    if (card.description) {
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Descrição</div>
                                <div class="card-description">${card.description}</div>
                            </div>
                        `;
                    }
                    
                    if (card.members && card.members.length > 0) {
                        modalBodyHTML += `
                            <div class="card-info">
                                <div class="card-info-label">Membros</div>
                                <div class="card-members">
                                    ${card.members.map(member => `
                                        <div class="member-avatar" style="background-color: ${member.color}" title="${member.name}">
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
                                <div class="card-tags">
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

        // Switch to board view
        function switchToBoard() {
            window.location.href = 'index.php';
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