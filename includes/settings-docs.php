
<h2>Aktuelle News</h2>
<?php
// Sicherstellen, dass die SimplePie-Bibliothek verfügbar ist
if (!function_exists('fetch_feed')) {
    include_once(ABSPATH . WPINC . '/feed.php');
}

// URL des RSS-Feeds
$rss_url = 'https://spidercomics.de/category/heropanel-updates/feed'; // Ersetze dies durch die tatsächliche URL des Feeds

// Feed abrufen
$rss = fetch_feed($rss_url);

if (!is_wp_error($rss)) { // Prüfen, ob der Feed erfolgreich abgerufen wurde
    $max_items = $rss->get_item_quantity(5); // Anzahl der anzuzeigenden Items
    $rss_items = $rss->get_items(0, $max_items);
} else {
    echo '<p>Der RSS-Feed konnte nicht geladen werden.</p>';
}
?>

<?php if (!empty($rss_items)) : ?>
    <ul>
        <?php foreach ($rss_items as $item) : ?>
            <li>
                <a href="<?php echo esc_url($item->get_permalink()); ?>" target="_blank">
                    <?php echo esc_html($item->get_title()); ?>
                </a>
                <br>
                <small><?php echo esc_html($item->get_date('j. F Y')); ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>Es gibt keine aktuellen News.</p>
<?php endif; ?>

<hr>

<h2 style="color: blue;">### <a href="https://spidercomics.de/bug-report/" style="text-decoration:none;">BUG MELDEN</a> ### <a href="https://spidercomics.de/heropanel-wordpress-plugin/" style="text-decoration:none;">DOCUMENTATION</a> ###</h2>

<hr>

<h2>Verfügbare Shortcodes</h2>
<ul>
    <li><strong>[wp_comics]</strong> - Zeigt die Detail-Cards der Comics an.</li>
    <li><strong>[wp_comics_compact]</strong> - Zeigt die Compact-Cards der Comics an.</li>    
    <li><strong>[wp_comics_table]</strong> - Zeigt eine Liste/Tabelle der Comics an.</li>
</ul>

<h3>Shortcode-Parameter</h3>
<ul>
    <li><strong>id</strong> - Zeigt einen bestimmten Comic anhand der ID an.</li>
    <li><strong>number</strong> - Anzahl der anzuzeigenden Comics pro Seite.</li>
    <li><strong>orderby</strong> - Bestimmt die Sortierreihenfolge (z.B. date, title).</li>
    <li><strong>order</strong> - Sortierreihenfolge (ASC für aufsteigend, DESC für absteigend).</li>
    <li><strong>genre</strong> - Filtert Comics nach Genre.</li>
    <li><strong>year</strong> - Filtert Comics nach Erscheinungsjahr.</li>
    <li><strong>publisher</strong> - Filtert Comics nach Verlag.</li>
    <li><strong>format</strong> - Filtert Comics nach Format.</li>
    <li><strong>columns</strong> - Bestimmt die Anzahl der Spalten im Layout.</li>
    <li><strong>layout</strong> - Layout-Optionen (grid für Raster, list für Liste).</li>
    <li><strong>paged</strong> - Bestimmt die aktuelle Seite für die Paginierung.</li>
    <li><strong>pagination</strong> - Aktiviert oder deaktiviert die Paginierung (true/false).</li>
</ul>
