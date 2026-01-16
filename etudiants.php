<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include 'includes/db.php';
include 'includes/header.php';

// Supprimer un étudiant
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM etudiants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['delete']]);
    echo "<p class='success'>Étudiant supprimé ✔</p>";
}

// Ajouter un étudiant
if (isset($_POST['add_etudiant'])) {
    $stmt = $conn->prepare("INSERT INTO etudiants (matricule, nom, prenom, classe_id) VALUES (:matricule, :nom, :prenom, :classe_id)");
    $stmt->execute([
        'matricule'=>$_POST['matricule'],
        'nom'=>$_POST['nom'],
        'prenom'=>$_POST['prenom'],
        'classe_id'=>$_POST['classe_id']
    ]);
    echo "<p class='success'>Étudiant ajouté ✔</p>";
}

// Modifier un étudiant
if (isset($_POST['update_etudiant'])) {
    $stmt = $conn->prepare("UPDATE etudiants SET matricule=:matricule, nom=:nom, prenom=:prenom, classe_id=:classe_id WHERE id=:id");
    $stmt->execute([
        'matricule'=>$_POST['matricule'],
        'nom'=>$_POST['nom'],
        'prenom'=>$_POST['prenom'],
        'classe_id'=>$_POST['classe_id'],
        'id'=>$_POST['id']
    ]);
    echo "<p class='success'>Étudiant modifié ✔</p>";
}

// Étudiant à modifier
$editEtudiant = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM etudiants WHERE id=:id");
    $stmt->execute(['id'=>$_GET['edit']]);
    $editEtudiant = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Liste des étudiants
$etudiants = $conn->query("SELECT e.*, c.nom as classe_nom FROM etudiants e LEFT JOIN classes c ON e.classe_id=c.id")->fetchAll(PDO::FETCH_ASSOC);
$classes = $conn->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestion des Étudiants</h2>

<div class="card">
<?php if($editEtudiant): ?>
<h3>Modifier un étudiant</h3>
<form method="post">
    <input type="hidden" name="id" value="<?= $editEtudiant['id']; ?>">
    <input type="text" name="matricule" value="<?= $editEtudiant['matricule']; ?>" placeholder="Matricule" required>
    <input type="text" name="nom" value="<?= $editEtudiant['nom']; ?>" placeholder="Nom" required>
    <input type="text" name="prenom" value="<?= $editEtudiant['prenom']; ?>" placeholder="Prénom" required>
    <select name="classe_id" required>
        <?php foreach($classes as $c): ?>
            <option value="<?= $c['id']; ?>" <?= ($editEtudiant['classe_id']==$c['id'])?'selected':''; ?>><?= $c['nom']; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="update_etudiant">Modifier ✏️</button>
</form>
<?php else: ?>
<h3>Ajouter un étudiant</h3>
<form method="post">
    <input type="text" name="matricule" placeholder="Matricule" required>
    <input type="text" name="nom" placeholder="Nom" required>
    <input type="text" name="prenom" placeholder="Prénom" required>
    <select name="classe_id" required>
        <?php foreach($classes as $c): ?>
            <option value="<?= $c['id']; ?>"><?= $c['nom']; ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="add_etudiant">Ajouter ➕</button>
</form>
<?php endif; ?>
</div>

<div class="card">
<table>
<tr>
<th>ID</th>
<th>Matricule</th>
<th>Nom</th>
<th>Prénom</th>
<th>Classe</th>
<th>Actions</th>
</tr>
<?php foreach($etudiants as $e): ?>
<tr>
<td><?= $e['id']; ?></td>
<td><?= $e['matricule']; ?></td>
<td><?= $e['nom']; ?></td>
<td><?= $e['prenom']; ?></td>
<td><?= $e['classe_nom']; ?></td>
<td>
<a href="etudiants.php?edit=<?= $e['id']; ?>">✏️ Modifier</a> |
<a href="etudiants.php?delete=<?= $e['id']; ?>" onclick="return confirm('Supprimer cet étudiant ?')">🗑️ Supprimer</a>
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
    padding: 15px;
}

h2 {
    text-align: center;
    color: #800000;
    margin-bottom: 15px;
}

.card {
    background: #fff;
    padding: 20px;
    margin: 15px auto;
    border-radius: 12px;
    max-width: 700px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

form input[type="text"], form input[type="number"], form select, form button {
    display: block;
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
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
    margin-top: 10px;
    font-size: 14px;
}

table th, table td {
    padding: 10px;
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
    padding: 6px 10px;
    border-radius: 6px;
    color: white;
    margin: 0 5px;
    font-size: 14px;
    transition: opacity 0.2s, transform 0.2s;
}

a.edit {
    background-color: #007bff;
}

a.edit:hover {
    opacity: 0.8;
    transform: scale(1.1);
}

a.delete {
    background-color: #dc3545;
}

a.delete:hover {
    opacity: 0.8;
    transform: scale(1.1);
}
</style>
