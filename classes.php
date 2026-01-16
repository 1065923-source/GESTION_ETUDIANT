<?php
session_start();

include 'includes/db.php';
include 'includes/header.php';

// Supprimer
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM classes WHERE id=:id");
    $stmt->execute(['id'=>intval($_GET['delete'])]);
    echo "<p class='success'>Classe supprimée ✔</p>";
}

// Ajouter
if (isset($_POST['add_class'])) {
    $nom = trim($_POST['nom'] ?? '');
    if ($nom !== '') {
        $stmt = $conn->prepare("INSERT INTO classes (nom) VALUES (:nom)");
        $stmt->execute(['nom'=>$nom]);
        echo "<p class='success'>Classe ajoutée ✔</p>";
    } else {
        echo "<p class='error'>Le nom de la classe est requis.</p>";
    }
}

// Modifier
if (isset($_POST['update_class'])) {
    $id = intval($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    if ($id > 0 && $nom !== '') {
        $stmt = $conn->prepare("UPDATE classes SET nom=:nom WHERE id=:id");
        $stmt->execute(['nom'=>$nom,'id'=>$id]);
        echo "<p class='success'>Classe modifiée ✔</p>";
    } else {
        echo "<p class='error'>Tous les champs sont requis.</p>";
    }
}

// Édition
$editClass = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM classes WHERE id=:id");
    $stmt->execute(['id'=>intval($_GET['edit'])]);
    $editClass = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Liste
$classes = $conn->query("SELECT * FROM classes")->fetchAll();
?>

<h2>Gestion des Classes</h2>

<div class="card">
<?php if($editClass): ?>
<h3>Modifier une classe</h3>
<form method="post">
    <input type="hidden" name="id" value="<?= $editClass['id'] ?>">
    <input type="text" name="nom" value="<?= htmlspecialchars($editClass['nom']) ?>" required>
    <button type="submit" name="update_class">Modifier</button>
</form>
<?php else: ?>
<h3>Ajouter une classe</h3>
<form method="post">
    <input type="text" name="nom" placeholder="Nom de la classe" required>
    <button type="submit" name="add_class">Ajouter</button>
</form>
<?php endif; ?>
</div>

<div class="card">
<table>
<tr>
<th>ID</th><th>Nom</th><th>Actions</th>
</tr>
<?php foreach($classes as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= htmlspecialchars($c['nom']) ?></td>
<td>
<a href="classes.php?edit=<?= $c['id'] ?>" class="edit">Modifier</a> |
<a href="classes.php?delete=<?= $c['id'] ?>" class="delete" onclick="return confirm('Supprimer ?')">Supprimer</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php include 'includes/footer.php'; ?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff0f5;
    padding: 15px; /* moins d'espace */
}

h2 {
    text-align: center;
    color: #800000;
    margin-bottom: 15px; /* un peu plus petit */
}

.card {
    background: #fff;
    padding: 20px; /* un peu plus petit */
    margin: 15px auto; /* réduit l'espace entre cartes */
    border-radius: 12px; /* légèrement plus petit */
    max-width: 700px; /* réduit largeur */
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

form input[type="text"], form button {
    display: block;
    width: 100%;
    padding: 10px; /* plus petit */
    margin: 10px 0; /* réduit marges */
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px; /* plus petit */
    box-sizing: border-box;
}

form button {
    background-color: #800000;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s, transform 0.2s;
}

form button:hover {
    background-color: #5c0000;
    transform: scale(1.03);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px; /* réduit marges */
    font-size: 14px; /* un peu plus petit */
}

table th, table td {
    padding: 10px; /* plus compact */
    text-align: left;
    border-bottom: 1px solid #eee;
}

table th {
    background-color: #800000;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f0f0;
}

table tr:hover {
    background-color: #f2dede;
}

.success {
    color: #28a745;
    font-weight: bold;
    margin: 8px 0;
}

.error {
    color: #dc3545;
    font-weight: bold;
    margin: 8px 0;
}

a.edit, a.delete {
    text-decoration: none;
    padding: 5px 8px; /* plus petit */
    border-radius: 5px;
    color: white;
    margin: 0 3px;
    font-size: 13px;
    transition: opacity 0.2s, transform 0.2s;
}

a.edit { background-color: #007bff; }
a.edit:hover { opacity: 0.8; transform: scale(1.05); }

a.delete { background-color: #dc3545; }
a.delete:hover { opacity: 0.8; transform: scale(1.05); }

a.edit::before { content: "✏️ "; }
a.delete::before { content: "🗑️ "; }
</style>
