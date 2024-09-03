<?php
// Registrierung der allgemeinen Einstellungen
add_action('admin_init', 'wp_comics_register_general_settings');

function wp_comics_register_general_settings() {
    add_settings_section(
        'wp_comics_settings_section_general',
        'Allgemeine Einstellungen',
        null,
        'wp_comics_settings_general'
    );

    add_settings_field(
        'wp_comics_display_options',
        'Anzuzeigende Metadaten',
        'wp_comics_display_options_callback',
        'wp_comics_settings_general',
        'wp_comics_settings_section_general'
    );

    register_setting('wp_comics_options_group', 'wp_comics_options', 'sanitize_callback_function');
}

function wp_comics_display_options_callback() {
    $options = get_option('wp_comics_options');
    $display_options = isset($options['wp_comics_display_options']) ? (array) $options['wp_comics_display_options'] : array('cover_image', 'issue_number', 'publication_year');

    $fields = array(
        'cover_image' => 'Cover-Bild',
        'issue_number' => 'Ausgabenummer',
        'publication_year' => 'Erscheinungsjahr',
        'publisher' => 'Verlag',
        'price' => 'Preis',
        'authors' => 'Autoren',
        'format' => 'Format',
        'page_count' => 'Anzahl Seiten'
    );

    foreach ($fields as $field => $label) {
        echo "<p><label><input type='checkbox' name='wp_comics_options[wp_comics_display_options][]' value='" . esc_attr($field) . "' " . checked(in_array($field, $display_options), true, false) . " /> " . esc_html($label) . "</label></p>";
    }
}

