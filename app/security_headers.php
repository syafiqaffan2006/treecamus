<?php
// header("Content-Security-Policy: default-src 'self';");
// header("Content-Security-Policy: default-src 'self'; style-src 'self' 'nonce-RANDOM_VALUE' https://cdnjs.cloudflare.com;");
// header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com 'nonce-RANDOM_VALUE';");
// header("Content-Security-Policy: default-src 'self'; style-src 'self' https://fonts.googleapis.com https://use.fontawesome.com https://cdnjs.cloudflare.com; script-src 'self' https://cdnjs.cloudflare.com 'nonce-RANDOM_VALUE';");
// $nonce = bin2hex(random_bytes(16));
// header("Content-Security-Policy: default-src 'self'; style-src 'self' 'nonce-$nonce' https://cdnjs.cloudflare.com 'unsafe-inline'; script-src 'self';");


//Testing by Faris 11122023
// Set Strict-Transport-Security (HSTS) header
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");

// Set X-Frame-Options header
header("Referrer-Policy: strict-origin-when-cross-origin");

// Set X-Frame-Options header
header("X-Frame-Options: DENY");

// Set X-Content-Type-Options header
header("X-Content-Type-Options: nosniff");

// Set X-XSS-Protection header
header("X-XSS-Protection: 1; mode=block");

header("Permissions-Policy: geolocation=(self 'https://https://csraub.uitm.edu.my')");

?>
