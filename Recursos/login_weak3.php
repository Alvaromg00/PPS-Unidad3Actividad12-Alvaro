<?php
// Conexión
$conn = new mysqli("database", "root", "tiger", "SQLi");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Procesamos petición
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
    $username = $_REQUEST["username"];
    $password = $_REQUEST["password"];

    print("Usuario: " . $username . "<br>");
    print("Contraseña: " . $password . "<br>");

    // Obtenemos datos del usuario
    $query = "SELECT contrasenya, failed_attempts, last_attempt FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password, $failed_attempts, $last_attempt);
        $stmt->fetch();

        $current_time = new DateTime();
        $is_blocked = false;

        // Si la cuenta está bloqueada (3 intentos fallidos)
        if ($failed_attempts >= 3 && $last_attempt !== null) {
            $last_attempt_time = new DateTime($last_attempt);
            $interval = $current_time->getTimestamp() - $last_attempt_time->getTimestamp();

            if ($interval < 900) { // Menos de 15 minutos
                $remaining = 900 - $interval;
                $minutes = floor($remaining / 60);
                $seconds = $remaining % 60;
                echo "⛔ Cuenta bloqueada. Intenta nuevamente en {$minutes} minutos y {$seconds} segundos.";
                $is_blocked = true;
            }
        }

        if (!$is_blocked) {
            // Verificamos contraseña
            if (password_verify($password, $hashed_password)) {
                echo "✅ Inicio de sesión exitoso";

                // Reiniciar intentos fallidos
                $reset_query = "UPDATE usuarios SET failed_attempts = 0, last_attempt = NULL WHERE usuario = ?";
                $reset_stmt = $conn->prepare($reset_query);
                $reset_stmt->bind_param("s", $username);
                $reset_stmt->execute();
                $reset_stmt->close();
            } else {
                // Incrementar intentos
                $failed_attempts++;
                echo "❌ Usuario o contraseña incorrectos (Intento $failed_attempts de 3)";

                $update_query = "UPDATE usuarios SET failed_attempts = ?, last_attempt = NOW() WHERE usuario = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("is", $failed_attempts, $username);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
    } else {
        echo "❌ Usuario no encontrado";
    }

    $stmt->close();
}
$conn->close();
?>

<!-- Formulario -->
<form method="post">
    <input type="text" name="username" placeholder="Usuario">
    <input type="password" name="password" placeholder="Contrasenya">
    <button type="submit">Iniciar Sesión</button>
</form>
