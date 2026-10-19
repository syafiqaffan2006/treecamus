<?php
/**
 * File: display_images.php
 * File-type: AJAX File
 * Author: Faris Aiman
 * Description: Retrieve and Display Images for Google Map & Leaflet In System
 * Date: March 2, 2024
 *
 * Other Details or Notes:
 * - None
 */

session_start();
include "../connection/connection.php";

if (!isset($_SESSION['userislogged']) || $_SESSION['userislogged'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Location of the tree images from the map.php.. different with map in index.php
$allowedDirectory = '../evidences/';

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

$defaultImagePath = '../evidences/default_error_img/no_img_found.png';
if (file_exists($defaultImagePath)) {
    header('Content-Type: image/png');
    readfile($defaultImagePath);
} else {
    header('Content-Type: image/png');
    echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/wcAAwAB/6H6H8wAAAAASUVORK5CYII=');
}
