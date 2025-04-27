<?php
require_once 'header.php';
require_once 'sidebar.php';

// Kullanıcı bilgilerini al
$userid = $_SESSION['UserID'];
$kullanicicek = $db->getRow("SELECT * FROM users WHERE UserID = :UserID", ['UserID' => $userid]);

// POST işlemleri
if (isset($_POST['mesaigonder'])) {
    try {
        $data = [
            'UserID' => $kullanicicek->UserID,
            'Zaman' => $_POST['mesaisaat'],
            'HaftaTur' => $_POST['tur'],
            'Tarih' => $_POST['mtarih']
        ];
        
        $mesaiID = $db->insert('mesai', $data);
        $mesaikontrol = !empty($mesaiID);

        if ($mesaikontrol) {
            header("Location: mesai.php?islem=ok");
        } else {
            header("Location: mesai.php?islem=no");
        }
        exit;
    } catch (Exception $e) {
        error_log("Mesai ekleme hatası: " . $e->getMessage());
        header("Location: mesai.php?islem=no");
        exit;
    }
}

if (isset($_POST['mesaisil'])) {
    try {
        // Database sınıfının delete metodunu kullan
        $where = [
            'MesaiID' => $_POST['MesaiID'],
            'UserID' => $kullanicicek->UserID
        ];
        $kontrol = $db->delete('mesai', $where);

        if ($kontrol) {
            header("Location: mesai.php?islem=ok");
        } else {
            header("Location: mesai.php?islem=no");
        }
        exit;
    } catch (Exception $e) {
        error_log("Mesai silme hatası: " . $e->getMessage());
        header("Location: mesai.php?islem=no");
        exit;
    }
}


$maasHesaplayici = new MaasHesaplayici($SaatUcreti);

// Debug için
error_log("Saat Ücreti: " . $maasHesaplayici->getSaatUcret());

// Varsayılan değerleri tanımla
$hicicek = null;
$hsonucek = null;
$thici = 0;
$thsonu = 0;
$mesaiList = [];

// Mesai bilgilerini getir
try {
    // Hafta içi mesai toplamı
    $haftaiciQuery = "SELECT 
        COALESCE(SUM(mesai.Zaman), 0) as total_hours,
        users.UserName
        FROM mesai
        LEFT JOIN users ON mesai.UserID = users.UserID
        WHERE mesai.HaftaTur = 1 
        AND mesai.UserID = :user_id";
    
    $hicicek = $db->getRow($haftaiciQuery, [':user_id' => $kullanicicek->UserID]);
    $hicicek = $hicicek ?: (object)['total_hours' => 0];
    $thici = $maasHesaplayici->hesaplaMesaiUcreti(floatval($hicicek->total_hours), 1);
    error_log("Hafta İçi Mesai Ücreti: " . $thici);

    // Hafta sonu mesai toplamı
    $haftasonuQuery = "SELECT 
        COALESCE(SUM(mesai.Zaman), 0) as total_hours,
        users.UserName
        FROM mesai
        LEFT JOIN users ON mesai.UserID = users.UserID
        WHERE mesai.HaftaTur = 2 
        AND mesai.UserID = :user_id";
    
    $hsonucek = $db->getRow($haftasonuQuery, [':user_id' => $kullanicicek->UserID]);
    $hsonucek = $hsonucek ?: (object)['total_hours' => 0];
    $thsonu = $maasHesaplayici->hesaplaMesaiUcreti(floatval($hsonucek->total_hours), 2);
    error_log("Hafta Sonu Mesai Ücreti: " . $thsonu);

    // Tüm mesaileri getir
    $mesaiListQuery = "SELECT * FROM mesai 
        WHERE UserID = :user_id 
        ORDER BY Tarih DESC";
    $mesaiList = $db->getRows($mesaiListQuery, [':user_id' => $kullanicicek->UserID]);
    $mesaiList = $mesaiList ?: [];
    $say = count($mesaiList);

} catch (Exception $e) {
    error_log("Mesai verisi çekilirken hata: " . $e->getMessage());
    // Hata durumunda varsayılan değerleri kullan
    $hicicek = (object)['total_hours' => 0];
    $hsonucek = (object)['total_hours' => 0];
    $thici = 0;
    $thsonu = 0;
    $mesaiList = [];
    $say = 0;
}
?>

