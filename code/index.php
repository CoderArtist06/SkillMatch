<?php
session_start();

// Controllo login
$is_logged = isset($_SESSION['email']);

// Connessione al database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'my_iisvalentin';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

//Controllo se è stato inviato un termine di ricerca
if (isset($_GET['termine_ricerca']) && !empty(trim($_GET['termine_ricerca']))) {
    $termine = $conn->real_escape_string($_GET['termine_ricerca']);
    $query = "SELECT * FROM FigureProfessionale 
              WHERE titolo LIKE '%$termine%' 
                 OR nomeAzienda LIKE '%$termine%' 
                 OR descrizione LIKE '%$termine%'";
} else {
    $query = "SELECT * FROM FigureProfessionale";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillMatch</title>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>
    <div id="header">
        <div id="logo">
            <h1>SkillMatch</h1>
        </div>
        <div id="navbar">
            <!-- Modificato il form per puntare a index.php -->
            <form action="index.php" method="GET">
                <input type="text" id="termine_ricerca" name="termine_ricerca" placeholder="Cerca un lavoro..." value="<?php echo isset($_GET['termine_ricerca']) ? htmlspecialchars($_GET['termine_ricerca']) : ''; ?>">
                <input type="submit" value="Cerca">
            </form>
            <div class="right-buttons">
                <?php if (!$is_logged): ?>
                    <a href="sign-In.php">
                        <h3 id="signin">Sign-In</h3>
                    </a>
                    <a href="sign-Up.php">
                        <h3 id="signup">Sign-Up</h3>
                    </a>
                <?php else: ?>
                    <form action="logout.php" method="post" style="display:inline;">
                        <input type="submit" value="Log-Out" id="signup">
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="container-content">
        <div id="content">
            <h1>Le nostre offerte di lavoro</h1>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="job-offer">
                        <hr class="riga">
                        <h1><?php echo htmlspecialchars($row['titolo']); ?></h1>
                        <h3><strong>Azienda:</strong> <?php echo htmlspecialchars($row['nomeAzienda']); ?></h3>
                        <h3><strong>Indirizzo:</strong> <?php echo htmlspecialchars($row['indirizzo']); ?></h3>
                        <h3><strong>Età richiesta:</strong> <?php echo htmlspecialchars($row['età_min']); ?> - <?php echo htmlspecialchars($row['età_max']); ?> anni</h3>
                        <h3><strong>Anni di esperienza richiesti:</strong> <?php echo htmlspecialchars($row['anni_esperienza']); ?></h3>
                        <h3><strong>Descrizione:</strong> <?php echo htmlspecialchars($row['descrizione']); ?></h3>

                        <?php if ($is_logged): ?>
                            <form action="accetta_offerta.php" method="POST">
                                <input type="hidden" name="offerta_id" value="<?php echo htmlspecialchars($row['ID_f']); ?>">
                                <input type="submit" name="submit" value="Candidati ora!">
                            </form>
                        <?php else: ?>
                            <h3 style="color:blue"><em>Accedi per candidarti!</em></h3>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Non ci sono offerte disponibili al momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="footer">
        <div class="footer-content">
            <p>&copy; 2025 SkillMatch - Trova il lavoro perfetto per te!</p>
            <p>Sviluppato con passione da Ghita Valentin Cristian & Cavazzini Fabio</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Termini e Condizioni</a></p>
            <p>Licenza: GPL-3.0</p>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
