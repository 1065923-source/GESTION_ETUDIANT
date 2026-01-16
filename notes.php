<?php
session_start();
if(!isset($_SESSION['user'])) header("Location:index.php");

include 'includes/db.php';
include 'includes/header.php';

// CRUD Notes
if(isset($_GET['delete'])){
    $stmt=$conn->prepare("DELETE FROM notes WHERE id=:id");
    $stmt->execute(['id'=>$_GET['delete']]);
    echo "<p style='color:red;'>Note supprimée !</p>";
}
if(isset($_POST['add_note'])){
    $stmt=$conn->prepare("INSERT INTO notes(etudiant_id,cours_id,note) VALUES(:etudiant_id,:cours_id,:note)");
    $stmt->execute([
        'etudiant_id'=>$_POST['etudiant_id'],
        'cours_id'=>$_POST['cours_id'],
        'note'=>$_POST['note']
    ]);
    echo "<p style='color:green;'>Note ajoutée !</p>";
}
if(isset($_POST['update_note'])){
    $stmt=$conn->prepare("UPDATE notes SET etudiant_id=:etudiant_id, cours_id=:cours_id, note=:note WHERE id=:id");
    $stmt->execute([
        'etudiant_id'=>$_POST['etudiant_id'],
        'cours_id'=>$_POST['cours_id'],
        'note'=>$_POST['note'],
        'id'=>$_POST['id']
    ]);
    echo "<p style='color:green;'>Note modifiée !</p>";
}

// Note à modifier
$editNote=null;
if(isset($_GET['edit'])){
    $stmt=$conn->prepare("SELECT * FROM notes WHERE id=:id");
    $stmt->execute(['id'=>$_GET['edit']]);
    $editNote=$stmt->fetch(PDO::FETCH_ASSOC);
}

// Données
$etudiants=$conn->query("SELECT * FROM etudiants")->fetchAll(PDO::FETCH_ASSOC);
$cours=$conn->query("SELECT * FROM cours")->fetchAll(PDO::FETCH_ASSOC);
$notes=$conn->query("SELECT n.*, e.nom as etudiant, c.nom as cours FROM notes n LEFT JOIN etudiants e ON n.etudiant_id=e.id LEFT JOIN cours c ON n.cours_id=c.id")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Gestion des Notes</h2>

<div class="card">
<?php if($editNote): ?>
<h3>Modifier Note</h3>
<form method="post">
<input type="hidden" name="id" value="<?= $editNote['id']; ?>">
<select name="etudiant_id" required>
<?php foreach($etudiants as $e): ?>
<option value="<?= $e['id']; ?>" <?= ($e['id']==$editNote['etudiant_id'])?'selected':''; ?>><?= $e['nom']; ?></option>
<?php endforeach; ?>
</select>
<select name="cours_id" required>
<?php foreach($cours as $c): ?>
<option value="<?= $c['id']; ?>" <?= ($c['id']==$editNote['cours_id'])?'selected':''; ?>><?= $c['nom']; ?></option>
<?php endforeach; ?>
</select>
<input type="number" name="note" value="<?= $editNote['note']; ?>" placeholder="Note" step="0.01" required>
<button type="submit" name="update_note">Modifier ✏️</button>
</form>
<?php else: ?>
<h3>Ajouter Note</h3>
<form method="post">
<select name="etudiant_id" required>
<option value="">Choisir un étudiant</option>
<?php foreach($etudiants as $e): ?>
<option value="<?= $e['id']; ?>"><?= $e['nom']; ?></option>
<?php endforeach; ?>
</select>
<select name="cours_id" required>
<option value="">Choisir un cours</option>
<?php foreach($cours as $c): ?>
<option value="<?= $c['id']; ?>"><?= $c['nom']; ?></option>
<?php endforeach; ?>
</select>
<input type="number" name="note" placeholder="Note" step="0.01" required>
<button type="submit" name="add_note">Ajouter ➕</button>
</form>
<?php endif; ?>
</div>

<div class="card">
<table>
<tr><th>ID</th><th>Étudiant</th><th>Cours</th><th>Note</th><th>Actions</th></tr>
<?php foreach($notes as $n): ?>
<tr>
<td><?= $n['id']; ?></td>
<td><?= $n['etudiant']; ?></td>
<td><?= $n['cours']; ?></td>
<td><?= $n['note']; ?></td>
<td>
<a href="notes.php?edit=<?= $n['id']; ?>">✏️ Modifier</a> |
<a href="notes.php?delete=<?= $n['id']; ?>" onclick="return confirm('Supprimer ?')">🗑️ Supprimer</a>
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
