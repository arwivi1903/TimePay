<?php
// functions klasöründeki tüm dosyaları dahil et
$functionsDir = __DIR__ . "/"; // functions klasörünün tam yolu

// echo "functions klasörü yolu: " . $functionsDir . "<br>";

if (is_dir($functionsDir)) {
    // Klasördeki tüm .php dosyalarını al
    $phpFiles = glob($functionsDir . "*.php"); // php dosyalarını al
    
    // Dosya listesi var mı kontrol et
    if ($phpFiles) {
        foreach ($phpFiles as $functionFile) {
            // echo "Dahil edilen dosya: " . $functionFile . "<br>";
            require_once $functionFile; // Dosyayı dahil et
        }
    } else {
        echo "PHP dosyaları bulunamadı!";
    }
} else {
    echo "Functions klasörü bulunamadı!";
}

// var_dump($phpFiles); // Tüm bulunan dosyaları kontrol et

// Fonksiyonun zaten tanımlı olup olmadığını kontrol et
if (!function_exists('swallAlert')) {
    function swallAlert($type, $message, $redirect = null) {
        $_SESSION['swall'] = [
            'type' => $type,
            'message' => $message
        ];
        
        if ($redirect) {
            header("Location: $redirect");
            exit;
        }
    }
}

if (!function_exists('SwallUserPasif')) {
    function SwallUserPasif($yonlendir) {
        swallAlert('error', 'Kullanıcı adı veya şifre hatalı!', $yonlendir.'.php');
    }
}

if (!function_exists('swallOk')) {
    function swallOk($url) {
        swallAlert('success', 'İşlem başarıyla gerçekleşti!', $url);
    }
}

if (!function_exists('swallError')) {
    function swallError($url) {
        swallAlert('error', 'İşlem sırasında bir hata oluştu!', $url);
    }
}

if (!function_exists('swallForm')) {
    function swallForm($url) {
        swallAlert('error', 'Lütfen tüm alanları doldurunuz!', $url);
    }
}

if (!function_exists('swallPass')) {
    function swallPass($url) {
        swallAlert('error', 'Şifreler eşleşmiyor!', $url);
    }
}

?>