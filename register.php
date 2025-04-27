<?php
require_once 'classes/allClass.php';
require_once 'functions/allFunctions.php';

session_start();
ob_start();

$alertScript = '';

$registration = new Registration();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $registration->register($_POST);
   
    $alertConfig = [
        'position' => 'top-end',
        'icon' => $result['success'] ? 'success' : 'error',
        'title' => $result['message'],
        'showConfirmButton' => false,
        'toast' => true,
        'timer' => $result['success'] ? 1000 : 5000
    ];
    
    $alertScript = "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire(" . json_encode($alertConfig) . ")" . 
            ($result['success'] ? ".then(function() { window.location.href = '{$result['redirect']}'; })" : "") . 
            ";
        });
    </script>";
    
    echo "<script>
    Swal.fire(" . json_encode($alertConfig) . ").then((result) => {
        if (" . ($result['success'] ? 'true' : 'false') . ") {
            window.location.href = '" . $result['redirect'] . "';
        }
    });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimePay | Üye Ol</title>
    <meta name="author" content="Bilal Sami Zahit ÖZGÜL">
    <meta name="description" content="TimePay sistemi ile üye olun.">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="register-page bg-body-secondary">
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <a href="register.php"
                    class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover">
                    <h1 class="mb-0"><b>Hesap</b>LTE</h1>
                </a>
            </div>

            <div class="card-body register-card-body">
                <p class="register-box-msg">Yeni bir üyelik kaydı yapın</p>

                <form action="" method="post">
                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="registerFullName" name="registerFullName" type="text" class="form-control"
                                placeholder="" required>
                            <label for="registerFullName">Ad Soyad</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-person"></span>
                        </div>
                    </div>

                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="registerEmail" name="registerEmail" type="email" class="form-control"
                                placeholder="" required>
                            <label for="registerEmail">Email</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-envelope"></span>
                        </div>
                    </div>

                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="registerPassword" name="registerPassword" type="password" class="form-control"
                                placeholder="" required>
                            <label for="registerPassword">Şifre</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-lock-fill"></span>
                        </div>
                    </div>

                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <select name="Shift" id="Shift" class="form-control">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="G">Gündüz</option>
                            </select>
                            <label for="Shift">Vardiya</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-house-door-fill"></span>
                        </div>
                    </div>

                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="Price" name="Price" type="text" class="form-control" 
                                   placeholder="Saat Ücreti" required 
                                   pattern="^₺?\s*\d+([.,]\d{0,2})?$"
                                   title="Lütfen geçerli bir saat ücreti giriniz (Örnek: ₺270,93)"
                                   value="₺0,00">
                            <label for="Price">Saat Ücreti</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-cash"></span>
                        </div>
                    </div>

                    <div class="input-group mb-1">
                        <div class="form-floating">
                            <input id="StartDate" name="StartDate" type="date" class="form-control" placeholder="İşbaşı Tarihi" required>
                            <label for="StartDate">İşbaşı Tarihi</label>
                        </div>
                        <div class="input-group-text">
                            <span class="bi bi-calendar"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-8 d-inline-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    Şartları kabul <a href="sozlesme" target="_blank">ediyorum</a>
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Kayıt Ol</button>
                            </div>
                        </div>
                    </div>
                </form>

                <p class="mb-0">
                    <a href="login" class="link-primary text-center">Zaten bir üyeliğim var</a>
                </p>
                <p class="mb-0">
                    <a href="#" class="link-primary text-center">Şifremi unuttum</a>
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="dist/js/adminlte.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const priceInput = document.getElementById('Price');
        
        // Başlangıç değerini formatla
        if (!priceInput.value) {
            priceInput.value = '₺0,00';
        }

        // Input alanından çıkıldığında formatla
        priceInput.addEventListener('blur', function(e) {
            let value = e.target.value;
            
            // Sadece sayıları ve noktaları al
            value = value.replace(/[^\d.,]/g, '');
            // Virgülü noktaya çevir
            value = value.replace(',', '.');
            
            // Sayıya çevir
            let number = parseFloat(value);
            if (isNaN(number)) number = 0;
            
            // Para birimi formatında göster
            e.target.value = `₺${number.toFixed(2).replace('.', ',')}`;
        });

        // Sadece sayı, virgül ve nokta girişine izin ver
        priceInput.addEventListener('input', function(e) {
            let value = e.target.value;
            value = value.replace(/[^\d₺.,]/g, '');
            
            // Birden fazla ₺ işareti varsa ilkini bırak
            const symbolCount = (value.match(/₺/g) || []).length;
            if (symbolCount > 1) {
                value = '₺' + value.replace(/₺/g, '');
            }
            
            // Birden fazla virgül veya nokta varsa ilkini bırak
            const decimalCount = (value.match(/[.,]/g) || []).length;
            if (decimalCount > 1) {
                const firstDecimal = value.indexOf('.') !== -1 ? value.indexOf('.') : value.indexOf(',');
                value = value.slice(0, firstDecimal + 1) + value.slice(firstDecimal + 1).replace(/[.,]/g, '');
            }
            
            e.target.value = value;
        });
    });
    </script>

    <?php 
    echo $alertScript; 
    ?>
</body>
</html>