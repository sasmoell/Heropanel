<?php

// Enqueue Scripts und Styles für den WordPress Color Picker
function wp_comics_enqueue_color_picker($hook_suffix) {
    // Prüfen, ob wir uns auf der richtigen Einstellungsseite befinden
    if ($hook_suffix === 'comic_page_wp_comics_settings') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        // Optional: Eigenes JavaScript direkt hier einfügen
        wp_enqueue_script(
            'wp_comics_custom_color_picker',
            plugins_url('/javascript/color-picker.js', dirname(__FILE__)), // Pfad zur color-picker.js Datei
            array('wp-color-picker'),
            filemtime(plugin_dir_path(__DIR__) . 'javascript/color-picker.js'), // Dynamische Version basierend auf dem Dateitimestamp
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'wp_comics_enqueue_color_picker');


// Einstellungen-Seite
function wp_comics_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Comics Einstellungen</h1>
        
        <!-- Erfolgsmeldung nach dem Speichern -->
        <?php settings_errors(); ?>

        <!-- Tabs Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="#tab-general" class="nav-tab nav-tab-active">Allgemeine Einstellungen</a>
            <a href="#tab-design" class="nav-tab">Design</a>
            <a href="#tab-docs" class="nav-tab">Dokumentation</a>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields('wp_comics_options_group'); ?>
        
            <!-- Allgemeine Einstellungen Tab -->
            <div id="tab-general" class="tab-content active">
                <!-- Allgemeine Einstellungen -->
                <?php do_settings_sections('wp_comics_settings_general'); ?>
                <?php submit_button(); ?>
            </div>
        
            <!-- Design Tab -->
            <div id="tab-design" class="tab-content">
                <!-- Design-Einstellungen -->
                <?php do_settings_sections('wp_comics_settings_design'); ?>
                <?php submit_button(); ?>
                <!-- Nonce-Feld für das Zurücksetzen auf Standardeinstellungen -->
                <?php wp_nonce_field('wp_comics_reset_defaults_action', 'wp_comics_reset_defaults_nonce'); ?>
                <!-- Button für Standardeinstellungen nur hier anzeigen -->
                <input type="submit" name="wp_comics_reset_defaults" class="button button-secondary" value="Standardeinstellungen wiederherstellen">
            </div>
        
            <!-- Dokumentation Tab -->
            <div id="tab-docs" class="tab-content">
                <!-- Dokumentation -->
                <?php include_once plugin_dir_path(__FILE__) . 'settings-docs.php'; ?>
            </div>
        </form>

    </div>

    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-tab');
            const contents = document.querySelectorAll('.tab-content');
            let activeTab = localStorage.getItem('activeTab') || '#tab-general';
        
            // Setze den aktiven Tab bei Seitenaufruf
            tabs.forEach(tab => {
                tab.classList.remove('nav-tab-active');
                if (tab.getAttribute('href') === activeTab) {
                    tab.classList.add('nav-tab-active');
                }
            });
        
            contents.forEach(content => {
                content.classList.remove('active');
                if (content.id === activeTab.substring(1)) {
                    content.classList.add('active');
                }
            });
        
            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
        
                    // Tabs aktivieren / deaktivieren
                    tabs.forEach(item => item.classList.remove('nav-tab-active'));
                    tab.classList.add('nav-tab-active');
        
                    // Inhalte anzeigen / verstecken
                    contents.forEach(content => content.classList.remove('active'));
                    const targetId = tab.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.add('active');
        
                    // Aktuellen Tab in localStorage speichern
                    localStorage.setItem('activeTab', `#${targetId}`);
                });
            });

            // Aktiviert den Color-Picker
            jQuery(document).ready(function($) {
                $('.wp-comics-color-picker').wpColorPicker();
            });
        });
    </script>


    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-tab');
            const contents = document.querySelectorAll('.tab-content');
            let activeTab = localStorage.getItem('activeTab') || '#tab-general';
        
            // Setze den aktiven Tab bei Seitenaufruf
            tabs.forEach(tab => {
                tab.classList.remove('nav-tab-active');
                if (tab.getAttribute('href') === activeTab) {
                    tab.classList.add('nav-tab-active');
                }
            });
        
            contents.forEach(content => {
                content.classList.remove('active');
                if (content.id === activeTab.substring(1)) {
                    content.classList.add('active');
                }
            });
        
            tabs.forEach(tab => {
                tab.addEventListener('click', function (e) {
                    e.preventDefault();
        
                    // Tabs aktivieren / deaktivieren
                    tabs.forEach(item => item.classList.remove('nav-tab-active'));
                    tab.classList.add('nav-tab-active');
        
                    // Inhalte anzeigen / verstecken
                    contents.forEach(content => content.classList.remove('active'));
                    const targetId = tab.getAttribute('href').substring(1);
                    document.getElementById(targetId).classList.add('active');
        
                    // Aktuellen Tab in localStorage speichern
                    localStorage.setItem('activeTab', #${targetId});
                });
            });

            // Aktiviert den Color-Picker
            jQuery(document).ready(function($) {
                $('.wp-comics-color-picker').wpColorPicker();
            });
        });
    </script>

    <?php
}

