<?php

/**
 * Plugin Name: Heropanel 1.4.0
 * Description: Verwalte und zeige Comics auf deiner WordPress-Website an.
 * Version: 1.4.0
 * Author: Sascha Moeller <a href="https://ccmoeller.de">CCM</a> | powered by <a href="https://spidercomics.de">SpiderComics.de</a>
 * License: GPL-3.0
 */

if (!defined('ABSPATH')) {exit;}

require_once plugin_dir_path(__FILE__) . 'includes/verlage.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxen.php';
require_once plugin_dir_path(__FILE__) . 'includes/export-import.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/serien.php';
require_once plugin_dir_path(__FILE__) . 'includes/qr-code.php';


// Tabelle für Comic-Serien erstellen
function wp_comics_create_series_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comic_series';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        start_year varchar(4) NOT NULL,
        end_year varchar(4) DEFAULT NULL,
        publisher_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY (publisher_id) REFERENCES {$wpdb->prefix}comics_verlage(id) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function wp_comics_activate() {
    wp_comics_create_series_table();
    wp_comics_create_verlage_table();
}
register_activation_hook(__FILE__, 'wp_comics_activate');


// Enqueue Scripts and Styles für Frontend
function wp_comics_enqueue_frontend_scripts() {
    if (is_singular('comic') || is_post_type_archive('comic') || is_page() || is_home()) { 
        wp_enqueue_style('wp-comics-frontend-styles', plugins_url('css/frontend-styles.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('wp-comics-frontend-scripts', plugins_url('javascript/frontend-scripts.js', __FILE__), array('jquery'), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'wp_comics_enqueue_frontend_scripts');

// Enqueue Scripts und Styles für Admin-Bereich
function wp_comics_enqueue_admin_scripts($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        if ('comic' === get_post_type()) {
            wp_enqueue_media(); // Für den Mediapicker

            wp_enqueue_style('wp-comics-admin-styles', plugins_url('css/admin-styles.css', __FILE__), array(), '1.0.0');
            wp_enqueue_script('wp-comics-admin-scripts', plugins_url('javascript/admin-scripts.js', __FILE__), array('jquery'), '1.0.0', true);
        }
    }
}
add_action('admin_enqueue_scripts', 'wp_comics_enqueue_admin_scripts');

// Admin-Menü
function wp_comics_verlage_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=comic',
        'Verlage',
        'Verlage',
        'manage_options',
        'wp_comics_verlage',
        'wp_comics_verlage_page'
    );
}
add_action('admin_menu', 'wp_comics_verlage_admin_menu');

// Untermenüpunkt für Serien unter "Comics" hinzufügen
function wp_comics_add_series_submenu() {
    add_submenu_page(
        'edit.php?post_type=comic',  // Der Slug des Elternmenüpunkts (Comics)
        'Comic Serien',              // Titel der Seite
        'Serien',                    // Titel des Menüpunkts
        'manage_options',            // Benutzerberechtigung
        'wp-comics-series',          // Slug des Menüpunkts
        'wp_comics_series_page'      // Funktion, die die Seite rendert
    );
}
add_action('admin_menu', 'wp_comics_add_series_submenu');




// Menüpunkt für den Export und Import hinzufügen
function wp_comics_export_import_menu() {
    add_submenu_page(
        'edit.php?post_type=comic',
        'Comics exportieren/importieren',
        'Export/Import',
        'manage_options',
        'wp_comics_export_import',
        'wp_comics_export_form'
    );
}
add_action('admin_menu', 'wp_comics_export_import_menu');

// Registrierung benutzerdefinierter Beitragstypen und Taxonomien
function wp_comics_register_post_types() {
    // Comic Beitragstyp registrieren
    register_post_type('comic', array(
        'labels' => array(
            'name' => __('Comics'),
            'singular_name' => __('Comic'),
            'add_new' => __('Neuen Comic hinzufügen'),
            'add_new_item' => __('Neuen Comic hinzufügen'),
            'edit_item' => __('Comic bearbeiten'),
            'new_item' => __('Neuer Comic'),
            'view_item' => __('Comic ansehen'),
            'search_items' => __('Comics durchsuchen'),
            'not_found' => __('Keine Comics gefunden'),
            'not_found_in_trash' => __('Keine Comics im Papierkorb gefunden'),
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_position' => 5,
        'menu_icon' => 'dashicons-book-alt',
        'show_in_rest' => true,
    ));

    // Genre-Taxonomie für Comics registrieren
    register_taxonomy('genre', 'comic', array(
        'labels' => array(
            'name' => __('Genres'),
            'singular_name' => __('Genre'),
            'search_items' => __('Genres durchsuchen'),
            'all_items' => __('Alle Genres'),
            'parent_item' => __('Eltern-Genre'),
            'parent_item_colon' => __('Eltern-Genre:'),
            'edit_item' => __('Genre bearbeiten'),
            'update_item' => __('Genre aktualisieren'),
            'add_new_item' => __('Neues Genre hinzufügen'),
            'new_item_name' => __('Name des neuen Genres'),
            'menu_name' => __('Genres'),
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'genre'),
    ));

    // Tags für den Comic-Post-Typ hinzufügen
    register_taxonomy_for_object_type('post_tag', 'comic');
}
add_action('init', 'wp_comics_register_post_types');

// Standardinhalt für Comic-Beiträge festlegen
function wp_comics_set_default_content($content) {
    global $post_type;

    // Überprüfen, ob es sich um den Comic-Post-Typ handelt
    if ($post_type == 'comic') {
        // Shortcode für Comics automatisch hinzufügen
        $content = '[wp_comics_metadata]';
    }

    return $content;
}
add_filter('default_content', 'wp_comics_set_default_content');



// Admin Menüs
function wp_comics_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=comic',
        'Einstellungen',
        'Einstellungen',
        'manage_options',
        'wp_comics_settings',
        'wp_comics_settings_page'
    );
}
add_action('admin_menu', 'wp_comics_admin_menu');

