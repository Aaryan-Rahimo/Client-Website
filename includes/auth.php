<?php

/*
 * Author: Aaryan
 * Date Created: 2026-04-01
 * Description: Authentication helper functions for the clinic web application, including login requirement checks and admin role verification.
 */

declare(strict_types=1);

function login_page_url(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    return (str_contains($script, '/actions/')) ? '../login.php' : 'login.php';
}

function require_login(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . login_page_url());
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        die('Access denied.');
    }
}
