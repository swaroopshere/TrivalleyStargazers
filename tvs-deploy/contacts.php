<?php
/**
 * TVS Contacts Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tri-Valley Stargazers Contacts';
$pageId = 'm_contacts';

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Contacts</h1>

<div class="contacts-page">

    <h2 class="subtitle">Officers <?= date('Y') ?></h2>
    <div class="contact-row">
        <div class="contact-title">President</div>
        <div class="contact-name"><span class="contact-link" data-user="president" data-domain="trivalleystargazers.org">Eric Dueltgen</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Vice President</div>
        <div class="contact-name"><span class="contact-link" data-user="vice_president" data-domain="trivalleystargazers.org">Aris Pope</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Treasurer</div>
        <div class="contact-name"><span class="contact-link" data-user="treasurer" data-domain="trivalleystargazers.org">John Forrest</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Secretary</div>
        <div class="contact-name"><span class="contact-link" data-user="secretary" data-domain="trivalleystargazers.org">Dave Lackey</span></div>
    </div>
    <br><br>

    <h2 class="subtitle">Board Members</h2>
    <div class="contact-row">
        <div class="contact-title">Past President</div>
        <div class="contact-name"><span class="contact-link" data-user="past_president" data-domain="trivalleystargazers.org">Ron Kane</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">At Large</div>
        <div class="contact-name"><span class="contact-link" data-user="astrophotography" data-domain="trivalleystargazers.org">Gert Gottschalk</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">At Large</div>
        <div class="contact-name"><span class="contact-link" data-user="observatory" data-domain="trivalleystargazers.org">Chuck Grant</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">At Large</div>
        <div class="contact-name"><span class="contact-link" data-user="webmaster" data-domain="trivalleystargazers.org">Swaroop Shere</span></div>
    </div>
    <br><br>

    <h2 class="subtitle">Volunteer Positions</h2>
    <div class="contact-row">
        <div class="contact-title">Astronomical League Representative</div>
        <div class="contact-name"><span class="contact-link" data-user="alrep" data-domain="trivalleystargazers.org">Don Dossa</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Del Valle Coordinator</div>
        <div class="contact-name"><span class="contact-link" data-user="delvalle" data-domain="trivalleystargazers.org">Dave Wilzius</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Historian</div>
        <div class="contact-name">Open</div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Librarian</div>
        <div class="contact-name"><span class="contact-link" data-user="librarian" data-domain="trivalleystargazers.org">Ron Kane</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Loaner Scope Manager</div>
        <div class="contact-name"><span class="contact-link" data-user="telescopes" data-domain="trivalleystargazers.org">Ron Kane</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Night Sky Network Representative</div>
        <div class="contact-name"><span class="contact-link" data-user="nnsn" data-domain="trivalleystargazers.org">Ross Gaunt</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Newsletter Editor</div>
        <div class="contact-name"><span class="contact-link" data-user="newsletter" data-domain="trivalleystargazers.org">Scott Schneider</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Observatory Director / Rebuild Chairman</div>
        <div class="contact-name"><span class="contact-link" data-user="observatory" data-domain="trivalleystargazers.org">Chuck Grant</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Observatory Co-Director</div>
        <div class="contact-name"><span class="contact-link" data-user="H2O-Events" data-domain="trivalleystargazers.org">Ross Gaunt</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Potluck Coordinator</div>
        <div class="contact-name"><span class="contact-link" data-user="potluck" data-domain="trivalleystargazers.org">Ron Kane</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Programs</div>
        <div class="contact-name"><span class="contact-link" data-user="programs" data-domain="trivalleystargazers.org">Ron Kane</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Publicity and Fundraising</div>
        <div class="contact-name">Open</div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Refreshments</div>
        <div class="contact-name">Open</div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Star Party Coordinator</div>
        <div class="contact-name"><span class="contact-link" data-user="coordinator" data-domain="trivalleystargazers.org">Johnathan Bailey</span></div>
    </div>
    <div class="contact-row">
        <div class="contact-title">Webmaster</div>
        <div class="contact-name"><span class="contact-link" data-user="webmaster" data-domain="trivalleystargazers.org">Swaroop Shere</span></div>
    </div>
    <br><br>

    <h2 class="subtitle">Member Astrophoto Links</h2>
    Here are links to some of the fine astrophotos taken by TVS members, past and present:<br><br>
    <a href="http://denizdemirci.ca/" target="_blank" title="Deniz's Web Page">Deniz Demirci</a><br>
    <a href="http://www.trivalleystargazers.org/gert/Astro_en.htm" target="_blank" title="Gert's Astrophoto Page">Gert Gottschalk</a><br>
    <a href="http://www.darklights.org/gallery" target="_blank" title="Hilary's Astrophoto Page">Hilary Jones</a><br>
    <a href="http://www.milkywaysky.com/" target="_blank" title="Axel's Milky Way Page">Axel Mellinger</a><br>
    <a href="http://www.trivalleystargazers.org/ken/index.html" target="_blank" title="Ken's Astrophoto Page">Ken Sperber</a><br>
    <a href="http://astrophotography.aa6g.org/" target="_blank" title="Chuck's Astrophoto Page">Chuck Vaughn</a>
</div>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
