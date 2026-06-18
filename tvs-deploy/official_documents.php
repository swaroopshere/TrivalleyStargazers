<?php
/**
 * TVS Official Documents Page
 *
 * Bylaws, articles of incorporation, and 501(c)(3) status
 * Converted from official_documents.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Official Documents - Tri-Valley Stargazers';
$pageId = 'm_about';

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Official TVS Documents</h1>

<div class="content">
    <p>
        On this page you will find several official documents detailing the Tri-Valley Stargazer's bylaws,
        formation, and application for status as a 501(c)(3) charitable organization.
    </p>

    <h2 class="subtitle">Bylaws</h2>
    <p>
        You can see our bylaws for the Tri-Valley Stargazers club
        <a href="pdfs/bylaws.pdf" title="See our bylaws" target="_blank">here</a>.
        These detail the purpose of the club, membership, the board of directors, officers, appointees, responsibilities,
        club meetings, elections, amendments, finances, and dissolution.
    </p>

    <h2 class="subtitle">Articles of Incorporation</h2>
    <p>
        You can see some of the documents that went into the incorporation of our club
        <a href="pdfs/articles-of-incorporation.pdf" title="See articles of incorporation" target="_blank">here</a>.
        These include the Articles of Incorporation submitted to state in 1991, and documents received back from the state
        that acknowledge our submission.
    </p>

    <h2 class="subtitle">501(c)(3) Status</h2>
    <p>
        The Tri-Valley Stargazers Astronomy Club is a tax exempt 501(c)(3) charitable organization. You can see our original
        tax exemption Determination Letter
        <a href="pdfs/determination_letter.pdf" title="See our tax exempt determination letter">here</a>.
        You can also confirm our current exemption status and see our recent tax filings by visiting the IRS
        <a href="https://www.irs.gov/charities-non-profits/tax-exempt-organization-search"
           title="Tax Exempt Status" target="_blank">Exempt Organization Select Check</a> web site.
    </p>
</div>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
