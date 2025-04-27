<?php
require_once 'header.php';
require_once 'sidebar.php';

if (isset($_POST['islem']) && $_POST['islem'] === 'uye_sil') {
    // Herhangi bir çıktı olmadığından emin olmak için output buffer'ı temizleyelim
    ob_clean();
    
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $user_id = intval($_POST['user_id']);
        
        if ($user_id <= 0) {
            throw new Exception('Geçersiz kullanıcı ID');
        }
        
        $where = ['UserID' => $user_id];
        $silme = $db->delete('users', $where);
        
        if ($silme !== false) {
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false, 
                'error' => 'Üye bulunamadı veya silinemedi'
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
?>
<main class="app-main">
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Üye Listesi</h1>
                    <small>Toplam <?= $db->getColumn("SELECT COUNT(UserID) FROM users"); ?> üye bulunuyor.</small>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="uyelerTable" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%; font-size: 14px;">
                        <thead>
                            <tr>
                                <th>Üye Adı</th>
                                <th>Üye Mail</th>
                                <th>Kayıt Zamanı</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sorgu = $db->getRows("SELECT * FROM users ORDER BY UserID DESC");
                            foreach ($sorgu as $uye) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($uye->UserName) ?></td>
                                    <td><?= htmlspecialchars($uye->UserMail) ?></td>
                                    <td><?= tr_datetime($uye->CreatedDate) ?></td>
                                    <td class="text-center">
                                        <button onclick="uyeSil(<?= $uye->UserID ?>, '<?= htmlspecialchars($uye->UserName, ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Sil
                                        </button>
                                    </td>
                                </tr>
                            <?php } ?>
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
function uyeSil(id, username) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: username + " isimli üyeyi silmek istediğinize emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    islem: 'uye_sil',
                    user_id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        Swal.fire({
                            title: 'Silindi!',
                            text: 'Üye başarıyla silindi.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Hata!',
                            text: (response && response.error) || 'Silme işlemi sırasında bir hata oluştu.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Sunucu ile iletişim kurulamadı.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.error) {
                            errorMessage = response.error;
                        }
                    } catch(e) {
                        console.error('Response parsing error:', xhr.responseText);
                    }
                    
                    Swal.fire({
                        title: 'Hata!',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        }
    });
}

$(function() {
    $("#uyelerTable").DataTable({
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
    }).buttons().container().appendTo('#uyelerTable_wrapper .col-md-6:eq(0)');
});
</script>