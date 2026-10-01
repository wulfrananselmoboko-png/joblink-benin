<?php
class GestionCv
{
    private const TAILLE_MAX = 2097152;   // 2 Mo

    private static function dossier(): string
    {
        return __DIR__ . '/../uploads/cv/';
    }

    // Seuls les noms générés par l'application sont acceptés
    public static function nomValide(string $nom): bool
    {
        return preg_match('/^cv_\d+_[a-f0-9]{32}\.pdf$/', $nom) === 1;
    }

    // Contrôle le fichier envoyé, puis l'enregistre sous un nom généré
    public static function enregistrer(array $fichier, int $idCandidat): string
    {
        if (!isset($fichier['error']) || is_array($fichier['error'])) {
            throw new RuntimeException('Envoi invalide.');
        }
        switch ($fichier['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Veuillez choisir un fichier PDF.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Le fichier dépasse 2 Mo.');
            default:
                throw new RuntimeException('Erreur lors de l\'envoi du fichier.');
        }

        if ($fichier['size'] > self::TAILLE_MAX) {
            throw new RuntimeException('Le fichier dépasse 2 Mo.');
        }
        if (!is_uploaded_file($fichier['tmp_name'])) {
            throw new RuntimeException('Envoi invalide.');
        }

        // Type réel du fichier (pas celui annoncé par le navigateur ni l'extension)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($fichier['tmp_name']) !== 'application/pdf') {
            throw new RuntimeException('Seuls les fichiers PDF sont acceptés.');
        }
        // Un vrai PDF commence par %PDF-
        if (file_get_contents($fichier['tmp_name'], false, null, 0, 5) !== '%PDF-') {
            throw new RuntimeException('Le fichier n\'est pas un PDF valide.');
        }

        if (!is_dir(self::dossier())) {
            mkdir(self::dossier(), 0755, true);
        }

        // Nom généré : on n'utilise jamais le nom envoyé par l'utilisateur
        $nom = 'cv_' . $idCandidat . '_' . bin2hex(random_bytes(16)) . '.pdf';
        if (!move_uploaded_file($fichier['tmp_name'], self::dossier() . $nom)) {
            throw new RuntimeException('Impossible d\'enregistrer le fichier.');
        }
        return $nom;
    }

    public static function supprimer(?string $nom): void
    {
        if ($nom !== null && self::nomValide($nom)) {
            $chemin = self::dossier() . $nom;
            if (is_file($chemin)) {
                unlink($chemin);
            }
        }
    }

        // Envoie le PDF au navigateur (affichage ou téléchargement), puis arrête le script
    public static function envoyer(string $nom, bool $telecharger = false, string $nomAffiche = 'cv.pdf'): void
    {
        $chemin = self::dossier() . $nom;
        if (!self::nomValide($nom) || !is_file($chemin)) {
            http_response_code(404);
            exit('CV introuvable.');
        }
        // Le nom proposé à l'utilisateur ne garde que des caractères sûrs
        $nomAffiche = preg_replace('/[^A-Za-z0-9._-]+/', '_', $nomAffiche);

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($telecharger ? 'attachment' : 'inline') . '; filename="' . $nomAffiche . '"');
        header('Content-Length: ' . filesize($chemin));
        header('X-Content-Type-Options: nosniff');
        readfile($chemin);
        exit;
    }
}