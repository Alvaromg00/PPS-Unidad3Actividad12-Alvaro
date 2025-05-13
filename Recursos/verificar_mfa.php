<?php
session_start();
$conn = new mysqli("database", "root", "tiger", "SQLi");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (!isset($_SESSION["mfa_user"])) {
    die("⚠️ No hay sesión activa para MFA.");
}

$username = $_SESSION["mfa_user"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_input = $_POST["mfa_code"];

    $query = "SELECT mfa_code, mfa_expires FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($mfa_code, $mfa_expires);
    $stmt->fetch();

    $now = new DateTime();
    $expires_time = new DateTime($mfa_expires);

    if ($code_input == $mfa_code && $now < $expires_time) {
        echo "✅ Autenticación multifactor exitosa. Bienvenido, $username.";

        // Limpieza del código MFA
        $clear = $conn->prepare("UPDATE usuarios SET mfa_code = NULL, mfa_expires = NULL WHERE usuario = ?");
        $clear->bind_param("s", $username);
        $clear->execute();

        session_destroy(); // o puedes mantener sesión como autenticado
    } else {
        echo "❌ Código incorrecto o expirado.";
    }
    $stmt->close();
}
$conn->close();
?>

<form method="post">
    <input type="text" name="mfa_code" placeholder="Código MFA" required>
    <button type="submit">Verificar Código</button>
</form>
