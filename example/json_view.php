<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu JSON - Produits Infomaniak</title>
    <!-- Intégration de Bootstrap 5 via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Améliore la lisibilité du bloc de code */
        pre {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: .25rem;
            padding: 1rem;
            white-space: pre-wrap; /* Permet le retour à la ligne */
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Aperçu JSON</h1>
            <?php
            // Construit l'URL pour le retour, en enlevant le paramètre 'format'
            $queryParams = $_GET;
            unset($queryParams['format']);
            $backUrl = '?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo $backUrl; ?>" class="btn btn-primary">Retour à la liste</a>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Erreur API</h4>
                <pre><code><?php echo htmlspecialchars($errorMessage); ?></code></pre>
            </div>
        <?php elseif (isset($body)): ?>
            <pre><code><?php
                // Décode et ré-encode le JSON pour un affichage "joli"
                echo htmlspecialchars(json_encode(json_decode($body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            ?></code></pre>
        <?php endif; ?>
    </main>
</body>
</html>
