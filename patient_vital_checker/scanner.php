<?php
function scanFolder($path){
    $files = scandir($path);
    foreach($files as $file){
        if($file == '.' || $file == '..') continue;
        $full = $path . "/" . $file;
        echo "File: $file <br>";
        if(is_dir($full)) scanFolder($full);
    }
}
?>
