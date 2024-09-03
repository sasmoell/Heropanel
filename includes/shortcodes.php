<?php



// SHORTCODE
function wp_comics_shortcode($atts) {
    // Standardwerte für Shortcode-Attribute
    $atts = shortcode_atts(array(
        'id' => '',
        'number' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
        'genre' => '',
        'year' => '',
        'publisher' => '',
        'format' => '',
        'columns' => 1, // Anzahl der Spalten
        'layout' => 'grid', // Layout-Option: grid oder list
        'paged' => get_query_var('paged', 1), // Holt den Wert von 'paged' aus der URL oder verwendet 1 als Standardwert
        'pagination' => 'false', // Option, um Paginierung ein- oder auszuschalten
    ), $atts, 'wp_comics');

    // Abfrage-Argumente für WP_Query
    $args = array(
        'post_type' => 'comic',
        'posts_per_page' => intval($atts['number']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
        'paged' => intval($atts['paged']),
    );

    // Wenn eine ID angegeben ist, wird nur dieser Comic angezeigt
    if (!empty($atts['id'])) {
        $args['p'] = intval($atts['id']);
        $args['posts_per_page'] = 1;
    }

    // Filterung nach Genre
    if (!empty($atts['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['genre']),
        );
    }

    // Filterung nach Erscheinungsjahr
    if (!empty($atts['year'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publication_year',
            'value' => sanitize_text_field($atts['year']),
            'compare' => '='
        );
    }

    // Filterung nach Verlag
    if (!empty($atts['publisher'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publisher',
            'value' => sanitize_text_field($atts['publisher']),
            'compare' => '='
        );
    }

    // Filterung nach Format
    if (!empty($atts['format'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_format',
            'value' => sanitize_text_field($atts['format']),
            'compare' => '='
        );
    }

    // WP_Query ausführen
    $query = new WP_Query($args);

    // Optionen aus den Plugin-Einstellungen
    $options = get_option('wp_comics_options');
    $display_options = isset($options['wp_comics_display_options']) ? (array)$options['wp_comics_display_options'] : array();
    $title_font_size = isset($options['wp_comics_title_font_size']) ? esc_attr($options['wp_comics_title_font_size']) : '16px';
    $display_limited_overlay = isset($options['wp_comics_display_limited_overlay']) ? $options['wp_comics_display_limited_overlay'] : 'yes';

    // Dynamische Klassen basierend auf der Spaltenanzahl und dem Layout hinzufügen
    $columns_class = 'columns-' . intval($atts['columns']);
    $layout_class = 'layout-' . esc_attr($atts['layout']);

    $output = '<div class="wp-comics-grid ' . esc_attr($columns_class) . ' ' . esc_attr($layout_class) . '">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cover_image = get_post_meta(get_the_ID(), '_wp_comics_cover_image', true);
            $issue_number = get_post_meta(get_the_ID(), '_wp_comics_issue_number', true);
            $publication_year = get_post_meta(get_the_ID(), '_wp_comics_publication_year', true);
            $publisher = get_post_meta(get_the_ID(), '_wp_comics_publisher', true);
            $price = get_post_meta(get_the_ID(), '_wp_comics_price', true);
            $authors = get_post_meta(get_the_ID(), '_wp_comics_authors', true);
            $description = get_post_meta(get_the_ID(), '_wp_comics_description', true);
            $format = get_post_meta(get_the_ID(), '_wp_comics_format', true);
            $pages = get_post_meta(get_the_ID(), '_wp_comics_page_count', true);
            $condition = get_post_meta(get_the_ID(), '_wp_comics_condition', true);
            $genres = wp_get_post_terms(get_the_ID(), 'genre', array('fields' => 'names'));
            $is_limited = get_post_meta(get_the_ID(), '_wp_comics_is_limited', true);

            // Neue Abfrage für die Serie
            $series_id = get_post_meta(get_the_ID(), '_wp_comics_series', true);
            $series_name = '';
            if ($series_id) {
                global $wpdb;
                $series_table = $wpdb->prefix . 'comic_series';
                $series_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $series_table WHERE id = %d", $series_id));
            }

            $output .= '<a href="' . get_permalink() . '" class="wp-comic-card-link" style="text-decoration: none;">';
            $output .= '<div class="wp-comic-card">';

            // Cover-Image mit optionalem Overlay
            if (in_array('cover_image', $display_options) && $cover_image) {
                $output .= '<div class="wp-comic-cover" style="position: relative;">';
                $output .= '<img src="' . esc_url($cover_image) . '" alt="' . get_the_title() . '">';

                // Overlay nur anzeigen, wenn die Einstellung aktiviert ist und der Comic limitiert ist
                if ($is_limited && $display_limited_overlay === 'yes') {
                    $output .= '<img src="' . esc_url(plugin_dir_url(__FILE__) . '../assets/limited_roll.png') . '" class="limited-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">';
                }
                $output .= '</div>';
            }

            $output .= '<div class="wp-comic-body">';
            $output .= '<h2 class="wp-comic-title" style="font-size: ' . esc_attr($title_font_size) . ';">' . get_the_title() . '</h2>';
            $output .= '<div class="wp-comic-meta">';

            $meta_data = array();
            if (in_array('issue_number', $display_options) && $issue_number) {
                $meta_data[] = 'Ausgabenummer: ' . esc_html($issue_number);
            }
            if (in_array('publication_year', $display_options) && $publication_year) {
                $meta_data[] = 'Erscheinungsjahr: ' . esc_html($publication_year);
            }
            if (in_array('publisher', $display_options) && $publisher) {
                $meta_data[] = 'Verlag: ' . esc_html($publisher);
            }
            if (in_array('format', $display_options) && $format) {
                $meta_data[] = 'Format: ' . esc_html($format);
            }
            if (in_array('page_count', $display_options) && $pages) {
                $meta_data[] = 'Seiten: ' . esc_html($pages);
            }
            if (!empty($condition)) {
                $meta_data[] = 'Zustand: ' . esc_html($condition);
            }
            if (in_array('price', $display_options) && $price) {
                $meta_data[] = 'Preis: ' . esc_html($price) . ' EUR';
            }
            if (in_array('authors', $display_options) && $authors) {
                $meta_data[] = 'Autoren: ' . esc_html($authors);
            }

            // Anzeige der Limitierung, falls in den Einstellungen aktiviert
            if (isset($options['wp_comics_display_limited']) && $options['wp_comics_display_limited'] === 'yes' && $is_limited) {
                $limited_number = get_post_meta(get_the_ID(), '_wp_comics_limited_number', true);
                $limited_total = get_post_meta(get_the_ID(), '_wp_comics_limited_total', true);
                $meta_data[] = '<span class="wp-comic-limited">Limitierung: Nr. ' . esc_html($limited_number) . ' von ' . esc_html($limited_total) . '</span>';
            }

            $output .= implode(' | ', $meta_data);
            $output .= '</div>'; // .wp-comic-meta

            // Ausgabe von Genre und Serie
            if (!empty($genres)) {
                $output .= '<div class="wp-comic-genres">' . esc_html(implode(', ', $genres));
                if ($series_name) {
                    $output .= ' | ' . esc_html($series_name);
                }
                $output .= '</div>';
            }

            // Check if description display is enabled
            if (!empty($description) && isset($options['wp_comics_display_description']) && $options['wp_comics_display_description'] === 'yes') {
                $output .= '<div class="wp-comic-description">' . wp_kses_post($description) . '</div>';
            }

            $output .= '</div>'; // .wp-comic-body
            $output .= '</div>'; // .wp-comic-card
            $output .= '</a>'; // Schließendes Link-Tag
        }

        // Paginierung
        if ($atts['pagination'] === 'true') {
            $big = 999999999; // Einzigartige Zahl für die Paginierung
            $output .= '<div class="wp-comic-pagination">';
            $output .= paginate_links(array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, $atts['paged']),
                'total' => $query->max_num_pages,
            ));
            $output .= '</div>';
        }
    } else {
        $output .= '<p>Kein Comic gefunden.</p>';
    }

    $output .= '</div>'; // .wp-comics-grid

    wp_reset_postdata();

    return $output;
}
add_shortcode('wp_comics', 'wp_comics_shortcode');








// Shortcode für kompakte Comic-Ansicht
function wp_comics_compact_shortcode($atts) {
    global $wpdb;

    $atts = shortcode_atts(array(
        'id' => '', // Neuer Parameter für die ID
        'number' => 8,
        'orderby' => 'date',
        'order' => 'DESC',
        'genre' => '',
        'year' => '',
        'publisher' => '',
        'format' => '',
    ), $atts, 'wp_comics_compact');

    $args = array(
        'post_type' => 'comic',
        'posts_per_page' => intval($atts['number']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
    );

    // Wenn eine ID angegeben ist, wird nur dieser Comic angezeigt
    if (!empty($atts['id'])) {
        $args['p'] = intval($atts['id']);
        $args['posts_per_page'] = 1;
    }

    if (!empty($atts['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['genre']),
        );
    }

    if (!empty($atts['year'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publication_year',
            'value' => sanitize_text_field($atts['year']),
        );
    }

    if (!empty($atts['publisher'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publisher',
            'value' => sanitize_text_field($atts['publisher']),
        );
    }

    if (!empty($atts['format'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_format',
            'value' => sanitize_text_field($atts['format']),
        );
    }

    $query = new WP_Query($args);

    $output = '<div class="wp-comics-compact-grid">';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cover_image = get_post_meta(get_the_ID(), '_wp_comics_cover_image', true);
            $series_id = get_post_meta(get_the_ID(), '_wp_comics_series', true); // Hier wird die Serie ID aus den Metadaten geholt

            // Serienname aus der Tabelle `comic_series` abrufen
            $series_name = '';
            if ($series_id) {
                $series_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}comic_series WHERE id = %d", $series_id));
            }

            $output .= '<div class="wp-comic-compact-card" style="background-image: url(' . esc_url($cover_image) . ');">';
            $output .= '<div class="wp-comic-compact-overlay">';
            $output .= '<h3 class="wp-comic-compact-title">' . get_the_title() . '</h3>';
            if ($series_name) {
                $output .= '<div class="wp-comic-compact-series">' . esc_html($series_name) . '</div>'; // Serie anzeigen
            }
            $output .= '<a href="' . get_permalink() . '" class="wp-comic-compact-button">Details</a>';
            $output .= '</div>'; // .wp-comic-compact-overlay
            $output .= '</div>'; // .wp-comic-compact-card
        }
    } else {
        $output .= '<p>Kein Comic gefunden.</p>';
    }

    $output .= '</div>'; // .wp-comics-compact-grid

    wp_reset_postdata();

    return $output;
}
add_shortcode('wp_comics_compact', 'wp_comics_compact_shortcode');



















// Shortcode für Comic-Tabelle
function wp_comics_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'number' => 200,
        'orderby' => 'title',
        'order' => 'ASC',
        'genre' => '',
        'year' => '',
        'publisher' => '',
    ), $atts, 'wp_comics_table');

    $args = array(
        'post_type' => 'comic',
        'posts_per_page' => intval($atts['number']),
        'orderby' => sanitize_text_field($atts['orderby']),
        'order' => sanitize_text_field($atts['order']),
    );

    // Filterung nach Genre
    if (!empty($atts['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'genre',
            'field' => 'slug',
            'terms' => sanitize_text_field($atts['genre']),
        );
    }

    // Filterung nach Erscheinungsjahr
    if (!empty($atts['year'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publication_year',
            'value' => sanitize_text_field($atts['year']),
            'compare' => '='
        );
    }

    // Filterung nach Verlag
    if (!empty($atts['publisher'])) {
        $args['meta_query'][] = array(
            'key' => '_wp_comics_publisher',
            'value' => sanitize_text_field($atts['publisher']),
            'compare' => '='
        );
    }

    $query = new WP_Query($args);

    $output = '<table class="wp-comics-table" style="width:100%; border-collapse:collapse; margin-bottom:20px;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Titel</th>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Verlag</th>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Serie</th>'; // Neue Spalte für Serie
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Erscheinungsjahr</th>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Format</th>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Seitenanzahl</th>';
    $output .= '<th style="border: 1px solid #ccc; padding: 8px;">Limitierung</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $title = get_the_title();
            $permalink = get_permalink();
            $publisher = get_post_meta(get_the_ID(), '_wp_comics_publisher', true);
            $series_id = get_post_meta(get_the_ID(), '_wp_comics_series', true);
            $year = get_post_meta(get_the_ID(), '_wp_comics_publication_year', true);
            $format = get_post_meta(get_the_ID(), '_wp_comics_format', true);
            $pages = get_post_meta(get_the_ID(), '_wp_comics_page_count', true); // Seitenanzahl richtig abrufen
            $is_limited = get_post_meta(get_the_ID(), '_wp_comics_is_limited', true) ? 'Ja' : 'Nein';

            // Serienname aus der Serien-ID abrufen
            $series_name = '';
            if (!empty($series_id)) {
                global $wpdb;
                $series_table = $wpdb->prefix . 'comic_series';
                $series_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $series_table WHERE id = %d", $series_id));
            }

            $output .= '<tr>';
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;"><a href="' . esc_url($permalink) . '" style="text-decoration:none; color:inherit;">' . esc_html($title) . '</a></td>';
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($publisher) . '</td>';
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($series_name) . '</td>'; // Serie anzeigen
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($year) . '</td>';
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($format) . '</td>';
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($pages) . '</td>'; // Seitenanzahl anzeigen
            $output .= '<td style="border: 1px solid #ccc; padding: 8px;">' . esc_html($is_limited) . '</td>';
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="7" style="border: 1px solid #ccc; padding: 8px; text-align:center;">Keine Comics gefunden.</td></tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    wp_reset_postdata();

    return $output;
}
add_shortcode('wp_comics_table', 'wp_comics_table_shortcode');
