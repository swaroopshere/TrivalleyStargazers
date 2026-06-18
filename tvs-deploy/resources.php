<?php
/**
 * TVS Resources Page
 *
 * Links to various club resources
 * Converted from resources.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Resources - Tri-Valley Stargazers';
$pageId = 'm_resources';

include __DIR__ . '/includes/templates/header.php';
?>

<a name="Anchor"></a>
<h1 class="title">Resources</h1>

<div class="content">
    <p>Here are some resources that you might want to look at:</p>
    <ul>
        <li><a href="loanerscope.php" title="Learn about our loaner scope program">Loaner Scopes</a></li>
        <li><a href="delvalle.php" title="Learn about the Del Valle observing site">Del Valle</a></li>
        <li><a href="h2o.php" title="Learn about the Hidden Hill Observatory dark sky site">H2O</a></li>
        <li><a href="contributions.php" title="Find special contributions to this site by our members">Member Contributions</a></li>
        <li><a href="telescopes.php" title="Learn about different kinds of telescopes">Telescopes</a></li>
        <li><a href="library.php" title="Learn about our lending library">Library</a></li>
        <li><a href="books.php" title="See some suggested books for reading">Books</a></li>
        <li><a href="links.php" title="See links to other astronomy sites">Links</a></li>
    </ul>
</div>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
