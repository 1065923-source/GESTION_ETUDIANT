<?php
session_start();
if(!isset($_SESSION['user'])) header("Location:index.php");

include 'includes/db.php';
include 'includes/header.php';

// CRUD Enseignants
if(isset($_GET['delete'])){
    $stmt=$conn->prepare("DELETE FROM enseignants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['delete']]);
    echo "<p style='color:red;'>Enseignant supprimé !</p>";
}
if(isset($_POST['add_enseignant'])){
    $stmt=$conn->prepare("INSERT INTO enseignants(nom,specialite) VALUES(:nom,:specialite)");
    $stmt->execute([
        'nom'=>$_POST['nom'],
        'specialite'=>$_POST['specialite']
    ]);
    echo "<p style='color:green;'>Enseignant ajouté !</p>";
}
if(isset($_POST['update_enseignant'])){
    $stmt=$conn->prepare("UPDATE enseignants SET nom=:nom,specialite=:specialite WHERE id=:id");
    $stmt->execute([
        'nom'=>$_POST['nom'],
        'specialite'=>$_POST['specialite'],
        'id'=>$_POST['id']
    ]);
    echo "<p style='color:green;'>Enseignant modifié !</p>";
}

$editEnseignant=null;
if(isset($_GET['edit'])){
    $stmt=$conn->prepare("SELECT * FROM enseignants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['edit']]);
    $editEnseignant=$stmt->fetch(PDO::FETCH_ASSOC);
}

$enseignants=$conn->query("SELECT * FROM enseignants")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestion des Enseignants</h2>

<div class="card">
<?php if($editEnseignant): ?>
<h3>Modifier Enseignant</h3>
<form method="post">
<input type="hidden" name="id" value="<?= $editEnseignant['id']; ?>">
<input type="text" name="nom" value="<?= $editEnseignant['nom']; ?>" placeholder="Nom" required>
<input type="text" name="specialite" value="<?= $editEnseignant['specialite']; ?>" placeholder="Spécialité" required>
<button type="submit" name="update_enseignant">Modifier ✏️</button>
</form>
<?php else: ?>
<h3>Ajouter Enseignant</h3>
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
    background: #fff0f5; /* léger fond rose clair */
    padding: 20px;
}

h2 {
    text-align: center;
    color: #800000; /* rouge bordeaux UCao */
    margin-bottom: 20px;
}

.card {
    background: #fff;
    padding: 25px;
    margin: 20px auto;
    border-radius: 15px;
    max-width: 800px;
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-3px);
}

form input[type="text"], form input[type="number"], form select, form button {
    display: block;
    width: 100%;
    padding: 12px;
    margin: 12px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 16px;
    box-sizing: border-box;
}

form button {
    background-color: #800000; /* rouge bordeaux */
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s, transform 0.2s;
}

form button:hover {
    background-color: #5c0000; /* bordeaux plus foncé */
    transform: scale(1.05);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    font-size: 15px;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

table th {
    background-color: #800000; /* bordeaux UCao */
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f0f0; /* très clair pour lignes paires */
}

table tr:hover {
    background-color: #f2dede; /* hover léger bordeaux clair */
}

.success {
    color: #28a745;
    font-weight: bold;
    margin: 10px 0;
}

.error {
    color: #dc3545;
    font-weight: bold;
    margin: 10px 0;
}

/* Liens modifier / supprimer */
a.edit, a.delete {
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
    margin: 0 5px;
    font-size: 14px;
    transition: opacity 0.2s, transform 0.2s;
}

a.edit {
    background-color: #007bff; /* bleu pour modifier */
}

a.edit:hover {
    opacity: 0.8;
    transform: scale(1.1);
}

a.delete {
    background-color: #dc3545; /* rouge pour supprimer */
}

a.delete:hover {
    opacity: 0.8;
    transform: scale(1.1);
}

/* Icônes ajoutées via pseudo-elements */
a.edit::before { content: "✏️ "; }
a.delete::before { content: "🗑️ "; }
</style>
