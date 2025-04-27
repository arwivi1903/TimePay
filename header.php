<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();
session_start();

require_once 'classes/allClass.php';
Database::configure([]);
require_once 'functions/allFunctions.php';

try {
    Database::configure([]);
    $db = new Database();
    $db->connection();
} catch (Exception $e) {
    echo '<div class="alert alert-danger text-center">' . htmlspecialchars($e->getMessage()) . '</div>';
}

function oturumKontrol() {
    try {
        Database::configure([]);
        $db   = new Database();
        $data = $db->getRow(
            'SELECT UserID, UserName, UserMail, UserPass, UserPicture, HourlyRate, StartDate, Shift, UserStatus, IpAdress, CreatedDate 
            FROM users WHERE UserMail = ?',
            [$_SESSION['UserMail']]
        );

        if ($data) {
            $_SESSION = array_merge($_SESSION, [
                'UserID'      => $data->UserID,
                'UserName'    => $data->UserName,
                'UserMail'    => $data->UserMail,
                'UserPass'    => $data->UserPass,
                'UserPicture' => $data->UserPicture,
                'HourlyRate'  => $data->HourlyRate,
                'StartDate'   => $data->StartDate,
                'Shift'       => $data->Shift,
                'UserStatus'  => $data->UserStatus,
                'CreatedDate' => $data->CreatedDate
            ]);
        } else {
            session_destroy(); header('Location: login.php');
            die();
        }
    } catch(PDOException $e) {
        error_log("Veritabanı hatası: " . $e->getMessage());
        exit();
    }
}

oturumKontrol();

@$sifrelenmisUcret  = encryptData($_SESSION['HourlyRate']);

@$cozulmusUcret     = decryptData($_SESSION['HourlyRate']);
$SaatUcreti         = floatval($cozulmusUcret);

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>TimePay | Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="TimePay  | Dashboard">
    <meta name="author" content="ColorlibHQ">
    <meta name="description"
        content="yeni bir zam hesaplama projesi">
    <meta name="keywords"
        content="saat ücret, heaplama, saat ücreti, saat ücreti heaplama, saat ücreti heaplama, zam hesaplama, saat ücreti zam hesaplama, ne alırım, eczane, vardiya, saat ücreti ne alırım, saat ücreti eczane, saat ücreti vardiya">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
        integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css"
        integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css"
        integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous">

    <link rel="stylesheet" href="dist/css/adminlte.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
        integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
        integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4=" crossorigin="anonymous">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.1/css/dataTables.bootstrap5.css">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (bazı Bootstrap özellikleri için gerekli olabilir) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6F36QNZDRX"></script>
    
    
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-6F36QNZDRX');
    </script>
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2871254640514878"
     crossorigin="anonymous"></script>
</head>


<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"> <i
                                class="bi bi-list"></i> </a> </li>
                    <li class="nav-item d-none d-md-block"> <a href="index" class="nav-link">Ana Sayfa</a> </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"> <a class="nav-link" href="#" data-lte-toggle="fullscreen"> <i
                        data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i> <i
                        data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none;"></i> </a>
                    </li>
                    <li class="nav-item dropdown user-menu"> <a href="#" class="nav-link dropdown-toggle"
                            data-bs-toggle="dropdown"> <img src="<?=$_SESSION['UserPicture']?>"
                                class="user-image rounded-circle shadow" alt="User Image"> <span
                                class="d-none d-md-inline"><?=$_SESSION['UserName']?></span> </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-primary"> <img src="<?=$_SESSION['UserPicture']?>"
                                    class="rounded-circle shadow" alt="User Image">
                                <p>
                                    <?=$_SESSION['UserName']?> - <?=$_SESSION['UserMail']?>
                                    <small><?= tr_datetime($_SESSION['CreatedDate'])?></small>
                                </p>
                            </li>
                            <li class="user-body">
                                <div class="row">
                                    <div class="col-4 text-center"> <a href="zam">Zam Hesaplama</a> </div>
                                    <div class="col-4 text-center"> <a href="mesai">Mesai Hesaplama</a> </div>
                                    <div class="col-4 text-center"> <a href="tatiller">Tatiller</a> </div>
                                </div>
                            </li>
                            <li class="user-footer"> 
                                <a href="profil" class="btn btn-default btn-flat">Profil</a> 
                                <a href="logout.php" class="btn btn-default btn-flat float-end">Çıkış Yap</a> 
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>