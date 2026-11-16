<?php
http_response_code(404);
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Page not found - Tri-Valley Stargazers';
include __DIR__ . '/includes/templates/header.php';
?>
<h1 class="title">Page not found</h1>
<p>The requested page could not be found. Please use the navigation above or <a href="/index.php">return to the home page</a>.</p>
<?php include __DIR__ . '/includes/templates/footer.php'; ?>
