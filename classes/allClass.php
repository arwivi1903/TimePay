<?php
// Autoload fonksiyonunu tanımlıyoruz
spl_autoload_register(function($class) {
    $path = __DIR__ . "/" . $class . ".class.php"; 
    
    if (file_exists($path)) {
        require_once $path;
    } else {
        echo "Dosya bulunamadı: " . $path . "<br>";
    }
});
?>