<main class="app-main">
    <div class="container-fluid">
        <!-- Başlık -->
        <div class="col-12 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">Mesai Takip</h4>
                            <small>Mesai planı ve ücret hesaplamaları</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Mesai Ekleme Formu -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Mesai Ekle</h5>
                        <small class="text-muted" style="margin-left: 5px;"> Mesai bilgilerini eksiksiz doldurunuz...</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" id="overtimeForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Mesai Tarihi</label>
                                    <input type="date" class="form-control" name="mtarih" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Saat (Süre)</label>
                                    <input type="number" step="0.1" class="form-control" name="mesaisaat" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label d-block">Mesai Türü</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tur" value="1" checked>
                                        <label class="form-check-label">Hafta İçi</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tur" value="2">
                                        <label class="form-check-label">Hafta Sonu</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="submit" name="mesaigonder" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Mesai Ekle
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mesai Özeti -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Mesai Özeti</h5>
                        <small class="text-muted" style="margin-left: 5px;">Bu ay yapılan mesailer (Brüt)</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                <span>Hafta İçi Mesai</span>
                                <span>
                                    <?= number_format($hicicek->total_hours ?? 0, 1) ?> saat -
                                    <?= number_format($thici ?? 0, 2, ',', '.') ?> ₺
                                </span>
                            </label>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: <?= min((($hicicek->total_hours ?? 0) / 60) * 100, 100) ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label d-flex justify-content-between">
                                <span>Hafta Sonu Mesai</span>
                                <span>
                                    <?= number_format($hsonucek->total_hours ?? 0, 1) ?> saat -
                                    <?= number_format($thsonu ?? 0, 2, ',', '.') ?> ₺
                                </span>
                            </label>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: <?= min((($hsonucek->total_hours ?? 0) / 60) * 100, 100) ?>%"></div>
                            </div>
                        </div>

                        <div class="pt-2 border-top">
                            <label class="form-label d-flex justify-content-between">
                                <strong>Toplam</strong>
                                <span>
                                    <?= number_format(($hicicek->total_hours ?? 0) + ($hsonucek->total_hours ?? 0), 1) ?> saat -
                                    <?= number_format(($thici ?? 0) + ($thsonu ?? 0), 2, ',', '.') ?> ₺
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mesai Listesi -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Mesai Listesi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Mesai Türü</th>
                                        <th>Süre (Saat)</th>
                                        <th>Tutar</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($mesaiList as $mesaicik): ?>
                                    <tr>
                                        <td><?= date("d-m-Y", strtotime($mesaicik->Tarih)) ?></td>
                                        <td>
                                            <?php if($mesaicik->HaftaTur == 1): ?>
                                                <span class="badge bg-primary">Hafta İçi</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Hafta Sonu</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $mesaicik->Zaman ?> saat</td>
                                        <td class="text-first">
                                            <?= 
                                                paraformatla($maasHesaplayici->hesaplaMesaiUcreti($mesaicik->Zaman, $mesaicik->HaftaTur))
                                            ?> 
                                        </td>
                                        <td>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="MesaiID" value="<?= $mesaicik->MesaiID ?>">
                                                <button type="button" class="btn btn-danger btn-sm delete-mesai" 
                                                        data-mesai-id="<?= $mesaicik->MesaiID ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($mesaiList)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Kayıtlı mesai bulunmamaktadır
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<script>
// SweetAlert2 mesajları
<?php if (isset($_GET['islem'])): ?>
    Swal.fire({
        position: 'top-end',
        icon: '<?= $_GET['islem'] == "ok" ? "success" : "error" ?>',
        title: '<?= $_GET['islem'] == "ok" ? "İşlem başarıyla tamamlandı" : "Bir hata oluştu" ?>',
        showConfirmButton: false,
        timer: 2000,
        toast: true
    });
<?php endif; ?>

// Mesai silme işlemi için SweetAlert2
document.querySelectorAll('.delete-mesai').forEach(button => {
    button.addEventListener('click', function() {
        const mesaiId = this.dataset.mesaiId;
        const form = this.closest('form');
        
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu mesai kaydını silmek istediğinize emin misiniz?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Evet, sil!',
            cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Gizli input ekle
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'mesaisil';
                form.appendChild(input);
                
                // Formu gönder
                form.submit();
            }
        });
    });
});

// Form validasyonu
document.getElementById('overtimeForm').addEventListener('submit', function(e) {
    const saat = this.querySelector('[name="mesaisaat"]').value;
    if (saat <= 0 || saat > 24) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Geçersiz Süre',
            text: 'Lütfen 0-24 saat arasında bir değer giriniz',
            confirmButtonText: 'Tamam'
        });
    }
});
</script> 