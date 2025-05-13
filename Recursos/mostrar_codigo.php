<?php
$code = $_GET["code"] ?? "XXXXXX";
echo "<h2>🔐 Tu código MFA es: <strong>$code</strong></h2>";
echo "<a href='verificar_mfa.php'>Ir a verificación MFA</a>";
?>
