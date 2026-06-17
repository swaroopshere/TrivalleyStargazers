<?php
/**
 * TVS Contacts Page
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Tri-Valley Stargazers Contacts';
$pageId = 'm_contacts';

$officers     = getContacts('officer');
$boardMembers = getContacts('board');
$volunteers   = getContacts('volunteer');

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title">Contacts</h1>

<div class="contacts-page">

    <h2 class="subtitle">Officers <?= date('Y') ?></h2>
    <?php foreach ($officers as $c): ?>
    <div class="contact-row">
        <div class="contact-title"><?= e($c['position']) ?></div>
        <div class="contact-name">
            <?= $c['email_user']
                ? contactLink($c['email_user'], $c['email_domain'], $c['name'])
                : e($c['name']) ?>
        </div>
    </div>
    <?php endforeach; ?>
    <br><br>

    <h2 class="subtitle">Board Members</h2>
    <?php foreach ($boardMembers as $c): ?>
    <div class="contact-row">
        <div class="contact-title"><?= e($c['position']) ?></div>
        <div class="contact-name">
            <?= $c['email_user']
                ? contactLink($c['email_user'], $c['email_domain'], $c['name'])
                : e($c['name']) ?>
        </div>
    </div>
    <?php endforeach; ?>
    <br><br>

    <h2 class="subtitle">Volunteer Positions</h2>
    <?php foreach ($volunteers as $c): ?>
    <div class="contact-row">
        <div class="contact-title"><?= e($c['position']) ?></div>
        <div class="contact-name">
            <?= $c['email_user']
                ? contactLink($c['email_user'], $c['email_domain'], $c['name'])
                : e($c['name']) ?>
        </div>
    </div>
    <?php endforeach; ?>
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
