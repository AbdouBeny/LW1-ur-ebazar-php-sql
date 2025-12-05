<h2>Renommer catégorie</h2>

<form method="POST" action="?action=renameCategory">
    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
    Nouveau nom :
    <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>">
    <button type="submit">Renommer</button>
</form>
