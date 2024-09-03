<?php

// Export-Logik registrieren
add_action('admin_init', 'wp_comics_export_data');

// Import-Logik registrieren
add_action('admin_init', 'wp_comics_import_data');

// Export-Formular anzeigen
function wp_comics_export_form() {
    ?>
    <div class="wrap">
        <h1>Comics Export/Import</h1>

        <?php
        // Nonce-Überprüfung für die GET-Parameter
        if (isset($_GET['import']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'wp_comics_import_status')): ?>
            <?php if ($_GET['import'] === 'success'): ?>
                <div class="notice notice-success is-dismissible">
                    <p>Der Import wurde erfolgreich abgeschlossen.</p>
                </div>
            <?php elseif ($_GET['import'] === 'error'): ?>
                <div class="notice notice-error is-dismissible">
                    <p>Beim Import ist ein Fehler aufgetreten: <?php echo isset($_GET['error_message']) ? esc_html(sanitize_text_field(wp_unslash($_GET['error_message']))) : 'Unbekannter Fehler.'; ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=wp_comics_export_import')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('wp_comics_export', 'wp_comics_export_nonce'); ?>
            <h2>Exportieren</h2>
            <p>
                <input type="checkbox" name="include_covers" id="include_covers" value="1">
                <label for="include_covers">Coverbilder im ZIP-Archiv exportieren</label>
            </p>
            <p>
                <input type="checkbox" name="include_drafts" id="include_drafts" value="1">
                <label for="include_drafts">Entwürfe in den Export einbeziehen</label>
            </p>
            <?php submit_button('Exportieren', 'primary', 'export_comics'); ?>
        </form>

        <hr>

        <h2>Importieren</h2>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin.php?page=wp_comics_export_import')); ?>">
            <?php wp_nonce_field('wp_comics_import', 'wp_comics_import_nonce'); ?>
            <p>
                <label for="import_file">Datei auswählen:</label>
                <input type="file" name="import_file" id="import_file" accept=".zip">
            </p>
            <?php submit_button('Importieren', 'primary', 'import_comics'); ?>
        </form>
    </div>
    <?php
}

