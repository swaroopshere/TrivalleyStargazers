<?php
/**
 * TVS All Events Page
 *
 * Embedded Google Calendar showing club events
 * Converted from allEvents.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tri-Valley Stargazers Events';
$pageId = 'm_resources';

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Club Events Calendar</h1>

<div id="tvsCalendar" class="tvsCalendar">
    <iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&ctz=America%2FLos_Angeles&bgcolor=%23ffffff&title=Trivalley%20Stargazers%20Club%20Events&src=bWNtMDU5YzNyNzh1bW1pMjhsbWQxdWduM28zOGIydDJAaW1wb3J0LmNhbGVuZGFyLmdvb2dsZS5jb20&color=%23C0CA33"
            style="border-width: 1px; display: block"
            width="800"
            height="600"
            frameborder="1"
            scrolling="no"></iframe>
</div>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
