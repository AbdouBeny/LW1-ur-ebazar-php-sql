<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>e-bazar - Accueil</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>

<h1>Bienvenue sur e-bazar</h1>

<h2>Catégories</h2>
<ul>
<?php foreach ($categories as $cat): ?>
    <li>
        <a href="index.php?action=category&id=<?=$cat['id']?>">
            <?=htmlspecialchars($cat['name'])?>
        </a>
        (<?=$cat['count']?> annonces)
    </li>
<?php endforeach; ?>
</ul>

<h2>Dernières annonces</h2>
<div class="annonces">
<?php foreach ($lastAnnonces as $a): ?>
    <div class="annonce-card">
        <img src="uploads/<?=$a['photo']?>" width="120">
        <h3><?=htmlspecialchars($a['title'])?></h3>
        <p><?=number_format($a['price'], 2)?> €</p>
        <a href="index.php?action=annonce&id=<?=$a['id']?>">Voir l'annonce</a>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
