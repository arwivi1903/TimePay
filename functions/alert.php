<?php
function alert() {
    if(isset($_SESSION['alert'])) {
        swallAlert('info', $_SESSION['alert']);
        unset($_SESSION['alert']);
    }
}
?>