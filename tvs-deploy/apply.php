<?php
/** Beta membership endpoint. See PR #31 for the production behavior ported here. */
require_once __DIR__ . '/includes/membership_submission.php';

header('Cache-Control: no-store');
header('Content-Type: text/html; charset=UTF-8');
$host = $_SERVER['HTTP_HOST'] ?? '';
$paymentUrl = membershipPaymentUrl($host);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    $result = ['status' => 405, 'message' => 'Please use the membership form. No application was sent.'];
} elseif ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
    $result = ['status' => 413, 'message' => 'Please shorten your comments. No application was sent.'];
} else {
    // Share production's existing Perl deduplication records during cutover.
    $directory = strpos($paymentUrl, 'https://beta.') === 0
        ? sys_get_temp_dir() . '/tvs-beta-membership-' . hash('sha256', __DIR__)
        : sys_get_temp_dir() . '/tvs-membership-' . getmyuid();
    $result = processMembershipApplication($_POST, 'mail', $directory);
}
http_response_code($result['status']);
if ($result['status'] === 303) {
    header('Location: ' . $paymentUrl, true, 303);
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>TVS membership application</title></head>
<body>
<h1><?= $result['status'] === 303 ? 'Application received' : 'Please check your application' ?></h1>
<p><?= htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') ?></p>
<?php if ($result['status'] === 303): ?>
<p><a href="<?= htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8') ?>">Continue to payment</a></p>
<?php else: ?>
<p><a href="membership.php">Return to the membership form</a></p>
<p><a href="mailto:secretary@trivalleystargazers.org">Contact the TVS secretary</a></p>
<?php endif; ?>
</body></html>
