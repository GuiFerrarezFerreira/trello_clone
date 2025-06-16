// ===================================
// js/utils/notifications.js
// ===================================

class NotificationService {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create notification container
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(this.container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const colors = {
            success: '#61bd4f',
            error: '#eb5a46',
            warning: '#f2d600',
            info: '#0079bf'
        };

        notification.style.cssText = `
            background: white;
            border-left: 4px solid ${colors[type]};
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            padding: 16px 20px;
            margin-bottom: 10px;
            min-width: 300px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease-out;
        `;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="color: ${colors[type]}; font-size: 20px; font-weight: bold;">
                    ${icons[type]}
                </span>
                <span style="color: #172b4d; font-size: 14px;">${message}</span>
            </div>
            <button onclick="this.parentElement.remove()" style="
                background: none;
                border: none;
                color: #6b778c;
                cursor: pointer;
                font-size: 18px;
                padding: 0 0 0 12px;
            ">×</button>
        `;

        this.container.appendChild(notification);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }

        return notification;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
}

// Create global notification service
const notify = new NotificationService();
