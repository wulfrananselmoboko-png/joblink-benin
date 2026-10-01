<?php
require_once __DIR__ . '/includes/auth.php';
exigerConnexion();

$titre = 'Tableau de bord';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3">Tableau de bord</h1>
<p>Bienvenue, <?= htmlspecialchars($_SESSION['admin_nom']) ?>.</p>

<?php require __DIR__ . '/includes/footer.php'; ?>