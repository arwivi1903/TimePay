<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'classes/allClass.php';
Database::configure([]);
require_once 'functions/allFunctions.php';

$db = new Database();

if (isset($_POST['resetPassword'])) {
    error_log('Reset password form submitted');
    $email = $_POST['email'];
    error_log('Email submitted: ' . $email);
    
    $user = $db->getRow('SELECT * FROM users WHERE UserMail = ?', array($email));
    error_log('Database query executed. User found: ' . ($user ? 'Yes' : 'No'));

    if ($user) {
        try {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $data = [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry
            ];
            $where = ['UserMail' => $email];
            
            if ($db->update('users', $data, $where)) {
                $_SESSION['reset_token'] = $token;
                $_SESSION['swall'] = [
                    'type' => 'success',
                    'message' => 'Şifre sıfırlama bağlantısı oluşturuldu.'
                ];
                header('Location: reset-password.php');
                exit;
            } else {
                $_SESSION['swall'] = [
                    'type' => 'error',
                    'message' => 'Şifre sıfırlama işlemi sırasında bir hata oluştu.'
                ];
            }
        } catch (Exception $e) {
            error_log('Token generation failed: ' . $e->getMessage());
            $_SESSION['swall'] = [
                'type' => 'error',
                'message' => 'Güvenlik anahtarı oluşturulurken bir hata oluştu.'
            ];
        }
    } else {
        $_SESSION['swall'] = [
            'type' => 'error',
            'message' => 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunamadı.'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TimePay | Şifremi Unuttum</title>
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
                <p class="login-box-msg">Şifrenizi mi unuttunuz? Buradan kolayca yeni bir şifre alabilirsiniz.</p>
                <form action="" method="post">
                    <div class="input-group mb-3">
                        <div class="form-floating">
                            <input type="email" name="email" class="form-control" placeholder="" required>
                            <label>Email</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" name="resetPassword" class="btn btn-primary btn-block">Yeni Şifre İste</button>
                        </div>
                    </div>
                </form>
                <p class="mt-3 mb-1">
                    <a href="login.php" class="text-decoration-none">Giriş Yap</a>
                </p>
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