// Spaltenüberschrift hinzufügen
function wp_comics_add_custom_columns($columns) {
    $columns['comic_id'] = __('Comic ID');
    $columns['comic_shortcode'] = __('Shortcode');
    return $columns;
}
add_filter('manage_comic_posts_columns', 'wp_comics_add_custom_columns');

// Inhalt der Spalten füllen
function wp_comics_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'comic_id':
            echo esc_html($post_id); // esc_html() sichert die Ausgabe ab
            break;

        case 'comic_shortcode':
            echo esc_html('[wp_comics id="' . $post_id . '"]'); // esc_html() sichert den generierten Shortcode ab
            break;
    }
}
add_action('manage_comic_posts_custom_column', 'wp_comics_custom_column_content', 10, 2);

// Serien-Spalte zur Comic-Übersicht hinzufügen und direkt neben die Genres-Spalte setzen
function wp_comics_add_series_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key == 'taxonomy-genre') {  // 'taxonomy-genre' ist der Schlüssel für die Genres-Spalte
            $new_columns['comic_series'] = __('Serie');
        }
    }

    return $new_columns;
}
add_filter('manage_comic_posts_columns', 'wp_comics_add_series_column');


// Serien-Spalteninhalt füllen
function wp_comics_series_column_content($column, $post_id) {
    if ($column == 'comic_series') {
        $series_id = get_post_meta($post_id, '_wp_comics_series', true);
        if ($series_id) {
            global $wpdb;
            $series_table = $wpdb->prefix . 'comic_series';
            $series = $wpdb->get_row($wpdb->prepare("SELECT name FROM $series_table WHERE id = %d", $series_id));

            // Überprüfen, ob die Serie gefunden wurde
            if ($series && !empty($series->name)) {
                echo esc_html($series->name);
            } else {
                echo __('Serie nicht gefunden');
            }
        } else {
            echo __('Keine Serie zugeordnet');
        }
    }
}
add_action('manage_comic_posts_custom_column', 'wp_comics_series_column_content', 10, 2);




// Verlage-Spalte zur Comic-Übersicht hinzufügen und nach der Serien-Spalte platzieren
function wp_comics_add_publisher_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key == 'comic_series') {  // 'comic_series' ist der Schlüssel für die Serien-Spalte
            $new_columns['comic_publisher'] = __('Verlag');
        }
    }

    return $new_columns;
}
add_filter('manage_comic_posts_columns', 'wp_comics_add_publisher_column');




