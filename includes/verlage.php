<?php


if (!defined('ABSPATH')) {exit;}

// Verlage-Verwaltungsseite
function wp_comics_verlage_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'comics_verlage';
    $editing_verlag = null;

    // Verlag hinzufügen
    if (isset($_POST['new_verlag_name']) && isset($_POST['wp_comics_verlage_nonce'])) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_comics_verlage_nonce'])), 'wp_comics_verlage_action')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        $name = sanitize_text_field(wp_unslash($_POST['new_verlag_name']));
        
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->insert($table_name, array('name' => $name));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        wp_cache_delete('all_verlage', 'wp_comics');
        add_settings_error('wp_comics_messages', 'wp_comics_message', 'Verlag erfolgreich hinzugefügt.', 'updated');
    }

    // Verlag bearbeiten
    if (isset($_POST['edit_verlag_id']) && isset($_POST['edit_verlag_name']) && isset($_POST['wp_comics_verlage_nonce'])) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_comics_verlage_nonce'])), 'wp_comics_verlage_action')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        $id = intval($_POST['edit_verlag_id']);
        $name = sanitize_text_field(wp_unslash($_POST['edit_verlag_name']));
        
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->update($table_name, array('name' => $name), array('id' => $id));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        wp_cache_delete('all_verlage', 'wp_comics');
        add_settings_error('wp_comics_messages', 'wp_comics_message', 'Verlag erfolgreich bearbeitet.', 'updated');
    }

    // Verlag zur Bearbeitung laden
    if (isset($_GET['edit_verlag']) && isset($_GET['wp_comics_verlage_nonce'])) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wp_comics_verlage_nonce'])), 'wp_comics_verlage_action')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        $id = intval($_GET['edit_verlag']);
        
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $editing_verlag = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
    }

    // Verlag löschen
    if (isset($_POST['delete_verlag_id']) && isset($_POST['wp_comics_verlage_nonce'])) {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wp_comics_verlage_nonce'])), 'wp_comics_verlage_action')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        $id = intval($_POST['delete_verlag_id']);
        
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->delete($table_name, array('id' => $id));
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

        wp_cache_delete('all_verlage', 'wp_comics');
        add_settings_error('wp_comics_messages', 'wp_comics_message', 'Verlag erfolgreich gelöscht.', 'updated');
    }

    // Verlage aus dem Cache holen oder Datenbankabfrage durchführen
    $verlage = wp_cache_get('all_verlage', 'wp_comics');
    if ($verlage === false) {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
        $verlage = $wpdb->get_results("SELECT * FROM $table_name");
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery
        wp_cache_set('all_verlage', $verlage, 'wp_comics', 3600); // Cache für 1 Stunde setzen
    }

    settings_errors('wp_comics_messages'); // Zeigt alle Erfolgsmeldungen an

    ?>
    <div class="wrap">
        <h1>Verlage verwalten</h1>

        <?php if ($editing_verlag): ?>
            <h2>Verlag bearbeiten</h2>
            <form method="post" action="">
                <?php wp_nonce_field('wp_comics_verlage_action', 'wp_comics_verlage_nonce'); ?>
                <input type="hidden" name="edit_verlag_id" value="<?php echo esc_attr($editing_verlag->id); ?>" />
                <input type="text" name="edit_verlag_name" value="<?php echo esc_attr($editing_verlag->name); ?>" placeholder="Verlagsname" required />
                <input type="submit" value="Speichern" class="button-primary" onclick="return confirm('Möchten Sie diese Änderung wirklich speichern?');" />
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=comic&page=wp_comics_verlage')); ?>" class="button-secondary">Abbrechen</a>
            </form>
        <?php else: ?>
            <form method="post" action="">
                <?php wp_nonce_field('wp_comics_verlage_action', 'wp_comics_verlage_nonce'); ?>
                <h2>Neuen Verlag hinzufügen</h2>
                <input type="text" name="new_verlag_name" value="" placeholder="Verlagsname" required />
                <input type="submit" value="Hinzufügen" class="button-primary" />
            </form>
        <?php endif; ?>

        <h2>Verfügbare Verlage</h2>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Verlag</th>
                    <th id="columnname" class="manage-column column-columnname" scope="col">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($verlage) : ?>
                    <?php foreach ($verlage as $verlag) : ?>
                        <tr>
                            <td class="column-columnname"><?php echo esc_html($verlag->name); ?></td>
                            <td class="column-columnname">
                                <a href="<?php echo esc_url(add_query_arg(array('edit_verlag' => $verlag->id, 'wp_comics_verlage_nonce' => wp_create_nonce('wp_comics_verlage_action')), admin_url('edit.php?post_type=comic&page=wp_comics_verlage'))); ?>" class="button-secondary">Bearbeiten</a>
                                <form method="post" action="" style="display:inline;">
                                    <?php wp_nonce_field('wp_comics_verlage_action', 'wp_comics_verlage_nonce'); ?>
                                    <input type="hidden" name="delete_verlag_id" value="<?php echo esc_attr($verlag->id); ?>" />
                                    <input type="submit" value="Löschen" class="button-secondary" onclick="return confirm('Möchten Sie diesen Verlag wirklich löschen?');" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="2">Keine Verlage gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function wp_comics_create_verlage_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'comics_verlage';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
