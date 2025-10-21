<?php
$pageTitle = "Aperçu JSON - Produits Infomaniak";
include __DIR__ . '/parts/header.php';
?>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Aperçu JSON</h1>
            <?php
            // Construit l'URL pour le retour, en enlevant les paramètres spécifiques à cette vue
            $queryParams = $_GET;
            unset($queryParams['format'], $queryParams['raw']);
            $backUrl = '?' . http_build_query($queryParams);
            ?>
            <a href="<?= $backUrl ?>" class="btn btn-primary">Retour à la liste</a>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Erreur API</h4>
                <pre><code><?= htmlspecialchars($errorMessage) ?></code></pre>
            </div>
        <?php elseif (isset($body)): ?>
            <pre><code><?php
                // Décode et ré-encode le JSON pour un affichage "joli"
                echo htmlspecialchars(json_encode(json_decode($body), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            ?></code></pre>
        <?php endif; ?>
<?php include __DIR__ . '/parts/footer.php'; ?>