// Export-Logik
function wp_comics_export_data() {
    if (isset($_POST['export_comics']) && check_admin_referer('wp_comics_export', 'wp_comics_export_nonce')) {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $include_covers = isset($_POST['include_covers']) && $_POST['include_covers'] == '1';
        $include_drafts = isset($_POST['include_drafts']) && $_POST['include_drafts'] == '1';

        $args = array(
            'post_type' => 'comic',
            'posts_per_page' => -1,
            'post_status' => $include_drafts ? array('publish', 'draft') : 'publish'
        );

        $query = new WP_Query($args);
        $comics = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Beschreibung säubern und in Anführungszeichen setzen
                $description = get_post_meta(get_the_ID(), '_wp_comics_description', true);
                $clean_description = wp_strip_all_tags($description); // HTML-Tags entfernen
                $clean_description = preg_replace('/\s+/', ' ', $clean_description); // Mehrfache Leerzeichen durch ein einzelnes Leerzeichen ersetzen
                $clean_description = trim($clean_description); // Leerzeichen am Anfang und Ende entfernen
                $clean_description = '"' . str_replace('"', '""', $clean_description) . '"'; // Anführungszeichen um die Beschreibung und doppelte Anführungszeichen innerhalb der Beschreibung

                $comic_data = array(
                    'title' => get_the_title(),
                    'publisher' => get_post_meta(get_the_ID(), '_wp_comics_publisher', true),
                    'issue_number' => get_post_meta(get_the_ID(), '_wp_comics_issue_number', true),
                    'publication_year' => get_post_meta(get_the_ID(), '_wp_comics_publication_year', true),
                    'format' => get_post_meta(get_the_ID(), '_wp_comics_format', true),
                    'page_count' => get_post_meta(get_the_ID(), '_wp_comics_page_count', true),
                    'description' => $clean_description, // Gesäuberte und korrekt formatierte Beschreibung
                    'issue_type' => get_post_meta(get_the_ID(), '_wp_comics_issue_type', true),
                    'is_limited' => get_post_meta(get_the_ID(), '_wp_comics_is_limited', true),
                    'limited_number' => get_post_meta(get_the_ID(), '_wp_comics_limited_number', true),
                    'limited_total' => get_post_meta(get_the_ID(), '_wp_comics_limited_total', true),
                    'cover_image' => get_post_meta(get_the_ID(), '_wp_comics_cover_image', true)
                );

                if ($include_covers && !empty($comic_data['cover_image'])) {
                    $upload_dir = wp_upload_dir();
                    $cover_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $comic_data['cover_image']);
                    if ($wp_filesystem->exists($cover_path)) {
                        $comic_data['cover_image_file'] = basename($cover_path);
                        $comic_data['cover_image_full_path'] = $cover_path;
                    }
                }

                $comics[] = $comic_data;
            }
            wp_reset_postdata();
        }

        $csv_filename = 'comics_export_' . gmdate('Y-m-d') . '.csv';
        $csv_content = 'Titel,Verlag,Ausgabenummer,Erscheinungsjahr,Format,Seitenanzahl,Beschreibung,Ausgabe-Typ,Limitierung,Limitierungsnummer,Gesamtanzahl,Coverbild' . "\n";

        foreach ($comics as $comic) {
            $csv_content .= implode(',', array(
                $comic['title'],
                $comic['publisher'],
                $comic['issue_number'],
                $comic['publication_year'],
                $comic['format'],
                $comic['page_count'],
                $comic['description'], // Gesäuberte und korrekt formatierte Beschreibung
                $comic['issue_type'],
                $comic['is_limited'],
                $comic['limited_number'],
                $comic['limited_total'],
                isset($comic['cover_image_file']) ? $comic['cover_image_file'] : ''
            )) . "\n";
        }

        $wp_filesystem->put_contents($csv_filename, $csv_content, FS_CHMOD_FILE);

        if ($include_covers) {
            $zip = new ZipArchive();
            $zip_filename = 'comics_export_' . gmdate('Y-m-d') . '.zip';

            if ($zip->open($zip_filename, ZipArchive::CREATE) === true) {
                $zip->addFile($csv_filename, basename($csv_filename));

                $added_files = array();

                foreach ($comics as $comic) {
                    if (!empty($comic['cover_image_file']) && !empty($comic['cover_image_full_path'])) {
                        if (!in_array($comic['cover_image_file'], $added_files)) {
                            if ($wp_filesystem->exists($comic['cover_image_full_path'])) {
                                $zip->addFile($comic['cover_image_full_path'], 'covers/' . $comic['cover_image_file']);
                                $added_files[] = $comic['cover_image_file'];
                            }
                        }
                    }
                }

                $zip->close();

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=' . esc_attr($zip_filename));
                header('Content-Length: ' . esc_attr($wp_filesystem->size($zip_filename)));

                $file_contents = $wp_filesystem->get_contents($zip_filename);
                if ($file_contents !== false) {
                    echo $file_contents;
                }

                $wp_filesystem->delete($csv_filename);
                $wp_filesystem->delete($zip_filename);
            }
        } else {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=' . esc_attr($csv_filename));

            $file_contents = $wp_filesystem->get_contents($csv_filename);
            if ($file_contents !== false) {
                echo $file_contents;
            }

            $wp_filesystem->delete($csv_filename);
        }

        exit;
    }
}






