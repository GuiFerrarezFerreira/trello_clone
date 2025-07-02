// ===================================
// js/services/api.service.js
// ===================================

class ApiService {
    constructor() {
        this.baseURL = 'http://localhost/trello_clone/api';
        this.token = localStorage.getItem('authToken');
    }

    // Set authorization header
    setAuthHeader() {
        if (this.token) {
            return {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json'
            };
        }
        return {
            'Content-Type': 'application/json'
        };
    }

    // Handle response
    async handleResponse(response) {
        const data = await response.json();
        
        if (!response.ok) {
            if (response.status === 401) {
                // Token expired or invalid
                this.logout();
                window.location.href = 'login.php';
            }
            throw new Error(data.message || 'Erro na requisição');
        }
        
        return data;
    }

    // Update token
    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('authToken', token);
        } else {
            localStorage.removeItem('authToken');
        }
    }

    // ===========================
    // Authentication Methods
    // ===========================

    async login(email, password, rememberMe = false) {
        try {
            const response = await fetch(`${this.baseURL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password, rememberMe })
            });

            const data = await this.handleResponse(response);
            
            if (data.success) {
                this.setToken(data.token);
                localStorage.setItem('userId', data.userId);
                localStorage.setItem('userName', data.userName);
                localStorage.setItem('userInitials', data.userInitials);
                localStorage.setItem('userColor', data.userColor);
            }
            
            return data;
        } catch (error) {
            throw error;
        }
    }

    async register(firstName, lastName, email, password) {
        try {
            const response = await fetch(`${this.baseURL}/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ firstName, lastName, email, password })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async validateToken() {
        if (!this.token) return false;

        try {
            const response = await fetch(`${this.baseURL}/validate-token.php`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            const data = await this.handleResponse(response);
            return data.success ? data.user : false;
        } catch (error) {
            return false;
        }
    }

    logout() {
        this.setToken(null);
        localStorage.removeItem('userId');
        localStorage.removeItem('userName');
        localStorage.removeItem('userInitials');
        localStorage.removeItem('userColor');
        window.location.href = 'login.php';
    }

    // ===========================
    // Board Methods
    // ===========================

    async getBoards() {
        try {
            const response = await fetch(`${this.baseURL}/boards.php`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async getBoard(boardId) {
        try {
            const response = await fetch(`${this.baseURL}/boards.php?id=${boardId}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async createBoard(title, color) {
        try {
            const response = await fetch(`${this.baseURL}/boards.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ title, color })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async updateBoard(boardId, updates) {
        try {
            const response = await fetch(`${this.baseURL}/boards.php?id=${boardId}`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify(updates)
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async deleteBoard(boardId) {
        try {
            const response = await fetch(`${this.baseURL}/boards.php?id=${boardId}`, {
                method: 'DELETE',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Board Members Methods
    // ===========================

    async addBoardMember(boardId, userId, role = 'reader') {
        try {
            const response = await fetch(`${this.baseURL}/board-members.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ boardId, userId, role })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async updateBoardMember(boardId, userId, role) {
        try {
            const response = await fetch(`${this.baseURL}/board-members.php`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ boardId, userId, role })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async removeBoardMember(boardId, userId) {
        try {
            const response = await fetch(`${this.baseURL}/board-members.php`, {
                method: 'DELETE',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ boardId, userId })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // List Methods
    // ===========================

    async createList(boardId, title) {
        try {
            const response = await fetch(`${this.baseURL}/lists.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ boardId, title })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async updateList(listId, updates) {
        try {
            const response = await fetch(`${this.baseURL}/lists.php?id=${listId}`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify(updates)
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async deleteList(listId) {
        try {
            const response = await fetch(`${this.baseURL}/lists.php?id=${listId}`, {
                method: 'DELETE',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async moveList(listId, newPosition) {
        try {
            const response = await fetch(`${this.baseURL}/lists.php?id=${listId}/move`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ position: newPosition })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Card Methods
    // ===========================

    async createCard(listId, title) {
        try {
            const response = await fetch(`${this.baseURL}/cards.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ listId, title })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async getCard(cardId) {
        try {
            const response = await fetch(`${this.baseURL}/cards.php?id=${cardId}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async updateCard(cardId, updates) {
        try {
            const response = await fetch(`${this.baseURL}/cards.php?id=${cardId}`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify(updates)
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async deleteCard(cardId) {
        try {
            const response = await fetch(`${this.baseURL}/cards.php?id=${cardId}`, {
                method: 'DELETE',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async moveCard(cardId, targetListId, position) {
        try {
            const response = await fetch(`${this.baseURL}/cards.php?id=${cardId}/move`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ listId: targetListId, position })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Card Members Methods
    // ===========================

    async addCardMember(cardId, userId) {
        try {
            const response = await fetch(`${this.baseURL}/card-members.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, userId })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async removeCardMember(cardId, userId) {
        try {
            const response = await fetch(`${this.baseURL}/card-members.php`, {
                method: 'DELETE',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, userId })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Card Labels Methods
    // ===========================

    async addCardLabel(cardId, label) {
        try {
            const response = await fetch(`${this.baseURL}/card-labels.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, label })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async removeCardLabel(cardId, label) {
        try {
            const response = await fetch(`${this.baseURL}/card-labels.php`, {
                method: 'DELETE',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, label })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Card Tags Methods
    // ===========================

    async addCardTag(cardId, tag) {
        try {
            const response = await fetch(`${this.baseURL}/card-tags.php`, {
                method: 'POST',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, tag })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async removeCardTag(cardId, tag) {
        try {
            const response = await fetch(`${this.baseURL}/card-tags.php`, {
                method: 'DELETE',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, tag })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Search Methods
    // ===========================

    async searchUsers(query) {
        try {
            const response = await fetch(`${this.baseURL}/users.php?search=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async searchCards(boardId, query) {
        try {
            const response = await fetch(`${this.baseURL}/search.php?boardId=${boardId}&q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

// ===========================
    // Activity Methods
    // ===========================

    async getBoardActivity(boardId, limit = 50) {
        try {
            const response = await fetch(`${this.baseURL}/activities.php?boardId=${boardId}&limit=${limit}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    // ===========================
    // Card Images Methods
    // ===========================

    async getCardImages(cardId) {
        try {
            const response = await fetch(`${this.baseURL}/card-images.php?cardId=${cardId}`, {
                method: 'GET',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async uploadCardImage(cardId, file) {
        try {
            const formData = new FormData();
            formData.append('cardId', cardId);
            formData.append('image', file);

            const response = await fetch(`${this.baseURL}/card-images.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                },
                body: formData
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async deleteCardImage(imageId) {
        try {
            const response = await fetch(`${this.baseURL}/card-images.php?id=${imageId}`, {
                method: 'DELETE',
                headers: this.setAuthHeader()
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }

    async setCoverImage(cardId, coverImageId) {
        try {
            const response = await fetch(`${this.baseURL}/card-cover.php`, {
                method: 'PUT',
                headers: this.setAuthHeader(),
                body: JSON.stringify({ cardId, coverImageId })
            });

            return await this.handleResponse(response);
        } catch (error) {
            throw error;
        }
    }    
}

// Create singleton instance
const apiService = new ApiService();