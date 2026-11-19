<?php
require __DIR__ . '/../../includes/membership_submission.php';
$tests = 0;
if (membershipPaymentUrl('beta.trivalleystargazers.org') !== 'https://beta.trivalleystargazers.org/pay.php?applicant=true'
    || membershipPaymentUrl('www.trivalleystargazers.org') !== 'https://www.trivalleystargazers.org/pay.php?applicant=true'
    || membershipPaymentUrl('untrusted.example') !== 'https://www.trivalleystargazers.org/pay.php?applicant=true') {
    throw new RuntimeException('Payment origin regression');
}
echo "PASS beta/production payment origin allowlist\n";
function check($condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
function runCase(string $name, callable $test): void {
    global $tests;
    $dir = sys_get_temp_dir() . '/tvs-beta-test-' . bin2hex(random_bytes(8));
    $post = ['name' => 'Test Member', 'address' => 'Example Street', 'email' => 'test+club@example.technology',
             'new' => 'Yes', 'blank' => '', 'preset' => 'Preset'];
    $calls = [];
    $send = function (...$args) use (&$calls): bool { $calls[] = $args; return true; };
    try {
        $test($post, $send, $dir, $calls);
        echo "PASS $name\n";
        $tests++;
    } finally {
        foreach (glob($dir . '/*.state') ?: [] as $file) unlink($file);
        if (is_dir($dir)) rmdir($dir);
        elseif (is_file($dir)) unlink($dir);
    }
}
runCase('new member and both recipients', function ($post, $send, $dir, &$calls) {
    check(processMembershipApplication($post, $send, $dir)['status'] === 303, 'New member failed');
    check(count($calls) === 1, 'Unexpected mail count');
    check($calls[0][0] === 'membership@trivalleystargazers.org, treasurer@trivalleystargazers.org', 'Wrong recipients');
    check(strpos($calls[0][2], 'New member') !== false, 'Wrong membership type');
    check(strpos($calls[0][3], 'Reply-To: test+club@example.technology') !== false, 'Reply-To lost');
});
runCase('returning member', function ($post, $send, $dir, &$calls) {
    $post['new'] = 'No';
    check(processMembershipApplication($post, $send, $dir)['status'] === 303, 'Renewal failed');
    check(strpos($calls[0][2], 'Returning member') !== false, 'Renewal status lost');
});
runCase('eight retries queue one message', function ($post, $send, $dir, &$calls) {
    for ($i=0; $i<8; $i++) check(processMembershipApplication($post, $send, $dir)['status'] === 303, 'Retry did not confirm');
    check(count($calls) === 1, 'Duplicate mail sent');
});
runCase('changed details can be sent', function ($post, $send, $dir, &$calls) {
    processMembershipApplication($post, $send, $dir);
    $post['address'] = 'Corrected address';
    processMembershipApplication($post, $send, $dir);
    check(count($calls) === 2, 'Correction was suppressed');
});
runCase('one-hour expiration', function ($post, $send, $dir, &$calls) {
    processMembershipApplication($post, $send, $dir, 100000);
    processMembershipApplication($post, $send, $dir, 103601);
    check(count($calls) === 2, 'Expired submission stayed suppressed');
});
runCase('concurrent retry is blocked', function ($post, $send, $dir, &$calls) {
    $wrapped = function (...$args) use ($post, $send, $dir): bool {
        check(processMembershipApplication($post, $send, $dir)['status'] === 409, 'Concurrent retry not blocked');
        return $send(...$args);
    };
    check(processMembershipApplication($post, $wrapped, $dir)['status'] === 303, 'Original failed');
    check(count($calls) === 1, 'Concurrent duplicate sent');
});
runCase('mail failure remains pending', function ($post, $send, $dir, &$calls) {
    $failed = function (...$args) use ($send): bool { $send(...$args); return false; };
    check(processMembershipApplication($post, $failed, $dir)['status'] === 503, 'Mail failure hidden');
    check(processMembershipApplication($post, $send, $dir)['status'] === 503, 'Uncertain mail retried');
    check(count($calls) === 1, 'Mail failure triggered resend');
});
runCase('interrupted handoff remains pending', function ($post, $send, $dir, &$calls) {
    $interrupted = function (...$args) use ($send): bool { $send(...$args); throw new RuntimeException('timeout'); };
    check(processMembershipApplication($post, $interrupted, $dir)['status'] === 503, 'Interrupted mail hidden');
    check(processMembershipApplication($post, $send, $dir)['status'] === 503, 'Interrupted mail resent');
    check(count($calls) === 1, 'Duplicate after interruption');
});
runCase('invalid email and header injection rejected', function ($post, $send, $dir, &$calls) {
    foreach (['invalid', "x@example.com\r\nBcc:evil@example.com"] as $email) {
        $post['email'] = $email;
        check(processMembershipApplication($post, $send, $dir)['status'] === 400, 'Invalid email accepted');
    }
    check(count($calls) === 0, 'Invalid email was sent');
});
runCase('honeypot and invalid member flag rejected', function ($post, $send, $dir, &$calls) {
    foreach ([['preset',''], ['blank','bot'], ['new','unset']] as [$key,$value]) {
        $invalid = $post; $invalid[$key] = $value;
        check(processMembershipApplication($invalid, $send, $dir)['status'] === 400, 'Invalid flag accepted');
    }
    check(count($calls) === 0, 'Spam was sent');
});
runCase('blank name and arrays rejected', function ($post, $send, $dir, &$calls) {
    foreach (['', ['invalid']] as $name) {
        $post['name'] = $name;
        check(processMembershipApplication($post, $send, $dir)['status'] === 400, 'Invalid name accepted');
    }
    check(count($calls) === 0, 'Invalid application sent');
});
runCase('optional email and private fingerprint', function ($post, $send, $dir, &$calls) {
    $post['email'] = '';
    check(processMembershipApplication($post, $send, $dir)['status'] === 303, 'Optional email regressed');
    check(strpos($calls[0][3], 'Reply-To:') === false, 'Invalid reply header');
    foreach (glob($dir.'/*.state') as $file) check(strpos(file_get_contents($file), 'Test Member') === false, 'Personal data stored');
});
runCase('state failure sends no mail', function ($post, $send, $dir, &$calls) {
    file_put_contents($dir, 'blocked');
    check(processMembershipApplication($post, $send, $dir)['status'] === 503, 'State error hidden');
    check(count($calls) === 0, 'State error sent mail');
});
echo "$tests handler tests passed\n";
