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
    $idFiguraProfessionale = $_POST['offerta_id']; // L'ID della figura professionale

    // Recupera il CF e il nome della persona
    $sqlPersona = "SELECT CF, nome FROM Persona WHERE email = ?";
    $stmtPersona = $conn->prepare($sqlPersona);
    if ($stmtPersona === false) {
        die("Errore nella preparazione della query (Persona): " . $conn->error);
    }
    $stmtPersona->bind_param("s", $emailUtente);
    $stmtPersona->execute();
    $resultPersona = $stmtPersona->get_result();

    if ($resultPersona->num_rows == 1) {
        $rowPersona = $resultPersona->fetch_assoc();
        $cfUtente = $rowPersona['CF'];
        $nomeUtente = $rowPersona['nome'];

        // Verifica se esiste già un interesse per questa offerta
        $sqlCheck = "SELECT * FROM Interessata WHERE CF_p = ? AND ID_f = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        if ($stmtCheck === false) {
            die("Errore nella preparazione della query (Interessata - Check): " . $conn->error);
        }
        $stmtCheck->bind_param("si", $cfUtente, $idFiguraProfessionale);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            echo "Hai già inviato la candidatura per questa offerta!";
        } else {
            // Inserisce nella tabella Interessata
            $sqlInsert = "INSERT INTO Interessata (CF_p, ID_f) VALUES (?, ?)";
            $stmtInsert = $conn->prepare($sqlInsert);
            if ($stmtInsert === false) {
                die("Errore nella preparazione della query (Interessata - Insert): " . $conn->error);
            }
            $stmtInsert->bind_param("si", $cfUtente, $idFiguraProfessionale);

            if ($stmtInsert->execute()) {
                echo "Candidatura inviata con successo!";

                // Recupera il titolo della figura professionale e il nome dell'azienda
                $sqlFigura = "SELECT titolo, nomeAzienda FROM FigureProfessionale WHERE ID_f = ?";
                $stmtFigura = $conn->prepare($sqlFigura);
                if ($stmtFigura === false) {
                    die("Errore nella preparazione della query (FigureProfessionale): " . $conn->error);
                }
                $stmtFigura->bind_param("i", $idFiguraProfessionale);
                $stmtFigura->execute();
                $resultFigura = $stmtFigura->get_result();

                if ($resultFigura->num_rows == 1) {
                    $rowFigura = $resultFigura->fetch_assoc();
                    $titoloFiguraProfessionale = $rowFigura['titolo'];
                    $nomeAzienda = $rowFigura['nomeAzienda'];

                    // EMAIL
                    $dataCandidatura = new DateTime();
                    $giorniAggiunti = 0;
                    while ($giorniAggiunti < 7) {
                        $dataCandidatura->modify('+1 day');
                        $giornoSettimana = $dataCandidatura->format('N'); // 1 (Lunedì) - 7 (Domenica)
                        if ($giornoSettimana >= 1 && $giornoSettimana <= 5) { // Esclude sabato e domenica
                            $giorniAggiunti++;
                        }
                    }
                    $dataAppuntamento = $dataCandidatura->format('Y-m-d');

                    $to = $_SESSION['email'];
                    $subject = "Conferma della Candidatura - SkillMatch";
                    $message = "Gentile " . $nomeUtente . ",\n\n"
                                . "Siamo lieti di confermare la tua candidatura per \"" . $titoloFiguraProfessionale . "\" presso \"" . $nomeAzienda . "\".\n\n"
                                . "Il colloquio si terrà presso la sede dell'offerta di lavoro di cui hai fatto richiesta.\n\n"
                                . "La preghiamo di contattarci per qualsiasi ulteriore informazione o esigenza.\n\n"
                                . "Cordiali saluti,\n"
                                . "Il Team di SkillMatch";
                    $headers = "From: info@skillmatch.com\r\n";
                    $headers .= "Reply-To: info@skillmatch.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                    if (mail($to, $subject, $message, $headers)) {
                        echo "Email inviata con successo!";
                    } else {
                        echo "Errore nell'invio dell'email.";
                    }

                    header("Location: index.php");
                    exit();
                } else {
                    echo "Errore: dettagli della figura professionale non trovati.";
                }
                $stmtFigura->close();

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