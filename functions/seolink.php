<?php
// Seo Link
function seourl($str){
    $str = mb_strtolower(trim($str), 'UTF-8');
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return $str;
 }
?>