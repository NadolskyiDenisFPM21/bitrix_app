<?php
// Proxy API calls to Bitrix24 to avoid CORS issues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');

    $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6IjQ4OGY0YWFiLTFiZWUtNGNiNS1hNjYyLTlhZTUxZDkwNGQ1OSJ9.eyJtY3AiOiJhaWFzc2lzdGFudC5iaXRyaXhfbWNwIiwic3ViIjoiNjE1ODg4NTFcL2IyNC41YWE2NWJlY2M3ODRjMC41NDkwMjY5MSIsInB1aWQiOjExMjgsImF1ZCI6InBldHJpa2l2a2EuYml0cml4MjQuZXUiLCJhZG1pbiI6dHJ1ZSwiZXhwIjoxNzgxMTg2MTU2LCJpYXQiOjE3ODAzMjIxNTYsImlzcyI6Imh0dHBzOlwvXC93d3cuYml0cml4MjQubmV0In0.JlK58luDdwYqskr_CbrMJHfDAC8wn8cFTig6Asec1T70uXFLkd2iy67W1b7ht--8XTG0hbSC1mCjwF0XNSI9Sgw2Zb4qMG7HC0CizdUOKU73CiRi5j80myISI4sJvIng5RQ-QN5UpgZO3BYoYiMlBS-NHkHetDDm6PH6TBEUXTHVRDZLDll0rBeiQUFw8FuK99DYfBC0T_fpHVtswYHf7QPGbGqzj_Jo-WmMsyBciYInoGpUqGCya3uYfOSVFyiCEo9LzmtGJcUWUxsh9nNEb5jeGwDQdugKiH0iJ6-yEdNSixE4tGYogPoHKh8zYGaYt1r4RHIQSFFaFfd3ECzHUA';
    $baseUrl = 'https://petrikivka.bitrix24.eu/rest/';

    $action = $_GET['action'];
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    $allowedMethods = ['bizproc.task.list', 'bizproc.task.complete', 'user.current'];
    if (!in_array($action, $allowedMethods)) {
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $baseUrl . $action,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo json_encode(['error' => $error]);
        exit;
    }

    http_response_code($httpCode);
    echo $response;
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Завдання автоматизацій — Bitrix24</title>
    <style>
        /* Bitrix24 design system */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --b24-blue: #2fc6f6;
            --b24-blue-dark: #0091d5;
            --b24-blue-hover: #00a2e8;
            --b24-green: #7bc63e;
            --b24-red: #e34747;
            --b24-orange: #f5a623;
            --b24-gray-1: #f5f5f5;
            --b24-gray-2: #eaeaea;
            --b24-gray-3: #d4d4d4;
            --b24-gray-4: #9b9b9b;
            --b24-text: #333333;
            --b24-text-light: #6a6a6a;
            --b24-border: #dfe0e2;
            --b24-white: #ffffff;
            --b24-sidebar: #1d2733;
            --b24-sidebar-hover: #283547;
            --b24-header-h: 56px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 2px 8px rgba(0,0,0,0.15);
            --radius: 4px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            color: var(--b24-text);
            background: var(--b24-gray-1);
            min-height: 100vh;
        }

        /* Header */
        .b24-header {
            height: var(--b24-header-h);
            background: var(--b24-white);
            border-bottom: 1px solid var(--b24-border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .b24-header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 16px;
            color: var(--b24-text);
            text-decoration: none;
        }
        .b24-header-logo svg { color: var(--b24-blue-dark); }
        .b24-header-divider {
            width: 1px;
            height: 24px;
            background: var(--b24-border);
        }
        .b24-header-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--b24-text);
        }
        .b24-header-spacer { flex: 1; }
        .b24-user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--b24-text-light);
            font-size: 13px;
        }
        .b24-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--b24-blue-dark);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }

        /* Main layout */
        .b24-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 24px;
        }

        /* Page title block */
        .b24-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .b24-page-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--b24-text);
        }
        .b24-page-subtitle {
            font-size: 13px;
            color: var(--b24-text-light);
            margin-top: 2px;
        }

        /* Stats bar */
        .b24-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
        }
        .b24-stat-card {
            background: var(--b24-white);
            border: 1px solid var(--b24-border);
            border-radius: var(--radius);
            padding: 14px 20px;
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }
        .b24-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .b24-stat-icon.blue { background: #e8f7fd; color: var(--b24-blue-dark); }
        .b24-stat-icon.green { background: #edf7e4; color: var(--b24-green); }
        .b24-stat-icon.orange { background: #fef6e7; color: var(--b24-orange); }
        .b24-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--b24-text);
            line-height: 1;
        }
        .b24-stat-label {
            font-size: 12px;
            color: var(--b24-text-light);
            margin-top: 2px;
        }

        /* Toolbar */
        .b24-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .b24-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            text-decoration: none;
            white-space: nowrap;
        }
        .b24-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .b24-btn-primary {
            background: var(--b24-blue-dark);
            color: #fff;
            border-color: var(--b24-blue-dark);
        }
        .b24-btn-primary:hover:not(:disabled) { background: var(--b24-blue-hover); border-color: var(--b24-blue-hover); }
        .b24-btn-default {
            background: var(--b24-white);
            color: var(--b24-text);
            border-color: var(--b24-border);
        }
        .b24-btn-default:hover:not(:disabled) { background: var(--b24-gray-1); border-color: var(--b24-gray-3); }
        .b24-btn-success {
            background: var(--b24-green);
            color: #fff;
            border-color: var(--b24-green);
        }
        .b24-btn-success:hover:not(:disabled) { background: #6bb330; border-color: #6bb330; }
        .b24-btn-danger {
            background: var(--b24-red);
            color: #fff;
            border-color: var(--b24-red);
        }
        .b24-btn-sm { padding: 4px 10px; font-size: 12px; }

        /* Search */
        .b24-search {
            position: relative;
            margin-left: auto;
        }
        .b24-search input {
            padding: 7px 12px 7px 34px;
            border: 1px solid var(--b24-border);
            border-radius: var(--radius);
            font-size: 13px;
            width: 220px;
            outline: none;
            transition: border-color 0.15s;
            background: var(--b24-white);
        }
        .b24-search input:focus { border-color: var(--b24-blue-dark); }
        .b24-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--b24-gray-4);
        }

        /* Table */
        .b24-card {
            background: var(--b24-white);
            border: 1px solid var(--b24-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .b24-table {
            width: 100%;
            border-collapse: collapse;
        }
        .b24-table thead th {
            background: var(--b24-gray-1);
            border-bottom: 2px solid var(--b24-border);
            padding: 10px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--b24-text-light);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
        }
        .b24-table thead th:hover { color: var(--b24-text); }
        .b24-table thead th.sort-asc::after { content: ' ↑'; }
        .b24-table thead th.sort-desc::after { content: ' ↓'; }
        .b24-table tbody tr {
            border-bottom: 1px solid var(--b24-gray-2);
            transition: background 0.1s;
        }
        .b24-table tbody tr:last-child { border-bottom: none; }
        .b24-table tbody tr:hover { background: #f0f8ff; }
        .b24-table tbody td {
            padding: 12px 16px;
            font-size: 13px;
            vertical-align: middle;
        }

        /* Task name */
        .task-name {
            font-weight: 500;
            color: var(--b24-text);
            max-width: 260px;
        }
        .task-description {
            font-size: 12px;
            color: var(--b24-text-light);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 260px;
        }

        /* Status badge */
        .b24-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .b24-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-pending .b24-badge-dot { background: var(--b24-orange); }
        .badge-running { background: #cfe2ff; color: #0a58ca; }
        .badge-running .b24-badge-dot { background: var(--b24-blue-dark); }
        .badge-completed { background: #d1e7dd; color: #0a5e3c; }
        .badge-completed .b24-badge-dot { background: var(--b24-green); }
        .badge-error { background: #f8d7da; color: #842029; }
        .badge-error .b24-badge-dot { background: var(--b24-red); }

        /* Priority */
        .priority-high { color: var(--b24-red); font-weight: 600; }
        .priority-medium { color: var(--b24-orange); font-weight: 600; }
        .priority-low { color: var(--b24-gray-4); }

        /* Workflow type icon */
        .b24-type-icon {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--b24-text-light);
            font-size: 12px;
        }
        .b24-type-icon svg { flex-shrink: 0; }

        /* Actions column */
        .b24-actions { display: flex; gap: 6px; align-items: center; }

        /* Empty state */
        .b24-empty {
            padding: 60px 24px;
            text-align: center;
        }
        .b24-empty-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.4; }
        .b24-empty-title { font-size: 16px; font-weight: 600; color: var(--b24-text); margin-bottom: 6px; }
        .b24-empty-text { font-size: 13px; color: var(--b24-text-light); }

        /* Loading */
        .b24-loading {
            padding: 60px 24px;
            text-align: center;
        }
        .b24-spinner {
            display: inline-block;
            width: 36px;
            height: 36px;
            border: 3px solid var(--b24-gray-2);
            border-top-color: var(--b24-blue-dark);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .b24-loading-text { margin-top: 12px; font-size: 13px; color: var(--b24-text-light); }

        /* Toast notifications */
        #toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .b24-toast {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius);
            background: var(--b24-white);
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            font-size: 13px;
            min-width: 260px;
            max-width: 400px;
            animation: slideIn 0.25s ease;
            border-left: 4px solid;
        }
        .b24-toast.success { border-color: var(--b24-green); }
        .b24-toast.error { border-color: var(--b24-red); }
        .b24-toast.info { border-color: var(--b24-blue-dark); }
        .b24-toast-icon { font-size: 16px; }
        .b24-toast-text { flex: 1; }
        .b24-toast-close { cursor: pointer; color: var(--b24-gray-4); font-size: 16px; line-height: 1; background: none; border: none; padding: 0; }
        @keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: none; opacity: 1; } }
        @keyframes fadeOut { to { opacity: 0; transform: translateX(20px); } }

        /* Modal */
        .b24-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .b24-modal {
            background: var(--b24-white);
            border-radius: 6px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
            width: 540px;
            max-width: 95vw;
            max-height: 90vh;
            overflow-y: auto;
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn { from { transform: translateY(-20px); opacity: 0; } to { transform: none; opacity: 1; } }
        .b24-modal-header {
            padding: 18px 24px 14px;
            border-bottom: 1px solid var(--b24-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .b24-modal-title { font-size: 16px; font-weight: 700; }
        .b24-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--b24-gray-4);
            line-height: 1;
            padding: 0;
        }
        .b24-modal-close:hover { color: var(--b24-text); }
        .b24-modal-body { padding: 20px 24px; }
        .b24-modal-footer {
            padding: 14px 24px;
            border-top: 1px solid var(--b24-border);
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        /* Form fields */
        .b24-field { margin-bottom: 16px; }
        .b24-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--b24-text-light);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .b24-label.required::after { content: ' *'; color: var(--b24-red); }
        .b24-input, .b24-textarea, .b24-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--b24-border);
            border-radius: var(--radius);
            font-size: 13px;
            color: var(--b24-text);
            background: var(--b24-white);
            outline: none;
            transition: border-color 0.15s;
            font-family: inherit;
        }
        .b24-input:focus, .b24-textarea:focus, .b24-select:focus { border-color: var(--b24-blue-dark); box-shadow: 0 0 0 2px rgba(0,145,213,0.12); }
        .b24-textarea { resize: vertical; min-height: 80px; }

        /* Task detail panel */
        .b24-detail {
            background: var(--b24-gray-1);
            border: 1px solid var(--b24-border);
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        .b24-detail-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .b24-detail-row:last-child { margin-bottom: 0; }
        .b24-detail-key { color: var(--b24-text-light); min-width: 120px; flex-shrink: 0; }
        .b24-detail-val { color: var(--b24-text); font-weight: 500; }

        /* Pagination */
        .b24-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-top: 1px solid var(--b24-border);
            background: var(--b24-white);
        }
        .b24-pagination-info { font-size: 12px; color: var(--b24-text-light); }
        .b24-pagination-btns { display: flex; gap: 4px; }
        .b24-page-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--b24-border);
            background: var(--b24-white);
            border-radius: var(--radius);
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--b24-text);
            transition: all 0.15s;
        }
        .b24-page-btn:hover:not(:disabled) { background: var(--b24-gray-1); }
        .b24-page-btn.active { background: var(--b24-blue-dark); color: #fff; border-color: var(--b24-blue-dark); }
        .b24-page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        /* Filter tabs */
        .b24-tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--b24-border);
            margin-bottom: 16px;
        }
        .b24-tab {
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            color: var(--b24-text-light);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .b24-tab:hover { color: var(--b24-text); }
        .b24-tab.active { color: var(--b24-blue-dark); border-bottom-color: var(--b24-blue-dark); font-weight: 600; }
        .b24-tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--b24-gray-2);
            color: var(--b24-text-light);
            border-radius: 10px;
            padding: 1px 6px;
            font-size: 11px;
            margin-left: 4px;
        }
        .b24-tab.active .b24-tab-count { background: var(--b24-blue-dark); color: #fff; }

        /* Responsive */
        @media (max-width: 768px) {
            .b24-main { padding: 16px; }
            .b24-stats { flex-direction: column; }
            .b24-table thead { display: none; }
            .b24-table tr { display: block; padding: 12px 0; border-bottom: 1px solid var(--b24-gray-2); }
            .b24-table td { display: flex; justify-content: space-between; padding: 4px 16px; border: none; }
            .b24-table td::before { content: attr(data-label); color: var(--b24-text-light); font-size: 12px; }
            .b24-search input { width: 160px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="b24-header">
    <a href="#" class="b24-header-logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
        </svg>
        Bitrix24
    </a>
    <div class="b24-header-divider"></div>
    <span class="b24-header-title">Завдання автоматизацій</span>
    <div class="b24-header-spacer"></div>
    <div class="b24-user-badge">
        <div class="b24-avatar" id="userAvatar">–</div>
        <span id="userName">Завантаження...</span>
    </div>
</header>

<!-- Main content -->
<main class="b24-main">
    <div class="b24-page-header">
        <div>
            <h1 class="b24-page-title">Мої завдання автоматизацій</h1>
            <p class="b24-page-subtitle">Задачі бізнес-процесів що очікують на виконання</p>
        </div>
        <button class="b24-btn b24-btn-default" onclick="loadTasks()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M23 4v6h-6M1 20v-6h6"/>
                <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
            </svg>
            Оновити
        </button>
    </div>

    <!-- Stats -->
    <div class="b24-stats">
        <div class="b24-stat-card">
            <div class="b24-stat-icon blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </div>
            <div>
                <div class="b24-stat-value" id="statTotal">—</div>
                <div class="b24-stat-label">Всього завдань</div>
            </div>
        </div>
        <div class="b24-stat-card">
            <div class="b24-stat-icon orange">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div>
                <div class="b24-stat-value" id="statPending">—</div>
                <div class="b24-stat-label">Очікують виконання</div>
            </div>
        </div>
        <div class="b24-stat-card">
            <div class="b24-stat-icon green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div>
                <div class="b24-stat-value" id="statCompleted">—</div>
                <div class="b24-stat-label">Завершено сьогодні</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="b24-tabs">
        <div class="b24-tab active" data-filter="all" onclick="setFilter('all', this)">
            Всі <span class="b24-tab-count" id="tabAll">0</span>
        </div>
        <div class="b24-tab" data-filter="0" onclick="setFilter('0', this)">
            Очікують <span class="b24-tab-count" id="tabPending">0</span>
        </div>
        <div class="b24-tab" data-filter="2" onclick="setFilter('2', this)">
            Виконано <span class="b24-tab-count" id="tabCompleted">0</span>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="b24-toolbar">
        <span style="font-size:12px;color:var(--b24-text-light)" id="selectedInfo" class="hidden"></span>
        <div class="b24-search">
            <svg class="b24-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Пошук завдань..." oninput="renderTable()">
        </div>
    </div>

    <!-- Table -->
    <div class="b24-card">
        <div id="tableContainer">
            <div class="b24-loading">
                <div class="b24-spinner"></div>
                <div class="b24-loading-text">Завантаження завдань...</div>
            </div>
        </div>
        <div class="b24-pagination" id="paginationBlock" style="display:none">
            <span class="b24-pagination-info" id="paginationInfo"></span>
            <div class="b24-pagination-btns" id="paginationBtns"></div>
        </div>
    </div>
</main>

<!-- Toast container -->
<div id="toast-container"></div>

<!-- Complete Task Modal -->
<div id="completeModal" class="b24-modal-overlay" style="display:none" onclick="if(event.target===this)closeModal()">
    <div class="b24-modal">
        <div class="b24-modal-header">
            <span class="b24-modal-title">Виконати завдання</span>
            <button class="b24-modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="b24-modal-body">
            <div class="b24-detail" id="taskDetailPanel"></div>
            <div id="taskFieldsContainer"></div>
        </div>
        <div class="b24-modal-footer">
            <button class="b24-btn b24-btn-default" onclick="closeModal()">Скасувати</button>
            <button class="b24-btn b24-btn-success" id="confirmCompleteBtn" onclick="submitComplete()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Виконати
            </button>
        </div>
    </div>
</div>

<script>
// ──────────────────────────────────────────────────
// State
// ──────────────────────────────────────────────────
let allTasks = [];
let currentFilter = 'all';
let currentPage = 1;
const PAGE_SIZE = 20;
let sortCol = null;
let sortDir = 'asc';
let currentTask = null;
let completedToday = 0;

// ──────────────────────────────────────────────────
// API helpers
// ──────────────────────────────────────────────────
async function apiCall(method, params = {}) {
    const res = await fetch(`?action=${encodeURIComponent(method)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(params)
    });
    const data = await res.json();
    if (data.error) throw new Error(data.error_description || data.error);
    return data;
}

// ──────────────────────────────────────────────────
// Load current user
// ──────────────────────────────────────────────────
async function loadUser() {
    try {
        const data = await apiCall('user.current');
        const user = data.result;
        if (user) {
            const name = [user.NAME, user.LAST_NAME].filter(Boolean).join(' ') || user.LOGIN || 'Користувач';
            document.getElementById('userName').textContent = name;
            const initials = (user.NAME?.[0] || '') + (user.LAST_NAME?.[0] || '');
            document.getElementById('userAvatar').textContent = initials || '?';
        }
    } catch (e) {
        document.getElementById('userName').textContent = 'Користувач';
    }
}

// ──────────────────────────────────────────────────
// Load tasks
// ──────────────────────────────────────────────────
async function loadTasks() {
    document.getElementById('tableContainer').innerHTML = `
        <div class="b24-loading">
            <div class="b24-spinner"></div>
            <div class="b24-loading-text">Завантаження завдань...</div>
        </div>`;
    document.getElementById('paginationBlock').style.display = 'none';

    try {
        // Fetch all tasks with pagination
        let tasks = [];
        let start = 0;
        while (true) {
            const data = await apiCall('bizproc.task.list', {
                select: ['ID', 'WORKFLOW_ID', 'DOCUMENT_NAME', 'NAME', 'DESCRIPTION',
                         'STATUS', 'CREATED_DATE', 'MODIFIED_DATE', 'WORKFLOW_TEMPLATE_NAME',
                         'WORKFLOW_STARTED_BY', 'USERS_ANSWER', 'PARAMETERS', 'USERS'],
                start: start
            });
            const batch = data.result || [];
            tasks = tasks.concat(batch);
            if (!data.next || batch.length < 50) break;
            start = data.next;
        }

        allTasks = tasks;
        updateStats();
        renderTable();
        showToast('success', `Завантажено ${tasks.length} завдань`);
    } catch (e) {
        document.getElementById('tableContainer').innerHTML = `
            <div class="b24-empty">
                <div class="b24-empty-icon">⚠️</div>
                <div class="b24-empty-title">Помилка завантаження</div>
                <div class="b24-empty-text">${escapeHtml(e.message)}</div>
            </div>`;
        showToast('error', 'Помилка: ' + e.message);
    }
}

// ──────────────────────────────────────────────────
// Stats
// ──────────────────────────────────────────────────
function updateStats() {
    const pending = allTasks.filter(t => t.STATUS == 0 || t.STATUS === '0').length;
    const completed = allTasks.filter(t => t.STATUS == 2 || t.STATUS === '2').length;
    document.getElementById('statTotal').textContent = allTasks.length;
    document.getElementById('statPending').textContent = pending;
    document.getElementById('statCompleted').textContent = completedToday;

    document.getElementById('tabAll').textContent = allTasks.length;
    document.getElementById('tabPending').textContent = pending;
    document.getElementById('tabCompleted').textContent = completed;
}

// ──────────────────────────────────────────────────
// Filter & Search
// ──────────────────────────────────────────────────
function setFilter(filter, el) {
    currentFilter = filter;
    currentPage = 1;
    document.querySelectorAll('.b24-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    renderTable();
}

function getFilteredTasks() {
    let tasks = allTasks;
    if (currentFilter !== 'all') {
        tasks = tasks.filter(t => String(t.STATUS) === String(currentFilter));
    }
    const q = document.getElementById('searchInput').value.trim().toLowerCase();
    if (q) {
        tasks = tasks.filter(t =>
            (t.NAME || '').toLowerCase().includes(q) ||
            (t.DOCUMENT_NAME || '').toLowerCase().includes(q) ||
            (t.WORKFLOW_TEMPLATE_NAME || '').toLowerCase().includes(q) ||
            (t.DESCRIPTION || '').toLowerCase().includes(q)
        );
    }
    if (sortCol) {
        tasks = [...tasks].sort((a, b) => {
            let av = a[sortCol] || '', bv = b[sortCol] || '';
            if (sortCol === 'ID') { av = +av; bv = +bv; }
            if (av < bv) return sortDir === 'asc' ? -1 : 1;
            if (av > bv) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });
    }
    return tasks;
}

// ──────────────────────────────────────────────────
// Render table
// ──────────────────────────────────────────────────
function renderTable() {
    const tasks = getFilteredTasks();
    const total = tasks.length;
    const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
    if (currentPage > totalPages) currentPage = totalPages;
    const pageTasks = tasks.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);

    if (total === 0) {
        document.getElementById('tableContainer').innerHTML = `
            <div class="b24-empty">
                <div class="b24-empty-icon">📋</div>
                <div class="b24-empty-title">Завдань не знайдено</div>
                <div class="b24-empty-text">Наразі немає завдань автоматизацій що відповідають фільтру</div>
            </div>`;
        document.getElementById('paginationBlock').style.display = 'none';
        return;
    }

    const rows = pageTasks.map(task => {
        const status = getStatusBadge(task.STATUS);
        const created = formatDate(task.CREATED_DATE);
        const isPending = task.STATUS == 0 || task.STATUS === '0';
        return `<tr>
            <td data-label="ID" style="color:var(--b24-text-light);font-size:12px;width:60px">#${escapeHtml(String(task.ID))}</td>
            <td data-label="Назва">
                <div class="task-name">${escapeHtml(task.NAME || 'Без назви')}</div>
                ${task.DESCRIPTION ? `<div class="task-description">${escapeHtml(task.DESCRIPTION)}</div>` : ''}
            </td>
            <td data-label="Документ">
                <div class="b24-type-icon">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    ${escapeHtml(task.DOCUMENT_NAME || '—')}
                </div>
            </td>
            <td data-label="Автоматизація" style="font-size:12px;color:var(--b24-text-light)">${escapeHtml(task.WORKFLOW_TEMPLATE_NAME || '—')}</td>
            <td data-label="Статус">${status}</td>
            <td data-label="Дата" style="font-size:12px;color:var(--b24-text-light);white-space:nowrap">${created}</td>
            <td data-label="Дії">
                <div class="b24-actions">
                    <button class="b24-btn b24-btn-default b24-btn-sm" onclick='openDetail(${JSON.stringify(task)})'>
                        Деталі
                    </button>
                    ${isPending ? `<button class="b24-btn b24-btn-success b24-btn-sm" onclick='openCompleteModal(${JSON.stringify(task)})'>
                        ✓ Виконати
                    </button>` : ''}
                </div>
            </td>
        </tr>`;
    }).join('');

    const thClass = col => col === sortCol ? `sort-${sortDir}` : '';
    document.getElementById('tableContainer').innerHTML = `
        <table class="b24-table">
            <thead>
                <tr>
                    <th class="${thClass('ID')}" onclick="setSort('ID')">#</th>
                    <th class="${thClass('NAME')}" onclick="setSort('NAME')">Назва завдання</th>
                    <th class="${thClass('DOCUMENT_NAME')}" onclick="setSort('DOCUMENT_NAME')">Документ</th>
                    <th class="${thClass('WORKFLOW_TEMPLATE_NAME')}" onclick="setSort('WORKFLOW_TEMPLATE_NAME')">Автоматизація</th>
                    <th class="${thClass('STATUS')}" onclick="setSort('STATUS')">Статус</th>
                    <th class="${thClass('CREATED_DATE')}" onclick="setSort('CREATED_DATE')">Дата</th>
                    <th>Дії</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;

    // Pagination
    const pag = document.getElementById('paginationBlock');
    pag.style.display = 'flex';
    document.getElementById('paginationInfo').textContent =
        `Показано ${(currentPage-1)*PAGE_SIZE+1}–${Math.min(currentPage*PAGE_SIZE, total)} з ${total}`;

    const btns = document.getElementById('paginationBtns');
    let html = `<button class="b24-page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
    for (let i = 1; i <= totalPages; i++) {
        if (totalPages <= 7 || Math.abs(i - currentPage) <= 2 || i === 1 || i === totalPages) {
            html += `<button class="b24-page-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
        } else if (Math.abs(i - currentPage) === 3) {
            html += `<button class="b24-page-btn" disabled>…</button>`;
        }
    }
    html += `<button class="b24-page-btn" onclick="goPage(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>›</button>`;
    btns.innerHTML = html;
}

function setSort(col) {
    if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
    else { sortCol = col; sortDir = 'asc'; }
    renderTable();
}

function goPage(p) {
    currentPage = p;
    renderTable();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ──────────────────────────────────────────────────
// Detail view (read-only popover)
// ──────────────────────────────────────────────────
function openDetail(task) {
    currentTask = task;
    document.getElementById('taskDetailPanel').innerHTML = buildDetailHtml(task);
    document.getElementById('taskFieldsContainer').innerHTML = '';
    document.getElementById('confirmCompleteBtn').style.display =
        (task.STATUS == 0 || task.STATUS === '0') ? 'inline-flex' : 'none';
    document.querySelector('.b24-modal-title').textContent = 'Деталі завдання';
    document.getElementById('completeModal').style.display = 'flex';
}

function openCompleteModal(task) {
    currentTask = task;
    document.getElementById('taskDetailPanel').innerHTML = buildDetailHtml(task);
    document.getElementById('taskFieldsContainer').innerHTML = buildFieldsHtml(task);
    document.getElementById('confirmCompleteBtn').style.display = 'inline-flex';
    document.querySelector('.b24-modal-title').textContent = 'Виконати завдання';
    document.getElementById('completeModal').style.display = 'flex';
}

function buildDetailHtml(task) {
    const rows = [
        ['ID', '#' + task.ID],
        ['Назва', task.NAME || '—'],
        ['Документ', task.DOCUMENT_NAME || '—'],
        ['Автоматизація', task.WORKFLOW_TEMPLATE_NAME || '—'],
        ['Статус', getStatusLabel(task.STATUS)],
        ['Створено', formatDate(task.CREATED_DATE)],
        ['Змінено', formatDate(task.MODIFIED_DATE)],
    ];
    if (task.DESCRIPTION) rows.push(['Опис', task.DESCRIPTION]);
    return rows.map(([k, v]) => `
        <div class="b24-detail-row">
            <span class="b24-detail-key">${k}:</span>
            <span class="b24-detail-val">${escapeHtml(String(v))}</span>
        </div>`).join('');
}

function buildFieldsHtml(task) {
    // Build answer fields from task parameters
    const params = task.PARAMETERS || [];
    if (!params.length && !task.USERS_ANSWER) {
        return `<div class="b24-field">
            <label class="b24-label">Коментар</label>
            <textarea class="b24-textarea" id="taskComment" placeholder="Введіть коментар (необов'язково)..."></textarea>
        </div>`;
    }

    let html = '';

    // If task has answer options (USERS_ANSWER)
    if (task.USERS_ANSWER && Array.isArray(task.USERS_ANSWER) && task.USERS_ANSWER.length) {
        html += `<div class="b24-field">
            <label class="b24-label required">Відповідь</label>
            <select class="b24-select" id="taskAnswer">
                <option value="">— Оберіть відповідь —</option>
                ${task.USERS_ANSWER.map(a => `<option value="${escapeHtml(a)}">${escapeHtml(a)}</option>`).join('')}
            </select>
        </div>`;
    } else if (typeof task.USERS_ANSWER === 'string' && task.USERS_ANSWER) {
        html += `<div class="b24-field">
            <label class="b24-label required">Відповідь</label>
            <input class="b24-input" id="taskAnswer" type="text" placeholder="${escapeHtml(task.USERS_ANSWER)}">
        </div>`;
    }

    // Dynamic parameters
    if (Array.isArray(params)) {
        params.forEach((p, i) => {
            const id = `param_${i}`;
            html += `<div class="b24-field">
                <label class="b24-label">${escapeHtml(p.NAME || p.Id || 'Параметр ' + (i+1))}</label>
                <input class="b24-input" id="${id}" data-param="${escapeHtml(p.Id || '')}"
                    type="text" placeholder="${escapeHtml(p.Default || '')}">
            </div>`;
        });
    }

    html += `<div class="b24-field">
        <label class="b24-label">Коментар</label>
        <textarea class="b24-textarea" id="taskComment" placeholder="Введіть коментар (необов'язково)..."></textarea>
    </div>`;

    return html;
}

// ──────────────────────────────────────────────────
// Complete task
// ──────────────────────────────────────────────────
async function submitComplete() {
    if (!currentTask) return;

    const btn = document.getElementById('confirmCompleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="b24-spinner" style="width:14px;height:14px;border-width:2px;border-top-color:#fff"></div> Виконання...';

    try {
        const fields = {};

        // Collect answer
        const answerEl = document.getElementById('taskAnswer');
        if (answerEl) {
            if (!answerEl.value && answerEl.tagName === 'SELECT') {
                showToast('error', 'Оберіть відповідь');
                btn.disabled = false;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Виконати';
                return;
            }
            if (answerEl.value) fields['answer'] = answerEl.value;
        }

        // Collect comment
        const commentEl = document.getElementById('taskComment');
        if (commentEl && commentEl.value) fields['comment'] = commentEl.value;

        // Collect dynamic params
        document.querySelectorAll('[data-param]').forEach(el => {
            if (el.dataset.param && el.value) fields[el.dataset.param] = el.value;
        });

        const payload = {
            TASK_ID: currentTask.ID,
            FIELDS: fields
        };

        await apiCall('bizproc.task.complete', payload);

        completedToday++;
        // Update task status locally
        const idx = allTasks.findIndex(t => t.ID === currentTask.ID);
        if (idx !== -1) allTasks[idx] = { ...allTasks[idx], STATUS: '2' };

        updateStats();
        renderTable();
        closeModal();
        showToast('success', `Завдання "${currentTask.NAME || '#' + currentTask.ID}" виконано`);
    } catch (e) {
        showToast('error', 'Помилка: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Виконати';
    }
}

function closeModal() {
    document.getElementById('completeModal').style.display = 'none';
    currentTask = null;
}

// ──────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────
function getStatusBadge(status) {
    const s = String(status);
    const map = {
        '0': ['badge-pending', '⏳', 'Очікує'],
        '1': ['badge-running', '▶', 'В роботі'],
        '2': ['badge-completed', '✓', 'Виконано'],
        '3': ['badge-error', '✕', 'Скасовано'],
    };
    const [cls, icon, label] = map[s] || ['badge-running', '?', 'Невідомо'];
    return `<span class="b24-badge ${cls}"><span class="b24-badge-dot"></span>${label}</span>`;
}

function getStatusLabel(status) {
    const map = { '0': 'Очікує', '1': 'В роботі', '2': 'Виконано', '3': 'Скасовано' };
    return map[String(status)] || 'Невідомо';
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr);
        if (isNaN(d)) return dateStr;
        const today = new Date();
        const isToday = d.toDateString() === today.toDateString();
        if (isToday) return 'Сьогодні ' + d.toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
        return d.toLocaleDateString('uk-UA', { day: '2-digit', month: '2-digit', year: 'numeric' })
             + ' ' + d.toLocaleTimeString('uk-UA', { hour: '2-digit', minute: '2-digit' });
    } catch { return dateStr; }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ──────────────────────────────────────────────────
// Toast
// ──────────────────────────────────────────────────
function showToast(type, message) {
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const c = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `b24-toast ${type}`;
    el.innerHTML = `
        <span class="b24-toast-icon">${icons[type] || 'ℹ'}</span>
        <span class="b24-toast-text">${escapeHtml(message)}</span>
        <button class="b24-toast-close" onclick="this.parentElement.remove()">×</button>`;
    c.appendChild(el);
    setTimeout(() => {
        el.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => el.remove(), 300);
    }, 4000);
}

// ──────────────────────────────────────────────────
// Keyboard shortcuts
// ──────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') { e.preventDefault(); loadTasks(); }
});

// ──────────────────────────────────────────────────
// Init
// ──────────────────────────────────────────────────
loadUser();
loadTasks();
</script>
</body>
</html>
