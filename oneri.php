<?php
require_once 'header.php';
require_once 'sidebar.php';

/**
 * Öneri durumunu güncelleyen fonksiyon
 * 
 * @param int $oneriId Güncellenecek önerinin ID'si
 * @param string $durum Yeni durum değeri
 * @return bool Güncelleme başarılı mı?
 */
function oneriDurumuGuncelle($oneriId, $durum) {
    $db = new Database();
    
    // Geçerli durum değerleri
    $gecerliDurumlar = ['pending', 'reviewed', 'implemented'];
    
    // Durum değeri geçerli mi kontrol et
    if (!in_array($durum, $gecerliDurumlar)) {
        return false;
    }
    
    // Güncelleme işlemini gerçekleştir
    return $db->update('oneri', ['Durum' => $durum], ['OneriID' => $oneriId]);
}

// POST isteği ile form gönderilmişse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['OneriID']) && isset($_POST['Durum'])) {
    $oneriId = (int)$_POST['OneriID'];
    $durum = $_POST['Durum'];
    
    // Öneri durumunu güncelle
    if (oneriDurumuGuncelle($oneriId, $durum)) {
        swallAlert('success', 'Öneri durumu başarıyla güncellendi', 'oneri.php');
    } else {
        swallAlert('error', 'Öneri durumu güncellenirken bir hata oluştu', 'oneri.php');
    }
}

// Veritabanı bağlantısı
$db = new Database();

// Toplam öneri sayısını al
$oneriSayisi = $db->getColumn("SELECT COUNT(OneriID) FROM oneri");

// Önerileri listele
$oneriler = $db->getRows("SELECT 
                            oneri.OneriID,
                            oneri.Baslik, 
                            oneri.OneriDetay, 
                            oneri.Created, 
                            oneri.Durum, 
                            users.UserName, 
                            users.UserMail 
                        FROM oneri 
                        LEFT JOIN users ON oneri.UserID = users.UserID 
                        ORDER BY oneri.OneriID DESC");
?>

<main class="app-main">
    <div class="content-wrapper">
        <!-- Başlık Bölümü -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Öneri Listesi</h1>
                        <small>Toplam <?= $oneriSayisi ?> öneri bulunuyor.</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- İçerik Bölümü -->
        <section class="content">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="oneriTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Ad Soyad</th>
                                    <th>E-Posta</th>
                                    <th>Başlık</th>
                                    <th>Öneri Detay</th>
                                    <th>Kayıt Zamanı</th>
                                    <th>Durum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($oneriler as $oneri): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($oneri->UserName) ?></td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($oneri->UserMail) ?>">
                                                <?= htmlspecialchars($oneri->UserMail) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($oneri->Baslik) ?></td>
                                        <td class="text-wrap"><?= htmlspecialchars($oneri->OneriDetay) ?></td>
                                        <td><?= tr_datetime($oneri->Created) ?></td>
                                        <td class="text-center">
                                            <?php
                                            // Bir sonraki durum değerini belirle
                                            $sonrakiDurum = 'pending';
                                            $butonRenk = 'btn-warning';
                                            
                                            if ($oneri->Durum === 'pending') {
                                                $sonrakiDurum = 'reviewed';
                                                $butonRenk = 'btn-warning';
                                            } elseif ($oneri->Durum === 'reviewed') {
                                                $sonrakiDurum = 'implemented';
                                                $butonRenk = 'btn-primary';
                                            } elseif ($oneri->Durum === 'implemented') {
                                                $sonrakiDurum = 'pending';
                                                $butonRenk = 'btn-success';
                                            }
                                            ?>
                                            <form method="POST" action="oneri.php">
                                                <input type="hidden" name="OneriID" value="<?= $oneri->OneriID ?>">
                                                <input type="hidden" name="Durum" value="<?= $sonrakiDurum ?>">
                                                <button type="submit" class="btn btn-sm <?= $butonRenk ?>">
                                                    <?= ucfirst($oneri->Durum) ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<?php require_once 'footer.php'; ?>

<script>
<?php
// SweetAlert bildirimlerini göster
if (isset($_SESSION['swall'])) {
    $alert = $_SESSION['swall'];
    unset($_SESSION['swall']);
    ?>
    Swal.fire({
        icon: '<?= $alert['type'] ?>',
        title: '<?= $alert['type'] == 'success' ? 'Başarılı!' : 'Hata!' ?>',
        text: '<?= $alert['message'] ?>',
        <?php if ($alert['type'] == 'success'): ?>
        showConfirmButton: false,
        timer: 1500
        <?php else: ?>
        confirmButtonText: 'Tamam'
        <?php endif; ?>
    });
    <?php
}
?>

// DataTable yapılandırması
$(function() {
    $("#oneriTable").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "ordering": false,
        "language": {
            "emptyTable": "Tabloda herhangi bir veri mevcut değil",
            "info": "_TOTAL_ kayıttan _START_ - _END_ arasındaki kayıtlar gösteriliyor",
            "infoEmpty": "Kayıt yok",
            "infoFiltered": "(_MAX_ kayıt içerisinden bulunan)",
            "lengthMenu": "Sayfada _MENU_ kayıt göster",
            "loadingRecords": "Yükleniyor...",
            "processing": "İşleniyor...",
            "search": "Ara:",
            "zeroRecords": "Eşleşen kayıt bulunamadı",
            "paginate": {
                "first": "İlk",
                "last": "Son",
                "next": "Sonraki",
                "previous": "Önceki"
            }
        },
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
        "dom": 'Bfrtip',
        "pageLength": 10,
        "processing": true
    }).buttons().container().appendTo('#oneriTable_wrapper .col-md-6:eq(0)');
});
</script>