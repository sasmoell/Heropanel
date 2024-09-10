<?php

if (!defined('ABSPATH')) {exit;}

// Meta-Boxen hinzufügen
function wp_comics_add_custom_meta_boxes() {
    add_meta_box('wp_comics_meta_box', 'Comic Details', 'wp_comics_meta_box_callback', 'comic', 'normal', 'high');
}
add_action('add_meta_boxes', 'wp_comics_add_custom_meta_boxes');

// Meta-Box Callback anpassen
function wp_comics_meta_box_callback($post) {
    wp_nonce_field('wp_comics_save_meta_box_data', 'wp_comics_meta_box_nonce');

    $meta = get_post_meta($post->ID);

    // Cover Image
    $cover_image = isset($meta['_wp_comics_cover_image'][0]) ? esc_url($meta['_wp_comics_cover_image'][0]) : '';
    echo '<p><label for="wp_comics_cover_image">Cover-Bild:</label><br />';
    echo '<input type="hidden" id="wp_comics_cover_image" name="wp_comics_cover_image" value="' . esc_attr($cover_image) . '" />';
    echo '<button type="button" class="button" id="wp_comics_cover_image_button">Bild auswählen</button>';
    echo '<div id="wp_comics_cover_image_preview" style="margin-top:10px;">';
    if ($cover_image) {
        echo '<img src="' . esc_url($cover_image) . '" alt="" style="max-width: 100%; height: auto;">';
    }
    echo '</div></p>';

    // Verlage aus der Datenbank abrufen
    global $wpdb;
    $verlage_table = $wpdb->prefix . 'comics_verlage';
    
    // Cache-Key basierend auf der Tabelle erstellen
    $cache_key = 'verlage_all_entries';
    $cache_group = 'my_plugin_group'; // Eine Cache-Gruppe zur Organisation
    
    // Versuche, die Verlage aus dem Cache zu holen
    $verlage = wp_cache_get($cache_key, $cache_group);
    
    if ($verlage === false) { // Wenn kein Cache vorhanden ist
        global $wpdb;
    
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $verlage = $wpdb->get_results("SELECT * FROM $verlage_table");
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
    
        // Ergebnis im Cache für 1 Stunde speichern (3600 Sekunden)
        wp_cache_set($cache_key, $verlage, $cache_group, 3600);
    }


    // Aktuell ausgewählter Verlag
    $publisher = isset($meta['_wp_comics_publisher'][0]) ? esc_attr($meta['_wp_comics_publisher'][0]) : '';

    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_publisher">Verlag:</label><br />';
    echo '<select id="wp_comics_publisher" name="wp_comics_publisher">';
    echo '<option value="">-- Wähle einen Verlag --</option>'; // Platzhalteroption
    foreach ($verlage as $verlag) {
        echo '<option value="' . esc_attr($verlag->name) . '" ' . selected($publisher, $verlag->name, false) . '>' . esc_html($verlag->name) . '</option>';
    }
    echo '</select></div>';

    // Serien aus der Datenbank abrufen
    $series_table = $wpdb->prefix . 'comic_series';

    // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
    $series = $wpdb->get_results("SELECT * FROM $series_table");
    // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

    // Aktuell ausgewählte Serie
    $selected_series = isset($meta['_wp_comics_series'][0]) ? intval($meta['_wp_comics_series'][0]) : '';

    // Serien Dropdown
    echo '<div class="wp-comics-field"><label for="wp_comics_series">Serie:</label><br />';
    echo '<select id="wp_comics_series" name="wp_comics_series">';
    echo '<option value="">-- Wähle eine Serie --</option>'; // Platzhalteroption
    foreach ($series as $serie) {
        echo '<option value="' . esc_attr($serie->id) . '" ' . selected($selected_series, $serie->id, false) . '>' . esc_html($serie->name) . ' (' . esc_html($serie->start_year) . ' - ' . esc_html($serie->end_year ?: 'heute') . ')</option>';
    }
    echo '</select></div>';
    echo '</div>'; // .wp-comics-field-group

    // Ausgabe-Typ (Regular/Variant)
    $issue_type = isset($meta['_wp_comics_issue_type'][0]) ? esc_attr($meta['_wp_comics_issue_type'][0]) : 'Regular';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_issue_type">Ausgabe-Typ:</label><br />';
    echo '<select id="wp_comics_issue_type" name="wp_comics_issue_type">';
    echo '<option value="Regular" ' . selected($issue_type, 'Regular', false) . '>Regular</option>';
    echo '<option value="Variant" ' . selected($issue_type, 'Variant', false) . '>Variant</option>';
    echo '</select></div>';
    echo '</div>'; // .wp-comics-field-group

    // Ausgabenummer und Erscheinungsjahr
    $issue_number = isset($meta['_wp_comics_issue_number'][0]) ? esc_attr($meta['_wp_comics_issue_number'][0]) : '';
    $publication_year = isset($meta['_wp_comics_publication_year'][0]) ? esc_attr($meta['_wp_comics_publication_year'][0]) : '';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_issue_number">Ausgabenummer:</label><br />';
    echo '<input type="number" id="wp_comics_issue_number" name="wp_comics_issue_number" value="' . esc_attr($issue_number) . '" min="0" max="9999" style="width:80px;" /></div>';
    echo '<div class="wp-comics-field"><label for="wp_comics_publication_year">Erscheinungsjahr:</label><br />';
    echo '<input type="number" id="wp_comics_publication_year" name="wp_comics_publication_year" value="' . esc_attr($publication_year) . '" min="1900" max="2099" style="width:80px;" /></div>';
    echo '</div>'; // .wp-comics-field-group

    // Autoren
    $authors = isset($meta['_wp_comics_authors'][0]) ? esc_attr($meta['_wp_comics_authors'][0]) : '';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_authors">Autoren:</label><br />';
    echo '<input type="text" id="wp_comics_authors" name="wp_comics_authors" value="' . esc_attr($authors) . '" style="width:450px;" /></div>';
    echo '</div>'; // .wp-comics-field-group

    // Format Dropdown
    $format = isset($meta['_wp_comics_format'][0]) ? esc_attr($meta['_wp_comics_format'][0]) : '';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_format">Format:</label><br />';
    echo '<select id="wp_comics_format" name="wp_comics_format">
            <option value="Heft" ' . selected($format, 'Heft', false) . '>Heft</option>
            <option value="Softcover" ' . selected($format, 'Softcover', false) . '>Softcover</option>
            <option value="Hardcover" ' . selected($format, 'Hardcover', false) . '>Hardcover</option>
          </select></div>';
    echo '</div>'; // .wp-comics-field-group

    // Seitenzahl
    $page_count = isset($meta['_wp_comics_page_count'][0]) ? esc_attr($meta['_wp_comics_page_count'][0]) : '';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_page_count">Anzahl Seiten:</label><br />';
    echo '<input type="number" id="wp_comics_page_count" name="wp_comics_page_count" value="' . esc_attr($page_count) . '" min="1" style="width:80px;" /></div>';
    echo '</div>'; // .wp-comics-field-group

    // Limitierte Ausgabe Checkbox
    $is_limited = isset($meta['_wp_comics_is_limited'][0]) ? esc_attr($meta['_wp_comics_is_limited'][0]) : '';
    $limited_number = isset($meta['_wp_comics_limited_number'][0]) ? esc_attr($meta['_wp_comics_limited_number'][0]) : '';
    $limited_total = isset($meta['_wp_comics_limited_total'][0]) ? esc_attr($meta['_wp_comics_limited_total'][0]) : '';

    $disabled = ($is_limited !== '1') ? 'disabled' : '';

    echo '<p><label for="wp_comics_is_limited"><input type="checkbox" id="wp_comics_is_limited" name="wp_comics_is_limited" value="1" ' . checked($is_limited, '1', false) . ' /> Limitierte Ausgabe</label></p>';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_limited_number">Nummer:</label><br />';
    echo '<input type="number" id="wp_comics_limited_number" name="wp_comics_limited_number" value="' . esc_attr($limited_number) . '" style="width:80px;" ' . esc_attr($disabled) . ' /></div>';
    echo '<div class="wp-comics-field"><label for="wp_comics_limited_total">Limitiert auf:</label><br />';
    echo '<input type="number" id="wp_comics_limited_total" name="wp_comics_limited_total" value="' . esc_attr($limited_total) . '" style="width:80px;" ' . esc_attr($disabled) . ' /></div>';
    echo '</div>'; // .wp-comics-field-group
    
    // Zustand Dropdown
    $condition = isset($meta['_wp_comics_condition'][0]) ? esc_attr($meta['_wp_comics_condition'][0]) : '';
    echo '<div class="wp-comics-field-group">';
    echo '<div class="wp-comics-field"><label for="wp_comics_condition">Zustand:</label><br />';
    echo '<select id="wp_comics_condition" name="wp_comics_condition">
            <option value="" ' . selected($condition, '', false) . '></option>
            <option value="0-1 (sehr gut)" ' . selected($condition, '0-1 (sehr gut)', false) . '>0-1 (sehr gut)</option>
            <option value="1-2" ' . selected($condition, '1-2', false) . '>1-2</option>
            <option value="2 (gut)" ' . selected($condition, '2 (gut)', false) . '>2 (gut)</option>
            <option value="2-3" ' . selected($condition, '2-3', false) . '>2-3</option>
            <option value="3 (noch akzeptabel)" ' . selected($condition, '3 (noch akzeptabel)', false) . '>3 (noch akzeptabel)</option>
            <option value="3-4" ' . selected($condition, '3-4', false) . '>3-4</option>
          </select></div>';
    echo '</div>';

    // Beschreibung (Text Editor)
    echo '<p><label for="wp_comics_description">Beschreibung:</label><br />';
    wp_editor(
        isset($meta['_wp_comics_description'][0]) ? wp_kses_post($meta['_wp_comics_description'][0]) : '',
        'wp_comics_description',
        array('textarea_name' => 'wp_comics_description', 'textarea_rows' => 5)
    );
}


