<?php
/**
 * TVS Observing Programs Page
 *
 * Information about club observing programs and awards
 * Converted from observing.shtml
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Observing Programs - Tri-Valley Stargazers';
$pageId = 'm_activities';

include __DIR__ . '/includes/templates/header.php';
?>

<h1 class="title" style="width: 500px;">Observing Programs</h1>

<h2 class="subtitle">Overview</h2>
<p>
    We offer several observing programs to TVS club members. These programs are designed to help you
    improve your observing skills and familiarity with the best deep-sky objects in the sky during
    each season. If you complete a program, it should make you more comfortable in participating in
    the club's outreach events and prepare you for participating in the Astronomical League's more
    extensive
    <a href="https://www.astroleague.org/observing.html" title="Learn about Astroleague programs" target="_blank">observing programs</a>.
</p>
<p>
    Our programs are designed to be completed by an individual in a single season. Some programs may
    require a 4-inch or greater telescope. Certificates will be awarded to members who complete a
    program.
</p>
<br>

<h2 class="subtitle">Available programs</h2>
<p>
    <a href="observing_summer.php" title="learn about this program">Summer program</a><br>
    <a href="observing_autumn.php" title="learn about this program">Autumn program</a><br>
    <a href="observing_winter.php" title="learn about this program">Winter program</a><br>
    <a href="observing_spring.php" title="learn about this program">Spring program</a><br>
</p>
<br>

<h2 class="subtitle">Observing Techniques</h2>
<p>
    Many of our programs require that you record information about how good the seeing and sky transparency were. There
    are many ways to do this, but you can read
    <a href="observing_techniques.php" title="techniques">here</a>
    for simple techniques that are suitable for our programs.
</p>
<p>
    Also do not overlook the resources on the Internet for learning about astronomy, telescopes and how to expand your
    skills. Observing is a particular skill required for astronomy and must be developed, this includes the ability to
    see features that may be fleeting when you do direct observation. We recommend that you use Internet to research each
    object in a program before doing your own observations. Online images are spectacular in comparison to what you'll
    see in a smaller aperture however you may be able to see many of the same features - outer stars may be resolved
    when looking at globular clusters, the colors of Albireo will be more apparent after you've prepared yourself, the
    dimensions of a lunar crater can be appreciated after you've researched it online. Prepare yourself for the
    differences of what you see in a Hubble image and what you observe yourself and understand why. Look for the small
    features and you'll learn to see the features acquired from large instruments. Get inspired!
</p>
<br>

<h2 class="subtitle">Logging</h2>
<p>
    To complete our programs, you will need to submit a log of what you saw and the conditions for your observations.
    We provide logs in Excel format and PDF format as one way for you to do this, and you can read
    <a href="observing_logging.php" title="logging">here</a>
    for ideas on what information to include.
</p>
<br>

<h2 class="subtitle">Awards</h2>
<p>
    We have created a number of observing programs for exploring the night skies. These activities are
    selected for seasonal observations as well as introductory activities
    paralleling the Astronomical League and other astronomy club programs. When
    you complete one of the programs you may submit your observations to the TVS
    <script type="text/javascript">
        contact("awards", "trivalleystargazers.org", "Astronomical Observations Program");
    </script>
    for their review and award recognition.
</p>
<p>
    We recognize those who have completed these programs in the
    following Honor Role and their certificate of completion number:
</p>

<h3 class="subtitle">Summer</h3>
<table class="awards" style="width: 100%;">
    <thead>
        <tr>
            <th>Cert #</th>
            <th>Recipient</th>
            <th>Date Awarded</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>#1</td><td>Jenny Siders</td><td>July 19, 2020</td></tr>
        <tr><td>#2</td><td>Ron Kane</td><td>July 19, 2020</td></tr>
        <tr><td>#3</td><td>Roland Albers</td><td>July 22, 2020</td></tr>
        <tr><td>#4</td><td>Ross Gaunt</td><td>July 27, 2020</td></tr>
        <tr><td>#5</td><td>Ken Sperber</td><td>August 11, 2020</td></tr>
        <tr><td>#6</td><td>Dennis Beckley</td><td>August 27, 2020</td></tr>
        <tr><td>#7</td><td>John Barclay</td><td>July 15, 2022</td></tr>
        <tr><td>#8 Imaging and Sketching</td><td>Maria Razbash and Vladimir Afanasiev</td><td>June 8, 2024</td></tr>
        <tr><td>#9</td><td>Johnathan Bailey</td><td>July 18, 2024</td></tr>
    </tbody>
</table>

<h3 class="subtitle">Autumn</h3>
<table class="awards" style="width: 100%;">
    <thead>
        <tr>
            <th>Cert #</th>
            <th>Recipient</th>
            <th>Date Awarded</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>#1</td><td>Ross Gaunt</td><td>October 16, 2020</td></tr>
        <tr><td>#2</td><td>Roland Albers</td><td>October 23, 2020</td></tr>
        <tr><td>#3</td><td>Ron Kane</td><td>November 17, 2020</td></tr>
        <tr><td>#4</td><td>Dennis Beckley</td><td>November 22, 2020</td></tr>
        <tr><td>#5</td><td>Ken Sperber</td><td>November 30, 2020</td></tr>
        <tr><td>#6</td><td>Maria Razbash and Vladimir Afanasiev</td><td>July 9, 2024</td></tr>
        <tr><td>#7</td><td>Johnathan Bailey</td><td>October 4, 2024</td></tr>
    </tbody>
</table>

<h3 class="subtitle">Winter</h3>
<table class="awards" style="width: 100%;">
    <thead>
        <tr>
            <th>Cert #</th>
            <th>Recipient</th>
            <th>Date Awarded</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>#1 with 4 Bonus Stars</td><td>Ross Gaunt</td><td>January 18, 2021</td></tr>
        <tr><td>#2 with 1 Bonus Star</td><td>Ron Kane</td><td>February 6, 2021</td></tr>
        <tr><td>#3 with 4 Bonus Stars</td><td>Dennis Beckley</td><td>February 28, 2021</td></tr>
        <tr><td>#4 with 4 Bonus Stars</td><td>Roland Albers</td><td>January 29, 2022</td></tr>
        <tr><td>#5 with 2 Bonus Stars - 2nd Award</td><td>Ron Kane</td><td>March 2, 2022</td></tr>
        <tr><td>#6 with 4 Bonus Stars +1 for Imaging</td><td>Maria Razbash and Vladimir Afanasiev</td><td>March 26, 2024</td></tr>
        <tr><td>#7 With 4 Bonus Stars</td><td>Johnathan Bailey</td><td>February 1, 2025</td></tr>
    </tbody>
</table>

<h3 class="subtitle">Spring</h3>
<table class="awards" style="width: 100%;">
    <thead>
        <tr>
            <th>Cert #</th>
            <th>Recipient</th>
            <th>Date Awarded</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>#1 with 3 of 3 Bonus Stars</td><td>Dennis Beckley</td><td>April 5, 2021</td></tr>
        <tr><td>#2 with 3 of 3 Bonus Stars</td><td>Ross Gaunt</td><td>April 12, 2021</td></tr>
        <tr><td>#3 with 3 of 3 Bonus Stars</td><td>Ron Kane</td><td>May 3, 2021</td></tr>
        <tr><td>#4 with 3 of 3 Bonus Stars</td><td>Roland Albers</td><td>May 8, 2021</td></tr>
        <tr><td>#5 with 3 of 3 Bonus Stars</td><td>John Barclay</td><td>May 9, 2022</td></tr>
        <tr><td>#6 Second Award</td><td>Ron Kane</td><td>May 12, 2022</td></tr>
        <tr><td>#7 Third Award / 3 Bonus All Imaging</td><td>Ron Kane</td><td>May 10, 2023</td></tr>
        <tr><td>#8 with 3 Bonus Stars All Imaging</td><td>Maria Razbash and Vladimir Afanasiev</td><td>April 28, 2024</td></tr>
        <tr><td>#9 With 3 Bonus Stars</td><td>Johnathan Bailey</td><td>April 2, 2025</td></tr>
        <tr><td>#10 With 0 Bonus Stars - Visual</td><td>Ron Kane</td><td>May 21, 2025</td></tr>
    </tbody>
</table>

<?php include __DIR__ . '/includes/templates/footer.php'; ?>
