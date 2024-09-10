<?php

if (!defined('ABSPATH')) {exit;}

// Bibliothek laden
require_once plugin_dir_path(__FILE__) . 'phpqrcode/qrcode.php';

// QR-Code für einen Comic generieren und anzeigen
function wp_comics_generate_qr_code($comic_id) {
    // Hole die URL des Comic-Beitrags
    $comic_url = get_permalink($comic_id);

    // Pfad für das QR-Code-Bild
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['path'] . "/comic_qr_{$comic_id}.png";

    // Erzeuge ein Bild des QR-Codes mit der URL
    $qr = QRCode::getMinimumQRCode($comic_url, QR_ERROR_CORRECT_LEVEL_L);
    $image = $qr->createImage(4); // Bild mit Größe 4 erstellen

    // QR-Code-Bild als PNG speichern
    imagepng($image, $file_path);
    imagedestroy($image); // Speicher freigeben

    // Rückgabe des QR-Code-Bildpfads
    return $upload_dir['url'] . "/comic_qr_{$comic_id}.png";
}

// QR-Code in der Admin-Oberfläche anzeigen
function wp_comics_display_qr_code($post) {
    if ($post->post_type == 'comic') {
        $qr_code_url = wp_comics_generate_qr_code($post->ID);
        echo '<div class="comic-qr-code">';
        echo '<h2>QR-Code:</h2>';
        echo '<img src="' . esc_url($qr_code_url) . '" alt="QR-Code" />';
        echo '</div>';
    }
}

// Metabox für QR-Code hinzufügen
function wp_comics_add_qr_code_metabox() {
    add_meta_box(
        'wp_comics_qr_code',
        'QR-Code',
        'wp_comics_display_qr_code',
        'comic',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'wp_comics_add_qr_code_metabox');

?>
