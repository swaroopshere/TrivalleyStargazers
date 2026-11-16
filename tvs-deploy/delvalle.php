<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Del Valle Observing Site - Tri-Valley Stargazers';
$pageId = 'm_sites';
include __DIR__ . '/includes/templates/header.php';
?>
<h1 class="title">Del Valle Observing Site</h1>
<p>Use the approved site guide to obtain a permit, plan your visit, or request the latest notification procedure as a current permit holder.</p>
<ul>
<li><a href="pdfs/delvalle.pdf?v=20261001#page=1">How to obtain a permit</a></li>
<li><a href="pdfs/delvalle.pdf?v=20261001#page=2">Helpful site information</a></li>
<li><a href="pdfs/delvalle.pdf?v=20261001#page=4">How current permit holders can request the notification procedure</a></li>
</ul>
<p><a href="pdfs/delvalle.pdf?v=20261001">Open or download the complete Del Valle site guide (PDF)</a>.</p>
<object data="pdfs/delvalle.pdf?v=20261001" type="application/pdf" width="100%" height="1050" title="Del Valle site guide">
<p><a href="pdfs/delvalle.pdf?v=20261001">Open the site guide in your PDF reader</a>.</p>
</object>
<?php include __DIR__ . '/includes/templates/footer.php'; ?>
