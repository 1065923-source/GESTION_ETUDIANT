<?php
session_start();
if (!isset($_SESSION['user'])) header("Location: index.php");

include 'includes/db.php';
include 'includes/header.php';

// Supprimer
if(isset($_GET['delete'])){
    $stmt=$conn->prepare("DELETE FROM enseignants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['delete']]);
    echo "<p class='success'>Enseignant supprimé ✔</p>";
}

// Ajouter
if(isset($_POST['add_enseignant'])){
    $stmt=$conn->prepare("INSERT INTO enseignants(nom,specialite) VALUES(:nom,:specialite)");
    $stmt->execute([
        'nom'=>$_POST['nom'],
        'specialite'=>$_POST['specialite']
    ]);
    echo "<p class='success'>Enseignant ajouté ✔</p>";
}

// Modifier
if(isset($_POST['update_enseignant'])){
    $stmt=$conn->prepare("UPDATE enseignants SET nom=:nom,specialite=:specialite WHERE id=:id");
    $stmt->execute([
        'nom'=>$_POST['nom'],
        'specialite'=>$_POST['specialite'],
        'id'=>$_POST['id']
    ]);
    echo "<p class='success'>Enseignant modifié ✔</p>";
}

// Edition
$editEnseignant=null;
if(isset($_GET['edit'])){
    $stmt=$conn->prepare("SELECT * FROM enseignants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['edit']]);
    $editEnseignant=$stmt->fetch(PDO::FETCH_ASSOC);
}

// Liste
$enseignants=$conn->query("SELECT * FROM enseignants")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestion des Enseignants</h2>

<div class="card">
<?php if($editEnseignant): ?>
<form method="post">
<input type="hidden" name="id" value="<?= $editEnseignant['id']; ?>">
<input type="text" name="nom" value="<?= $editEnseignant['nom']; ?>" placeholder="Nom" required>
<input type="text" name="specialite" value="<?= $editEnseignant['specialite']; ?>" placeholder="Spécialité" required>
<button type="submit" name="update_enseignant">Modifier ✏️</button>
</form>
<?php else: ?>
<form method="post">
<input type="text" name="nom" placeholder="Nom" required>
<input type="text" name="specialite" placeholder="Spécialité" required>
<button type="submit" name="add_enseignant">Ajouter ➕</button>
</form>
<?php endif; ?>
</div>

<div class="card">
<table>
<tr><th>ID</th><th>Nom</th><th>Spécialité</th><th>Actions</th></tr>
<?php foreach($enseignants as $ens): ?>
<tr>
<td><?= $ens['id']; ?></td>
<td><?= $ens['nom']; ?></td>
<td><?= $ens['specialite']; ?></td>
<td>
<a href="enseignants.php?edit=<?= $ens['id']; ?>">✏️ Modifier</a> |
<a href="enseignants.php?delete=<?= $ens['id']; ?>" onclick="return confirm('Supprimer ?')">🗑️ Supprimer</a>
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
