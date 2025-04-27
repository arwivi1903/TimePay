<?php
function tum_bosluklari_temizle($metin) {
    // Tüm boşluk karakterlerini kaldır
    $metin = preg_replace('/\s+/u', '', $metin);
    // İşaretlenmemiş boşluk karakterlerini kaldır
    $metin = trim($metin);
    return $metin;
}
?>