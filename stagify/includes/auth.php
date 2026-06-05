<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . getRoot() . 'index.php?error=session');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: ' . getRoot() . 'index.php?error=access');
        exit;
    }
}

function getRoot(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $doc    = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $rel    = str_replace($doc, '', $script);
    $depth  = substr_count(trim($rel, '/'), '/');
    return str_repeat('../', $depth);
}

function currentUser(): array {
    return $_SESSION ?? [];
}

function userName(): string {
    return htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
}

function userInitials(): string {
    $p = $_SESSION['prenom'] ?? 'U';
    $n = $_SESSION['nom']    ?? '';
    return strtoupper(mb_substr($p, 0, 1) . mb_substr($n, 0, 1));
}

function flashMessage(): string {
    $html = '';
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'] === 'success' ? 'alert-success' : 'alert-danger';
        $msg  = htmlspecialchars($_SESSION['flash']['msg']);
        $html = "<div class=\"alert {$type}\">{$msg}</div>";
        unset($_SESSION['flash']);
    }
    return $html;
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
