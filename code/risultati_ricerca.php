<?php
session_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'my_iisvalentin';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$termine = isset($_GET['termine_ricerca']) ? trim($_GET['termine_ricerca']) : '';

$query = "SELECT * FROM FigureProfessionale WHERE 1=1";

if (!empty($termine)) {
    $termine = $conn->real_escape_string($termine);
    $query .= " AND (
        titolo LIKE '%$termine%' OR
        nomeAzienda LIKE '%$termine%' OR
        descrizione LIKE '%$termine%'
    )";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Risultati della Ricerca</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Risultati per "<?php echo htmlspecialchars($termine); ?>"</h1>
    
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="job-offer">
                <h2><?php echo htmlspecialchars($row['titolo']); ?></h2>
                <p><strong>Azienda:</strong> <?php echo htmlspecialchars($row['nomeAzienda']); ?></p>
                <p><strong>Esperienza:</strong> <?php echo htmlspecialchars($row['anni_esperienza']); ?> anni</p>
                <p><strong>Età richiesta:</strong> <?php echo htmlspecialchars($row['età_min']); ?> - <?php echo htmlspecialchars($row['età_max']); ?></p>
                <p><strong>Descrizione:</strong> <?php echo htmlspecialchars($row['descrizione']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nessun risultato trovato.</p>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
