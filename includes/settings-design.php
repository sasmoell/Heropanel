<?php

add_action('admin_init', 'wp_comics_register_design_settings');

function wp_comics_register_design_settings() {
    add_settings_section(
        'wp_comics_settings_section_design',
        'Design-Einstellungen',
        null,
        'wp_comics_settings_design'
    );

    add_settings_field(
        'wp_comics_card_max_width',
        'Maximale Breite der Comic-Card',
        'wp_comics_card_max_width_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );

    add_settings_field(
        'wp_comics_title_font_size',
        'Titel Schriftgröße',
        'wp_comics_font_size_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );

    register_setting('wp_comics_options_group', 'wp_comics_options', 'sanitize_callback_function');
}

function wp_comics_card_max_width_callback() {
    $options = get_option('wp_comics_options');
    $max_width = isset($options['wp_comics_card_max_width']) ? esc_attr($options['wp_comics_card_max_width']) : '100%';
    echo "<input type='text' name='wp_comics_options[wp_comics_card_max_width]' value='" . esc_attr($max_width) . "' placeholder='z.B. 100% oder 300px' />";
}

function wp_comics_font_size_callback() {
    $options = get_option('wp_comics_options');
    $font_size = isset($options['wp_comics_title_font_size']) ? esc_attr($options['wp_comics_title_font_size']) : '16px';
    echo "<input type='text' name='wp_comics_options[wp_comics_title_font_size]' value='" . esc_attr($font_size) . "' placeholder='z.B. 16px oder 1.5em' />";
}

