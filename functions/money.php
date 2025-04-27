<?php

function paraFormatla($miktar, $kuruSymbol = "₺", $virgulAyiraci = ".", $noktaAyiraci = ",", $ondalikBasamakSayisi = 2) {
    // Önce verilen miktar değerinin sayısal olup olmadığını kontrol ediyoruz
    if (!is_numeric($miktar)) {
        return false;
    }

    // Miktarı virgülden sonra belirtilen ondalık basamak sayısı kadar biçimlendiriyoruz
    $miktar = number_format($miktar, $ondalikBasamakSayisi, $noktaAyiraci, $virgulAyiraci);

    // Biçimlendirilmiş miktarın başına kur simgesini ekliyoruz
    $miktar = $kuruSymbol . " " . $miktar;

    return $miktar;
}



?>