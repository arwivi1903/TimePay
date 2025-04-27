<?php 
require_once 'header.php'; 
require_once 'sidebar.php';

// Maaş hesaplayıcı sınıfını başlat
$maasHesaplayici = new MaasHesaplayici($SaatUcreti);

// Yan ödemeleri getir
$query = 'SELECT YanCocuk, YanYakacak, YanAyakkabi, YanRamazan, YanKurban, YanIzin 
          FROM sabitler 
          WHERE YanID = :YanID';
$sabitler = $db->getRow($query, [':YanID' => 1]);

// Yan ödemeleri değişkenlere ata
extract((array)$sabitler, EXTR_PREFIX_ALL, 'yan');

// Mesai hesaplamaları
function getMesaiSaatleri($db, $userID, $haftaTur) {
    $query = "SELECT SUM(mesai.Zaman) FROM mesai
             LEFT JOIN users ON mesai.UserID = users.UserID
             WHERE mesai.HaftaTur = :haftaTur AND mesai.UserID = :userID";
    return $db->getColumn($query, [
        ':haftaTur' => $haftaTur,
        ':userID' => $userID
    ]) ?: 0;
}

$haftaiciZaman = getMesaiSaatleri($db, $_SESSION['UserID'], 1);
$haftasonuZaman = getMesaiSaatleri($db, $_SESSION['UserID'], 2);

// Mesai ücretlerini hesapla
$thici = ($haftaiciZaman * 1.5) * $SaatUcreti;
$thsonu = ($haftasonuZaman * 2) * $SaatUcreti;
$toplamMesai = $thici + $thsonu;

// Form gönderildiğinde
if (isset($_POST['nealirim'])) {
    try {
        // Form verilerini al
        $formData = [
            'Brut' => $_POST['brut'],
            'Cocuk' => $_POST['cocuk'] * $yan_YanCocuk,
            'Yakacak' => $_POST['yakacak'],
            'Ayakkabi' => $_POST['ayakkabi'],
            'Ramazan' => isset($_POST['ramazan']) ? $_POST['ramazan'] : 0,
            'Kurban' => isset($_POST['kurban']) ? $_POST['kurban'] : 0,
            'Izin' => $_POST['izin'],
            'Vergi' => $_POST['vergi'] / 100
        ];

        // Hesaplamaları yap
        $n_toplam = array_sum(array_filter($formData, function($key) {
            return $key !== 'Vergi';
        }, ARRAY_FILTER_USE_KEY));

        $n_ivergi = $n_toplam * 0.14;
        $n_issizlik = $n_toplam * 0.01;
        $n_damga = $n_toplam * 0.00759;
        $n_vmatrah = $n_toplam - ($n_ivergi + $n_issizlik);
        $gelirvergi = $n_vmatrah * $formData['Vergi'];
        $n_tahmininet = $n_toplam - ($n_ivergi + $n_issizlik + $n_damga + $gelirvergi);

        // Session'a hesaplama sonuçlarını kaydedelim
        $_SESSION['hesaplama'] = [
            'brut' => $formData['Brut'],
            'toplam' => $n_toplam,
            'ivergi' => $n_ivergi,
            'issizlik' => $n_issizlik,
            'damga' => $n_damga,
            'vmatrah' => $n_vmatrah,
            'gelirvergi' => $gelirvergi,
            'tahmininet' => $n_tahmininet
        ];

        // Başarılı mesajı ve yönlendirme
        header("Location: nealirim.php?hesaplandi=1");
        exit;
    } catch (Exception $e) {
        swallAlert('error', 'Hesaplama sırasında bir hata oluştu: ' . $e->getMessage(), 'nealirim.php');
        exit;
    }
}

// Hesaplama sonuçlarını session'dan alalım
if (isset($_SESSION['hesaplama'])) {
    $hesaplama = $_SESSION['hesaplama'];
    unset($_SESSION['hesaplama']); // Bir kez kullandıktan sonra temizleyelim
}
?>

<!-- HTML kısmı -->
<main class="app-main">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="col-12 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0">Ne Alırım!..</h4>
                    <small>Tahmini detaylı maaş tablosu</small>
                </div>
            </div>
        </div>

        <!-- Ana İçerik -->
        <div class="col-12">
            <div class="card shadow">
                <!-- Form kısmını card-body içine ekleyelim -->
