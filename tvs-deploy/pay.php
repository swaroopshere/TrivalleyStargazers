<?php
/**
 * TVS Payment Page
 *
 * Membership dues payment via PayPal
 * Converted from pay.shtml - preserves PayPal JavaScript integration
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Pay Your TVS Dues';
$pageId = 'm_membership';

// Check if user came from membership application
$isApplicant = isset($_GET['applicant']) && $_GET['applicant'] !== '';

// Custom onload for this page
$onload = "updateTotal();";
$onbeforeunload = $isApplicant ? "return unloadHandler();" : "";

include __DIR__ . '/includes/templates/header.php';
?>

<script type="text/javascript">
// Warn user before leaving if they're an applicant
function unloadHandler() {
    return "Your application will not be processed until we receive your payment!";
}
<?php if ($isApplicant): ?>
// Keep warning for applicants
<?php else: ?>
// No warning needed for regular users
document.body.onbeforeunload = "";
<?php endif; ?>
</script>

<?php if (!$isApplicant): ?>
<div id="regular">
    <h1 class="title" style="width: 280px;">Pay your TVS dues here</h1>

    <p>Joining the TVS is a two step process. If you haven't already done so, you must first fill out the
    <a href="membership.php" title="Apply for TVS membership">application form</a>,
    which gives us information about you such as your name and email address. Then you can
    fill out the form below to pay dues, arrange for access to H2O,
    and/or make a contribution to the TVS. Returning members can also use this form to renew
    their membership and/or choose new options. If you would rather not use PayPal, you can click
    <a title="TVS Membership Application" href="pdfs/TVSapplication.pdf" target="_blank">here</a>
    to download an application form in PDF format. Then mail the form along with a
    check to Tri-Valley Stargazers, P.O. Box 2476, Livermore, CA 94551-2476.</p>
    <br>
</div>
<?php else: ?>
<div id="applicant">
    <h1 class="title" style="width: 490px;">Complete your application by paying dues</h1>

    <p>Thank you for submitting your membership application form. Before your application is complete,
    you must still pay dues. You can do this by filling out the form below, where you can also
    arrange for access to H2O, and/or make a contribution to the TVS.
    If you would rather not use PayPal, you can click
    <a title="TVS Membership Application" href="pdfs/TVSapplication.pdf" target="_blank">here</a>
    to download an application form in PDF format. Then mail the form along with a
    check to Tri-Valley Stargazers, P.O. Box 2476, Livermore, CA 94551-2476.</p>

    <h2 style="color: #A8311B;">Warning: your application will not be processed until we receive your payment!</h2>
    <br>
</div>
<?php endif; ?>

<table class="items">
    <tr><td class="items">
        <span class="itemTitle">
            <label for="membershipType">Membership:&nbsp;&nbsp;&nbsp;</label>
            <select id="membershipType" onchange="updateTotal();">
                <option value="0">None</option>
                <option value="10">Student $10</option>
                <option value="30">Regular $30</option>
                <!-- Patron membership disabled until observatory is reconstructed -->
                <!-- <option value="100">Patron $100</option> -->
            </select>
        </span>
        <div class="itemDetails">
            Regular membership costs $30. Student membership costs $10. (Must be a full time high-school or
            college student.) Patron membership costs $100, which allows you access to the club's 17.5" telescope.
            Patron membership is available to those who have been TVS members for at least a year, and they must be a
            key holder. All members will receive an email notification when the PDF version of the newsletter is
            available for viewing on the
            <a href="newsletter.php">newsletter</a>
            web page.<br><br>
            <b>Note: Patron Membership will not be available until our observatory is reconstructed</b>
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="H2OAccess">H2O yearly site access (optional):&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" id="H2OAccess" value="10" onchange="updateTotal();">
        </span>
        <div class="itemDetails">
            This is a $10 fee, which must be paid every year in order to access the club's
            <a href="h2o.php">H2O</a>
            dark sky observing site. Access to H2O is only available to club members.
            You will also need a key to the site and the combination to a lock.
            Patron membership does not include this fee; so be sure to order it too.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="H2OKey">H2O Key Deposit (optional):&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" id="H2OKey" value="20" onchange="updateTotal();">
        </span>
        <div class="itemDetails">
            This is a refundable $20 deposit for the key that you will need to be able to access the club's
            <a href="h2o.php">H2O</a>
            dark sky observing site. You must be a TVS member before you can get a key. You will also need to
            have heard an orientation lecture, and you will have to sign a key agreement
            form. You do not need to pay for this every year.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="donation">Donation (optional):&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="donation" value="0" size="5" style="width:4em;" onchange="updateTotal();">
        </span>
        <div class="itemDetails">
            Donations are always welcome. We will use donations to support club activities and help maintain
            our club's
            <a href="h2o.php">H2O</a>
            dark sky observing site.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="other">Other:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="other" value="0" size="5" style="width:4em;" onchange="updateTotal();">
            &nbsp;&nbsp;&nbsp;<label for="explanation">Explanation:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="explanation" value="" style="width:400px;" title="Explain this item">
        </span>
        <div class="itemDetails">
            Use this to pay for other things that aren't covered above. For example you can pay here for
            RASC handbooks and calendars, TVS logo wine glasses, etc. Be sure to fill in the
            explanation so we know what this payment is for.
        </div>
    </td></tr>

    <tr><td style="background-color:white;"><br></td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="total">Total:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="total" readonly value="0" size="5" style="width:4em;">
        </span>
        <div style="float:right;">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif"
                   alt="Pay with PayPal" onclick="callPayPal();">
        </div>
    </td></tr>
</table>
<br>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