// Import-Logik
function wp_comics_import_data() {
    if (isset($_POST['import_comics']) && check_admin_referer('wp_comics_import', 'wp_comics_import_nonce')) {
        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Überprüfen, ob die Datei im Formular hochgeladen wurde
        if (isset($_FILES['import_file']['tmp_name']) && !empty($_FILES['import_file']['tmp_name'])) {
            $file_name = isset($_FILES['import_file']['name']) ? sanitize_file_name(wp_unslash($_FILES['import_file']['name'])) : '';
            $file_path = sanitize_text_field(wp_unslash($_FILES['import_file']['tmp_name']));

            // Dateityp überprüfen
            $file_type = wp_check_filetype($file_name);

            if ($file_type['ext'] === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($file_path) === true) {
                    $extract_path = wp_upload_dir()['basedir'] . '/comics_import/';
                    $zip->extractTo($extract_path);
                    $zip->close();

                    $csv_file = $extract_path . 'comics_export_' . gmdate('Y-m-d') . '.csv';
                    if ($wp_filesystem->exists($csv_file)) {
                        $csv_data = $wp_filesystem->get_contents($csv_file);
                        $csv_lines = explode(PHP_EOL, $csv_data);

                        // Erste Zeile (Header) überspringen
                        array_shift($csv_lines);

                        foreach ($csv_lines as $line) {
                            if (!empty(trim($line))) {
                                $data = str_getcsv($line);

                                $post_id = wp_insert_post(array(
                                    'post_title' => sanitize_text_field($data[0]),
                                    'post_type' => 'comic',
                                    'post_status' => 'draft'
                                ));

                                if ($post_id && !is_wp_error($post_id)) {
                                    update_post_meta($post_id, '_wp_comics_publisher', sanitize_text_field($data[1]));
                                    update_post_meta($post_id, '_wp_comics_issue_number', sanitize_text_field($data[2]));
                                    update_post_meta($post_id, '_wp_comics_publication_year', sanitize_text_field($data[3]));
                                    update_post_meta($post_id, '_wp_comics_format', sanitize_text_field($data[4]));
                                    update_post_meta($post_id, '_wp_comics_page_count', sanitize_text_field($data[5]));
                                    update_post_meta($post_id, '_wp_comics_description', sanitize_textarea_field($data[6]));
                                    update_post_meta($post_id, '_wp_comics_issue_type', sanitize_text_field($data[7]));
                                    update_post_meta($post_id, '_wp_comics_is_limited', sanitize_text_field($data[8]));
                                    update_post_meta($post_id, '_wp_comics_limited_number', sanitize_text_field($data[9]));
                                    update_post_meta($post_id, '_wp_comics_limited_total', sanitize_text_field($data[10]));

                                    if (!empty($data[11])) {
                                        $cover_path = $extract_path . 'covers/' . basename(sanitize_file_name($data[11]));
                                        if ($wp_filesystem->exists($cover_path)) {
                                            $upload = wp_upload_bits(basename($cover_path), null, $wp_filesystem->get_contents($cover_path));
                                            if (!$upload['error']) {
                                                update_post_meta($post_id, '_wp_comics_cover_image', esc_url_raw($upload['url']));
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $wp_filesystem->delete($csv_file);
                        $wp_filesystem->rmdir($extract_path, true);

                        wp_redirect(admin_url('admin.php?page=wp_comics_export_import&import=success&_wpnonce=' . wp_create_nonce('wp_comics_import_status')));
                        exit;
                    }
                }
            } elseif ($file_type['ext'] === 'csv') {
                $csv_data = $wp_filesystem->get_contents($file_path);
                $csv_lines = explode(PHP_EOL, $csv_data);

                // Erste Zeile (Header) überspringen
                array_shift($csv_lines);

                foreach ($csv_lines as $line) {
                    if (!empty(trim($line))) {
                        $data = str_getcsv($line);

                        $post_id = wp_insert_post(array(
                            'post_title' => sanitize_text_field($data[0]),
                            'post_type' => 'comic',
                            'post_status' => 'draft'
                        ));

                        if ($post_id && !is_wp_error($post_id)) {
                            update_post_meta($post_id, '_wp_comics_publisher', sanitize_text_field($data[1]));
                            update_post_meta($post_id, '_wp_comics_issue_number', sanitize_text_field($data[2]));
                            update_post_meta($post_id, '_wp_comics_publication_year', sanitize_text_field($data[3]));
                            update_post_meta($post_id, '_wp_comics_format', sanitize_text_field($data[4]));
                            update_post_meta($post_id, '_wp_comics_page_count', sanitize_text_field($data[5]));
                            update_post_meta($post_id, '_wp_comics_description', sanitize_textarea_field($data[6]));
                            update_post_meta($post_id, '_wp_comics_issue_type', sanitize_text_field($data[7]));
                            update_post_meta($post_id, '_wp_comics_is_limited', sanitize_text_field($data[8]));
                            update_post_meta($post_id, '_wp_comics_limited_number', sanitize_text_field($data[9]));
                            update_post_meta($post_id, '_wp_comics_limited_total', sanitize_text_field($data[10]));
                        }
                    }
                }

                wp_redirect(admin_url('admin.php?page=wp_comics_export_import&import=success&_wpnonce=' . wp_create_nonce('wp_comics_import_status')));
                exit;
            }
        }
    }
}
?>
