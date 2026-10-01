<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../classes/Offre.php';

exigerConnexion();

$offres = Offre::avecNbCandidatures();

$statuts = [
    'brouillon' => ['Brouillon', 'secondary'],
    'publiee'   => ['Publiée', 'success'],
    'cloturee'  => ['Clôturée', 'dark'],
];

$titre = 'Candidatures';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-1">Candidatures par offre</h1>
<p class="text-muted mb-3">Choisissez une offre pour voir ses candidatures et traiter chaque dossier.</p>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Offre</th>
                    <th>Entreprise</th>
                    <th>Statut de l'offre</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">En attente</th>
                    <th class="text-center">Retenues</th>
                    <th class="text-center">Refusées</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($offres)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">Aucune offre.</td></tr>
            <?php else: ?>
                <?php foreach ($offres as $o): ?>
                    <?php [$texte, $couleur] = $statuts[$o['statut']] ?? [$o['statut'], 'secondary']; ?>
                    <tr>
                        <td><?= htmlspecialchars($o['titre']) ?></td>
                        <td><?= htmlspecialchars($o['entreprise']) ?></td>
                        <td><span class="badge text-bg-<?= $couleur ?>"><?= $texte ?></span></td>
                        <td class="text-center fw-bold"><?= (int) $o['nb_total'] ?></td>
                        <td class="text-center"><?= (int) $o['nb_attente'] ?></td>
                        <td class="text-center"><?= (int) $o['nb_retenues'] ?></td>
                        <td class="text-center"><?= (int) $o['nb_refusees'] ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="candidatures_offre.php?id=<?= (int) $o['id'] ?>">Voir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>