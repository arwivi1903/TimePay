<?php
/*Şifreleme*/
function sifreleme($mail) {
	$gizlianahtar = '6932877ce2dcc881d70859ea0ec556e1';
	return md5(sha1(md5($_SERVER['REMOTE_ADDR'] . $gizlianahtar . $mail . "zam" . date('d.m.Y H:i:s') . $_SERVER['HTTP_USER_AGENT'])));
}

// Log
function xlog($LogType,$LogDesc)
{
    $db = new Database();
    
    $logEkle = $db->Insert('INSERT INTO log SET LogType = ?, LogDesc = ?, UserID = ?', 
    array($LogType, $LogDesc, $_SESSION["UserID"]));
}

// büyük harf türkçe
function tr_strtoupper($text)
{
    $search=array("ç","i","ı","ğ","ö","ş","ü");
    $replace=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
    $text=str_replace($search,$replace,$text);
    $text=strtoupper($text);
    return $text;
}

// yet
function yetkili(){
    if ($_SESSION['UserStatus'] == 1) {
        return true;
    } else {
        return false;
    }
}
 
//post - get kontrol
function security($text) {
    if(is_array($text)) {
        foreach($text as $key => $value) {
            $text[$key] = security($value);
        }
    } else {
        $text = trim($text);
        $text = stripslashes($text);
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    return $text;
}

function temizle($deger) {
    // ₺ ve virgülleri kaldır, sayıya dönüştür
    return floatval(str_replace([',', '₺', ' '], ['', '', ''], $deger));
}

?>