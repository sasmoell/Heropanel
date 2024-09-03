<?php

if (!defined('ABSPATH')) {
    exit;
}

// Serien hinzufügen
function wp_comics_add_series($name, $start_year, $end_year, $publisher_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comic_series';

    // Speichern der neuen Serie in der Datenbank
    $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'start_year' => $start_year,
            'end_year' => $end_year,
            'publisher_id' => $publisher_id
        )
    );
}

// Serien auslesen
function wp_comics_get_series() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comic_series';

    return $wpdb->get_results("SELECT * FROM $table_name");
}

// Serie bearbeiten
function wp_comics_update_series($id, $name, $start_year, $end_year, $publisher_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comic_series';

    $wpdb->update(
        $table_name,
        array(
            'name' => $name,
            'start_year' => $start_year,
            'end_year' => $end_year,
            'publisher_id' => $publisher_id
        ),
        array('id' => $id)
    );
}

// Serie löschen
function wp_comics_delete_series($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comic_series';

    $wpdb->delete($table_name, array('id' => $id));
}

// Stile nur für die Serien-Verwaltungsseite im Admin-Bereich einbinden
function wp_comics_enqueue_admin_styles($hook) {
    if ($hook == 'comic_page_wp-comics-series') {
        wp_enqueue_style('wp-comics-admin-styles', plugins_url('../css/frontend-styles.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'wp_comics_enqueue_admin_styles');

// Seite für Serienverwaltung
function wp_comics_series_page() {
    global $wpdb;

    // Verlage aus der Tabelle 'comics_verlage' holen
    $publishers = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}comics_verlage");

    // Daten verarbeiten (Hinzufügen, Bearbeiten, Löschen)
    if (isset($_POST['add_series'])) {
        $name = sanitize_text_field($_POST['name']);
        $start_year = sanitize_text_field($_POST['start_year']);
        $end_year = sanitize_text_field($_POST['end_year']);
        $publisher_id = intval($_POST['publisher_id']);
        wp_comics_add_series($name, $start_year, $end_year, $publisher_id);
    } elseif (isset($_POST['update_series'])) {
        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $start_year = sanitize_text_field($_POST['start_year']);
        $end_year = sanitize_text_field($_POST['end_year']);
        $publisher_id = intval($_POST['publisher_id']);
        wp_comics_update_series($id, $name, $start_year, $end_year, $publisher_id);
    } elseif (isset($_POST['delete_series'])) {
        $id = intval($_POST['id']);
        wp_comics_delete_series($id);
    }

    // Serien aus der Datenbank holen
    $series = wp_comics_get_series();

    // Formular und Serienliste anzeigen
    ?>
    <div class="wrap">
        <h1>Comic Serien Verwaltung</h1>

        <form method="post" action="">
            <div class="comic-series-form" style="margin-top: 20px;">
                <div class="comic-series-form-group">
                    <label for="name">Serienname</label>
                    <input type="text" id="name" name="name" value="" required />
                </div>
                <div class="comic-series-form-group">
                    <label for="start_year">Startjahr</label>
                    <input type="text" id="start_year" name="start_year" maxlength="4" pattern="\d{4}" required />
                </div>
                <div class="comic-series-form-group">
                    <label for="end_year">Endjahr</label>
                    <input type="text" id="end_year" name="end_year" maxlength="4" pattern="\d{4}" />
                </div>
                <div class="comic-series-form-group">
                    <label for="publisher_id">Verlag</label>
                    <select id="publisher_id" name="publisher_id" required>
                        <option value="">Wähle einen Verlag</option>
                        <?php foreach ($publishers as $publisher) : ?>
                            <option value="<?php echo esc_attr($publisher->id); ?>"><?php echo esc_html($publisher->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <p class="submit">
                <input type="submit" name="add_series" class="button button-primary" value="Serie hinzufügen" />
            </p>
        </form>

        <!-- Liste der vorhandenen Serien -->
        <h2>Vorhandene Serien</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Startjahr</th>
                    <th>Endjahr</th>
                    <th>Verlag</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($series) : ?>
                    <?php foreach ($series as $serie) : ?>
                        <tr>
                            <td><?php echo esc_html($serie->id); ?></td>
                            <td><?php echo esc_html($serie->name); ?></td>
                            <td><?php echo esc_html($serie->start_year); ?></td>
                            <td><?php echo esc_html($serie->end_year); ?></td>
                            <td><?php 
                                $publisher_name = $wpdb->get_var($wpdb->prepare(
                                    "SELECT name FROM {$wpdb->prefix}comics_verlage WHERE id = %d", 
                                    $serie->publisher_id
                                ));
                                echo esc_html($publisher_name); 
                            ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($serie->id); ?>" />
                                    <input type="submit" name="delete_series" class="button button-secondary" value="Löschen" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">Keine Serien gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
