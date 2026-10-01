<?php
session_start();

function adminConnecte(): bool
{
    return isset($_SESSION['admin_id']);
}

// À appeler en haut de chaque page protégée
function exigerConnexion(): void
{
    if (!adminConnecte()) {
        header('Location: login.php');
        exit;
    }
}

// Message affiché après une action (succès ou erreur)
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function afficherFlash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . htmlspecialchars($f['type']) . ' alert-dismissible fade show">'
           . htmlspecialchars($f['message'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

// Protection CSRF : un jeton secret propre à la session
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifierCsrf(): void
{
    $jeton = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $jeton)) {
        http_response_code(403);
        exit('Requête invalide.');
    }
}