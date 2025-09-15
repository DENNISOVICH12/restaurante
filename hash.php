<?php
// hash.php
$password = "admin123"; // 👉 cámbialo por la contraseña que quieras
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Contraseña en texto plano: $password\n";
echo "Hash generado (Bcrypt): $hash\n";
