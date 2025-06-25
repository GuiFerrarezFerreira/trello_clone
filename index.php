<?php
// ===================================
// index.php - Página Principal
// ===================================
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Dados do usuário da sessão
$user = [
    'id' => $_SESSION['user_id'] ?? '',
    'firstName' => $_SESSION['user_firstName'] ?? '',
    'lastName' => $_SESSION['user_lastName'] ?? '',
    'initials' => $_SESSION['user_initials'] ?? '',
    'color' => $_SESSION['user_color'] ?? '#0079bf'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trello Clone</title>
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

        .loading-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 40px;
            color: #0079bf;
            margin: 0 auto 20px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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

        .navbar {
            background: rgba(0, 0, 0, 0.15);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            backdrop-filter: blur(5px);
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

        .current-user {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .current-user:hover {
            background: rgba(255, 255, 255, 0.3);
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

        .navbar h1 {
            color: white;
            font-size: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar .logo {
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

        .boards-menu-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .boards-menu-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .boards-menu {
            position: absolute;
            top: 60px;
            left: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 8px 16px -4px rgba(9, 30, 66, 0.25);
            width: 320px;
            max-height: 80vh;
            overflow-y: auto;
            display: none;
            z-index: 100;
        }

        .boards-menu.active {
            display: block;
        }

        .boards-menu-header {
            padding: 16px;
            border-bottom: 1px solid #e4e6ea;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .boards-menu-close {
            cursor: pointer;
            font-size: 20px;
            color: #6b778c;
            padding: 4px;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .boards-menu-close:hover {
            background: #f4f5f7;
            color: #172b4d;
        }

        .boards-list {
            padding: 8px;
        }

        .board-item {
            padding: 12px;
            border-radius: 3px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s;
            margin-bottom: 4px;
            position: relative;
        }

        .board-item:hover {
            background: #f4f5f7;
        }

        .board-item:hover .board-delete {
            opacity: 1;
        }

        .board-item.active {
            background: #e4f0f6;
            color: #0079bf;
        }

        .board-delete {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 28px;
            border-radius: 3px;
            background: rgba(0, 0, 0, 0.1);
            color: #6b778c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            opacity: 0;
            transition: all 0.2s;
            cursor: pointer;
        }

        .board-delete:hover {
            background: #eb5a46;
            color: white;
        }

        .board-item-color {
            width: 40px;
            height: 32px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .board-item-info {
            flex: 1;
        }

        .board-item-title {
            font-weight: 500;
            font-size: 14px;
        }

        .board-item-lists {
            font-size: 12px;
            color: #6b778c;
        }

        .create-board-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            width: 100%;
            border: none;
            background: transparent;
            cursor: pointer;
            color: #5e6c84;
            font-size: 14px;
            text-align: left;
            border-radius: 3px;
            transition: all 0.2s;
        }

        .create-board-btn:hover {
            background: #f4f5f7;
            color: #172b4d;
        }

        .create-board-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .create-board-modal.active {
            display: flex;
        }

        .create-board-content {
            background: white;
            border-radius: 8px;
            width: 400px;
            padding: 24px;
            animation: slideUp 0.3s ease-out;
        }

        .create-board-header {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #5e6c84;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .form-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #0079bf;
        }

        .color-options {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .color-option {
            height: 48px;
            border-radius: 3px;
            cursor: pointer;
            position: relative;
            transition: transform 0.2s;
        }

        .color-option:hover {
            transform: scale(1.05);
        }

        .color-option.selected::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        .board-header {
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .board-header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .board-members {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .board-members-list {
            display: flex;
            gap: -8px;
        }

        .board-member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .board-member-avatar:hover {
            transform: translateY(-2px);
            z-index: 1;
        }

        .add-member-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: white;
            font-size: 20px;
        }

        .add-member-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Members Management Modal */
        .members-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .members-modal.active {
            display: flex;
        }

        .members-modal-content {
            background: white;
            border-radius: 8px;
            width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        .members-modal-header {
            padding: 20px;
            border-bottom: 1px solid #e4e6ea;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .members-modal-title {
            font-size: 20px;
            font-weight: 600;
            color: #172b4d;
        }

        .members-modal-body {
            padding: 20px;
        }

        .member-row {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 8px;
            transition: background 0.2s;
        }

        .member-row:hover {
            background: #f4f5f7;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .member-details {
            flex: 1;
        }

        .member-name {
            font-weight: 500;
            color: #172b4d;
        }

        .member-email {
            font-size: 12px;
            color: #6b778c;
        }

        .member-role {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 8px;
        }

        .role-admin {
            background: #ff991a;
            color: white;
        }

        .role-editor {
            background: #4bbf6b;
            color: white;
        }

        .role-reader {
            background: #b3d4ff;
            color: #0052cc;
        }

        .role-selector {
            padding: 4px 8px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 8px;
        }

        .remove-member-btn {
            padding: 4px 8px;
            background: transparent;
            border: 1px solid #eb5a46;
            color: #eb5a46;
            border-radius: 3px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .remove-member-btn:hover {
            background: #eb5a46;
            color: white;
        }

        .add-member-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e4e6ea;
        }

        .add-member-form {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .member-select {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
        }

        .user-search-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-size: 14px;
        }

        .user-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 10;
        }

        .user-search-results.active {
            display: block;
        }

        .user-result-item {
            padding: 8px 12px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .user-result-item:hover {
            background: #f4f5f7;
        }

        .no-access-message {
            text-align: center;
            padding: 40px;
            color: #5e6c84;
        }

        .no-access-message h2 {
            color: #172b4d;
            margin-bottom: 12px;
        }

        .board-title {
            font-size: 18px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 12px;
            border-radius: 3px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .board-title:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .board-container {
            padding: 0 20px 20px;
            display: flex;
            gap: 12px;
            overflow-x: auto;
            height: calc(100vh - 120px);
            align-items: flex-start;
        }

        .list {
            background: #ebecf0;
            border-radius: 3px;
            width: 280px;
            max-height: 100%;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .list-header {
            padding: 10px 12px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-title {
            flex: 1;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 14px;
            padding: 4px 8px;
            border-radius: 3px;
            cursor: pointer;
        }

        .list-title:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        .list-title:focus {
            background: white;
            box-shadow: inset 0 0 0 2px #0079bf;
            outline: none;
            cursor: text;
        }

        .list-menu {
            cursor: pointer;
            padding: 6px;
            border-radius: 3px;
            transition: background 0.2s;
            position: relative;
        }

        .list-menu:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        .list-menu-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 3px;
            box-shadow: 0 8px 16px -4px rgba(9, 30, 66, 0.25);
            width: 200px;
            display: none;
            z-index: 100;
            margin-top: 4px;
        }

        .list-menu-dropdown.active {
            display: block;
        }

        .list-menu-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 14px;
            color: #172b4d;
        }

        .list-menu-item:hover {
            background: #f4f5f7;
        }

        .list-menu-item.delete {
            color: #eb5a46;
        }

        .list-menu-item.delete:hover {
            background: #ffeae8;
        }

        .cards-container {
            padding: 0 8px;
            overflow-y: auto;
            max-height: calc(100vh - 200px);
            min-height: 5px;
        }

        .card {
            background: white;
            border-radius: 3px;
            padding: 8px 12px;
            margin-bottom: 8px;
            cursor: pointer;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            transition: all 0.2s;
            position: relative;
        }

        .card:hover {
            box-shadow: 0 1px 4px rgba(9, 30, 66, 0.2);
            transform: translateY(-1px);
        }

        .card:hover .card-delete {
            opacity: 1;
        }

        .card-delete {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            border-radius: 3px;
            background: rgba(0, 0, 0, 0.1);
            color: #6b778c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            opacity: 0;
            transition: all 0.2s;
            cursor: pointer;
            z-index: 10;
        }

        .card-delete:hover {
            background: #eb5a46;
            color: white;
            transform: scale(1.1);
        }

        .card.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            cursor: grabbing;
        }

        .card.drag-over {
            border-top: 3px solid #0079bf;
            padding-top: 20px;
        }

        .card-labels {
            display: flex;
            gap: 4px;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }

        .label {
            height: 8px;
            width: 40px;
            border-radius: 4px;
            display: inline-block;
        }

        .label-green { background: #61bd4f; }
        .label-yellow { background: #f2d600; }
        .label-red { background: #eb5a46; }
        .label-blue { background: #0079bf; }
        .label-purple { background: #c377e0; }

        .card-badges {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #5e6c84;
            font-size: 12px;
        }

        .badge-due-date {
            padding: 2px 6px;
            border-radius: 3px;
            background: #f4f5f7;
        }

        .badge-due-date.overdue {
            background: #eb5a46;
            color: white;
        }

        .badge-due-date.due-soon {
            background: #f2d600;
            color: #172b4d;
        }

        .card-members {
            display: flex;
            margin-left: auto;
            gap: -8px;
        }

        .member-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
            border: 2px solid white;
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .member-avatar:hover {
            transform: translateY(-2px);
            z-index: 1;
        }

        .card-tags {
            display: flex;
            gap: 4px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .tag {
            background: #e4e6ea;
            color: #5e6c84;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        .add-card {
            padding: 8px;
            margin: 8px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            color: #5e6c84;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .add-card:hover {
            background: rgba(0, 0, 0, 0.08);
            color: #172b4d;
        }

        .add-card-form {
            padding: 8px;
            display: none;
        }

        .add-card-form.active {
            display: block;
        }

        .card-composer {
            width: 100%;
            border: none;
            border-radius: 3px;
            padding: 8px 12px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            box-shadow: 0 1px 0 rgba(9, 30, 66, 0.13);
            margin-bottom: 8px;
            min-height: 70px;
        }

        .card-composer:focus {
            outline: none;
            box-shadow: inset 0 0 0 2px #0079bf;
        }

        .composer-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }

        .btn-primary {
            background: #0079bf;
            color: white;
        }

        .btn-primary:hover {
            background: #026aa7;
        }

        .btn-cancel {
            background: transparent;
            color: #6b778c;
            padding: 6px;
        }

        .btn-cancel:hover {
            color: #172b4d;
        }

        .add-list {
            background: rgba(235, 236, 240, 0.8);
            backdrop-filter: blur(5px);
            border-radius: 3px;
            width: 280px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            color: #172b4d;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .add-list:hover {
            background: rgba(235, 236, 240, 0.95);
        }

        .add-list-form {
            background: #ebecf0;
            border-radius: 3px;
            width: 280px;
            padding: 12px;
            display: none;
            flex-shrink: 0;
        }

        .add-list-form.active {
            display: block;
        }

        .list-name-input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #0079bf;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            font-family: inherit;
        }

        .list-name-input:focus {
            outline: none;
            border-color: #026aa7;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #f4f5f7;
            border-radius: 8px;
            width: 90%;
            max-width: 768px;
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
            padding: 20px 20px 8px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
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
            padding: 0 20px 20px;
            display: grid;
            grid-template-columns: 1fr 200px;
            gap: 20px;
        }

        .modal-main {
            min-width: 0;
        }

        .modal-sidebar {
            padding-top: 20px;
        }

        .card-detail-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            padding: 8px;
            border-radius: 3px;
            cursor: text;
        }

        .card-detail-title:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        .card-section {
            margin-top: 20px;
        }

        .card-section h3 {
            font-size: 16px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .description-input {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: none;
            border-radius: 3px;
            background: rgba(0, 0, 0, 0.04);
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .description-input:focus {
            outline: none;
            background: white;
            box-shadow: inset 0 0 0 2px #0079bf;
        }

        .sidebar-section {
            margin-bottom: 20px;
        }

        .sidebar-section h4 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #5e6c84;
            margin-bottom: 8px;
        }

        .sidebar-button {
            width: 100%;
            padding: 8px 12px;
            background: rgba(0, 0, 0, 0.04);
            border: none;
            border-radius: 3px;
            text-align: left;
            cursor: pointer;
            margin-bottom: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-button:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        .members-selector {
            display: none;
            background: white;
            border-radius: 3px;
            box-shadow: 0 8px 16px -4px rgba(9, 30, 66, 0.25);
            padding: 12px;
            margin-top: 8px;
        }

        .members-selector.active {
            display: block;
        }

        .member-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            border-radius: 3px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .member-option:hover {
            background: #f4f5f7;
        }

        .member-option.selected {
            background: #e4f0f6;
        }

        .member-option .avatar {
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

        .date-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            font-family: inherit;
            font-size: 14px;
        }

        .date-input:focus {
            outline: none;
            border-color: #0079bf;
        }

        .tags-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            padding: 8px;
            background: white;
            border: 1px solid #dfe1e6;
            border-radius: 3px;
            min-height: 40px;
            cursor: text;
        }

        .tags-input-container:focus-within {
            border-color: #0079bf;
        }

        .tag-item {
            background: #091e420a;
            color: #172b4d;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .tag-item .remove {
            cursor: pointer;
            color: #6b778c;
            font-weight: bold;
        }

        .tag-item .remove:hover {
            color: #172b4d;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 100px;
            font-family: inherit;
            font-size: 14px;
        }

        .assigned-members {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .assigned-member {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: white;
            position: relative;
        }

        .assigned-member .remove-member {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 16px;
            height: 16px;
            background: #eb5a46;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 10px;
            border: 2px solid #f4f5f7;
        }

        .assigned-member:hover .remove-member {
            display: flex;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 12px;
        }

        .empty-state p {
            font-size: 16px;
            opacity: 0.8;
            margin-bottom: 20px;
        }

        .empty-state .btn-primary {
            font-size: 16px;
            padding: 10px 20px;
        }

        /* Confirm dialog */
        .confirm-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 3000;
        }

        .confirm-dialog.active {
            display: flex;
        }

        .confirm-content {
            background: white;
            border-radius: 8px;
            padding: 24px;
            max-width: 400px;
            animation: slideUp 0.2s ease-out;
        }

        .confirm-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #172b4d;
        }

        .confirm-message {
            font-size: 14px;
            color: #5e6c84;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .confirm-buttons {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-danger {
            background: #eb5a46;
            color: white;
        }

        .btn-danger:hover {
            background: #cf513d;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Error state */
        .error-message {
            background: #ffeae8;
            color: #c9372c;
            padding: 12px;
            border-radius: 4px;
            margin: 20px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-body {
                grid-template-columns: 1fr;
            }
            
            .modal-sidebar {
                padding-top: 0;
                border-top: 1px solid #e4e6ea;
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-content">
            <div class="loading-logo">T</div>
            <h2>Carregando...</h2>
            <div class="loading-spinner"></div>
        </div>
    </div>

    <nav class="navbar">
        <div class="navbar-left">
            <h1>
                <div class="logo">T</div>
                Trello Clone
            </h1>
            <button class="boards-menu-btn" onclick="toggleBoardsMenu()">
                📋 Quadros
                <span>▼</span>
            </button>
        </div>
        <div class="navbar-right">
            <div class="current-user">
                <div class="user-avatar-small" id="userAvatar" style="background-color: <?php echo htmlspecialchars($user['color']); ?>"><?php echo htmlspecialchars($user['initials']); ?></div>
                <span id="userName"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></span>
            </div>
            <button class="logout-btn" onclick="logout()">Sair</button>
        </div>
    </nav>

    <div class="boards-menu" id="boardsMenu">
        <div class="boards-menu-header">
            <span>Seus Quadros</span>
            <span class="boards-menu-close" onclick="toggleBoardsMenu()">×</span>
        </div>
        <div class="boards-list" id="boardsList"></div>
        <button class="create-board-btn" onclick="showCreateBoardModal()">
            + Criar novo quadro
        </button>
    </div>

    <div class="create-board-modal" id="createBoardModal">
        <div class="create-board-content">
            <div class="create-board-header">
                <span>Criar Quadro</span>
                <span class="boards-menu-close" onclick="hideCreateBoardModal()">×</span>
            </div>
            <div class="form-group">
                <label class="form-label">Título do Quadro</label>
                <input type="text" class="form-input" id="newBoardTitle" placeholder="Digite o título do quadro..." autofocus>
            </div>
            <div class="form-group">
                <label class="form-label">Cor do Fundo</label>
                <div class="color-options" id="colorOptions">
                    <div class="color-option selected" data-color="#0079bf" style="background: #0079bf"></div>
                    <div class="color-option" data-color="#d29034" style="background: #d29034"></div>
                    <div class="color-option" data-color="#519839" style="background: #519839"></div>
                    <div class="color-option" data-color="#b04632" style="background: #b04632"></div>
                    <div class="color-option" data-color="#89609e" style="background: #89609e"></div>
                    <div class="color-option" data-color="#cd5a91" style="background: #cd5a91"></div>
                    <div class="color-option" data-color="#4bbf6b" style="background: #4bbf6b"></div>
                    <div class="color-option" data-color="#00aecc" style="background: #00aecc"></div>
                    <div class="color-option" data-color="#838c91" style="background: #838c91"></div>
                    <div class="color-option" data-color="#172b4d" style="background: #172b4d"></div>
                </div>
            </div>
            <button class="btn btn-primary" onclick="createBoard()">Criar Quadro</button>
        </div>
    </div>

    <div id="boardContent"></div>

    <!-- Members Management Modal -->
    <div class="members-modal" id="membersModal">
        <div class="members-modal-content">
            <div class="members-modal-header">
                <h2 class="members-modal-title">Gerenciar Membros do Quadro</h2>
                <span class="modal-close" onclick="closeMembersModal()">×</span>
            </div>
            <div class="members-modal-body" id="membersModalBody">
                <!-- Members list will be rendered here -->
            </div>
        </div>
    </div>

    <!-- Confirm Dialog -->
    <div class="confirm-dialog" id="confirmDialog">
        <div class="confirm-content">
            <div class="confirm-title" id="confirmTitle">Confirmar exclusão</div>
            <div class="confirm-message" id="confirmMessage">Tem certeza que deseja excluir?</div>
            <div class="confirm-buttons">
                <button class="btn btn-cancel" onclick="hideConfirmDialog()">Cancelar</button>
                <button class="btn btn-danger" id="confirmButton">Excluir</button>
            </div>
        </div>
    </div>

    <div class="modal" id="cardModal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <div class="card-detail-title" id="modalCardTitle" contenteditable="true">Título do cartão</div>
            </div>
            <div class="modal-body">
                <div class="modal-main">
                    <div class="card-section">
                        <h3>Descrição</h3>
                        <textarea class="description-input" id="cardDescription" placeholder="Adicione uma descrição mais detalhada..."></textarea>
                    </div>
                </div>
                <div class="modal-sidebar">
                    <div class="sidebar-section">
                        <h4>Adicionar ao cartão</h4>
                        <button class="sidebar-button" onclick="toggleMembersSelector()">
                            👤 Membros
                        </button>
                        <div class="members-selector" id="membersSelector"></div>
                        <div class="assigned-members" id="assignedMembers"></div>
                    </div>
                    
                    <div class="sidebar-section">
                        <h4>Data de término</h4>
                        <input type="datetime-local" class="date-input" id="dueDateInput" onchange="updateDueDate()">
                    </div>
                    
                    <div class="sidebar-section">
                        <h4>Tags</h4>
                        <div class="tags-input-container" onclick="focusTagInput()">
                            <div id="tagsList"></div>
                            <input type="text" class="tag-input" id="tagInput" placeholder="Adicionar tag..." onkeydown="handleTagInput(event)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adicionando CSS para animações de notificação -->
    <style>
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>

    <script src="js/services/api.service.js"></script>
    <script src="js/services/auth.guard.js"></script>
    <script src="js/utils/notifications.js"></script>
    <script>
        // App state
        let appState = {
            currentUserId: '<?php echo htmlspecialchars($user['id']); ?>',
            currentBoardId: null,
            currentBoard: null,
            boards: [],
            users: [],
            draggedCard: null,
            sourceListId: null
        };

        // Initialize app
        async function initializeApp() {
            try {
                // Não precisamos mais validar o token aqui, pois o PHP já fez isso
                // Apenas carregamos os boards
                await loadBoards();

                // Hide loading screen
                hideLoadingScreen();
            } catch (error) {
                console.error('Initialization error:', error);
                notify.error('Erro ao inicializar aplicação');
            }
        }

        // Load boards
        async function loadBoards() {
            try {
                const response = await apiService.getBoards();
                if (response.success) {
                    appState.boards = response.boards;
                    renderBoardsList();
                    
                    // Select first board if exists
                    if (appState.boards.length > 0 && !appState.currentBoardId) {
                        await switchBoard(appState.boards[0].id);
                    } else if (appState.currentBoardId) {
                        await loadCurrentBoard();
                    } else {
                        renderEmptyState();
                    }
                }
            } catch (error) {
                console.error('Error loading boards:', error);
                notify.error('Erro ao carregar quadros');
            }
        }

        // Load current board
        async function loadCurrentBoard() {
            if (!appState.currentBoardId) {
                renderEmptyState();
                return;
            }

            showLoadingScreen();
            
            try {
                const response = await apiService.getBoard(appState.currentBoardId);
                if (response.success) {
                    appState.currentBoard = response.board;
                    renderCurrentBoard();
                }
            } catch (error) {
                console.error('Error loading board:', error);
                notify.error('Erro ao carregar quadro');
                renderEmptyState();
            } finally {
                hideLoadingScreen();
            }
        }

        // Switch board
        async function switchBoard(boardId) {
            appState.currentBoardId = boardId;
            renderBoardsList();
            await loadCurrentBoard();
            toggleBoardsMenu();
        }

        // Toggle boards menu
        function toggleBoardsMenu() {
            document.getElementById('boardsMenu').classList.toggle('active');
        }

        // Show/hide loading screen
        function showLoadingScreen() {
            document.getElementById('loadingScreen').classList.remove('hide');
        }

        function hideLoadingScreen() {
            document.getElementById('loadingScreen').classList.add('hide');
        }

        // Render boards list
        function renderBoardsList() {
            const boardsList = document.getElementById('boardsList');
            
            boardsList.innerHTML = appState.boards.map(board => {
                const isActive = board.id === appState.currentBoardId;
                const canDelete = board.role === 'admin';
                
                return `
                    <div class="board-item ${isActive ? 'active' : ''}" onclick="switchBoard('${board.id}')">
                        <div class="board-item-color" style="background: ${board.color}"></div>
                        <div class="board-item-info">
                            <div class="board-item-title">${board.title}</div>
                            <div class="board-item-lists">${board.listCount || 0} listas · ${board.cardCount || 0} cartões · ${board.role}</div>
                        </div>
                        ${canDelete ? `
                            <div class="board-delete" onclick="confirmDeleteBoard(event, '${board.id}')" title="Excluir quadro">
                                🗑️
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        // Render empty state
        function renderEmptyState() {
            const boardContent = document.getElementById('boardContent');
            boardContent.innerHTML = `
                <div class="empty-state">
                    <h2>Nenhum quadro selecionado</h2>
                    <p>Crie um novo quadro para começar</p>
                    <button class="btn btn-primary" onclick="showCreateBoardModal()">Criar Quadro</button>
                </div>
            `;
        }

        // Render current board
        function renderCurrentBoard() {
            const board = appState.currentBoard;
            const boardContent = document.getElementById('boardContent');
            
            if (!board) {
                renderEmptyState();
                return;
            }

            const canEdit = board.role === 'admin' || board.role === 'editor';
            const isAdmin = board.role === 'admin';

            // Update background color
            document.body.style.background = `linear-gradient(135deg, ${board.color} 0%, ${board.color}dd 100%)`;
            
            // Render board members
            const membersHTML = board.members.map(member => `
                <div class="board-member-avatar" 
                     style="background-color: ${member.color}" 
                     title="${member.name} (${member.role})">
                    ${member.initials}
                </div>
            `).join('');
            
            boardContent.innerHTML = `
                <div class="board-header">
                    <div class="board-header-left">
                        <div class="board-title" ${canEdit ? 'onclick="editBoardTitle()"' : ''}>${board.title}</div>
                        <span class="member-role role-${board.role}">${board.role}</span>
                    </div>
                    <div class="board-members">
                        <div class="board-members-list">
                            ${membersHTML}
                        </div>
                        ${isAdmin ? '<div class="add-member-btn" onclick="openMembersModal()" title="Gerenciar membros">+</div>' : ''}
                    </div>
                </div>
                <div class="board-container" id="board">
                    ${board.lists.map(list => createListHTML(list, canEdit)).join('')}
                    ${canEdit ? `
                        <div class="add-list" onclick="showAddListForm()">
                            <span>+ Adicionar uma lista</span>
                        </div>
                        <div class="add-list-form" id="addListForm">
                            <input type="text" class="list-name-input" id="listNameInput" placeholder="Insira o título da lista..." autofocus>
                            <div class="composer-controls">
                                <button class="btn btn-primary" onclick="addList()">Adicionar lista</button>
                                <button class="btn btn-cancel" onclick="hideAddListForm()">✕</button>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        // Create list HTML
        function createListHTML(list, canEdit) {
            return `
                <div class="list" data-list-id="${list.id}">
                    <div class="list-header">
                        <input type="text" class="list-title" value="${list.title}" 
                               ${canEdit ? `onblur="updateListTitle('${list.id}', this.value)"` : 'readonly'}>
                        ${canEdit ? `
                            <span class="list-menu" onclick="toggleListMenu('${list.id}')">⋯</span>
                            <div class="list-menu-dropdown" id="listMenu-${list.id}">
                                <div class="list-menu-item delete" onclick="confirmDeleteList('${list.id}')">
                                    Excluir lista
                                </div>
                            </div>
                        ` : ''}
                    </div>
                    <div class="cards-container" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" data-list-id="${list.id}">
                        ${list.cards.map(card => createCardHTML(card, canEdit)).join('')}
                    </div>
                    ${canEdit ? `
                        <div class="add-card" onclick="showAddCardForm('${list.id}')">
                            <span>+ Adicionar um cartão</span>
                        </div>
                        <div class="add-card-form" id="addCardForm-${list.id}">
                            <textarea class="card-composer" id="cardComposer-${list.id}" placeholder="Insira um título para este cartão..."></textarea>
                            <div class="composer-controls">
                                <button class="btn btn-primary" onclick="addCard('${list.id}')">Adicionar cartão</button>
                                <button class="btn btn-cancel" onclick="hideAddCardForm('${list.id}')">✕</button>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        // Create card HTML
        function createCardHTML(card, canEdit) {
            const labelsHTML = card.labels ? card.labels.map(label => 
                `<span class="label label-${label}"></span>`
            ).join('') : '';
            
            // Create badges HTML
            let badgesHTML = '';
            if (card.dueDate || (card.members && card.members.length > 0) || (card.tags && card.tags.length > 0)) {
                badgesHTML = '<div class="card-badges">';
                
                // Due date badge
                if (card.dueDate) {
                    const dueDate = new Date(card.dueDate);
                    const now = new Date();
                    const diffTime = dueDate - now;
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    let dueDateClass = '';
                    if (diffDays < 0) dueDateClass = 'overdue';
                    else if (diffDays <= 1) dueDateClass = 'due-soon';
                    
                    const dateStr = dueDate.toLocaleDateString('pt-BR', { day: 'numeric', month: 'short' });
                    badgesHTML += `<span class="badge badge-due-date ${dueDateClass}">📅 ${dateStr}</span>`;
                }
                
                // Members avatars
                if (card.members && card.members.length > 0) {
                    badgesHTML += '<div class="card-members">';
                    card.members.forEach(member => {
                        badgesHTML += `<div class="member-avatar" style="background-color: ${member.color}" title="${member.name}">${member.initials}</div>`;
                    });
                    badgesHTML += '</div>';
                }
                
                badgesHTML += '</div>';
            }
            
            // Tags HTML
            let tagsHTML = '';
            if (card.tags && card.tags.length > 0) {
                tagsHTML = '<div class="card-tags">';
                card.tags.forEach(tag => {
                    tagsHTML += `<span class="tag">#${tag}</span>`;
                });
                tagsHTML += '</div>';
            }
            
            return `
                <div class="card" draggable="${canEdit ? 'true' : 'false'}" data-card-id="${card.id}" 
                     ${canEdit ? `ondragstart="handleDragStart(event)" ondragend="handleDragEnd(event)"` : ''}
                     onclick="openCardModal('${card.id}')">
                    ${canEdit ? `
                        <div class="card-delete" onclick="deleteCard(event, '${card.id}')" title="Excluir cartão">
                            ×
                        </div>
                    ` : ''}
                    ${labelsHTML ? `<div class="card-labels">${labelsHTML}</div>` : ''}
                    ${card.title}
                    ${tagsHTML}
                    ${badgesHTML}
                </div>
            `;
        }

        // Board operations
        function showCreateBoardModal() {
            document.getElementById('createBoardModal').classList.add('active');
            document.getElementById('newBoardTitle').focus();
            toggleBoardsMenu();
        }

        function hideCreateBoardModal() {
            document.getElementById('createBoardModal').classList.remove('active');
            document.getElementById('newBoardTitle').value = '';
            
            // Reset color selection
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector('.color-option[data-color="#0079bf"]').classList.add('selected');
        }

        async function createBoard() {
            const title = document.getElementById('newBoardTitle').value.trim();
            const selectedColor = document.querySelector('.color-option.selected').dataset.color;
            
            if (!title) {
                notify.error('Por favor, insira um título para o quadro');
                return;
            }

            try {
                const response = await apiService.createBoard(title, selectedColor);
                if (response.success) {
                    notify.success('Quadro criado com sucesso!');
                    hideCreateBoardModal();
                    await loadBoards();
                    await switchBoard(response.boardId);
                }
            } catch (error) {
                console.error('Error creating board:', error);
                notify.error('Erro ao criar quadro');
            }
        }

        async function editBoardTitle() {
            const board = appState.currentBoard;
            if (!board || (board.role !== 'admin' && board.role !== 'editor')) return;
            
            const newTitle = prompt('Novo título do quadro:', board.title);
            
            if (newTitle && newTitle.trim() && newTitle !== board.title) {
                try {
                    const response = await apiService.updateBoard(board.id, { 
                        title: newTitle.trim(),
                        color: board.color 
                    });
                    
                    if (response.success) {
                        notify.success('Título atualizado!');
                        await loadBoards();
                        await loadCurrentBoard();
                    }
                } catch (error) {
                    console.error('Error updating board:', error);
                    notify.error('Erro ao atualizar título');
                }
            }
        }

        function confirmDeleteBoard(event, boardId) {
            event.stopPropagation();
            
            const board = appState.boards.find(b => b.id === boardId);
            if (!board || board.role !== 'admin') {
                notify.error('Apenas administradores podem excluir quadros');
                return;
            }
            
            showConfirmDialog(
                'Excluir quadro',
                `Tem certeza que deseja excluir o quadro "${board.title}" e todo o seu conteúdo?`,
                () => deleteBoard(boardId)
            );
        }

        async function deleteBoard(boardId) {
            try {
                const response = await apiService.deleteBoard(boardId);
                if (response.success) {
                    notify.success('Quadro excluído com sucesso!');
                    hideConfirmDialog();
                    
                    // If deleting current board, clear selection
                    if (appState.currentBoardId === boardId) {
                        appState.currentBoardId = null;
                        appState.currentBoard = null;
                    }
                    
                    await loadBoards();
                }
            } catch (error) {
                console.error('Error deleting board:', error);
                notify.error('Erro ao excluir quadro');
            }
        }

        // List operations
        function showAddListForm() {
            document.querySelector('.add-list').style.display = 'none';
            const form = document.getElementById('addListForm');
            form.classList.add('active');
            document.getElementById('listNameInput').focus();
        }

        function hideAddListForm() {
            document.querySelector('.add-list').style.display = 'flex';
            document.getElementById('addListForm').classList.remove('active');
            document.getElementById('listNameInput').value = '';
        }

        async function addList() {
            const title = document.getElementById('listNameInput').value.trim();
            
            if (!title) {
                notify.error('Por favor, insira um título para a lista');
                return;
            }

            try {
                const response = await apiService.createList(appState.currentBoardId, title);
                if (response.success) {
                    notify.success('Lista criada com sucesso!');
                    hideAddListForm();
                    await loadCurrentBoard();
                }
            } catch (error) {
                console.error('Error creating list:', error);
                notify.error('Erro ao criar lista');
            }
        }

        async function updateListTitle(listId, newTitle) {
            if (!newTitle.trim()) return;
            
            try {
                const response = await apiService.updateList(listId, { title: newTitle });
                if (response.success) {
                    await loadBoards(); // Update counts
                }
            } catch (error) {
                console.error('Error updating list:', error);
                notify.error('Erro ao atualizar título da lista');
            }
        }

        function toggleListMenu(listId) {
            const menu = document.getElementById(`listMenu-${listId}`);
            const allMenus = document.querySelectorAll('.list-menu-dropdown');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m.id !== `listMenu-${listId}`) {
                    m.classList.remove('active');
                }
            });
            
            menu.classList.toggle('active');
        }

        function confirmDeleteList(listId) {
            const list = appState.currentBoard.lists.find(l => l.id === listId);
            
            showConfirmDialog(
                'Excluir lista',
                `Tem certeza que deseja excluir a lista "${list.title}" e todos os seus cartões?`,
                () => deleteList(listId)
            );
        }

        async function deleteList(listId) {
            try {
                const response = await apiService.deleteList(listId);
                if (response.success) {
                    notify.success('Lista excluída com sucesso!');
                    hideConfirmDialog();
                    await loadCurrentBoard();
                    await loadBoards(); // Update counts
                }
            } catch (error) {
                console.error('Error deleting list:', error);
                notify.error('Erro ao excluir lista');
            }
        }

        // Card operations
        function showAddCardForm(listId) {
            document.querySelector(`#addCardForm-${listId}`).classList.add('active');
            document.querySelector(`#cardComposer-${listId}`).focus();
        }

        function hideAddCardForm(listId) {
            document.querySelector(`#addCardForm-${listId}`).classList.remove('active');
            document.querySelector(`#cardComposer-${listId}`).value = '';
        }

        async function addCard(listId) {
            const composer = document.querySelector(`#cardComposer-${listId}`);
            const title = composer.value.trim();
            
            if (!title) {
                notify.error('Por favor, insira um título para o cartão');
                return;
            }

            try {
                const response = await apiService.createCard(listId, title);
                if (response.success) {
                    notify.success('Cartão criado com sucesso!');
                    hideAddCardForm(listId);
                    await loadCurrentBoard();
                    await loadBoards(); // Update counts
                }
            } catch (error) {
                console.error('Error creating card:', error);
                notify.error('Erro ao criar cartão');
            }
        }

        async function deleteCard(event, cardId) {
            event.stopPropagation();
            
            try {
                const response = await apiService.deleteCard(cardId);
                if (response.success) {
                    await loadCurrentBoard();
                    await loadBoards(); // Update counts
                }
            } catch (error) {
                console.error('Error deleting card:', error);
                notify.error('Erro ao excluir cartão');
            }
        }

        // Card modal
        let currentCardId = null;

        async function openCardModal(cardId) {
            currentCardId = cardId;
            const board = appState.currentBoard;
            const canEdit = board.role === 'admin' || board.role === 'editor';
            
            try {
                const response = await apiService.getCard(cardId);
                if (response.success) {
                    const card = response.card;
                    
                    // Set card details
                    document.getElementById('modalCardTitle').textContent = card.title;
                    document.getElementById('modalCardTitle').contentEditable = canEdit;
                    document.getElementById('cardDescription').value = card.description || '';
                    document.getElementById('cardDescription').readOnly = !canEdit;
                    
                    // Set due date
                    if (card.dueDate) {
                        const date = new Date(card.dueDate);
                        const localDateTime = new Date(date.getTime() - date.getTimezoneOffset() * 60000)
                            .toISOString()
                            .slice(0, 16);
                        document.getElementById('dueDateInput').value = localDateTime;
                    } else {
                        document.getElementById('dueDateInput').value = '';
                    }
                    document.getElementById('dueDateInput').disabled = !canEdit;
                    
                    // Show/hide edit controls based on permissions
                    document.querySelectorAll('.sidebar-button').forEach(btn => {
                        btn.style.display = canEdit ? 'flex' : 'none';
                    });
                    
                    document.getElementById('tagInput').style.display = canEdit ? 'block' : 'none';
                    
                    // Render members selector
                    renderMembersSelector(card);
                    
                    // Render tags
                    renderTags(card);
                    
                    // Show modal
                    document.getElementById('cardModal').classList.add('active');
                }
            } catch (error) {
                console.error('Error loading card:', error);
                notify.error('Erro ao carregar cartão');
            }
        }

        async function closeModal() {
            // Save changes before closing
            const board = appState.currentBoard;
            const canEdit = board.role === 'admin' || board.role === 'editor';
            
            if (currentCardId && canEdit) {
                const title = document.getElementById('modalCardTitle').textContent;
                const description = document.getElementById('cardDescription').value;
                
                try {
                    await apiService.updateCard(currentCardId, { title, description });
                    await loadCurrentBoard();
                } catch (error) {
                    console.error('Error updating card:', error);
                }
            }
            
            document.getElementById('cardModal').classList.remove('active');
            document.getElementById('membersSelector').classList.remove('active');
            currentCardId = null;
        }

        // Members management
        function openMembersModal() {
            renderMembersModal();
            document.getElementById('membersModal').classList.add('active');
        }

        function closeMembersModal() {
            document.getElementById('membersModal').classList.remove('active');
        }

        function renderMembersModal() {
            const board = appState.currentBoard;
            const modalBody = document.getElementById('membersModalBody');
            
            // Current members
            const membersHTML = board.members.map(member => {
                const isCurrentUser = member.userId === appState.currentUserId;
                
                return `
                    <div class="member-row">
                        <div class="member-info">
                            <div class="avatar" style="background-color: ${member.color}">
                                ${member.initials}
                            </div>
                            <div class="member-details">
                                <div class="member-name">${member.name}</div>
                                <div class="member-email">${member.email}</div>
                            </div>
                        </div>
                        <div>
                            <select class="role-selector" onchange="updateMemberRole('${member.userId}', this.value)" 
                                    ${isCurrentUser ? 'disabled' : ''}>
                                <option value="admin" ${member.role === 'admin' ? 'selected' : ''}>Admin</option>
                                <option value="editor" ${member.role === 'editor' ? 'selected' : ''}>Editor</option>
                                <option value="reader" ${member.role === 'reader' ? 'selected' : ''}>Leitor</option>
                            </select>
                            ${!isCurrentUser ? `
                                <button class="remove-member-btn" onclick="removeBoardMember('${member.userId}')">
                                    Remover
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            
            modalBody.innerHTML = `
                <div>
                    <h3>Membros do Quadro</h3>
                    ${membersHTML}
                </div>
                <div class="add-member-section">
                    <h3>Adicionar Membro</h3>
                    <div class="add-member-form" style="position: relative;">
                        <input type="text" class="user-search-input" 
                               id="userSearchInput" 
                               placeholder="Buscar usuário por nome ou email..."
                               onkeyup="searchUsers(this.value)">
                        <div class="user-search-results" id="userSearchResults"></div>
                        <select class="role-selector" id="newMemberRole">
                            <option value="reader">Leitor</option>
                            <option value="editor">Editor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
            `;
        }

        let searchTimeout;
        async function searchUsers(query) {
            clearTimeout(searchTimeout);
            const resultsDiv = document.getElementById('userSearchResults');
            
            if (query.length < 2) {
                resultsDiv.classList.remove('active');
                return;
            }
            
            searchTimeout = setTimeout(async () => {
                try {
                    const response = await apiService.searchUsers(query);
                    if (response.success) {
                        const board = appState.currentBoard;
                        const memberIds = board.members.map(m => m.userId);
                        const availableUsers = response.users.filter(u => !memberIds.includes(u.id));
                        
                        if (availableUsers.length > 0) {
                            resultsDiv.innerHTML = availableUsers.map(user => `
                                <div class="user-result-item" onclick="selectUserToAdd('${user.id}', '${user.name}')">
                                    <strong>${user.name}</strong> - ${user.email}
                                </div>
                            `).join('');
                            resultsDiv.classList.add('active');
                        } else {
                            resultsDiv.innerHTML = '<div class="user-result-item">Nenhum usuário encontrado</div>';
                            resultsDiv.classList.add('active');
                        }
                    }
                } catch (error) {
                    console.error('Error searching users:', error);
                }
            }, 300);
        }

        let selectedUserId = null;
        function selectUserToAdd(userId, userName) {
            selectedUserId = userId;
            document.getElementById('userSearchInput').value = userName;
            document.getElementById('userSearchResults').classList.remove('active');
            addBoardMember();
        }

        async function addBoardMember() {
            if (!selectedUserId) {
                notify.error('Por favor, selecione um usuário');
                return;
            }
            
            const role = document.getElementById('newMemberRole').value;
            
            try {
                const response = await apiService.addBoardMember(appState.currentBoardId, selectedUserId, role);
                if (response.success) {
                    notify.success('Membro adicionado com sucesso!');
                    selectedUserId = null;
                    await loadCurrentBoard();
                    renderMembersModal();
                }
            } catch (error) {
                console.error('Error adding member:', error);
                notify.error('Erro ao adicionar membro');
            }
        }

        async function updateMemberRole(userId, newRole) {
            try {
                const response = await apiService.updateBoardMember(appState.currentBoardId, userId, newRole);
                if (response.success) {
                    notify.success('Permissão atualizada!');
                    await loadCurrentBoard();
                    renderMembersModal();
                }
            } catch (error) {
                console.error('Error updating member role:', error);
                notify.error('Erro ao atualizar permissão');
            }
        }

        async function removeBoardMember(userId) {
            try {
                const response = await apiService.removeBoardMember(appState.currentBoardId, userId);
                if (response.success) {
                    notify.success('Membro removido!');
                    await loadCurrentBoard();
                    renderMembersModal();
                }
            } catch (error) {
                console.error('Error removing member:', error);
                notify.error('Erro ao remover membro');
            }
        }

        // Card members
        function toggleMembersSelector() {
            const selector = document.getElementById('membersSelector');
            selector.classList.toggle('active');
        }

        function renderMembersSelector(card) {
            const selector = document.getElementById('membersSelector');
            const assignedMembers = document.getElementById('assignedMembers');
            const board = appState.currentBoard;
            const canEdit = board.role === 'admin' || board.role === 'editor';
            
            if (!canEdit) {
                selector.innerHTML = '';
                renderAssignedMembers(card);
                return;
            }
            
            // Render member options (board members only)
            selector.innerHTML = board.members.map(member => {
                const isSelected = card.members && card.members.find(m => m.id === member.userId);
                return `
                    <div class="member-option ${isSelected ? 'selected' : ''}" 
                         onclick="toggleMember('${member.userId}')">
                        <div class="avatar" style="background-color: ${member.color}">${member.initials}</div>
                        <div>${member.name}</div>
                    </div>
                `;
            }).join('');
            
            renderAssignedMembers(card);
        }

        function renderAssignedMembers(card) {
            const assignedMembers = document.getElementById('assignedMembers');
            const board = appState.currentBoard;
            const canEdit = board.role === 'admin' || board.role === 'editor';
            
            if (card.members && card.members.length > 0) {
                assignedMembers.innerHTML = card.members.map(member => `
                    <div class="assigned-member" style="background-color: ${member.color}" title="${member.name}">
                        ${member.initials}
                        ${canEdit ? `<div class="remove-member" onclick="removeMember('${member.id}')">×</div>` : ''}
                    </div>
                `).join('');
            } else {
                assignedMembers.innerHTML = '';
            }
        }

        async function toggleMember(userId) {
            try {
                // Check if member is already assigned
                const response = await apiService.getCard(currentCardId);
                const card = response.card;
                const isAssigned = card.members && card.members.find(m => m.id === userId);
                
                if (isAssigned) {
                    await apiService.removeCardMember(currentCardId, userId);
                } else {
                    await apiService.addCardMember(currentCardId, userId);
                }
                
                // Reload card data
                await openCardModal(currentCardId);
                await loadCurrentBoard();
            } catch (error) {
                console.error('Error toggling member:', error);
                notify.error('Erro ao atualizar membro');
            }
        }

        async function removeMember(userId) {
            event.stopPropagation();
            await toggleMember(userId);
        }

        // Due date
        async function updateDueDate() {
            const dueDateInput = document.getElementById('dueDateInput');
            
            try {
                const dueDate = dueDateInput.value ? new Date(dueDateInput.value).toISOString() : null;
                await apiService.updateCard(currentCardId, { dueDate });
                await loadCurrentBoard();
            } catch (error) {
                console.error('Error updating due date:', error);
                notify.error('Erro ao atualizar data');
            }
        }

        // Tags
        function renderTags(card) {
            const tagsList = document.getElementById('tagsList');
            const board = appState.currentBoard;
            const canEdit = board.role === 'admin' || board.role === 'editor';
            
            if (card.tags && card.tags.length > 0) {
                tagsList.innerHTML = card.tags.map(tag => `
                    <div class="tag-item">
                        ${tag}
                        ${canEdit ? `<span class="remove" onclick="removeTag('${tag}')">×</span>` : ''}
                    </div>
                `).join('');
            } else {
                tagsList.innerHTML = '';
            }
        }

        async function handleTagInput(event) {
            if (event.key === 'Enter' && event.target.value.trim()) {
                await addTag(event.target.value.trim());
                event.target.value = '';
            }
        }

        async function addTag(tag) {
            try {
                await apiService.addCardTag(currentCardId, tag);
                const response = await apiService.getCard(currentCardId);
                renderTags(response.card);
                await loadCurrentBoard();
            } catch (error) {
                console.error('Error adding tag:', error);
                notify.error('Erro ao adicionar tag');
            }
        }

        async function removeTag(tag) {
            try {
                await apiService.removeCardTag(currentCardId, tag);
                const response = await apiService.getCard(currentCardId);
                renderTags(response.card);
                await loadCurrentBoard();
            } catch (error) {
                console.error('Error removing tag:', error);
                notify.error('Erro ao remover tag');
            }
        }

        function focusTagInput() {
            document.getElementById('tagInput').focus();
        }

        // Drag and drop
        function handleDragStart(e) {
            const board = appState.currentBoard;
            if (board.role !== 'admin' && board.role !== 'editor') {
                e.preventDefault();
                return;
            }
            
            appState.draggedCard = e.target;
            appState.sourceListId = e.target.closest('.cards-container').dataset.listId;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragEnd(e) {
            e.target.classList.remove('dragging');
            document.querySelectorAll('.card').forEach(card => {
                card.classList.remove('drag-over');
            });
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            const container = e.currentTarget;
            const afterElement = getDragAfterElement(container, e.clientY);
            
            document.querySelectorAll('.card').forEach(card => {
                card.classList.remove('drag-over');
            });
            
            if (afterElement && afterElement !== appState.draggedCard) {
                afterElement.classList.add('drag-over');
            }
        }

        async function handleDrop(e) {
            e.preventDefault();
            
            const targetListId = e.currentTarget.dataset.listId;
            const targetContainer = e.currentTarget;
            const afterElement = getDragAfterElement(targetContainer, e.clientY);
            
            const cardId = appState.draggedCard.dataset.cardId;
            const cards = [...targetContainer.querySelectorAll('.card:not(.dragging)')];
            const position = afterElement ? cards.indexOf(afterElement) : cards.length;
            
            try {
                await apiService.moveCard(cardId, targetListId, position);
                await loadCurrentBoard();
                await loadBoards(); // Update counts
            } catch (error) {
                console.error('Error moving card:', error);
                notify.error('Erro ao mover cartão');
            }
        }

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.card:not(.dragging)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Confirm dialog
        function showConfirmDialog(title, message, onConfirm) {
            const dialog = document.getElementById('confirmDialog');
            document.getElementById('confirmTitle').textContent = title;
            document.getElementById('confirmMessage').textContent = message;
            
            const confirmButton = document.getElementById('confirmButton');
            confirmButton.onclick = onConfirm;
            
            dialog.classList.add('active');
        }

        function hideConfirmDialog() {
            document.getElementById('confirmDialog').classList.remove('active');
        }

        // Logout
        function logout() {
            apiService.logout();
        }

        // Color selection
        document.querySelectorAll('.color-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Close any open forms or modals
                document.querySelectorAll('.add-card-form.active').forEach(form => {
                    form.classList.remove('active');
                });
                hideAddListForm();
                closeModal();
                hideCreateBoardModal();
                hideConfirmDialog();
                closeMembersModal();
                document.getElementById('boardsMenu').classList.remove('active');
                document.querySelectorAll('.list-menu-dropdown').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
            
            if (e.key === 'Enter') {
                if (e.target.id === 'listNameInput') {
                    addList();
                } else if (e.target.id === 'newBoardTitle') {
                    createBoard();
                }
            }
        });

        // Click outside modals to close
        document.getElementById('cardModal').addEventListener('click', (e) => {
            if (e.target.id === 'cardModal') {
                closeModal();
            }
        });

        document.getElementById('createBoardModal').addEventListener('click', (e) => {
            if (e.target.id === 'createBoardModal') {
                hideCreateBoardModal();
            }
        });

        document.getElementById('membersModal').addEventListener('click', (e) => {
            if (e.target.id === 'membersModal') {
                closeMembersModal();
            }
        });

        // Close menus when clicking outside
        document.addEventListener('click', (e) => {
            const boardsMenu = document.getElementById('boardsMenu');
            const boardsMenuBtn = document.querySelector('.boards-menu-btn');
            
            if (!boardsMenu.contains(e.target) && !boardsMenuBtn.contains(e.target)) {
                boardsMenu.classList.remove('active');
            }
            
            // Close list menus when clicking outside
            if (!e.target.closest('.list-menu') && !e.target.closest('.list-menu-dropdown')) {
                document.querySelectorAll('.list-menu-dropdown').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
            
            // Close user search results
            if (!e.target.closest('.user-search-input') && !e.target.closest('.user-search-results')) {
                document.getElementById('userSearchResults')?.classList.remove('active');
            }
        });

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', () => {
            initializeApp();
        });
    </script>
</body>
</html>