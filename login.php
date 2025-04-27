<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); 

require_once 'classes/allClass.php';
Database::configure([]);
require_once 'functions/allFunctions.php';

$db = new Database();

if (isset($_POST['Login'])) {
    $usermail   = $_POST['UserMail'];
    $userpass   = $_POST['UserPass'];

    $kullanicisor = $db->getRow('SELECT * FROM users WHERE UserMail = ?', array($usermail));

    if (!$kullanicisor) {
        header("Location: login.php?durum=error&message=kullanici_bulunamadi");
        exit;
    } else {
        if (password_verify($userpass, $kullanicisor->UserPass)) {
            session_regenerate_id(true);
            $_SESSION["Login"]          = true;
            $_SESSION['SessionMail']    = sifreleme($usermail); 
            $_SESSION['UserMail']       = $kullanicisor->UserMail;
            $_SESSION['UserID']         = $kullanicisor->UserID;
            $_SESSION['UserName']       = $kullanicisor->UserName;
            $_SESSION['UserPicture']    = $kullanicisor->UserPicture;

            $IpAdres = $_SERVER['REMOTE_ADDR'];

            $data = [
                'IpAdress' => $IpAdres,
                'SessionMail' => sifreleme($usermail),
            ];
            $where = [
                'UserMail' => $usermail,
            ];

            $ipkaydet = $db->update('users', $data, $where);

            if ($ipkaydet) {
                header("Location: login.php?durum=success");
                exit;
            } else {
                header("Location: login.php?durum=error&message=ip_guncelleme_hatasi");
                exit;
            }
        } else {
            header("Location: login.php?durum=error&message=hatali_sifre");
            exit;
        }
    }
}

logVisitorInfo();

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>TimePay | Giriş Yap</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="title" content="TimePay 4 | Giriş Yap">
    <meta name="author" content="ColorlibHQ">
    <meta name="description" content="TimePay is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS.">
    <meta name="keywords" content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css" integrity="sha256-dSokZseQNT08wYEWiz5iLI8QPlKxG+TswNRD8k35cpg=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css" integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous">
    <link rel="stylesheet" href="dist/css/adminlte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">

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
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header"> <a href="index.php" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
                    <h1 class="mb-0"> <b>Time</b>Pay
                    </h1>
                </a> </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Oturumunuzu başlatmak için oturum açın</p>
                <form action="" method="post">
                    <div class="input-group mb-1">
                        <div class="form-floating"> <input id="loginEmail" type="email" name="UserMail" class="form-control" value="" placeholder=""> <label for="loginEmail">Email</label> </div>
                        <div class="input-group-text"> <span class="bi bi-envelope"></span> </div>
                    </div>
                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="loginPassword" type="password" name="UserPass" class="form-control" placeholder="">
                            <label for="loginPassword">Şifre</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                        <div class="input-group-text" style="cursor: pointer" onclick="togglePasswordVisibility()">
                            <span class="bi bi-eye-fill" id="togglePassword"></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 text-end">
                            <a href="forgot-password.php" class="text-decoration-none">Şifremi Unuttum</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-8 d-inline-flex align-items-center">
                            <div class="form-check"> <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault"> 
                                <label class="form-check-label" for="flexCheckDefault">
                                   Beni Hatırla
                                </label> 
                            </div>
                        </div> 
                        <div class="col-4">
                            <div class="d-grid gap-2"> <button type="submit" name="Login" class="btn btn-primary">Giriş Yap</button> </div>
                        </div>
                    </div> 
                </form>
                <!-- <div class="social-auth-links text-center mb-3 d-grid gap-2">
                    <p>- Diğer Seçenekler -</p> 
                    <a href="#" class="btn btn-primary"> <i class="bi bi-apple me-2"></i> Apple Sign in using </a> 
                    <a href="#" class="btn btn-danger"> <i class="bi bi-google me-2"></i> Sign in using Google
                    </a>
                </div>  -->
                <p class="mb-1"> Hesabınız yok mu? <a href="register.php"> Üye Ol</a> </p>
                <!-- <p class="mb-0"> Eski sitede hesap yapmak için <a href="https://bilal.teknikservis.club/zam/old">tıklayınız</a> </p> -->
            </div> <!-- /.login-card-body -->
        </div>
    </div> <!-- /.login-box --> <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-H2VM7BKda+v2Z4+DRy69uknwxjyDRhszjXFhsL4gD3w=" crossorigin="anonymous"></script> <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha256-whL0tQWoY1Ku1iskqPFvmZ+CHsvmRWx/PIoEvIeWh4I=" crossorigin="anonymous"></script> <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script> <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(TimePay)-->
    <script src="dist/js/adminlte.js"></script> <!--end::Required Plugin(TimePay)--><!--begin::OverlayScrollbars Configure-->
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script> <!--end::OverlayScrollbars Configure--> <!--end::Script-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
    // URL parametrelerini kontrol et
    const urlParams = new URLSearchParams(window.location.search);
    const durum = urlParams.get('durum');
    const message = urlParams.get('message');

    if (durum === 'error') {
        let errorMessage = '';
        switch(message) {
            case 'kullanici_bulunamadi':
                errorMessage = 'Kullanıcı bulunamadı!';
                break;
            case 'hatali_sifre':
                errorMessage = 'Kullanıcı adı veya şifre hatalı!';
                break;
            case 'ip_guncelleme_hatasi':
                errorMessage = 'Giriş yapılırken bir hata oluştu!';
                break;
            default:
                errorMessage = 'Bir hata oluştu!';
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: errorMessage,
            confirmButtonText: 'Tamam'
        });
    } else if (durum === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: 'Giriş yapılıyor...',
            showConfirmButton: false,
            timer: 1500
        }).then(function() {
            window.location = "index.php";
        });
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
    <?php
    if (isset($_SESSION['swall'])) {
        $alert = $_SESSION['swall'];
        unset($_SESSION['swall']);
        ?>
        Swal.fire({
            icon: '<?php echo $alert['type']; ?>',
            title: '<?php echo $alert['type'] == 'success' ? 'Başarılı!' : 'Hata!'; ?>',
            text: '<?php echo $alert['message']; ?>',
            <?php if ($alert['type'] == 'success') { ?>
            showConfirmButton: false,
            timer: 1500
            <?php } else { ?>
            confirmButtonText: 'Tamam'
            <?php } ?>
        })<?php if ($alert['type'] == 'success') { ?>.then(function() {
            if (typeof redirect !== 'undefined') {
                window.location = redirect;
            }
        })<?php } ?>;
        <?php
    }
    ?>
    </script>
    <script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('loginPassword');
        const toggleIcon = document.getElementById('togglePassword');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye-fill');
            toggleIcon.classList.add('bi-eye-slash-fill');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash-fill');
            toggleIcon.classList.add('bi-eye-fill');
        }
    }
    </script>
</body><!--end::Body-->

</html>