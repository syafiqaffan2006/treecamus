<?php
//##FOR VIEW TREE SCREEN
// Location of the tree images
$allowedDirectory = '../evidences/';


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['filename'])) {
        $filename = $_POST['filename'];
        $sanitizedFilename = basename($filename);
        $imagePath = $allowedDirectory . $sanitizedFilename;

        if (file_exists($imagePath) && strpos(realpath($imagePath), realpath($allowedDirectory)) === 0) {

            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $contentType = 'image/jpeg'; 

            if ($extension === 'png') {
                $contentType = 'image/png';
            }

            header("Content-Type: $contentType");
            readfile($imagePath);
            exit;
        }
    }

    $defaultImagePath = 'evidences/default_error_img/no_img_found.png';
    if (file_exists($defaultImagePath)) {
        header('Content-Type: image/png');
        readfile($defaultImagePath);
    } else {

        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/6H6H8wAAAAASUVORK5CYII=');
    }
    exit;
}


$filename = isset($_GET['filename']) ? $_GET['filename'] : '';
if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
    $sanitizedFilename = basename($filename);

    $imagePath = $allowedDirectory . $sanitizedFilename;

    if (file_exists($imagePath) && strpos(realpath($imagePath), realpath($allowedDirectory)) === 0) {
        header('Content-Type: image/jpeg');
        readfile($imagePath);
        exit;
    }
}

$defaultImagePath = 'evidences/default_error_img/no_img_found.png';
if (file_exists($defaultImagePath)) {
    header('Content-Type: image/png');
    readfile($defaultImagePath);
} else {
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/6H6H8wAAAAASUVORK5CYII=');
}
