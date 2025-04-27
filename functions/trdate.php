<?php

date_default_timezone_set('Europe/Istanbul');

function date_turkey($mytime){

    $en_months = array("January","February","March","April","May","June","July","August","September","October","November","December");
    $tr_months = array("Ocak","Şubat","Mart","Nisam","Mayıs","Haziran","Temmuz","Ağustos","Eylül","Ekim","Kasım","Aralık");

    $en_days = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
    $tr_days = array("Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi","Pazar");

    $mytime = str_replace($en_months,$tr_months,$mytime);
    $mytime = str_replace($en_days,$tr_days,$mytime);

    return $mytime;

}

function tr_date($tarih)
{
    if (!$tarih) {
        return '-';
    }
    $d = DateTime::createFromFormat('Y-m-d', $tarih, new DateTimeZone('Europe/Istanbul'));
    if (!$d) {
        return '-';
    }
    return $d->format('d.m.Y');
}

function tr_datetime($tarih)
{
    $d=DateTime::createFromFormat('Y-m-d H:i:s', $tarih, new DateTimeZone('Europe/Istanbul'));
	return date_format($d, 'd.m.Y H:i:s');
}

function simdi()
{
    // Geçerli tarihi Avrupa/İstanbul saat diliminde al
    $d = new DateTime('now', new DateTimeZone('Europe/Istanbul'));
    return $d->format('d.m.Y');
}



?>