// Registrierung der Einstellungen
add_action('admin_init', 'wp_comics_register_settings');

function wp_comics_register_settings() {
    // Allgemeine Einstellungen
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

    // Neue Felder für Beschreibung und Limitierung
    add_settings_field(
        'wp_comics_display_description',
        'Beschreibung anzeigen',
        'wp_comics_display_description_callback',
        'wp_comics_settings_general',
        'wp_comics_settings_section_general'
    );

    add_settings_field(
        'wp_comics_display_limited',
        'Limitierung anzeigen',
        'wp_comics_display_limited_callback',
        'wp_comics_settings_general',
        'wp_comics_settings_section_general'
    );
    
    add_settings_field(
        'wp_comics_display_limited_overlay',
        'Overlay für limitierte Ausgaben anzeigen',
        'wp_comics_display_limited_overlay_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );


    // Design-Einstellungen
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

    add_settings_field(
        'wp_comics_card_background_color',
        'Hintergrundfarbe der Comic-Card',
        'wp_comics_card_background_color_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );

    add_settings_field(
        'wp_comics_card_text_color',
        'Textfarbe der Comic-Card',
        'wp_comics_card_text_color_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );

    add_settings_field(
        'wp_comics_card_title_color',
        'Titel-Farbe der Comic-Card',
        'wp_comics_card_title_color_callback',
        'wp_comics_settings_design',
        'wp_comics_settings_section_design'
    );

    register_setting('wp_comics_options_group', 'wp_comics_options', 'sanitize_callback_function');
}



// Allgemeine Einstellungen - Callback-Funktionen
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

// Callback-Funktion für das Feld "Beschreibung anzeigen"
function wp_comics_display_description_callback() {
    $options = get_option('wp_comics_options');
    $display_description = isset($options['wp_comics_display_description']) ? $options['wp_comics_display_description'] : 'yes';
    ?>
    <select name="wp_comics_options[wp_comics_display_description]">
        <option value="yes" <?php selected($display_description, 'yes'); ?>>Ja</option>
        <option value="no" <?php selected($display_description, 'no'); ?>>Nein</option>
    </select>
    <?php
}

// Callback-Funktion für das Feld "Limitierung anzeigen"
function wp_comics_display_limited_callback() {
    $options = get_option('wp_comics_options');
    $display_limited = isset($options['wp_comics_display_limited']) ? $options['wp_comics_display_limited'] : 'yes';
    ?>
    <select name="wp_comics_options[wp_comics_display_limited]">
        <option value="yes" <?php selected($display_limited, 'yes'); ?>>Ja</option>
        <option value="no" <?php selected($display_limited, 'no'); ?>>Nein</option>
    </select>
    <?php
}

// Callback-Funktion für das Feld "Overlay für limitierte Ausgaben anzeigen"
function wp_comics_display_limited_overlay_callback() {
    $options = get_option('wp_comics_options');
    $display_overlay = isset($options['wp_comics_display_limited_overlay']) ? $options['wp_comics_display_limited_overlay'] : 'yes';
    ?>
    <select name="wp_comics_options[wp_comics_display_limited_overlay]">
        <option value="yes" <?php selected($display_overlay, 'yes'); ?>>Ja</option>
        <option value="no" <?php selected($display_overlay, 'no'); ?>>Nein</option>
    </select>
    <?php
}



// Callback-Funktion für die maximale Breite der Comic-Card
function wp_comics_card_max_width_callback() {
    $options = get_option('wp_comics_options');
    $max_width = isset($options['wp_comics_card_max_width']) ? esc_attr($options['wp_comics_card_max_width']) : '100';
    $unit = isset($options['wp_comics_card_max_width_unit']) ? esc_attr($options['wp_comics_card_max_width_unit']) : '%';
    ?>
    <input type="number" name="wp_comics_options[wp_comics_card_max_width]" value="<?php echo esc_attr($max_width); ?>" placeholder="Breite" min="0" />
    <select name="wp_comics_options[wp_comics_card_max_width_unit]">
        <option value="%" <?php selected($unit, '%'); ?>>%</option>
        <option value="px" <?php selected($unit, 'px'); ?>>px</option>
    </select>
    <p class="description">Wählen Sie, ob die Breite in Prozent (%) oder Pixel (px) angegeben wird.</p>
    <?php
}

// Callback-Funktion für die Titel-Schriftgröße
function wp_comics_font_size_callback() {
    $options = get_option('wp_comics_options');
    $font_size = isset($options['wp_comics_title_font_size']) ? esc_attr($options['wp_comics_title_font_size']) : '16px';
    echo "<input type='text' name='wp_comics_options[wp_comics_title_font_size]' value='" . esc_attr($font_size) . "' placeholder='z.B. 16px oder 1.5em' />";
}

// Callback-Funktionen für die Color-Picker Felder
function wp_comics_card_background_color_callback() {
    $options = get_option('wp_comics_options');
    $color = isset($options['wp_comics_card_background_color']) ? esc_attr($options['wp_comics_card_background_color']) : '#ffffff';
    echo '<input type="text" name="wp_comics_options[wp_comics_card_background_color]" value="' . esc_attr($color) . '" class="wp-comics-color-picker" />';
}

function wp_comics_card_text_color_callback() {
    $options = get_option('wp_comics_options');
    $color = isset($options['wp_comics_card_text_color']) ? esc_attr($options['wp_comics_card_text_color']) : '#000000';
    echo '<input type="text" name="wp_comics_options[wp_comics_card_text_color]" value="' . esc_attr($color) . '" class="wp-comics-color-picker" />';
}

function wp_comics_card_title_color_callback() {
    $options = get_option('wp_comics_options');
    $color = isset($options['wp_comics_card_title_color']) ? esc_attr($options['wp_comics_card_title_color']) : '#000000';
    echo '<input type="text" name="wp_comics_options[wp_comics_card_title_color]" value="' . esc_attr($color) . '" class="wp-comics-color-picker" />';
}


// Sanitize Callback-Funktion aktualisieren
function sanitize_callback_function($input) {
    $sanitized_input = array();

    if (isset($input['wp_comics_display_limited_overlay'])) {
        $sanitized_input['wp_comics_display_limited_overlay'] = sanitize_text_field($input['wp_comics_display_limited_overlay']);
    }

    if (isset($input['wp_comics_title_font_size'])) {
        $sanitized_input['wp_comics_title_font_size'] = sanitize_text_field($input['wp_comics_title_font_size']);
    }

    if (isset($input['wp_comics_display_options'])) {
        $sanitized_input['wp_comics_display_options'] = array_map('sanitize_text_field', $input['wp_comics_display_options']);
    }

    if (isset($input['wp_comics_display_description'])) {
        $sanitized_input['wp_comics_display_description'] = sanitize_text_field($input['wp_comics_display_description']);
    }

    if (isset($input['wp_comics_display_limited'])) {
        $sanitized_input['wp_comics_display_limited'] = sanitize_text_field($input['wp_comics_display_limited']);
    }

    if (isset($input['wp_comics_card_max_width'])) {
        $sanitized_input['wp_comics_card_max_width'] = sanitize_text_field($input['wp_comics_card_max_width']);
    }

    if (isset($input['wp_comics_card_max_width_unit'])) {
        $sanitized_input['wp_comics_card_max_width_unit'] = sanitize_text_field($input['wp_comics_card_max_width_unit']);
    }

    if (isset($input['wp_comics_card_background_color'])) {
        $sanitized_input['wp_comics_card_background_color'] = sanitize_hex_color($input['wp_comics_card_background_color']);
    }

    if (isset($input['wp_comics_card_text_color'])) {
        $sanitized_input['wp_comics_card_text_color'] = sanitize_hex_color($input['wp_comics_card_text_color']);
    }

    if (isset($input['wp_comics_card_title_color'])) {
        $sanitized_input['wp_comics_card_title_color'] = sanitize_hex_color($input['wp_comics_card_title_color']);
    }

    return $sanitized_input;
}

// Dynamisches CSS für die Comic-Card basierend auf den Einstellungen
add_action('wp_head', 'wp_comics_custom_css');
function wp_comics_custom_css() {
    $options = get_option('wp_comics_options');
    $max_width = isset($options['wp_comics_card_max_width']) ? esc_attr($options['wp_comics_card_max_width']) : '100';
    $unit = isset($options['wp_comics_card_max_width_unit']) ? esc_attr($options['wp_comics_card_max_width_unit']) : '%';

    // Dynamisches CSS ausgeben
    echo "<style>
    .wp-comic-card {
        max-width: " . esc_attr($max_width) . esc_attr($unit) . ";
    }
    </style>";
}

// Dynamische Anwendung der Benutzerfarben für die Comic-Cards
add_action('wp_head', 'wp_comics_custom_colors');
function wp_comics_custom_colors() {
    $options = get_option('wp_comics_options');
    $background_color = isset($options['wp_comics_card_background_color']) ? esc_attr($options['wp_comics_card_background_color']) : '#ffffff';
    $text_color = isset($options['wp_comics_card_text_color']) ? esc_attr($options['wp_comics_card_text_color']) : '#000000';
    $title_color = isset($options['wp_comics_card_title_color']) ? esc_attr($options['wp_comics_card_title_color']) : '#000000';

    // Die Farben korrekt innerhalb der CSS-Ausgabe escapen
    echo '<style>
    .wp-comic-card {
        background-color: ' . esc_attr($background_color) . ';
    }
    .wp-comic-card .wp-comic-title {
        color: ' . esc_attr($title_color) . ';
    }
    .wp-comic-card .wp-comic-meta,
    .wp-comic-card .wp-comic-genres,
    .wp-comic-card .wp-comic-description {
        color: ' . esc_attr($text_color) . ';
    }
    </style>';
}

// Funktion zur Handhabung des Zurücksetzens auf die Standardeinstellungen für Design-Einstellungen
function wp_comics_handle_reset_defaults() {
    if (isset($_POST['wp_comics_reset_defaults'])) {
        // Nonce-Überprüfung
        if (!isset($_POST['wp_comics_reset_defaults_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_comics_reset_defaults_nonce'])), 'wp_comics_reset_defaults_action')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        // Debugging: Überprüfen, ob die Funktion aufgerufen wird
        error_log('Reset-Button wurde gedrückt.');

        // Die aktuellen Einstellungen abrufen, um die Allgemeinen Einstellungen beizubehalten
        $current_settings = get_option('wp_comics_options');

        // Standardeinstellungen definieren (nur für Design-Einstellungen)
        $default_settings = array(
            'wp_comics_card_max_width' => '80',
            'wp_comics_card_max_width_unit' => '%',
            'wp_comics_title_font_size' => '26px',
            'wp_comics_card_background_color' => '#eaeaea',
            'wp_comics_card_text_color' => '#000000',
            'wp_comics_card_title_color' => '#000000',
        );

        // Die allgemeinen Einstellungen aus den aktuellen Einstellungen beibehalten
        $merged_settings = array_merge($current_settings, $default_settings);

        // Optionen zurücksetzen
        update_option('wp_comics_options', $merged_settings);

        // Erfolgsmeldung anzeigen
        add_settings_error('wp_comics_messages', 'wp_comics_message', 'Standardeinstellungen wurden erfolgreich wiederhergestellt.', 'updated');

        // Seite erneut laden, um die Änderungen sichtbar zu machen
        wp_safe_redirect(add_query_arg(array('settings-updated' => 'true'), wp_get_referer()));
        exit;
    }
}
add_action('admin_init', 'wp_comics_handle_reset_defaults');
