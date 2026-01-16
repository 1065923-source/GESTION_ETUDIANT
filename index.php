<?php
session_start();
include 'includes/db.php';

if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Vérification utilisateur
    $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE username=:user AND password=:pass");
    $stmt->execute(['user'=>$user, 'pass'=>$pass]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $_SESSION['user'] = $result['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<h2>Connexion ERP Scolaire</h2>

<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>
    <button name="login">Se connecter</button>
</form>
