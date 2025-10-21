<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Produits Infomaniak' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Dashboard Infomaniak</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="index.php">Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'products.php') ? 'active' : '' ?>" href="products.php">Tous les produits</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5">
