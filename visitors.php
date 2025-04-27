<?php
require_once 'header.php';
require_once 'sidebar.php';
?>
<main class="app-main">
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mt-2">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title">Ziyaretçi Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="visitor" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%; font-size: 14px;">
                                    <thead>
                                        <tr class="text-center">
                                            <th>IP Adresi</th>
                                            <th>Ülke</th>
                                            <th>Browser</th>
                                            <th>Dil</th>
                                            <!-- <th>Referans</th>7 -->
                                            <th>Sistem</th>
                                            <th>Ziyaret Sayısı</th>
                                            <th>Tarih</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                                $visitorcek = $db->getRows('SELECT visitor_info.ip_address, visitor_info.ip_country, visitor_info.browser, visitor_info.language, visitor_info.referer, visitor_info.device, visitor_info.visit_count, visitor_info.visit_time  FROM visitor_info 
                                                ORDER BY visitor_info.visit_time DESC');   

                                                foreach ($visitorcek as $item) {
                                            ?>
                                        <tr>
                                            <td><?= $item->ip_address ?></td>
                                            <td><?= $item->ip_country ?></td>
                                            <?php
                                            $browser = $item->browser;

                                            if (strpos($browser, 'Chrome') !== false) {
                                                $browserName = 'Chrome';
                                            } elseif (strpos($browser, 'Firefox') !== false) {
                                                $browserName = 'Firefox';
                                            } elseif (strpos($browser, 'Safari') !== false && strpos($browser, 'Chrome') === false) {
                                                // Safari, Chrome'un bir alt kümesi olduğu için Chrome dışında Safari'yi kontrol et
                                                $browserName = 'Safari';
                                            } elseif (strpos($browser, 'Edge') !== false) {
                                                $browserName = 'Edge';
                                            } elseif (strpos($browser, 'Opera') !== false || strpos($browser, 'OPR') !== false) {
                                                $browserName = 'Opera';
                                            } else {
                                                $browserName = 'Bilinmeyen Tarayıcı';
                                            }
                                            ?>
                                            <td><?= $browserName ?></td>

                                            <td><?= substr($item->language, 0, 5) ?></td>
                                            <!-- <td><?= $item->referer ?></td> -->
                                            <td><?= $item->device ?></td>
                                            <td><?= $item->visit_count ?></td>
                                            <td><?= tr_datetime($item->visit_time) ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</main>
<?php require_once 'footer.php'; ?>
<script>
$(function() {
    $("#visitor").DataTable({
        "lengthChange": false,
        "autoWidth": false,
        "ordering": true,
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
    }).buttons().container().appendTo('#visitor_wrapper .col-md-6:eq(0)');
});
</script>