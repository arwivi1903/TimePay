<?php
session_start();      // Oturumu başlat
session_unset();      // Oturumdaki tüm değişkenleri temizle
session_destroy();    // Oturumu sonlandır
setcookie(session_name(), '', time() - 3600, '/'); // Oturum çerezini temizle

header("Location: login.php");  // Kullanıcıyı giriş sayfasına yönlendir
exit();  // Kodun devamını engelle
?>
