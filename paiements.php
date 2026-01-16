<?php
session_start();

/* Vérification connexion */
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include 'includes/db.php';
include 'includes/header.php';

/* ===== AJOUT PAIEMENT ===== */
if (
    isset($_POST['add_paiement']) &&
    isset($_POST['etudiant_id'], $_POST['montant'], $_POST['mois'], $_POST['annee']) &&
    $_POST['etudiant_id'] !== '' &&
    $_POST['montant'] !== '' &&
    $_POST['mois'] !== '' &&
    $_POST['annee'] !== ''
) {
    $stmt = $conn->prepare("
        INSERT INTO paiements (etudiant_id, montant, mois, annee)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        (int) $_POST['etudiant_id'],
        (float) $_POST['montant'],
        trim($_POST['mois']),
        (int) $_POST['annee']
    ]);

    $message = "<p class='success'>Paiement ajouté avec succès ✔</p>";
} elseif (isset($_POST['add_paiement'])) {
    $message = "<p class='error'>Veuillez remplir tous les champs</p>";
}

/* ===== LISTES ===== */
$etudiants = $conn->query("SELECT id, nom FROM etudiants ORDER BY nom")->fetchAll();

$paiements = $conn->query("
    SELECT p.*, e.nom AS etudiant
    FROM paiements p
    JOIN etudiants e ON e.id = p.etudiant_id
    ORDER BY p.date_paiement DESC
")->fetchAll();
?>

<h2>Gestion des Paiements</h2>

<?= $message ?? '' ?>

<div class="card">
    <h3>Ajouter un paiement</h3>
    <form method="post">
        <select name="etudiant_id" required>
            <option value="">-- Sélectionner un étudiant --</option>
            <?php foreach ($etudiants as $e): ?>
                <option value="<?= $e['id'] ?>">
                    <?= htmlspecialchars($e['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="montant" placeholder="Montant" min="0" required>

        <select name="mois" required>
            <option value="">-- Mois --</option>
            <option>Janvier</option>
            <option>Février</option>
            <option>Mars</option>
            <option>Avril</option>
            <option>Mai</option>
            <option>Juin</option>
            <option>Juillet</option>
            <option>Août</option>
            <option>Septembre</option>
            <option>Octobre</option>
            <option>Novembre</option>
            <option>Décembre</option>
        </select>

        <input type="number" name="annee" placeholder="Année (ex: 2024)" min="2000" max="2100" required>

        <button type="submit" name="add_paiement">Ajouter ➕</button>
    </form>
</div>

<div class="card">
    <table>
        <tr>
            <th>ID</th>
            <th>Étudiant</th>
            <th>Mois</th>
            <th>Année</th>
            <th>Montant</th>
        </tr>

        <?php foreach ($paiements as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= htmlspecialchars($p['etudiant']) ?></td>
            <td><?= $p['mois'] ?></td>
            <td><?= $p['annee'] ?></td>
            <td><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
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
    max-width: 720px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}

form input, form select, form button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
}

form button {
    background: #800000;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    border: none;
}

form button:hover {
    background: #5c0000;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

table th, table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

table th {
    background: #800000;
    color: #fff;
}

table tr:nth-child(even) {
    background: #f9f0f0;
}

.success {
    color: #28a745;
    font-weight: bold;
    text-align: center;
}

.error {
    color: #dc3545;
    font-weight: bold;
    text-align: center;
}
</style>
