<?php
$pageTitle = "Tous les produits";
include __DIR__ . '/parts/header.php';

use App\Product;
?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Liste des produits Infomaniak</h1>
            <div class="btn-group">
                <a href="?<?= http_build_query(array_merge($_GET, ['format' => 'json'])) ?>" class="btn btn-outline-secondary">JSON (Traité)</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['format' => 'json', 'raw' => '1'])) ?>" class="btn btn-outline-info">JSON (Brut)</a>
            </div>
        </div>

        <?php if (!empty($dataFrom)) : ?>
            <p class="text-muted fst-italic">
                Données chargées depuis : <strong><?= $dataFrom ?></strong>
            </p>
        <?php endif; ?>

        <!-- Formulaire de filtrage -->
        <form method="GET" class="mb-4 p-3 bg-light border rounded">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="search_name" class="form-label fw-bold">Rechercher par nom :</label>
                    <input type="text" name="search_name" id="search_name" class="form-control" placeholder="Ex: Mon site web" value="<?= htmlspecialchars($searchName ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label for="account_id" class="form-label fw-bold">Filtrer par compte :</label>
                    <select name="account_id" id="account_id" class="form-select">
                        <option value="">Tous les comptes</option>
                        <?php foreach ($accounts as $id => $name) : ?>
                            <option value="<?= $id ?>" <?= (isset($selectedAccountId) && $selectedAccountId == $id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) . ' (' . $id . ')' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label fw-bold">Filtrer par type :</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous les types</option>
                        <?php foreach ($productTypes as $type) : ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= (isset($selectedType) && $selectedType == $type) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="per_page" class="form-label fw-bold">Par page :</label>
                    <select name="per_page" id="per_page" class="form-select">
                        <?php foreach ($itemsPerPageOptions as $option) : ?>
                            <option value="<?= $option ?>" <?= ($itemsPerPage == $option) ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </div>
        </form>

        <?php if (isset($data) && $data['result'] === 'success' && !empty($data['data'])) : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ID Produit</th>
                            <th scope="col">Nom du produit</th>
                            <th scope="col">Type de service</th>
                            <th scope="col">Expirations</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php /** @var Product $product */ foreach ($data['data'] as $product) : ?>
                            <!-- Ajout des données du produit en attribut data- pour le JS -->
                            <!-- Utilisation de htmlspecialchars pour sécuriser le JSON dans l'attribut HTML -->
                            <tr data-product-details='<?= htmlspecialchars(json_encode($product->getRawData())) ?>'>
                                <td><?= $product->getId() ?></td>
                                <td><?= $product->getCustomerName() ?></td>
                                <td><?= $product->getServiceName() ?></td>
                                <td>
                                    <?= $product->getProductExpirationStatusBadge() ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info view-details-btn" data-bs-toggle="modal" data-bs-target="#detailsModal">
                                        Détails
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            // --- Bloc de Pagination ---
            if (isset($data['page']) && isset($data['pages']) && $data['pages'] > 1) {
                $currentPage = (int) $data['page'];
                $totalPages = (int) $data['pages'];
                $range = 2; // Nombre de pages à afficher avant et après la page courante
                ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <!-- Bouton Précédent -->
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">Précédent</a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <?php
                            // Conditions pour afficher le numéro de page
                            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $range && $i <= $currentPage + $range)) {
                                ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                                <?php
                            }
                            // Ajoute les ellipses "..." si nécessaire
                            elseif ($i == $currentPage - $range - 1 || $i == $currentPage + $range + 1) {
                                ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php
                            }
                            ?>
                        <?php endfor; ?>

                        <!-- Bouton Suivant -->
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            <?php } ?>

        <?php elseif (isset($errorMessage)) : ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Une erreur est survenue !</h4>
                <p>Impossible de récupérer les données depuis l'API d'Infomaniak.</p>
                <hr>
                <p class="mb-0">Détail de l'erreur : <?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php else : ?>
            <div class="alert alert-info" role="alert">
                Aucun produit ne correspond à votre recherche.
            </div>
        <?php endif; ?>

<?php include __DIR__ . '/parts/footer.php'; ?>

<!-- Modal pour les détails du produit -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Détails du produit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="modal-product-name"></h6>
                <p><strong>Compte associé :</strong> <span id="modal-account-name"></span></p>
                <pre><code id="modal-json-details" class="json" style="white-space: pre-wrap; word-break: break-all;"></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const detailsModal = document.getElementById('detailsModal');
        if (detailsModal) {
            detailsModal.addEventListener('show.bs.modal', function (event) {
                // Bouton qui a déclenché la modale
                const button = event.relatedTarget;
                // Récupérer les données depuis l'attribut data- de la ligne <tr> parente
                const productRow = button.closest('tr');
                const productRawData = JSON.parse(productRow.getAttribute('data-product-details'));

                // Récupérer le nom du compte depuis la liste PHP
                const accounts = <?= json_encode($accounts) ?>;
                const accountName = accounts[productRawData.account_id] || `ID: ${productRawData.account_id}`;

                // Mettre à jour le contenu de la modale
                const modalTitle = detailsModal.querySelector('.modal-title');
                const modalProductName = detailsModal.querySelector('#modal-product-name');
                const modalAccountName = detailsModal.querySelector('#modal-account-name');
                const modalJsonDetails = detailsModal.querySelector('#modal-json-details');

                modalTitle.textContent = `Détails pour : ${productRawData.customer_name}`;
                modalProductName.textContent = productRawData.service_name;
                modalAccountName.textContent = accountName;

                // Afficher toutes les données brutes dans un format JSON propre
                if (productRawData) {
                    modalJsonDetails.textContent = JSON.stringify(productRawData, null, 2);
                } else {
                    modalJsonDetails.textContent = 'Aucun détail technique disponible pour ce produit.';
                }
            });
        }
    });
</script>
