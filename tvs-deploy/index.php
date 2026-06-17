<?php
/**
 * TVS Home Page
 *
 * Converted from index.shtml to dynamic PHP
 * Updated January 2026 with modern responsive layout
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Page configuration
$pageTitle = 'Tri-Valley Stargazers, Livermore CA';
$pageId = 'm_home';
$lastUpdateDate = date('F j, Y');
$showHero = true;

// Get dynamic content from database
$publicMeeting = getCurrentPublicMeeting();
$boardMeeting = getCurrentBoardMeeting();
$upcomingEvents = getUpcomingEvents(5);

// Get all upcoming events for sidebar
$allUpcomingEvents = getAllUpcomingEvents(12);

// Get events for display
$h2oEvents = getVisibleEvents('h2o');
$teslaEvents = getVisibleEvents('tesla');
$announcements = getVisibleEvents('announcement');
$bbqEvents = dbQuery("SELECT * FROM events WHERE event_type = 'bbq' AND is_visible = 1 ORDER BY event_date ASC LIMIT 1");
$potluckEvents = dbQuery("SELECT * FROM events WHERE event_type = 'potluck' AND is_visible = 1 ORDER BY event_date ASC LIMIT 1");

// Determine which optional sections to show
$showTalk = !empty($publicMeeting);
$showTalkDetails = !empty($publicMeeting['presentation_topic']);
$showH2o = !empty($h2oEvents);
$showTesla = !empty($teslaEvents);
$showAnnouncement = !empty($announcements);
$showBbq = !empty($bbqEvents);
$showPotluck = !empty($potluckEvents);

include __DIR__ . '/includes/templates/header.php';
?>

<!-- Welcome Section -->
<div class="intro">
    <p>
        The Tri-Valley Stargazers Astronomy Club, Livermore CA, welcomes you. There is a lot
        of information here on the many activities of our club.
        Learn why you should <a href="membership.php">join the club</a> to get the most out of your
        amateur astronomy hobby in the east San Francisco Bay Area.
    </p>
</div>

<?php if ($showAnnouncement): ?>
<?php foreach ($announcements as $announcement): ?>
<div class="announcement">
    <h3 class="announcement-title"><?= e($announcement['title']) ?></h3>
    <p><?= nl2br(e($announcement['description'])) ?></p>
</div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Main Content with Sidebar -->
<div class="home-layout">
    <!-- Main Content Area -->
    <div class="home-main">
        <!-- Meeting Cards Grid -->
        <div class="home-grid">
            <!-- Left Column: Public Meeting & Events -->
            <div>
                <!-- Next Public Meeting Card -->
                <div class="meeting-card">
                    <h3 class="meeting-header">Next Public Meeting</h3>
                    <?php if ($publicMeeting): ?>
                    <div class="meeting-details">
                        <p class="meeting-time"><?= formatMeetingDateTime($publicMeeting['meeting_date'], $publicMeeting['meeting_time']) ?></p>
                        <p class="meeting-location">
                            <?= e($publicMeeting['location']) ?>
                            <?php if ($publicMeeting['location_address']): ?>
                                <br><?= e($publicMeeting['location_address']) ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if (!empty($publicMeeting['presentation_topic'])): ?>
                    <div class="meeting-presenter">
                        <strong>Topic:</strong> "<?= e($publicMeeting['presentation_topic']) ?>"<br>
                        <strong>Presenter:</strong> <?= e($publicMeeting['presenter_name']) ?>
                        <?php if ($publicMeeting['presenter_title']): ?>
                            , <?= e($publicMeeting['presenter_title']) ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <div class="meeting-description">
                        <?php if ($publicMeeting['meeting_format'] === 'hybrid'): ?>
                            This will be a hybrid meeting. The meeting will be held in person at the <?= e($publicMeeting['location']) ?> and will also be available via Zoom.
                        <?php elseif ($publicMeeting['meeting_format'] === 'zoom'): ?>
                            This meeting will be held via Zoom only.
                        <?php else: ?>
                            This meeting will be held in person only.
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <p class="text-muted">No meeting currently scheduled.</p>
                    <?php endif; ?>
                </div>

                <?php if ($showBbq && $bbqEvents): ?>
                <!-- Summer BBQ Card -->
                <div class="meeting-card">
                    <h3 class="meeting-header">Summer Barbecue</h3>
                    <div class="meeting-details">
                        <p class="meeting-time"><?= formatDate($bbqEvents[0]['event_date'], 'l, F j, Y') ?></p>
                        <?php if (!empty($bbqEvents[0]['description'])): ?>
                        <?= nl2br(e($bbqEvents[0]['description'])) ?>
                        <?php else: ?>
                        <p>Set up at 6:00 p.m.<br>Dinner starts at 7:30 p.m.</p>
                        <p class="meeting-location">Unitarian Universalist Church<br>1893 N. Vasco Rd., Livermore</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($showPotluck && $potluckEvents): ?>
                <!-- Winter Potluck Card -->
                <div class="meeting-card">
                    <h3 class="meeting-header">Winter Solstice Potluck</h3>
                    <div class="meeting-details">
                        <p class="meeting-time"><?= formatDate($potluckEvents[0]['event_date'], 'l, F j, Y') ?></p>
                        <?php if (!empty($potluckEvents[0]['description'])): ?>
                        <?= nl2br(e($potluckEvents[0]['description'])) ?>
                        <?php else: ?>
                        <p>Set up at 6:30 p.m.<br>Dinner starts at 7:00 p.m.</p>
                        <p class="meeting-location">Unitarian Universalist Church<br>1893 N. Vasco Rd., Livermore</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Board Meeting -->
            <div>
                <div class="meeting-card">
                    <h3 class="meeting-header">Next Board Meeting</h3>
                    <?php if ($boardMeeting): ?>
                    <div class="meeting-details">
                        <p class="meeting-time"><?= formatMeetingDateTime($boardMeeting['meeting_date'], $boardMeeting['meeting_time']) ?></p>
                        <p class="meeting-description"><?= e($boardMeeting['description']) ?></p>
                    </div>
                    <?php else: ?>
                        <p class="text-muted">No board meeting currently scheduled.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- News & Events Section -->
        <section class="mt-8">
            <h2 class="section-title">The Latest News &amp; Upcoming Events</h2>

            <?php if ($showTalkDetails): ?>
            <!-- Monthly Presentation Details -->
            <div class="mb-6">
                <?php
                $presentationMonth = (int)date('n', strtotime($publicMeeting['meeting_date']));
                $presentationYear = (int)date('Y', strtotime($publicMeeting['meeting_date']));
                ?>
                <h3 class="subtitle"><?= getMonthName($presentationMonth) ?> <?= $presentationYear ?> Member Meeting Presentation</h3>

                <?php if ($publicMeeting['meeting_format'] !== 'in-person'): ?>
                <p>
                    This meeting will be live at the Unitarian church and will also be available using the video conference utility
                    <a href="https://zoom.us/" target="_blank">Zoom</a>.
                    The meeting link will be emailed to members. For non-members,
                    if you would like to join the meeting, please send an email to
                    <span class="contact-link" data-user="president" data-domain="trivalleystargazers.org">the club president</span>
                    asking for the meeting link and telling us a bit about your areas of interest in astronomy.
                </p>
                <?php endif; ?>

                <div class="meeting-card">
                    <h3 class="meeting-header">Speaker Information</h3>
                    <div class="meeting-details">
                        <p><strong>Topic:</strong> <?= e($publicMeeting['presentation_topic']) ?></p>
                        <p><strong>Presenter:</strong> <?= e($publicMeeting['presenter_name']) ?><?php if ($publicMeeting['presenter_title']): ?>, <?= e($publicMeeting['presenter_title']) ?><?php endif; ?></p>
                    </div>
                    <div class="meeting-description">
                        <?php if (!empty($publicMeeting['presentation_abstract'])): ?>
                        <p><strong>Abstract:</strong> <?= e($publicMeeting['presentation_abstract']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($publicMeeting['presenter_bio'])): ?>
                        <p class="mb-0"><strong>Bio:</strong> <?= e($publicMeeting['presenter_bio']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($showH2o && $h2oEvents): ?>
            <!-- H2O Open House -->
            <div class="mb-6">
                <h3 class="subtitle">H2O Open House</h3>
                <p>
                    Our next open house is <strong><?= formatDate($h2oEvents[0]['event_date'], 'F j, Y') ?></strong> for the club's dark sky site,
                    <a href="h2o.php">Hidden Hill Observatory</a> (aka H2O).
                </p>
                <p>
                    Anyone can come to our open houses, not just club members; but you cannot go
                    there without an escort. We will meet at the corner of Mines and Tesla Roads
                    at 5:00 PM, then caravan to the site on a drive that takes about 50 minutes.
                    The admission is $3/car; please bring the exact amount.
                </p>
            </div>
            <?php endif; ?>

            <?php if ($showTesla && $teslaEvents): ?>
            <!-- Tesla Vineyard Star Parties -->
            <div class="mb-6">
                <h3 class="subtitle">Member Star Parties at Tesla Vineyard</h3>
                <p>
                    Tesla Vintner star parties are generally open to club members and their guests. The star parties will run from 8:00 PM
                    until midnight. Tesla Vintners is in Livermore near the intersection of Tesla and Mines.
                </p>
            </div>
            <?php endif; ?>

            <p>
                For a complete and up-to-date calendar of upcoming events, visit our
                <a href="https://groups.io/g/trivalleystargazers/calendar" target="_blank">groups.io calendar</a>.
                Please contact
                <span class="contact-link" data-user="coordinator" data-domain="trivalleystargazers.org">Johnathan Bailey</span>
                for further information.
            </p>
        </section>

        <!-- H2O Rebuild Section -->
        <section class="mt-8">
            <h3 class="subtitle">We Continue to Rebuild the Hidden Hill Observatory</h3>
            <div class="h2o-rebuild-section">
                <p>
                    Brand new updates on our H2O Rebuild project! The new dome has been installed and painted!
                    The mount and the telescope have been installed as well! Volunteers have been tirelessly
                    working to bring the dome to life! Work on the mount is in progress.
                </p>
                <p>Click any image below to view full size.</p>
                <div class="photo-gallery" data-gallery="h2o-rebuild">
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/H2O 2018.jpg"
                         data-title="H2O Observatory Before Fire">
                        <img src="images/fire/album/thumbs/H2O 2018.jpg" alt="H2O Observatory Before Fire">
                        <span class="gallery-caption">Before Fire</span>
                    </div>
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/New OTA built by Rich.jpg"
                         data-title="New Telescope Assembly by Rich">
                        <img src="images/fire/album/thumbs/New OTA built by Rich.jpg" alt="New Telescope Assembly">
                        <span class="gallery-caption">New OTA</span>
                    </div>
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/H2O_OTA_Deploy_20200531_10.jpg"
                         data-title="OTA Deployment">
                        <img src="images/fire/album/thumbs/H2O_OTA_Deploy_20200531_10.jpg" alt="OTA Deployment">
                        <span class="gallery-caption">OTA Deployment</span>
                    </div>
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/D0042_0016 Astrophysics mount and power panel.jpg"
                         data-title="Astrophysics Mount Installation">
                        <img src="images/fire/album/thumbs/D0042_0016 Astrophysics mount and power panel.jpg" alt="Mount Installation">
                        <span class="gallery-caption">Mount Installation</span>
                    </div>
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/IMG_1547_CC_crop.jpg"
                         data-title="Dome Construction">
                        <img src="images/fire/album/thumbs/IMG_1547_CC_crop.jpg" alt="Dome Construction">
                        <span class="gallery-caption">Dome Construction</span>
                    </div>
                    <div class="gallery-item"
                         data-full="images/fire/album/slides/shed IMG_2955_crop_sm.jpg"
                         data-title="Completed Equipment Shed">
                        <img src="images/fire/album/thumbs/shed IMG_2955_crop_sm.jpg" alt="Completed Shed">
                        <span class="gallery-caption">Equipment Shed</span>
                    </div>
                </div>
                <p class="gallery-link">
                    <a href="images/fire/album/index.html" class="btn btn-outline">View All Rebuild Photos</a>
                </p>
            </div>
        </section>

        <!-- Member Astrophotography Gallery -->
        <section class="mt-8">
            <h3 class="subtitle">Member Astrophotography</h3>
            <p>Our members capture stunning images of the cosmos. Click any image to view full size.</p>
            <div class="photo-gallery" data-gallery="member-photos">
                <div class="gallery-item"
                     data-full="images/banners/03-Comet-ISON.jpg"
                     data-title="Comet ISON by Ken Sperber">
                    <img src="images/banners/03-Comet-ISON.jpg" alt="Comet ISON">
                    <span class="gallery-caption">Comet ISON</span>
                </div>
                <div class="gallery-item"
                     data-full="images/banners/04-Milky-Way.jpg"
                     data-title="The Milky Way by Alex Mellinger">
                    <img src="images/banners/04-Milky-Way.jpg" alt="The Milky Way">
                    <span class="gallery-caption">The Milky Way</span>
                </div>
                <div class="gallery-item"
                     data-full="images/banners/05-NGC-4631.jpg"
                     data-title="The Whale Galaxy (NGC 4631) by Hilary Jones">
                    <img src="images/banners/05-NGC-4631.jpg" alt="The Whale Galaxy">
                    <span class="gallery-caption">Whale Galaxy</span>
                </div>
                <div class="gallery-item"
                     data-full="images/banners/06-Horsehead-Nebula.jpg"
                     data-title="Horsehead Nebula by Chuck Vaughn">
                    <img src="images/banners/06-Horsehead-Nebula.jpg" alt="Horsehead Nebula">
                    <span class="gallery-caption">Horsehead Nebula</span>
                </div>
                <div class="gallery-item"
                     data-full="images/banners/07-Solar-Eclipse.jpg"
                     data-title="Solar Eclipse by Gert Gottschalk">
                    <img src="images/banners/07-Solar-Eclipse.jpg" alt="Solar Eclipse">
                    <span class="gallery-caption">Solar Eclipse</span>
                </div>
            </div>
        </section>

        <!-- Club Merchandise Section -->
        <section class="mt-8">
            <div class="grid grid-2">
                <!-- Wine Glasses -->
                <div class="card card-accent">
                    <div class="card-body clearfix">
                        <img src="images/wine_glass.jpg" alt="TVS Wine Glass" width="120" style="float: right; margin: 0 0 1rem 1rem; border-radius: 0.5rem;">
                        <h3 class="subtitle mt-0">TVS Crystal Wine Glasses</h3>
                        <p>
                            TVS is offering elegant crystal wine glasses for sale to club members.
                            You don't have to drink wine to enjoy the beautiful TVS logo-etched stemware.
                        </p>
                        <p>
                            Look for them at club meetings, where they will be sold for <strong>$10 each</strong>.
                            Don't drink alone, buy two! Support TVS.
                        </p>
                    </div>
                </div>

                <!-- Logo Wear -->
                <div class="card card-accent">
                    <div class="card-body">
                        <h3 class="subtitle mt-0">TVS Logo Wear</h3>
                        <p>
                            You may have seen some TVS members wearing shirts, caps and jackets
                            embroidered with the TVS logo. If you are interested in obtaining an
                            embroidered logo item, you can order through
                            <a href="https://business.landsend.com/" target="_blank"><strong>Land's End Corporate Sales</strong></a>.
                        </p>
                        <p class="text-sm text-muted mb-0">
                            Specify TVS logo #0118948 and customer number 3452021.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Sidebar: Upcoming Events -->
    <aside class="home-sidebar">
        <div class="events-sidebar">
            <h3 class="events-sidebar-title">Upcoming Events</h3>

            <?php if (!empty($allUpcomingEvents)): ?>
            <ul class="events-list">
                <?php foreach ($allUpcomingEvents as $event): ?>
                <li class="event-item event-type-<?= e($event['event_type']) ?>">
                    <div class="event-date">
                        <span class="event-month"><?= formatDate($event['event_date'], 'M') ?></span>
                        <span class="event-day"><?= formatDate($event['event_date'], 'j') ?></span>
                    </div>
                    <div class="event-details">
                        <span class="event-badge event-badge-<?= e($event['event_type']) ?>">
                            <?php
                            $badges = [
                                'meeting' => 'Meeting',
                                'board' => 'Board',
                                'h2o' => 'H2O',
                                'tesla' => 'Tesla',
                                'starparty' => 'Star Party'
                            ];
                            echo $badges[$event['event_type']] ?? 'Event';
                            ?>
                        </span>
                        <span class="event-title"><?= e($event['title']) ?></span>
                        <?php if (!empty($event['location'])): ?>
                        <span class="event-location"><?= e($event['location']) ?></span>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-muted text-sm">No upcoming events scheduled.</p>
            <?php endif; ?>

            <div class="events-sidebar-footer">
                <a href="https://groups.io/g/trivalleystargazers/calendar" target="_blank" class="btn btn-primary btn-sm">
                    View Full Calendar
                </a>
            </div>
        </div>
    </aside>
</div>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
