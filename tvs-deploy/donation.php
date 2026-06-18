<?php
/**
 * TVS Donation Page
 * Converted from donation.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Donate to Tri-Valley Stargazers';
$pageId = 'm_home';

include __DIR__ . '/includes/templates/header.php';
?>

<div id="regular">
    <h1 class="title">Donate to the Tri-Valley Stargazers</h1>

    <p>
        The Tri-Valley Stargazers is a 501(c)(3) nonprofit organization, so all donations
        are tax-deductible. You can make a donation by filling out the following form. Funds
        received this way will be used to help the club with its functions. Currently one of
        our most important projects is rebuilding our observatory and replacing equipment
        damaged in a recent wild fire. These photos by Jannette Bennett show some of the damage:
    </p>

    <div style="display:flex; gap:2rem; justify-content:center; align-items:flex-start; flex-wrap:wrap;">
        <img src="images/fire/img_1547.jpg" alt="Fire damage" title="Fire damage" style="width:25%;">
        <img src="images/fire/img_1506.jpg" alt="Fire damage" title="Fire damage" style="width:25%;">
    </div>
</div>

<table class="items">

    <tr><td class="items">
        <span class="itemTitle">
            <label for="donation">Donation:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="donation" value="$0" size="5" style="width:4em;" required>
        </span>
        <div class="itemDetails">
            The Tri-Valley Stargazers is a 501(c)(3) nonprofit organization, so all
            donations are tax deductible. Donations are used to support various
            club functions, and in particular, restoring the Hidden Hill
            Observatory (H2O) and equipment after recent wildfires in the area.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="name">Your name:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="name" size="5" style="width:40em;" required>
        </span>
        <div class="itemDetails">
            Enter your name so we can know who you are.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="email">Your email:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="email" size="5" style="width:40em;" required>
        </span>
        <div class="itemDetails">
            Enter your email address so we can send you a followup email acknowledging
            your tax-deductible donation.
        </div>
    </td></tr>

    <tr><td class="items">
        <span class="itemTitle">
            <label for="comment">Comment:&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="comment" size="5" style="width:40em;">
        </span>
        <div class="itemDetails">
            If you want to send us more information about you and your contribution,
            enter it here.
        </div>
    </td></tr>


    <tr><td style="background-color:white;"><br></td></tr>

    <tr><td class="items">
        <div style="float:right;">
            <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_paynowCC_LG.gif" onclick="doPayPalDonation();">
        </div>
    </td></tr>

</table>
<br>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
