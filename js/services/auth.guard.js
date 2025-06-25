// ===================================
// js/services/auth.guard.js
// ===================================

class AuthGuard {
    constructor() {
        this.publicPages = ['login.php', 'register.php'];
    }

    async checkAuth() {
        const currentPage = window.location.pathname;
        const isPublicPage = this.publicPages.some(page => currentPage.includes(page));
        
        // Check if user has token
        const token = localStorage.getItem('authToken');
        
        if (!token && !isPublicPage) {
            // No token and trying to access protected page
            window.location.href = 'login.php';
            return false;
        }
        
        if (token && isPublicPage) {
            // Has token and trying to access public page
            window.location.href = 'index.php';
            return false;
        }
        
        if (token && !isPublicPage) {
            // Validate token
            const user = await apiService.validateToken();
            if (!user) {
                // Invalid token
                apiService.logout();
                return false;
            }
            
            // Update user info
            this.updateUserInfo(user);
            return true;
        }
        
        return true;
    }

    updateUserInfo(user) {
        // Update navbar user info
        const userAvatar = document.querySelector('.user-avatar-small');
        const userName = document.querySelector('.current-user span');
        
        if (userAvatar && userName) {
            userAvatar.textContent = user.initials;
            userAvatar.style.backgroundColor = user.color;
            userName.textContent = `${user.firstName} ${user.lastName}`;
        }
    }
}

// Initialize auth guard
const authGuard = new AuthGuard();

// Check authentication on page load
document.addEventListener('DOMContentLoaded', () => {
    authGuard.checkAuth();
});