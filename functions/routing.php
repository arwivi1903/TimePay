<?php 

function go($url, $time=0){

    if($time != 0){
        header("Refresh:$time;url=$url");
    }else{
        header("Location:$url");
    }

}

function comeBack($url, $time=0){

    $url=$_SERVER["HTTP_REFERER"];
    if($time != 0 ){
        header("Refresh:$time;url=$url");
    }else{
        header("Location:$url");
    }
    
}

function swallOk($url){

    header("Location:$url?islem=ok");

}

function swallError($url){

    header("Location:$url?islem=no");

}

function swallOkFatura($url){

    header("Location:$url&islem=ok");

}

function swallErrorFatura($url){

    header("Location:$url&islem=nofatura");

}

function swallVirmanError($url){

    header("Location:$url?islem=virmanno");

}

function swallVirmanErrors($url){

    header("Location:$url?islem=virmanerror");

}

function swallVirmanZero($url){

    header("Location:$url?islem=virmanzero");

}

function swallBakiyeZero($url){

    header("Location:$url?islem=bakiyezero");

}

function swallBakiyeError($url){

    header("Location:$url?islem=bakiyeno");

}

function swallForm($url){

    header("Location:$url?islem=hata");

}

function swallPass($url){

    header("Location:$url?islem=pass");

}

function swallType($url){

    header("Location:$url?islem=type");

}

function swallSifre($url){

    header("Location:$url?islem=hata");

}

function swallSifreAyni($url){

    header("Location:$url?islem=sifreayni");

}

function swallUser($url){

    header("Location:$url?islem=user");

}

function swallMsg($url, $status){

    header("Location:$url?islem=$status");

}

function swallPassError($url, $status){

    header("Location:$url?islem=$status");

}
function swallPassTwo($url, $status){

    header("Location:$url?islem=$status");

}
function swallBos($url){

    header("Location:$url?islem=bos");

}