// Verlage-Spalteninhalt füllen
function wp_comics_publisher_column_content($column, $post_id) {
    if ($column == 'comic_publisher') {
        global $wpdb;

        // Direkte Abfrage der Meta-Daten für diesen Comic
        $publisher_name = $wpdb->get_var($wpdb->prepare("
            SELECT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE post_id = %d 
            AND meta_key = '_wp_comics_publisher'
        ", $post_id));

        if ($publisher_name) {
            echo esc_html($publisher_name);
        } else {
            echo __('Kein Verlag zugeordnet');
        }
    }
}

add_action('manage_comic_posts_custom_column', 'wp_comics_publisher_column_content', 20, 2);

// Filter-Dropdown für Serien und Genres in der Admin-Übersicht hinzufügen
function wp_comics_genre_series_filter() {
    global $typenow, $wpdb;

    if ($typenow == 'comic') {
        // Filter für Genres
        $taxonomy = 'genre'; 
        $selected = isset($_GET[$taxonomy]) ? sanitize_text_field($_GET[$taxonomy]) : '';
        $info_taxonomy = get_taxonomy($taxonomy);

        wp_dropdown_categories(array(
            'show_option_all' => sprintf(__('Alle %s', 'textdomain'), $info_taxonomy->label),
            'taxonomy'        => $taxonomy,
            'name'            => $taxonomy,
            'orderby'         => 'name',
            'selected'        => $selected,
            'show_count'      => true,
            'hide_empty'      => true,
            'value_field'     => 'slug',
        ));

        // Serien aus den Metadaten der Comics abrufen
        $series_ids = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_wp_comics_series'
            AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'comic')
        ");

        if (!empty($series_ids)) {
            $series_list = $wpdb->get_results("
                SELECT id, name 
                FROM {$wpdb->prefix}comic_series 
                WHERE id IN (" . implode(',', array_map('intval', $series_ids)) . ") 
                ORDER BY name ASC
            ");
        }

        $selected_series = isset($_GET['comic_series']) ? intval($_GET['comic_series']) : '';
        echo '<select name="comic_series" id="filter-by-series">';
        echo '<option value="">' . __('Alle Serien', 'textdomain') . '</option>';

        if (!empty($series_list)) {
            foreach ($series_list as $serie) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($serie->id),
                    selected($selected_series, $serie->id, false),
                    esc_html($serie->name)
                );
            }
        }

        echo '</select>';
    }
}


// Filter-Dropdown für Verlage in der Admin-Übersicht hinzufügen
function wp_comics_genre_series_publisher_filter() {
    global $typenow, $wpdb;

    if ($typenow == 'comic') {
        // Serien- und Genre-Filter einfügen
        wp_comics_genre_series_filter();

        // Verlage aus der Tabelle 'comics_verlage' abrufen
        $verlage_table = $wpdb->prefix . 'comics_verlage';
        $publisher_names = $wpdb->get_col("SELECT name FROM $verlage_table");

        // Optional: Eigenverlag als Standardoption hinzufügen
        if (!in_array('Eigenverlag', $publisher_names)) {
            $publisher_names[] = 'Eigenverlag';
        }

        $selected_publisher = isset($_GET['comic_publisher']) ? sanitize_text_field($_GET['comic_publisher']) : '';
        echo '<select name="comic_publisher" id="filter-by-publisher">';
        echo '<option value="">' . __('Alle Verlage', 'textdomain') . '</option>';

        if (!empty($publisher_names)) {
            foreach ($publisher_names as $publisher_name) {
                if (!empty(trim($publisher_name))) { // Leere Verlage ausschließen
                    printf(
                        '<option value="%s"%s>%s</option>',
                        esc_attr($publisher_name),
                        selected($selected_publisher, $publisher_name, false),
                        esc_html($publisher_name)
                    );
                }
            }
        }

        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'wp_comics_genre_series_publisher_filter');


function wp_comics_filter_by_genre_series_and_publisher($query) {
    global $pagenow;

    if ($pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'comic' && $query->is_main_query()) {
        $meta_query = array('relation' => 'AND');

        // Filtern nach Serie
        if (!empty($_GET['comic_series'])) {
            $meta_query[] = array(
                'key'     => '_wp_comics_series',
                'value'   => sanitize_text_field($_GET['comic_series']),
                'compare' => '='
            );
        }

        // Filtern nach Verlag
        if (!empty($_GET['comic_publisher'])) {
            $meta_query[] = array(
                'key'     => '_wp_comics_publisher',
                'value'   => sanitize_text_field($_GET['comic_publisher']),
                'compare' => '='
            );
        }

        if (count($meta_query) > 1) { // Wenn mindestens ein Filter gesetzt ist
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'wp_comics_filter_by_genre_series_and_publisher');




?>
