<?php
/**
 * TVS Website Header Template
 *
 * Modern responsive header with sticky navigation and hero banner
 *
 * Variables that can be set before including:
 * - $pageTitle: Page title (appears in <title>)
 * - $pageId: ID for nav highlighting (e.g., 'm_home', 'm_newsletter')
 * - $lastUpdateDate: Date of last update (defaults to today)
 * - $showHero: Whether to show the hero banner section (defaults to true for index)
 */

if (!defined('ROOT_PATH')) {
    require_once dirname(__DIR__) . '/config.php';
}

// Default values
$pageTitle = $pageTitle ?? 'Tri-Valley Stargazers';
$pageId = $pageId ?? '';
$lastUpdateDate = $lastUpdateDate ?? date('F j, Y');
$showHero = $showHero ?? ($pageId === 'm_home');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Ron Kane">
    <meta name="copyright" content="Tri-Valley Stargazers, <?= date('Y') ?>">
    <meta name="description" content="Tri-Valley Stargazers - Amateur astronomy club in Livermore, California. Join us for star parties, monthly meetings, and community events.">
    <meta name="keywords" content="Tri-Valley, Stargazers, astronomy, Livermore, California, star party, telescope, H2O, Hidden Hill Observatory">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a1628">
    <title><?= e($pageTitle) ?></title>

    <!-- Preconnect to font services -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link href="tvs.css" rel="stylesheet" type="text/css">

    <!-- JavaScript -->
    <script src="tvs.js" type="text/javascript" defer></script>

    <script type="text/javascript">
        var lastUpdateDate = "<?= e($lastUpdateDate) ?>";
    </script>

    <?php if ($pageId): ?>
    <style type='text/css'>
        #appleNav li a#<?= e($pageId) ?> {
            color: var(--color-accent, #d4a84b);
            position: relative;
        }
        #appleNav li a#<?= e($pageId) ?>::after {
            content: '';
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 2px;
            background: var(--color-accent, #d4a84b);
            border-radius: 1px;
        }
        .mobile-nav-link.active {
            color: var(--color-accent, #d4a84b) !important;
        }
    </style>
    <?php endif; ?>
</head>

<body<?php if (isset($onload)): ?> onload="<?= e($onload) ?>"<?php endif; ?>>

<div class="page-wrapper">

<!-- Site Header -->
<header class="site-header">
    <div class="header-container">
        <!-- Logo -->
        <a href="index.php" class="site-logo" title="TVS Home page">
            <img id="logo" src="images/logo1.png" alt="TVS Logo"
                 onmouseover="this.src='images/logo2.png'"
                 onmouseout="this.src='images/logo1.png'">
            <span class="site-logo-text">Tri-Valley Stargazers</span>
        </a>

        <!-- Desktop Navigation -->
        <nav class="main-nav" aria-label="Main navigation">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="index.php" class="nav-link<?= $pageId === 'm_home' ? ' active' : '' ?>" id="m_home">Home</a>
                </li>
                <li class="nav-item">
                    <a href="about.php" class="nav-link<?= $pageId === 'm_about' ? ' active' : '' ?>" id="m_about">
                        About
                        <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="about.php">General Info</a>
                        <a href="pdfs/TVSbrochure.pdf" target="_blank">TVS Brochure</a>
                        <a href="official_documents.php">Official Documents</a>
                        <a href="privacy.pdf">Privacy Policy</a>
                        <a href="history.php">History</a>
                        <a href="speakers.php">Past Speakers</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="contacts.php" class="nav-link<?= $pageId === 'm_contacts' ? ' active' : '' ?>" id="m_contacts">Contacts</a>
                </li>
                <li class="nav-item">
                    <a href="newsletter.php" class="nav-link<?= $pageId === 'm_newsletter' ? ' active' : '' ?>" id="m_newsletter">Newsletter</a>
                </li>
                <li class="nav-item">
                    <a href="parties.php" class="nav-link<?= $pageId === 'm_activities' ? ' active' : '' ?>" id="m_activities">
                        Activities
                        <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="parties.php">Star Parties</a>
                        <a href="observing.php">Observing Programs</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="membership.php" class="nav-link<?= $pageId === 'm_membership' ? ' active' : '' ?>" id="m_membership">
                        Membership
                        <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="benefits.php">Membership Benefits</a>
                        <a href="membership.php">Join</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="resources.php" class="nav-link<?= $pageId === 'm_resources' ? ' active' : '' ?>" id="m_resources">
                        Resources
                        <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="loanerscope.php">Loaner Scopes</a>
                        <a href="contributions.php">Member Contributions</a>
                        <a href="books.php">Books</a>
                        <a href="links.php">Links</a>
                        <a href="allEvents.php">All Events</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="h2o.php" class="nav-link<?= $pageId === 'm_sites' ? ' active' : '' ?>" id="m_sites">
                        Viewing Sites
                        <svg class="nav-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </a>
                    <div class="nav-dropdown">
                        <a href="h2o.php">H2O Site</a>
                        <a href="h2oagreement.php">H2O Agreement</a>
                        <a href="delvalle.php">Del Valle Site</a>
                    </div>
                </li>
            </ul>

            <!-- Search -->
            <div class="nav-search">
                <form action="https://search.freefind.com/find.html" method="get" accept-charset="utf-8">
                    <input type="hidden" name="si" value="9600280">
                    <input type="hidden" name="pid" value="r">
                    <input type="hidden" name="n" value="0">
                    <input type="hidden" name="_charset_" value="">
                    <input type="hidden" name="bcd" value="&#247;">
                    <input type="text" name="query" placeholder="Search..." aria-label="Search site">
                </form>
            </div>
        </nav>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>

<!-- Mobile Navigation -->
<nav class="mobile-nav" id="mobileNav" aria-label="Mobile navigation">
    <ul class="mobile-nav-list">
        <li class="mobile-nav-item">
            <a href="index.php" class="mobile-nav-link<?= $pageId === 'm_home' ? ' active' : '' ?>">Home</a>
        </li>
        <li class="mobile-nav-item">
            <a href="#" class="mobile-nav-link" onclick="toggleMobileDropdown(event, 'aboutDropdown')">
                About
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="mobile-dropdown" id="aboutDropdown">
                <a href="about.php">General Info</a>
                <a href="pdfs/TVSbrochure.pdf" target="_blank">TVS Brochure</a>
                <a href="official_documents.php">Official Documents</a>
                <a href="privacy.pdf">Privacy Policy</a>
                <a href="history.php">History</a>
                <a href="speakers.php">Past Speakers</a>
            </div>
        </li>
        <li class="mobile-nav-item">
            <a href="contacts.php" class="mobile-nav-link<?= $pageId === 'm_contacts' ? ' active' : '' ?>">Contacts</a>
        </li>
        <li class="mobile-nav-item">
            <a href="newsletter.php" class="mobile-nav-link<?= $pageId === 'm_newsletter' ? ' active' : '' ?>">Newsletter</a>
        </li>
        <li class="mobile-nav-item">
            <a href="#" class="mobile-nav-link" onclick="toggleMobileDropdown(event, 'activitiesDropdown')">
                Activities
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="mobile-dropdown" id="activitiesDropdown">
                <a href="parties.php">Star Parties</a>
                <a href="observing.php">Observing Programs</a>
            </div>
        </li>
        <li class="mobile-nav-item">
            <a href="#" class="mobile-nav-link" onclick="toggleMobileDropdown(event, 'membershipDropdown')">
                Membership
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="mobile-dropdown" id="membershipDropdown">
                <a href="benefits.php">Membership Benefits</a>
                <a href="membership.php">Join</a>
            </div>
        </li>
        <li class="mobile-nav-item">
            <a href="#" class="mobile-nav-link" onclick="toggleMobileDropdown(event, 'resourcesDropdown')">
                Resources
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="mobile-dropdown" id="resourcesDropdown">
                <a href="loanerscope.php">Loaner Scopes</a>
                <a href="contributions.php">Member Contributions</a>
                <a href="books.php">Books</a>
                <a href="links.php">Links</a>
                <a href="allEvents.php">All Events</a>
            </div>
        </li>
        <li class="mobile-nav-item">
            <a href="#" class="mobile-nav-link" onclick="toggleMobileDropdown(event, 'sitesDropdown')">
                Viewing Sites
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </a>
            <div class="mobile-dropdown" id="sitesDropdown">
                <a href="h2o.php">H2O Site</a>
                <a href="h2oagreement.php">H2O Agreement</a>
                <a href="delvalle.php">Del Valle Site</a>
            </div>
        </li>
        <li class="mobile-nav-item" style="padding: 1rem;">
            <form action="https://search.freefind.com/find.html" method="get" accept-charset="utf-8" style="width: 100%;">
                <input type="hidden" name="si" value="9600280">
                <input type="hidden" name="pid" value="r">
                <input type="hidden" name="n" value="0">
                <input type="text" name="query" placeholder="Search..."
                       style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); color: white; font-size: 1rem;"
                       aria-label="Search site">
            </form>
        </li>
    </ul>
