<?php
session_start();

// Controllo login
if (!isset($_SESSION['email'])) {
    die("Utente non loggato.");
}

// Connessione al database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'my_iisvalentin';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

if (isset($_POST["submit"]) && isset($_POST['offerta_id'])) {
    $emailUtente = $_SESSION['email'];
    $idOfferta = $_POST['offerta_id'];

    // Recupera il CF della persona
    $sqlPersona = "SELECT CF FROM Persona WHERE email = ?";
    $stmtPersona = $conn->prepare($sqlPersona);
    $stmtPersona->bind_param("s", $emailUtente);
    $stmtPersona->execute();
    $resultPersona = $stmtPersona->get_result();

    if ($resultPersona->num_rows == 1) {
        $rowPersona = $resultPersona->fetch_assoc();
        $cfUtente = $rowPersona['CF'];

        // Verifica se esiste già un interesse per questa offerta
        $sqlCheck = "SELECT * FROM Interessata WHERE CF_p = ? AND ID_f = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $cfUtente, $idOfferta);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            echo "Hai già inviato la candidatura per questa offerta!";
        } else {
            // Inserisce nella tabella Interessata
            $sqlInsert = "INSERT INTO Interessata (CF_p, ID_f) VALUES (?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param("si", $cfUtente, $idOfferta);

            if ($stmtInsert->execute()) {
                echo "Candidatura inviata con successo!";
                header("Location: index.php");
                exit();
            } else {
                echo "Errore nell'invio della candidatura: " . $stmtInsert->error;
            }

            $stmtInsert->close();
        }

        $stmtCheck->close();
    } else {
        echo "Errore: utente non trovato.";
    }

    $stmtPersona->close();
} else {
    echo "Errore: dati mancanti.";
}

$conn->close();
?>
