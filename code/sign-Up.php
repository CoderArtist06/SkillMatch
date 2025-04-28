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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomeForm = $_POST['nome'];
    $cognomeForm = $_POST['cognome'];
    $dataNascitaForm = $_POST['dataN'];
    $residenzaForm = $_POST['residenza'];
    $codiceFiscaleForm = $_POST['codiceFiscale'];
    $telefonoForm = $_POST['telefono'];
    $emailForm = $_POST['email'];
    $passwordForm = $_POST['password'];

    /* 
        È fondamentale salvare la password in un formato non leggibile, ad esempio utilizzando una funzione di hashing:

        $passwordForm = password_hash($_POST['password'], PASSWORD_DEFAULT);

        Il problema emerge al momento del login: non riesco a verificare se la password corrisponde a quella salvata per l'utente registrato.
    */

    // Gestione file CV
    $cvFile = null;
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] == UPLOAD_ERR_OK) {
        $cvFile = file_get_contents($_FILES['cv']['tmp_name']);
    }

    // Gestione file Foto
    $fotoFile = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $fotoFile = file_get_contents($_FILES['foto']['tmp_name']);
    }

    $sql = "INSERT INTO Persona (nome, cognome, età, residenza, codiceFiscale, telefono, email, password, cv, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param(
            "ssssssssss", 
            $nomeForm, 
            $cognomeForm, 
            $dataNascitaForm, 
            $residenzaForm, 
            $codiceFiscaleForm, 
            $telefonoForm, 
            $emailForm, 
            $passwordForm, 
            $cvFile, 
            $fotoFile
        );

        if ($stmt->execute()) {
            echo "<script>alert('Registrazione avvenuta con successo!'); window.location.href='sign-In.php';</script>";
        } else {
            echo "Errore durante l'inserimento: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Errore nella preparazione della query: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillMatch</title>

    <!-- Font Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="signUp-style.css" type="text/css">
</head>
<body>
    <div id="container-content">
        <div id="form">
            <div class="form-container">
                <a href="index.php"><h1 class="title">SkillMatch</h1></a>

                <!-- htmlspecialchars() è una protezione in più contro attacchi XSS. -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="input-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        <div class="input-group">
                            <label for="cognome">Cognome</label>
                            <input type="text" id="cognome" name="cognome" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="dataN">Data di nascita</label>
                            <input type="date" id="dataN" name="dataN" required>
                        </div>
                        <div class="input-group">
                            <label for="residenza">Residenza</label>
                            <input type="text" id="residenza" name="residenza" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="codiceFiscale">Codice Fiscale</label>
                            <input type="text" id="codiceFiscale" name="codiceFiscale" required>
                        </div>
                        <div class="input-group">
                            <label for="telefono">Telefono</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="input-group">
                            <label for="cv">CV</label>
                            <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx">
                        </div>
                        <div class="input-group">
                            <label for="foto">Foto</label>
                            <input type="file" id="foto" name="foto" accept=".png">
                        </div>
                    </div>

                    <div class="form-buttons">
                        <input type="submit" class="button" name="submit" value="Sign-Up">
                        <p class="divider">Oppure</p>
                        <input type="button" class="button-opp" value="Sign-In" onclick="window.location.href='sign-In.php';">
                    </div>
                </form>
            </div>
        </div>
        <div id="image">
            <img src="img/image_sign-Up.png" alt="Persone che parlano">
        </div>
    </div>
</body>
</html>
