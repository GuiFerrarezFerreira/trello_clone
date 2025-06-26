// ===================================
// js/services/auth.guard.js
// ===================================

class AuthGuard {
    constructor() {
        this.publicPages = ['login.php', 'register.php'];
        this.initialized = false;
    }

async checkAuth() {
        const currentPage = window.location.pathname;
        const isPublicPage = this.publicPages.some(page => currentPage.includes(page));
        
        // Para páginas públicas, não precisa verificar autenticação
        if (isPublicPage) {
            const token = localStorage.getItem('authToken');
            if (token) {
                // Se tem token em página pública, redireciona para index
                window.location.href = 'index.php';
                return false;
            }
            return true;
        }
        
        // Aguarda o apiService estar disponível
        if (typeof apiService === 'undefined') {
            console.warn('ApiService não está carregado ainda. Aguardando...');
            // Tenta novamente após 100ms
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Se ainda não estiver disponível após esperar, redireciona para login
            if (typeof apiService === 'undefined') {
                console.error('ApiService não foi carregado');
                window.location.href = 'login.php';
                return false;
            }
        }

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
            try {
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
            } catch (error) {
                console.error('Erro ao validar token:', error);
                // Em caso de erro, remove token inválido
                localStorage.removeItem('authToken');
                window.location.href = 'login.php';
                return false;
            }
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

    // Método para inicializar o AuthGuard quando tudo estiver pronto
    async init() {
        if (this.initialized) return;
        
        // Aguarda o DOM estar completamente carregado
        if (document.readyState === 'loading') {
            await new Promise(resolve => {
                document.addEventListener('DOMContentLoaded', resolve);
            });
        }
        
        // Verifica se o apiService está disponível
        let attempts = 0;
        const maxAttempts = 50; // 5 segundos no máximo
        
        while (typeof apiService === 'undefined' && attempts < maxAttempts) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        if (typeof apiService === 'undefined') {
            console.error('ApiService não foi carregado. Verifique se api.service.js está incluído antes de auth.guard.js');
            return;
        }
        
        // Executa a verificação de autenticação
        await this.checkAuth();
        this.initialized = true;
    }
}

// Cria instância do AuthGuard
const authGuard = new AuthGuard();

// Inicializa quando o script for carregado
// Usa setTimeout para garantir que seja executado após todos os scripts síncronos
setTimeout(() => {
    authGuard.init().catch(error => {
        console.error('Erro ao inicializar AuthGuard:', error);
    });
}, 0);