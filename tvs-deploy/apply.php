<?php
/**
 * TVS Membership Application Handler
 *
 * Processes POST from membership.php. The form action is set to this file
 * by setupForm() in tvs.js at page load time (anti-spam: bots that skip JS
 * see action="" and never find this endpoint).
 *
 * On success: emails application to secretary, redirects to pay.php?applicant=1
 * On bot/invalid: silently redirects without sending email
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Only process POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: membership.php');
    exit;
}

// Anti-spam check 1: preset field must equal "Preset"
// setupForm() sets this via JS — bots that don't run JS will have the empty default
if (($_POST['preset'] ?? '') !== 'Preset') {
    header('Location: pay.php?applicant=1');
    exit;
}

// Anti-spam check 2: blank honeypot field must remain empty
if (!empty($_POST['blank'])) {
    header('Location: pay.php?applicant=1');
    exit;
}

// Collect and sanitize inputs
$isNew     = (($_POST['new'] ?? 'unset') === 'Yes');
$name      = trim(strip_tags($_POST['name']     ?? ''));
$address   = trim(strip_tags($_POST['address']  ?? ''));
$city      = trim(strip_tags($_POST['city']     ?? ''));
$state     = trim(strip_tags($_POST['state']    ?? ''));
$zip       = trim(strip_tags($_POST['zip']      ?? ''));
$phone     = trim(strip_tags($_POST['phone']    ?? ''));
$email     = trim(strip_tags($_POST['email']    ?? ''));
$comments  = trim(strip_tags($_POST['comments'] ?? ''));

// Require at minimum a name
if (empty($name)) {
    header('Location: membership.php');
    exit;
}

// Sanitize name for use in email Subject header (prevent header injection)
$safeName = str_replace(["\r", "\n", "\t"], ' ', $name);

$memberType = $isNew ? 'New Member' : 'Returning Member';
$subject = 'TVS Membership Application: ' . $memberType . ' – ' . $safeName;

$body  = "TVS Membership Application\n";
$body .= "==========================\n\n";
$body .= "Type:    " . $memberType . "\n\n";
$body .= "Name:    " . $name . "\n";
$body .= "Address: " . $address . "\n";
$body .= "City:    " . $city . "\n";
$body .= "State:   " . $state . "\n";
$body .= "Zip:     " . $zip . "\n";
$body .= "Phone:   " . $phone . "\n";
$body .= "Email:   " . $email . "\n";

if (!empty($comments)) {
    $body .= "\nComments:\n" . $comments . "\n";
}

$body .= "\n---\n";
$body .= "Submitted: " . date('F j, Y \a\t g:i A T') . "\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$to = 'secretary@trivalleystargazers.org';

$headers  = "From: " . SITE_EMAIL . "\r\n";
// Reply-To the applicant's email if it looks valid, otherwise fall back to site email
if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $headers .= "Reply-To: " . $email . "\r\n";
} else {
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
}
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($to, $subject, $body, $headers);

// Redirect to payment page; ?applicant=1 triggers the
// "complete your application by paying dues" mode in pay.php
header('Location: pay.php?applicant=1');
exit;
