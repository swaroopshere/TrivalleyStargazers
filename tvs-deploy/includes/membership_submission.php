<?php
function membershipPaymentUrl(string $host): string {
    $origin = strtolower(explode(':', $host)[0]) === 'beta.trivalleystargazers.org'
        ? 'https://beta.trivalleystargazers.org' : 'https://www.trivalleystargazers.org';
    return $origin . '/pay.php?applicant=true';
}

/** Retry-safe membership processing; $send is injectable for mail-free tests. */
function processMembershipApplication(array $post, callable $send, string $directory, ?int $now = null): array {
    $now = $now ?? time();
    $failure = function (int $status, string $message): array {
        return ['status' => $status, 'received' => false, 'message' => $message];
    };
    $received = ['status' => 303, 'received' => true, 'message' => 'Your membership application has been received. Please do not submit it again.'];
    $data = [];
    foreach (['name', 'address', 'city', 'state', 'zip', 'phone', 'email', 'comments', 'blank', 'preset', 'new'] as $field) {
        if (isset($post[$field]) && !is_string($post[$field])) {
            return $failure(400, 'Invalid form data. No application was sent. Please return to the membership form.');
        }
        $data[$field] = trim($post[$field] ?? '');
    }
    if ($data['blank'] !== '' || $data['preset'] !== 'Preset' || !in_array($data['new'], ['Yes', 'No'], true)) {
        return $failure(400, 'Please enable JavaScript and submit from the membership form. No application was sent.');
    }
    if ($data['name'] === '') {
        return $failure(400, 'Please enter your name. No application was sent.');
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return $failure(400, 'Please correct your email address. No application was sent.');
    }
    $identity = '';
    foreach (['name', 'address', 'city', 'state', 'zip', 'phone', 'email', 'comments', 'new'] as $field) {
        $value = $field === 'email' ? strtolower($data[$field]) : $data[$field];
        $identity .= strlen($value) . ':' . $value;
    }
    if ((!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory))
            || is_link($directory)
            || (PHP_OS_FAMILY !== 'Windows' && (fileperms($directory) & 0077))) {
        error_log('TVS beta membership: state unavailable');
        return $failure(503, 'The application service is temporarily unavailable. No application was sent. Please try again later or contact the secretary.');
    }
    $path = $directory . '/' . hash('sha256', $identity) . '.state';
    $state = !is_link($path) ? @fopen($path, 'c+') : false;
    if (!$state) {
        return $failure(503, 'The application service is temporarily unavailable. No application was sent. Please contact the secretary.');
    }
    @chmod($path, 0600);
    if (!flock($state, LOCK_EX | LOCK_NB)) {
        fclose($state);
        return $failure(409, 'An identical application is already being processed. Please wait for the original submission; do not submit it again.');
    }
    try {
        $record = stream_get_contents($state);
        if (preg_match('/^(\d+) (sent|pending)\n/', $record, $match) && $now - (int)$match[1] < 3600) {
            return $match[2] === 'sent' ? $received : $failure(503,
                'An identical submission was started, but receipt could not be confirmed. Please contact the secretary before submitting again.');
        }
        $save = function (string $status) use ($state, $now): void {
            $record = "$now $status\n";
            if (!rewind($state) || !ftruncate($state, 0) || fwrite($state, $record) !== strlen($record) || !fflush($state)) {
                throw new RuntimeException('State write failed');
            }
        };
        // Persist pending before the mail call: a killed/timed-out request must
        // not cause the next browser retry to send another application.
        $save('pending');
        $type = $data['new'] === 'Yes' ? 'New member' : 'Returning member';
        $body = "TVS Membership Application\nStatus: $type\n\n";
        foreach (['name', 'address', 'city', 'state', 'zip', 'phone', 'email', 'comments'] as $field) {
            $body .= ucfirst($field) . ': ' . $data[$field] . "\n";
        }
        $headers = "From: webmaster@trivalleystargazers.org\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        if ($data['email'] !== '') $headers .= 'Reply-To: ' . $data['email'] . "\r\n";
        if (!$send('membership@trivalleystargazers.org, treasurer@trivalleystargazers.org', 'TVS membership application', $body, $headers)) {
            throw new RuntimeException('Mail handoff failed');
        }
        $save('sent');
        error_log('TVS beta membership: mail queued');
        return $received;
    } catch (Throwable $error) {
        error_log('TVS beta membership: receipt not confirmed');
        return $failure(503, 'We could not confirm receipt. Please contact the secretary before submitting again; your application may already have been received.');
    } finally {
        flock($state, LOCK_UN);
        fclose($state);
    }
}
