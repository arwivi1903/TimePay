<?php
include "header.php";
include "sidebar.php";

$saatUcreti = floatval(decryptData($_SESSION['HourlyRate']));
if($saatUcreti <= 0) {
    echo "<script>
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Saat ücreti bilgisi alınamadı',
            showConfirmButton: false,
            timer: 1500
        });
    </script>";
    exit;
}

// POST işlemi kontrolü
if (isset($_POST['saat'])) {
    $yeniUcret = str_replace(',', '.', $_POST['saatUcret']); // Virgülü noktaya çevir
    $yeniUcret = floatval($yeniUcret); // Sayısal değere çevir
    
    $data = [
        'HourlyRate' => encryptData($yeniUcret)
    ];
    
    $where = [
        'UserID' => $_SESSION['UserID']
    ];

    $sonuc = $db->update('users', $data, $where);
    
    if ($sonuc) {
        $_SESSION['HourlyRate'] = $yeniUcret; // Session'ı güncelle
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Başarılı!',
                text: 'Saat ücreti güncellendi',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = 'zam.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Güncelleme başarısız oldu',
                showConfirmButton: false,
                timer: 1500
            })
        </script>";
    }
}

if(isset($_POST['hesapla'])) {
    // Gelen değerlerin sayısal olduğundan emin olalım
    if(!is_numeric($_POST['mevcutUcret']) || !is_numeric($_POST['zamOrani'])) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Geçersiz değer girişi',
                showConfirmButton: false,
                timer: 1500
            });
        </script>";
        exit;
    }
    
    // Mevcut kodunuz...
}
?>
<main class="app-main">
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Zam Hesaplama</h3>
                        </div>
                        <div class="card-body">
                            <form id="zamHesaplaForm" method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mevcut Saat Ücreti</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?= number_format($saatUcreti, 2, ',', '.') ?>" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="mevcutUcret" value="<?= $saatUcreti ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Zam Oranı</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="zamOrani" step="0.01" min="0" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Seyyanen Zam</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="seyyanen" step="0.01" min="0">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">₺</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" name="hesapla" class="btn btn-primary mt-3">Hesapla</button>
                            </form>

                            <?php
                                if(isset($_POST['hesapla'])) {
                                    $mevcutUcret = floatval($_POST['mevcutUcret']);
                                    $zamOrani = floatval($_POST['zamOrani']);
                                    $seyyanen = empty($_POST['seyyanen']) ? 0 : floatval($_POST['seyyanen']);
                                    
                                    // Hesaplamalar
                                    $oransalZam = $mevcutUcret * ($zamOrani / 100);
                                    $seyyanenSaatlik = $seyyanen / 225;
                                    $yeniUcret = $mevcutUcret + $oransalZam + $seyyanenSaatlik;
                                    
                                    // Aylık hesaplamalar
                                    $mevcutAylik = $mevcutUcret * 225;
                                    $yeniAylik = $yeniUcret * 225;
                                    $toplamArtis = $yeniAylik - $mevcutAylik;
                                }
                            ?>

                            <div id="sonuclar" style="display: <?php echo (isset($_POST['hesapla']) ? 'block' : 'none'); ?>" class="mt-4">
                                <div class="row">
                                    <!-- Mevcut Durum Card -->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-info">
                                                <h3 class="card-title text-white">Mevcut Durum</h3>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Saat Ücreti</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($mevcutUcret, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Aylık Brüt</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($mevcutAylik, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Zam Detayları Card -->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-warning">
                                                <h3 class="card-title text-white">Zam Detayları</h3>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Zam Oranı</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo '%' . number_format($zamOrani, 2, ',', '.'); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Oransal Zam (Saat)</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($oransalZam, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Seyyanen (Aylık)</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($seyyanen, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Seyyanen (Saat)</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($seyyanenSaatlik, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Yeni Durum Card -->
                                    <div class="col-md-4">
                                        <div class="card">
                                            <div class="card-header bg-success">
                                                <h3 class="card-title text-white">Yeni Durum</h3>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <th>Yeni Saat Ücreti</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($yeniUcret, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th>Yeni Aylık Brüt</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($yeniAylik, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                    <tr class="table-success font-weight-bold">
                                                        <th>Toplam Artış</th>
                                                        <td><?php if(isset($_POST['hesapla'])) echo number_format($toplamArtis, 2, ',', '.') . ' ₺'; ?></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kaydet Butonu -->
                                <?php if(isset($_POST['hesapla'])): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <form action="" method="POST" id="ucretForm">
                                            <input type="hidden" name="saatUcret" value="<?php echo number_format($yeniUcret, 2, '.', ''); ?>">
                                            <input type="hidden" name="saat" value="1">
                                            <button type="button" onclick="confirmSubmit()" class="btn btn-success btn-lg btn-block">
                                                <i class="fas fa-save mr-2"></i> Yeni Ücreti Kaydet
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <script>
                                function confirmSubmit() {
                                    Swal.fire({
                                        title: 'Emin misiniz?',
                                        text: 'Yeni saat ücretini kaydetmek istediğinizden emin misiniz?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'Evet, Kaydet',
                                        cancelButtonText: 'İptal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            document.getElementById('ucretForm').submit();
                                        } else {
                                            document.getElementById('ucretForm').reset();
                                        }
                                    });
                                }
                                </script>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</main>



<?php include "footer.php"; ?> 