<div class="card-body">
    <form action="" method="POST">
        <div class="row">
            <!-- Ücretler Görünümü -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Ücretler Görünümü</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Brüt:</span>
                            <strong><?= paraformatla($maasHesaplayici->brutMaas()) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>İkramiye Dahil Brüt:</span>
                            <strong><?= paraformatla($maasHesaplayici->ikramiyeDahilBrut()) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>İkramiyeli Brüt:</span>
                            <strong><?= paraFormatla($maasHesaplayici->ikramiye()) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mesailer Görünümü -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Mesailer Görünümü</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Hafta İçi:</span>
                            <strong><?= paraFormatla($thici) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Hafta Sonu:</span>
                            <strong><?= paraFormatla($thsonu) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Toplam Mesai:</span>
                            <strong><?= paraFormatla($toplamMesai) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Genel Durum -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Genel (Toplam) Durum</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Brüt:</span>
                            <strong><?= paraformatla($maasHesaplayici->brutMaas() + $toplamMesai) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>İkramiye Dahil Brüt:</span>
                            <strong><?= paraformatla($maasHesaplayici->ikramiyeDahilBrut() + $toplamMesai) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>İkramiyeli Brüt:</span>
                            <strong><?= paraFormatla($maasHesaplayici->ikramiye() + $toplamMesai) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Maaş Hesaplama Formu</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Brüt Maaş -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label for="brut" class="form-label"><b>Brüt Maaş</b></label>
                                <input type="number" id="brut" name="brut" class="form-control" min="0" step="0.01"
                                    pattern="^\d*(\.\d{0,2})?$" required placeholder="Brüt maaşınızı girin">
                            </div>

                            <!-- Çocuk Sayısı -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label for="cocuk" class="form-label"><b>Çocuk Sayısı</b></label>
                                <input type="number" id="cocuk" name="cocuk" class="form-control" min="0" required>
                            </div>

                            <!-- Yakacak -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label class="form-label"><b>Yakacak</b></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="yakacak" value="<?= $yan_YanYakacak ?>" required>
                                        <label class="form-check-label">Var</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="yakacak" value="0">
                                        <label class="form-check-label">Yok</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Ayakkabı -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label class="form-label"><b>Ayakkabı</b></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="ayakkabi" value="<?= $yan_YanAyakkabi ?>" required>
                                        <label class="form-check-label">Var</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="ayakkabi" value="0">
                                        <label class="form-check-label">Yok</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Bayram -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label class="form-label"><b>Bayram</b></label>
                                <div class="d-flex flex-column gap-2">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="ramazan" value="<?= $yan_YanRamazan ?>">
                                        <label class="form-check-label">Ramazan Bayramı</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="kurban" value="<?= $yan_YanKurban ?>">
                                        <label class="form-check-label">Kurban Bayramı</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Yıllık İzin -->
                            <div class="col-lg-2 col-md-6 mb-3">
                                <label class="form-label"><b>Yıllık İzin</b></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="izin" value="<?= $yan_YanIzin ?>" required>
                                        <label class="form-check-label">Var</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="izin" value="0">
                                        <label class="form-check-label">Yok</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Vergi Dilimi -->
                            <div class="col-lg-4 col-md-6 mb-3">
                                <label for="vergi" class="form-label"><b>Vergi Dilimi</b></label>
                                <select id="vergi" name="vergi" class="form-select" required>
                                    <option value="">Seçiniz...</option>
                                    <option value="15">%15</option>
                                    <option value="20">%20</option>
                                    <option value="27">%27</option>
                                    <option value="35">%35</option>
                                    <option value="40">%40</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <small class="text-muted d-block mb-3">* Tüm alanları doldurunuz. Bu hesap tahminidir, gerçeği yansıtmaz.</small>
                            <button type="submit" name="nealirim" class="btn btn-primary">
                                <i class="fas fa-calculator me-2"></i>Hesapla
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Sonuç Tablosu -->
    <?php if (isset($hesaplama)): ?>
    <div class="card mt-4" id="sonuclar">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Hesaplanan Sonuçlar</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Brüt Maaş</th>
                            <th>Toplam</th>
                            <th>SGK İ. Payı</th>
                            <th>İşsizlik Payı</th>
                            <th>Damga Vergisi</th>
                            <th>Vergi Matrahı</th>
                            <th>Gelir Vergisi</th>
                            <th>Tahmini Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= paraformatla($hesaplama['brut']) ?></td>
                            <td><?= paraformatla($hesaplama['toplam']) ?></td>
                            <td><?= paraformatla($hesaplama['ivergi']) ?></td>
                            <td><?= paraformatla($hesaplama['issizlik']) ?></td>
                            <td><?= paraformatla($hesaplama['damga']) ?></td>
                            <td><?= paraformatla($hesaplama['vmatrah']) ?></td>
                            <td><?= paraformatla($hesaplama['gelirvergi']) ?></td>
                            <td class="fw-bold"><?= paraFormatla($hesaplama['tahmininet']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<!-- SweetAlert2 Script -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hesaplama yapıldıysa sonuçlara kaydır
    <?php if (isset($_GET['hesaplandi'])): ?>
    const sonuclar = document.getElementById('sonuclar');
    if (sonuclar) {
        sonuclar.scrollIntoView({ behavior: 'smooth' });
        // Başarılı mesajını göster
        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: 'Maaş hesaplaması başarıyla tamamlandı.',
            confirmButtonText: 'Tamam'
        });
    }
    <?php endif; ?>

    // Form validasyonu
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Lütfen tüm zorunlu alanları doldurunuz.',
                confirmButtonText: 'Tamam'
            });
        }
    });

    // SweetAlert2 mesajlarını göster
    <?php if (isset($_SESSION['swall'])): ?>
    Swal.fire({
        icon: '<?php echo $_SESSION['swall']['type']; ?>',
        title: '<?php echo $_SESSION['swall']['type'] == 'success' ? 'Başarılı!' : 'Hata!'; ?>',
        text: '<?php echo $_SESSION['swall']['message']; ?>',
        confirmButtonText: 'Tamam'
    });
    <?php unset($_SESSION['swall']); endif; ?>
});
</script>