<?php 
    session_start(); // Avvio sessione per gestire login utente

    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'my_iisvalentin';
    
    // Connessione al database
    $conn = new mysqli($host, $username, $password, $database);
    
    // Verifica errori di connessione
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }
    
    // Controlla se il form è stato inviato
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Protezione contro SQL Injection
        $emailForm = $_POST['email'];
        $passwordForm = $_POST['password'];
    
        $sql = "SELECT * FROM Persona WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            $stmt->bind_param("ss", $emailForm, $passwordForm);
            $stmt->execute();
            $result = $stmt->get_result();
    
            if ($result->num_rows > 0) {
                // Login corretto
                $_SESSION['email'] = $emailForm; // Salvo l'email in sessione
                header("Location: index.php"); // Reindirizza alla pagina di benvenuto
                exit();
            } else {
                echo "<script>alert('Email o password errati.');</script>";
            }
            $stmt->close();
        } else {
            echo "Errore nella preparazione della query.";
        }
    }

    // Connessione chiusa
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

    <link rel="stylesheet" href="signIn-style.css" type="text/css">
</head>
<body>
    <div id="container-content">
        <div id="image">
            <img src="img/image_sign-In.png" alt="Persone che parlano">
        </div>
        <div id="form">
            <div class="form-container">
                <a href="index.php"><h1 class="title">SkillMatch</h1></a>

                <!-- htmlspecialchars() è una protezione in più contro attacchi XSS. -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="form-buttons">
                        <input type="submit" class="button" name="submit" value="Sign-In">
                        <p class="divider">Oppure</p>
                        <input type="button" class="button-opp" value="Sign-Up" onclick="window.location.href='sign-Up.php';">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>