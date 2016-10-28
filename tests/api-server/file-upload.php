<?php

var_dump($_GET);
var_dump($_POST);
foreach ($_FILES as $inputName => $files) {
    var_dump($inputName);
    foreach ($files as $key => $file) {
        if ($key == 'tmp_name') {
            if (is_array($file)) {
                foreach ($file as $f) {
                    var_dump(file_get_contents($f));
                }
            } else {
                var_dump(file_get_contents($file));
            }
            continue;
        }
        var_dump($key, $file);
    }
}