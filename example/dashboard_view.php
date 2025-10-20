<?php include __DIR__ . '/parts/header.php'; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo $pageTitle ?? 'Liste des produits'; ?></h1>
        </div>

        <?php if (!empty($dataFrom)) : ?>
            <p class="text-muted fst-italic">
                Données chargées depuis : <strong><?php echo $dataFrom; ?></strong>
            </p>
        <?php endif; ?>

        <?php if (isset($data) && $data['result'] === 'success' && !empty($data['data'])) : ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">Nom du produit</th>
                            <th scope="col">Compte</th>
                            <th scope="col">Type de service</th>
                            <th scope="col">Stockage</th>
                            <th scope="col">Expirations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['data'] as $product) : ?>
                            <tr>
                                <td><?php echo $product->getCustomerName(); ?></td>
                                <td><?php echo htmlspecialchars($accounts[$product->getAccountId()] ?? 'ID: ' . $product->getAccountId()); ?></td>
                                <td><?php echo $product->getServiceName(); ?></td>
                                <td><?php echo $product->getDiskUsageStatusBadge(); ?></td>
                                <td>
                                    <?php echo $product->getProductExpirationStatusBadge(); ?><br>
                                    <?php echo $product->getSslExpirationStatusBadge(); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($errorMessage)) : ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Une erreur est survenue !</h4>
                <p>Impossible de récupérer les données depuis l'API d'Infomaniak.</p>
                <hr>
                <p class="mb-0">Détail de l'erreur : <?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php else : ?>
            <div class="alert alert-success" role="alert">
                <h4 class="alert-heading">Tout est en ordre !</h4>
                <p>Aucun produit ne nécessite une action critique pour le moment.</p>
            </div>
        <?php endif; ?>

<?php include __DIR__ . '/parts/footer.php'; ?>