</nav>

<?php if ($showHero): ?>
<!-- Hero Banner Section -->
<section class="hero-section">
    <div class="hero-banner">
        <!-- Banner images with crossfade -->
        <div class="banner-container">
            <div id="bannerBottom"></div>
            <div id="bannerTop"></div>
        </div>

        <!-- Gradient overlay -->
        <div class="banner-overlay"></div>

        <!-- Hero content -->
        <div class="hero-content">
            <img src="images/logo1.png" alt="TVS Logo" class="hero-logo"
                 onmouseover="this.src='images/logo2.png'"
                 onmouseout="this.src='images/logo1.png'">
            <h1 class="hero-title">Tri-Valley Stargazers</h1>
            <p class="hero-subtitle">Livermore, California</p>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Content Area -->
<main class="main-content">
<div class="content-wrapper">

<script>
// Mobile menu functions
function toggleMobileMenu() {
    const mobileNav = document.getElementById('mobileNav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    const isActive = mobileNav.classList.toggle('active');
    toggle.classList.toggle('active');
    toggle.setAttribute('aria-expanded', isActive);

    // Prevent body scroll when menu is open
    document.body.style.overflow = isActive ? 'hidden' : '';
}

function toggleMobileDropdown(event, dropdownId) {
    event.preventDefault();
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('active');

    // Rotate arrow
    const arrow = event.currentTarget.querySelector('svg');
    if (arrow) {
        arrow.style.transform = dropdown.classList.contains('active') ? 'rotate(180deg)' : '';
    }
}

// Close mobile menu on resize to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        const mobileNav = document.getElementById('mobileNav');
        const toggle = document.querySelector('.mobile-menu-toggle');
        if (mobileNav && mobileNav.classList.contains('active')) {
            mobileNav.classList.remove('active');
            toggle.classList.remove('active');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    }
});
</script>
