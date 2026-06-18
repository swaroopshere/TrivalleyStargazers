<?php
/**
 * TVS Summer Observing Program
 * Converted from observing_summer.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Summer Observing Program - Tri-Valley Stargazers';
$pageId = 'm_activities';

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Summer Observing Program</h1>

<h2 class="subtitle">Introduction</h2>
<p>
    Welcome to the Summer! With the Tri-Valley blessed with clear skies and warm evenings on most summer days, this is
    the best time of year to explore the heavens. Tri-Valley Stargazers invite you to take up a simple challenge during
    the months of June, July and August to document your observations of the 29 astronomical objects shown in the list
    that follows. We have included a
    <a href="observing_summer_spreadsheet.xls" title="download targets and log">spreadsheet</a>
    that lists the targets for this program and includes a log that you can use to keep track of your progress. You can
    also print these files by clicking on these links:
    <a href="observing_summer_targets.pdf" title="Targets">target list</a> and
    <a href="observing_summer_log.pdf" title="Logging form">log</a>.
</p>

<h2 class="subtitle">Rules and Regulations</h2>
<p>
    To participate in this program you must be a TVS member. You should use a telescope with an aperture of 4 inches or
    greater. Observations should be made visually, not photographically. Observe all 29 objects and keep a log of your
    observations using the provided form or any other that includes this information:
</p>
<ul>
    <li>Date and time</li>
    <li>Observing site</li>
    <li>Seeing and transparency</li>
    <li>Aperture size of telescope</li>
    <li>Power used</li>
    <li>A description of the object as it appears in the eyepiece</li>
    <li>An optional sketch of the object</li>
</ul>
<p>
    Go-to telescopes are allowed, but star hopping is recommended. Star hopping increases one's familiarity with both
    one's equipment and with the stars and constellations.
</p>

<h2 class="subtitle">Submitting for Certification</h2>
<p>
    When you complete the suggested program you may submit your observations to the TVS
    <script type="text/javascript">
        contact("awards", "trivalleystargazers.org", "Astronomical Observations Program");
    </script>
    for their review and award recognition.
</p>

<h2 class="subtitle">Links</h2>
<p>
    Targets + log: <a href="observing_summer_spreadsheet.xls">(Excel format)</a><br>
    Target list: <a href="observing_summer_targets.pdf">(PDF format)</a><br>
    Logging form: <a href="observing_summer_log.pdf">(PDF format)</a>
</p>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
