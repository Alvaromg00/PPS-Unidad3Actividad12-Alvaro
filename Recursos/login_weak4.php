<?php
$conn = new mysqli("database", "root", "tiger", "SQLi");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT contrasenya FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ Login correcto - generar MFA
            $mfa_code = strval(rand(100000, 999999));
            $expires = (new DateTime('+5 minutes'))->format('Y-m-d H:i:s');

            // Guardar código MFA
            $update = $conn->prepare("UPDATE usuarios SET mfa_code = ?, mfa_expires = ? WHERE usuario = ?");
            $update->bind_param("sss", $mfa_code, $expires, $username);
            $update->execute();

            // Guardar usuario en sesión para MFA
            $_SESSION["mfa_user"] = $username;

            // Redirigir a mostrar el código y luego a verificación
            header("Location: mostrar_codigo.php?code=$mfa_code");
            exit();
        } else {
            echo "❌ Contraseña incorrecta.";
        }
    } else {
        echo "❌ Usuario no encontrado.";
    }
    $stmt->close();
}
$conn->close();
?>

<form method="post">
    <input type="text" name="username" placeholder="Usuario" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <button type="submit">Iniciar sesión</button>
</form>
