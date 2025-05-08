<?php
// creamos la conexión 
$conn = new mysqli("database", "root", "tiger", "SQLi");

if ($conn->connect_error) {
        // Excepción si nos da error de conexión
        die("Error de conexión: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST" || $_SERVER["REQUEST_METHOD"] == "GET") {
        // Recogemos los datos pasados
        $username = $_REQUEST["username"];
        $password = $_REQUEST["password"];

        print("Usuario: " . $username . "<br>");
        print("Contraseña: " . $password . "<br>");

        // preparamos la consulta
        $query = "SELECT * FROM usuarios WHERE usuario = '$username' AND contrasenya = '$password'";
        print("Consulta SQL: " . $query . "<br>");

        //realizamos la consulta y recogemos los resultados
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
        echo "Inicio de sesión exitoso";
        } else {
                echo "Usuario o contraseña incorrectos";
        }
}
$conn->close();

?>
<form method="post">
        <input type="text" name="username" placeholder="Usuario">
        <input type="password" name="password" placeholder="Contrasenya">
        <button type="submit">Iniciar Sesión</button>
</form>
