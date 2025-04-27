<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/allClass.php';
Database::configure([]);
require_once 'functions/allFunctions.php';

$db = new Database();

if (!isset($_SESSION['reset_token'])) {
    $_SESSION['swall'] = [
        'type' => 'error',
        'message' => 'Geçersiz şifre sıfırlama oturumu.'
    ];
    header('Location: login.php');
    exit;
}

$token = $_SESSION['reset_token'];
$currentTime = date('Y-m-d H:i:s');

// Token'ı kontrol et ve süresi geçmemiş olduğundan emin ol
$user = $db->getRow('SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > ?', array($token, $currentTime));

if (!$user) {
    unset($_SESSION['reset_token']);
    $_SESSION['swall'] = [
        'type' => 'error',
        'message' => 'Geçersiz veya süresi dolmuş şifre sıfırlama bağlantısı.'
    ];
    header('Location: login.php');
    exit;
}

if (isset($_POST['updatePassword'])) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        $_SESSION['swall'] = [
            'type' => 'error',
            'message' => 'Şifreler eşleşmiyor.'
        ];
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'UserPass' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expiry' => null
        ];
        $where = ['UserID' => $user->UserID];

        if ($db->update('users', $data, $where)) {
            unset($_SESSION['reset_token']);
            $_SESSION['swall'] = [
                'type' => 'success',
                'message' => 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.'
            ];
            header('Location: login.php');
            exit;
        } else {
            $_SESSION['swall'] = [
                'type' => 'error',
                'message' => 'Şifre güncellenirken bir hata oluştu.'
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TimePay | Şifre Sıfırlama</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="index.php" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
                    <h1 class="mb-0"><b>Time</b>Pay</h1>
                </a>
            </div>
            <div class="card-body login-card-body">
                <p class="login-box-msg">Yeni şifrenizi belirleyin</p>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="password" name="password" class="form-control" placeholder="" required>
                            <label>Yeni Şifre</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-lock"></span>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="password" name="confirm_password" class="form-control" placeholder="" required>
                            <label>Şifre Tekrar</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-lock"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="updatePassword" class="btn btn-primary btn-block">Şifreyi Güncelle</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="dist/js/adminlte.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
    <?php
    if (isset($_SESSION['swall'])) {
        $alert = $_SESSION['swall'];
        unset($_SESSION['swall']);
        ?>
        Swal.fire({
            icon: '<?php echo $alert["type"]; ?>',
            title: '<?php echo $alert["type"] == "success" ? "Başarılı!" : "Hata!"; ?>',
            text: '<?php echo $alert["message"]; ?>',
            confirmButtonText: 'Tamam'
        });
        <?php
    }
    ?>
    </script>
</body>
</html>