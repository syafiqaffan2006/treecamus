<?php
/**
 * File: display_images.php 
 * File-type: AJAX File
 * Author: Faris Aiman
 * Description: Retrieve and Display Images for PUBLIC Google Map & Leaflet
 * Date: March 2, 2024
 *
 * Other Details or Notes:
 * - None
 */

$allowedDirectory = 'evidences/';

// - check if the request is an ajax request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['filename'])) {
        $filename = $_POST['filename'];
        $sanitizedFilename = basename($filename);
        $imagePath = $allowedDirectory . $sanitizedFilename;

        // - check if the requested file exists and is within the allowed directory
        if (file_exists($imagePath) && strpos(realpath($imagePath), realpath($allowedDirectory)) === 0) {
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $contentType = 'image/jpeg';

            if ($extension === 'png') {
                $contentType = 'image/png';
            }

            // - set the appropriate Content-Type header
            header("Content-Type: $contentType");
            // - output the image file
            readfile($imagePath);
            exit;
        }
    }

    // - if the file does not exist or is outside the allowed directory, return a default image
    $defaultImagePath = 'evidences/default_error_img/no_img_found.png';
    if (file_exists($defaultImagePath)) {
        header('Content-Type: image/png');
        readfile($defaultImagePath);
    } else {
        // - if the default image is not available, return a placeholder image
        header('Content-Type: image/png');
        echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/6H6H8wAAAAASUVORK5CYII=');
    }
    exit;
}

// - check if the requested filename exists in the URL
$filename = isset($_GET['filename']) ? $_GET['filename'] : '';

// - if the filename exists and matches the allowed format, proceed to retrieve path
if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
    $sanitizedFilename = basename($filename);
    $imagePath = $allowedDirectory . $sanitizedFilename;

    // - check if the requested file exists and is within the allowed directory
    if (file_exists($imagePath) && strpos(realpath($imagePath), realpath($allowedDirectory)) === 0) {
        header('Content-Type: image/jpeg');
        readfile($imagePath);
        exit;
    }
}

header("Location: index.php");
exit;
