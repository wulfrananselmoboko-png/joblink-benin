<?php
// Nom de session propre à l'espace candidat : il ne se mélange pas avec celle de l'admin
session_name('joblink_candidat');
session_start();

function candidatConnecte(): bool
{
    return isset($_SESSION['candidat_id']);
}

// À appeler en haut de chaque page protégée
function exigerConnexionCandidat(): void
{
    if (!candidatConnecte()) {
        header('Location: login.php');
        exit;
    }
}

// Message affiché une seule fois après une action
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

// Protection CSRF
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