<?php
/**
 * TVS Website Footer Template
 *
 * Modern responsive footer with multi-column layout
 */
?>
</div><!-- end of content-wrapper -->
</main><!-- end of main-content -->

<!-- Site Footer -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="container">
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-section">
                    <h4>About TVS</h4>
                    <p>
                        The Tri-Valley Stargazers is an amateur astronomy club based in Livermore, California.
                        We welcome stargazers of all experience levels to join us for monthly meetings,
                        star parties, and community events.
                    </p>
                    <p>
                        <a href="about.php">Learn more about us &rarr;</a>
                    </p>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="membership.php">Join TVS</a></li>
                        <li><a href="parties.php">Star Parties</a></li>
                        <li><a href="newsletter.php">Newsletter</a></li>
                        <li><a href="h2o.php">H2O Site</a></li>
                        <li><a href="contacts.php">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="observing.php">Observing Programs</a></li>
                        <li><a href="loanerscope.php">Loaner Scopes</a></li>
                        <li><a href="books.php">Recommended Books</a></li>
                        <li><a href="links.php">Astronomy Links</a></li>
                        <li><a href="allEvents.php">All Events</a></li>
                        <li><a href="https://groups.io/g/trivalleystargazers/calendar" target="_blank">Calendar</a></li>
                    </ul>
                </div>

                <!-- Contact & Sky Chart -->
                <div class="footer-section">
                    <h4>Connect</h4>
                    <div class="footer-contact">
                        <p>
                            <?= contactLink('secretary', 'trivalleystargazers.org', 'Email Us') ?>
                        </p>
                        <p>
                            P.O. Box 2476<br>
                            Livermore, CA 94551
                        </p>
                        <p>
                            <a href="https://groups.io/g/trivalleystargazers" target="_blank">Groups.io Community</a>
                        </p>
                    </div>

                    <!-- Sky Chart Widget -->
                    <div class="footer-skychart">
                        <a href="https://cleardarksky.com/c/HiddenOBCAkey.html" target="_blank" title="Clear Sky Chart for H2O">
                            <img src="https://cleardarksky.com/c/HiddenOBCAcs0.gif?1" alt="Clear Sky Chart for Hidden Hill Observatory">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container">
            <div class="footer-bottom-content">
                <p class="footer-copyright">
                    &copy; <?= date('Y') ?> Tri-Valley Stargazers. All rights reserved.
                    <a href="privacy.pdf" style="color: rgba(255,255,255,0.6); margin-left: 1rem;">Privacy Policy</a>
                </p>
                <p class="footer-update">
                    Last updated: <?= e($lastUpdateDate ?? date('F j, Y')) ?>
                </p>
            </div>
        </div>
    </div>
</footer>

</div><!-- end of page-wrapper -->

</body>
</html>