// Daten speichern
function wp_comics_save_meta_box_data($post_id) {
    // Nonce-Überprüfung
    if (!isset($_POST['wp_comics_meta_box_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_comics_meta_box_nonce'])), 'wp_comics_save_meta_box_data')) {
        return;
    }

    // Auto-Save und Berechtigung überprüfen
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Felder definieren
    $fields = array(
        'cover_image',
        'issue_number',
        'publication_year',
        'publisher',
        'price',
        'authors',
        'format',
        'page_count',
        'description',
        'issue_type',
        'series' // Neues Feld für die Serie
    );

    // Felder speichern
    foreach ($fields as $field) {
        if ($field === 'description') {
            $value = isset($_POST['wp_comics_description']) ? wp_kses_post(wp_unslash($_POST['wp_comics_description'])) : '';
        } elseif (in_array($field, array('issue_number', 'publication_year', 'page_count', 'series'))) {
            $value = isset($_POST["wp_comics_{$field}"]) ? absint(wp_unslash($_POST["wp_comics_{$field}"])) : '';
        } else {
            $value = isset($_POST["wp_comics_{$field}"]) ? sanitize_text_field(wp_unslash($_POST["wp_comics_{$field}"])) : '';
        }
        update_post_meta($post_id, "_wp_comics_{$field}", $value);
    }

    // Limitierte Ausgabe speichern
    $is_limited = isset($_POST['wp_comics_is_limited']) ? '1' : '';
    $limited_number = isset($_POST['wp_comics_limited_number']) ? absint(wp_unslash($_POST['wp_comics_limited_number'])) : '';
    $limited_total = isset($_POST['wp_comics_limited_total']) ? absint(wp_unslash($_POST['wp_comics_limited_total'])) : '';

    update_post_meta($post_id, '_wp_comics_is_limited', $is_limited);
    update_post_meta($post_id, '_wp_comics_limited_number', $limited_number);
    update_post_meta($post_id, '_wp_comics_limited_total', $limited_total);

    // Zustand speichern
    $condition = isset($_POST['wp_comics_condition']) ? sanitize_text_field(wp_unslash($_POST['wp_comics_condition'])) : '';
    update_post_meta($post_id, '_wp_comics_condition', $condition);
}
add_action('save_post', 'wp_comics_save_meta_box_data');
?>
