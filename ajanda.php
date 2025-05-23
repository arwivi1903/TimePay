<?php
require_once 'header.php';
require_once 'sidebar.php';

$db = new Database();

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'delete'
){
    try {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        
        $titleId = $_POST['TitleID'] ?? null;
        if (empty($titleId)) {
            throw new Exception("Silinecek etkinlik ID'si belirtilmedi.");
        }
        $deleted = $db->delete('events', [
            'TitleID' => $titleId,
            'UserID' => $_SESSION['UserID']
        ]);
        http_response_code(200);
        echo json_encode([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Etkinlik başarıyla silindi.' : 'Etkinlik silinirken bir hata oluştu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'update'
){
    try {
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/json');
        
        $titleId = $_POST['TitleID'] ?? null;
        if (empty($titleId)) {
            throw new Exception("Güncellenecek etkinlik ID'si belirtilmedi.");
        }

        $updated = $db->update('events', [
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ], [
            'TitleID' => $titleId,
            'UserID' => $_SESSION['UserID']
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => (bool)$updated,
            'message' => $updated ? 'Etkinlik başarıyla güncellendi.' : 'Etkinlik güncellenirken bir hata oluştu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    (!isset($_POST['action']) || $_POST['action'] !== 'delete')
) {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    try {
        // $required = ['title', 'description', 'start_date', 'end_date', 'color'];
        $required = ['title', 'start_date', 'end_date', 'color'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception('Lütfen tüm alanları doldurun.');
            }
        }
        
        $inserted = $db->insert('events', [
            'title'        => $_POST['title'],
            'description'  => $_POST['description'],
            'start_date'   => $_POST['start_date'],
            'end_date'     => $_POST['end_date'],
            'color'        => $_POST['color'],
            'UserID'       => $_SESSION['UserID']
        ]);
        http_response_code(200);
        echo json_encode([
            'success' => (bool)$inserted,
            'message' => $inserted ? 'Etkinlik başarıyla kaydedildi.' : 'Etkinlik kaydedilirken bir hata oluştu.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>
<main class="app-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Event Modal -->
<div class="modal fade" id="eventModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title text-white">Yeni Etkinlik Ekle</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form id="eventForm" autocomplete="off">
                    <div class="form-group mb-3">
                        <label for="title" class="form-label">Başlık:</label>
                        <input type="text" class="form-control" id="title" name="title" required maxlength="100">
                    </div>
                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Açıklama:</label>
                        <textarea class="form-control" id="description" name="description" maxlength="500"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="start_date" class="form-label">Başlangıç:</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="end_date" class="form-label">Bitiş:</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="color" class="form-label">Renk:</label>
                        <input type="color" class="form-control" id="color" name="color" value="#3c8dbc" style="height: 75px; padding: 10px; width: 65%">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" id="saveEvent">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title text-white">Etkinlik Detayları</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div id="eventDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="deleteEvent">Sil</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'footer.php';
$events = $db->getRows('SELECT TitleID, title, description, start_date as startDate, end_date as endDate, color FROM events WHERE UserID = ?', [$_SESSION['UserID']]);
?>

<script>
$(document).ready(function() {

    function getNowDateTimeLocal() {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    $('#start_date, #end_date').val(getNowDateTimeLocal());

    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        editable: true,
        eventLimit: true,
        selectable: true,
        selectHelper: true,
        locale: 'tr',
        firstDay: 1,
        timeZone: 'local',
        buttonText: {
            today: 'Bugün',
            month: 'Ay',
            week: 'Hafta',
            day: 'Gün',
            list: 'Liste'
        },
        views: {
            week: {
                titleFormat: 'D MMMM YYYY',
                columnHeaderFormat: 'dddd',
                timeFormat: 'HH:mm'
            },
            day: {
                titleFormat: 'D MMMM YYYY',
                timeFormat: 'HH:mm'
            }
        },
        minTime: '08:00:00',
        monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül',
            'Ekim', 'Kasım', 'Aralık'
        ],
        monthNamesShort: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas',
            'Ara'
        ],
        dayNames: ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'],
        dayNamesShort: ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'],
        dayClick: function(date) {
            // Başlangıç tarihini seçilen günün başlangıcına ayarla
            const startDate = moment(date).startOf('day').add(9, 'hours'); // Saat 09:00'da başlasın
            const endDate = moment(date).startOf('day').add(17, 'hours'); // Saat 17:00'da bitsin
            
            // Tarihleri datetime-local input formatına uygun şekilde ayarla
            const startDateStr = startDate.format('YYYY-MM-DD') + 'T' + startDate.format('HH:mm');
            const endDateStr = endDate.format('YYYY-MM-DD') + 'T' + endDate.format('HH:mm');
            
            // Form alanlarını güncelle
            document.getElementById('start_date').value = startDateStr;
            document.getElementById('end_date').value = endDateStr;
            
            // Modal'ı göster
            $('#eventModal').modal('show');
        },
        events: [
            <?php foreach($events as $event): ?> {
                title: '<?php echo addslashes(htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8')); ?>',
                description: '<?php echo addslashes(htmlspecialchars($event->description, ENT_QUOTES, 'UTF-8')); ?>',
                start: '<?php echo $event->startDate; ?>',
                end: '<?php echo $event->endDate; ?>',
                color: '<?php echo $event->color; ?>',
                TitleID: '<?php echo $event->TitleID; ?>',
                editable: true
            },
            <?php endforeach; ?>
        ],
        eventDrop: function(event, delta, revertFunc) {
            $.ajax({
                url: 'ajanda.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update',
                    TitleID: event.TitleID,
                    start_date: moment(event.start).format('YYYY-MM-DD HH:mm:ss'),
                    end_date: moment(event.end).format('YYYY-MM-DD HH:mm:ss')
                },
                success: function(response) {
                    if (response && response.success) {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: 'Etkinlik tarihi güncellendi',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        revertFunc();
                        Swal.fire({
                            title: 'Hata!',
                            text: response && response.message ? response.message : 'Etkinlik güncellenirken bir hata oluştu',
                            icon: 'error'
                        });
                    }
                },
                error: function() {
                    revertFunc();
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Sunucu hatası oluştu',
                        icon: 'error'
                    });
                }
            });
        },
        eventClick: function(event) {
            const details = `
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-event"></i> ${event.title}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><i class="bi bi-card-text"></i> ${event.description || '<em>Açıklama yok</em>'}</p>
                        <ul class="list-group list-group-flush mb-2">
                            <li class="list-group-item px-0"><strong><i class="bi bi-clock"></i> Başlangıç:</strong> ${moment(event.start).format('DD.MM.YYYY HH:mm')}</li>
                            ${
                                (moment(event.end).isValid() && moment(event.start).isValid() && moment(event.end).format('DD.MM.YYYY HH:mm') !== moment(event.start).format('DD.MM.YYYY HH:mm'))
                                ? `<li class="list-group-item px-0"><strong><i class="bi bi-clock-history"></i> Bitiş:</strong> ${moment(event.end).format('DD.MM.YYYY HH:mm')}</li>`
                                : ''
                            }
                        </ul>
                    </div>
                </div>
            `;
            $('#eventDetails').html(details);
            $('#eventDetailsModal').modal('show');

            $('#deleteEvent').off('click').on('click', function() {
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu etkinliği silmek istediğinize emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Evet, sil!',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'ajanda.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'delete',
                                TitleID: event.TitleID
                            },
                            success: function(response) {
                                try {
                                    if (!response || typeof response !==
                                        'object') {
                                        throw new Error(
                                            'Geçersiz sunucu yanıtı'
                                            );
                                    }
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Silindi!',
                                            text: response
                                                .message,
                                            icon: 'success',
                                            confirmButtonText: 'Tamam',
                                            timer: 2000
                                        }).then(() => {
                                            $('#calendar')
                                                .fullCalendar(
                                                    'removeEvents',
                                                    event
                                                    .TitleID);
                                            $('#eventDetailsModal')
                                                .modal('hide')
                                                .on('hidden.bs.modal',
                                                    function() {
                                                        $('#calendar')
                                                            .fullCalendar(
                                                                'refetchEvents'
                                                                );
                                                    });
                                        });
                                    } else {
                                        throw new Error(response
                                            .message ||
                                            'Silme işlemi başarısız'
                                            );
                                    }
                                } catch (e) {
                                    console.error('Silme hatası:', e);
                                    Swal.fire({
                                        title: 'Hata!',
                                        text: e.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                let msg = 'Sunucu hatası: ' + error;
                                if (xhr.responseText) {
                                    try {
                                        let resp = JSON.parse(xhr
                                            .responseText);
                                        if (resp && resp.message) msg =
                                            resp.message;
                                    } catch (e) {}
                                }
                                Swal.fire({
                                    title: 'Hata!',
                                    text: msg,
                                    icon: 'error',
                                    confirmButtonText: 'Tamam'
                                });
                            }
                        });
                    }
                });
            });
        }
    });

    $('#saveEvent').on('click', function() {
        $.ajax({
            url: 'ajanda.php',
            type: 'POST',
            dataType: 'json',
            data: {
                title: $('#title').val(),
                description: $('#description').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                color: $('#color').val()
            },
            success: function(response) {
                if (response && response.success) {
                    // Yeni etkinliği takvime ekle
                    const newEvent = {
                        title: $('#title').val(),
                        description: $('#description').val(),
                        start: $('#start_date').val(),
                        end: $('#end_date').val(),
                        color: $('#color').val()
                    };
                    
                    $('#calendar').fullCalendar('renderEvent', newEvent, true);
                    
                    Swal.fire({
                        title: 'Etkinlik başarıyla kaydedildi!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Tamam',
                        timer: 2000
                    }).then(() => {
                        $('#eventModal').modal('hide');
                        $('#eventForm')[0].reset();
                    });
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response && response.message ? response.message : 'Beklenmeyen bir hata oluştu',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            },
            error: function(xhr, status, error) {
                let msg = 'Sunucu hatası: ' + error;
                if (xhr.responseText) {
                    try {
                        let resp = JSON.parse(xhr.responseText);
                        if (resp && resp.message) msg = resp.message;
                    } catch (e) {}
                }
                Swal.fire({
                    title: 'Hata!',
                    text: msg,
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        });
    });

    $('#eventModal').on('hidden.bs.modal', function () {
        $('#eventForm')[0].reset();
    });
});
</script>