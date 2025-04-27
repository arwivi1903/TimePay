<?php
    function uploadFile($fileInputName, $destination) {

        $fileName = $_FILES[$fileInputName]["name"];
        $fileTMP  = $_FILES[$fileInputName]["tmp_name"];
        $newName = rand() . "_" . time() . "_" . $fileName;
        $filePath = $destination . $newName;
        
        if (move_uploaded_file($fileTMP, $filePath)) {
            return $filePath;
        } else {
            return false;
        }
    }
?>