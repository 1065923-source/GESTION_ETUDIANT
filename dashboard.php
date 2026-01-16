<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

include 'includes/db.php';

$role = $_SESSION['user']['role'] ?? 'user';

/* ===== STATS ===== */
$totalEtudiants = $conn->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$totalClasses   = $conn->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalProfs     = $conn->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();

/* ===== DATA ===== */
$classes = $conn->query("SELECT * FROM classes")->fetchAll(PDO::FETCH_ASSOC);
$cours   = $conn->query("SELECT * FROM cours")->fetchAll(PDO::FETCH_ASSOC);

/* ===== FACTURATION PAR MOIS ===== */
$factures = $conn->query("
    SELECT mois, SUM(montant) as total
    FROM paiements
    GROUP BY mois
")->fetchAll(PDO::FETCH_ASSOC);

$mois = [];
$totaux = [];
foreach ($factures as $f) {
    $mois[] = $f['mois'];
    $totaux[] = $f['total'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard UCao</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#f6f7fb;display:flex;min-height:100vh;transition:.3s;}
body.dark{background:#121212;color:#eee;}
#loader{position:fixed;inset:0;background:#800000;display:flex;align-items:center;justify-content:center;z-index:9999;}
.spinner{width:60px;height:60px;border:6px solid #fff;border-top:6px solid transparent;border-radius:50%;animation:spin 1s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

/* SIDEBAR */
.sidebar{width:240px;background:#800000;color:#fff;padding:20px;transition:all .3s;}
.sidebar h2{text-align:center;margin-bottom:30px;font-weight:bold;}
.sidebar a{display:flex;align-items:center;gap:12px;color:#fff;padding:12px 15px;margin:8px 0;text-decoration:none;border-radius:8px;transition:0.3s;}
.sidebar a:hover{background:#5c0000;transform:scale(1.02);}

/* MAIN */
.main{flex:1;padding:25px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;}
.topbar div{display:flex;align-items:center;gap:15px;}
.badge{background:#ff4d4d;color:#fff;padding:6px 12px;border-radius:20px;font-size:14px;font-weight:bold;}
button{border:none;background:#800000;color:#fff;padding:10px 14px;border-radius:50%;cursor:pointer;font-size:16px;transition:0.3s;}
button:hover{background:#5c0000;transform:scale(1.1);}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:30px;}
.stat{background:#fff;padding:25px;border-radius:15px;box-shadow:0 10px 20px rgba(0,0,0,.12);text-align:center;transition:.6s;opacity:0;transform:translateY(30px);}
.stat.visible{opacity:1;transform:translateY(0);}
.stat i{font-size:35px;color:#800000;margin-bottom:10px;}
.stat h4{color:#800000;margin-bottom:8px;}
.stat p{font-size:28px;font-weight:bold;}
body.dark .stat{background:#1e1e1e;}

/* CARD */
.card{background:#fff;padding:25px;border-radius:15px;box-shadow:0 10px 20px rgba(0,0,0,.12);margin-bottom:30px;opacity:0;transform:translateY(20px);transition:.8s;}
.card.visible{opacity:1;transform:translateY(0);}
.card h3{margin-bottom:20px;color:#800000;}
body.dark .card{background:#1e1e1e;}

/* TABLES */
table{width:100%;border-collapse:collapse;margin-top:15px;font-size:14px;}
table th, table td{padding:12px;text-align:left;border-bottom:1px solid #eee;}
table th{background-color:#800000;color:white;}
table tr:nth-child(even){background-color:#f9f0f0;}
table tr:hover{background-color:#f2dede;}

/* RESPONSIVE */
@media(max-width:768px){.sidebar{display:none}}
</style>
</head>

<body>
<div id="loader"><div class="spinner"></div></div>

<div class="sidebar">
    <h2>UCao</h2>
    <a href="#"><i class="fa fa-home"></i> Dashboard</a>
    <a href="etudiants.php"><i class="fa fa-user-graduate"></i> Étudiants</a>
    <a href="classes.php"><i class="fa fa-school"></i> Classes</a>
    <a href="enseignants.php"><i class="fa fa-chalkboard-user"></i> Enseignants</a>
    <a href="paiement.php"><i class="fa fa-money-bill"></i> Paiements</a>
    <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Déconnexion</a>
</div>

<div class="main">
<div class="topbar">
    <div>
        <div>Bienvenue <strong><?= ucfirst($role) ?></strong></div>
        <small><?= date('d/m/Y H:i') ?></small>
    </div>
    <div>
        <span class="badge">🔔 3 notifications</span>
        <button onclick="toggleDark()">🌙</button>
    </div>
</div>

<div class="stats">
    <div class="stat"><i class="fa fa-user-graduate"></i><h4>Étudiants</h4><p><?= $totalEtudiants ?></p></div>
    <div class="stat"><i class="fa fa-school"></i><h4>Classes</h4><p><?= $totalClasses ?></p></div>
    <div class="stat"><i class="fa fa-chalkboard-user"></i><h4>Enseignants</h4><p><?= $totalProfs ?></p></div>
</div>

<div class="card">
    <h3>Étudiants par classe</h3>
    <canvas id="classeChart"></canvas>
</div>

<?php if($role==='admin'||$role==='finance'): ?>
<div class="card">
    <h3>Facturation</h3>
    <canvas id="factureChart"></canvas>
</div>
<?php endif; ?>

<div class="card">
    <h3>Moyenne des notes</h3>
    <canvas id="notesChart"></canvas>
</div>

</div>

<script>
window.onload=()=>document.getElementById('loader').style.display='none';
function toggleDark(){document.body.classList.toggle('dark');}

/* ===== FADE-IN ===== */
const observer=new IntersectionObserver(entries=>{
    entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible');}});
},{threshold:0.1});
document.querySelectorAll('.stat, .card').forEach(el=>observer.observe(el));

/* ===== CHARTS ===== */
new Chart(classeChart,{
    type:'bar',
    data:{
        labels:[<?php foreach($classes as $c){echo "'".$c['nom']."',";} ?>],
        datasets:[{
            data:[<?php foreach($classes as $c){$q=$conn->prepare("SELECT COUNT(*) FROM etudiants WHERE classe_id=?");$q->execute([$c['id']]);echo $q->fetchColumn().",";} ?>],
            backgroundColor: function(ctx){const colors=['#ff9999','#ff4d4d','#ff1a1a','#800000','#b30000','#cc0000'];return colors[ctx.dataIndex%colors.length];}
        }]
    },
    options:{plugins:{legend:{display:false}}}
});

<?php if($role==='admin'||$role==='finance'): ?>
new Chart(factureChart,{
    type:'line',
    data:{
        labels:<?= json_encode($mois) ?>,
        datasets:[{
            data:<?= json_encode($totaux) ?>,
            borderColor:'#800000',
            backgroundColor:'rgba(255,77,77,0.2)',
            fill:true,
            tension:.4,
            pointRadius:6,
            pointBackgroundColor:'#800000'
        }]
    },
    options:{
        plugins:{
            legend:{display:false},
            tooltip:{backgroundColor:'#800000',titleColor:'#fff',bodyColor:'#fff'}
        }
    }
});
<?php endif; ?>

new Chart(notesChart,{
    type:'bar',
    data:{
        labels:[<?php foreach($cours as $c){echo "'".$c['nom']."',";} ?>],
        datasets:[{
            data:[<?php foreach($cours as $c){$q=$conn->prepare("SELECT AVG(note) FROM notes WHERE cours_id=?");$q->execute([$c['id']]);echo round($q->fetchColumn(),2).",";} ?>],
            backgroundColor: function(ctx){const colors=['#ffb3b3','#ff6666','#ff3333','#800000','#b30000','#cc0000'];return colors[ctx.dataIndex%colors.length];}
        }]
    },
    options:{scales:{y:{beginAtZero:true,max:20}},plugins:{legend:{display:false},tooltip:{backgroundColor:'#800000',titleColor:'#fff',bodyColor:'#fff'}}}
});
</script>

</body>
</html>
