<?php
// Einfache Test-Datei um zu prüfen, ob PHP funktioniert
echo "<h1>PHP funktioniert!</h1>";
echo "<p>PHP-Version: " . phpversion() . "</p>";
echo "<p>Zeit: " . date('Y-m-d H:i:s') . "</p>";
echo "<h2>Server-Informationen:</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";
?>
