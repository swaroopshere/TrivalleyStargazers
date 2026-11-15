"""Run with real Perl + CGI.pm; mail is captured locally and never delivered."""
import concurrent.futures
import os
from pathlib import Path
import subprocess
import tempfile
import time
import unittest
from urllib.parse import urlencode

ROOT = Path(__file__).resolve().parents[2]
PERL = os.environ.get('TVS_TEST_PERL', 'perl')


class HandlerTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='tvs-membership-test-')
        self.addCleanup(self.temp.cleanup)
        self.folder = Path(self.temp.name)
        self.mail_log = self.folder / 'mail.log'
        self.env = dict(os.environ, TVS_MEMBERSHIP_STATE_DIR=str(self.folder / 'state'),
                        TVS_MEMBERSHIP_SENDMAIL=(ROOT / 'tests/membership/fake-sendmail.pl').as_posix(),
                        TVS_TEST_MAIL_LOG=str(self.mail_log), HTTP_HOST='untrusted.example',
                        TVS_MEMBERSHIP_MAIL_TIMEOUT='5')
        self.data = dict(name='Test Member', address='Example Street', city='Example',
                         state='CA', zip='00000', phone='555-0100',
                         email='test+club@example.technology', comments='Test <comment>',
                         blank='', preset='Preset', new='Yes')

    def submit(self, data=None, method='POST', **env):
        payload = urlencode(self.data if data is None else data).encode()
        process = subprocess.run([PERL, str(ROOT / 'cgi-bin/apply.pl')], input=payload,
            env=dict(self.env, REQUEST_METHOD=method, CONTENT_TYPE='application/x-www-form-urlencoded',
                     CONTENT_LENGTH=str(len(payload)), **env), capture_output=True, timeout=12)
        self.assertEqual(process.returncode, 0, process.stderr.decode(errors='replace'))
        return process.stdout.decode(errors='replace'), process.stderr.decode(errors='replace')

    def count_mail(self):
        return self.mail_log.read_text().count('=== MESSAGE ===') if self.mail_log.exists() else 0

    def test_new_member_https_redirect_and_mail(self):
        output, log = self.submit()
        self.assertIn('Status: 303 See Other', output)
        self.assertIn('Location: https://www.trivalleystargazers.org/pay.shtml?applicant=true', output)
        self.assertIn('Continue to payment', output)
        self.assertIn('Cache-Control: no-store', output)
        mail = self.mail_log.read_text()
        self.assertIn('membership@trivalleystargazers.org, treasurer@trivalleystargazers.org', mail)
        self.assertIn('Reply-To: test+club@example.technology', mail)
        self.assertIn('New member', mail)
        self.assertIn('Test &lt;comment&gt;', mail)
        self.assertEqual(self.count_mail(), 1)
        self.assertNotIn('Test Member', log)

    def test_returning_member(self):
        output, _ = self.submit(dict(self.data, new='No'))
        self.assertIn('Status: 303', output)
        self.assertIn('Returning member', self.mail_log.read_text())

    def test_optional_email_is_preserved(self):
        self.assertIn('Status: 303', self.submit(dict(self.data, email=''))[0])
        self.assertNotIn('Reply-To:', self.mail_log.read_text())

    def test_oversized_application_is_rejected(self):
        self.assertIn('Status: 413', self.submit(dict(self.data, comments='x'*66000))[0])
        self.assertEqual(self.count_mail(), 0)

    def test_retries_send_only_once(self):
        for _ in range(8):
            output, _ = self.submit()
            self.assertIn('Status: 303', output)
        self.assertEqual(self.count_mail(), 1)

    def test_changed_application_is_sent(self):
        self.submit()
        self.submit(dict(self.data, address='Corrected Street'))
        self.assertEqual(self.count_mail(), 2)

    def test_expired_application_can_be_sent_again(self):
        self.submit()
        next((self.folder / 'state').glob('*.state')).write_text('1 sent\n')
        self.submit()
        self.assertEqual(self.count_mail(), 2)

    def test_concurrent_duplicate(self):
        with concurrent.futures.ThreadPoolExecutor() as pool:
            first = pool.submit(self.submit, TVS_TEST_MAIL_DELAY='2')
            for _ in range(100):
                if self.mail_log.exists(): break
                time.sleep(0.02)
            second, _ = self.submit()
            self.assertIn('Status: 409', second)
            self.assertIn('Status: 303', first.result()[0])
        self.assertEqual(self.count_mail(), 1)

    def test_mail_failure_does_not_claim_success_or_resend(self):
        output, _ = self.submit(TVS_TEST_MAIL_EXIT='1')
        self.assertIn('Status: 503', output)
        self.assertIn('could not confirm receipt', output)
        self.assertNotIn('Location:', output)
        self.assertIn('Status: 503', self.submit()[0])
        self.assertEqual(self.count_mail(), 1)

    def test_mail_timeout_does_not_resend(self):
        output, _ = self.submit(TVS_TEST_MAIL_DELAY='4', TVS_MEMBERSHIP_MAIL_TIMEOUT='1')
        self.assertIn('Status: 503', output)
        self.assertIn('Status: 503', self.submit()[0])
        self.assertEqual(self.count_mail(), 1)

    def test_validation_never_sends_mail(self):
        for change in [dict(email='invalid'), dict(email='x@example.com\r\nBcc:evil@example.com'),
                       dict(preset=''), dict(blank='bot'), dict(new='unset')]:
            self.assertIn('Status: 400', self.submit(dict(self.data, **change))[0])
        blank = dict.fromkeys(self.data, '')
        blank.update(preset='Preset', new='Yes')
        self.assertIn('Status: 400', self.submit(blank)[0])
        self.assertEqual(self.count_mail(), 0)

    def test_get_cannot_send_application(self):
        self.assertIn('Status: 405', self.submit(method='GET')[0])
        self.assertEqual(self.count_mail(), 0)

    def test_state_failure_never_sends_mail(self):
        bad = self.folder / 'not-a-directory'
        bad.write_text('blocked')
        output, _ = self.submit(TVS_MEMBERSHIP_STATE_DIR=str(bad))
        self.assertIn('Status: 503', output)
        self.assertEqual(self.count_mail(), 0)


if __name__ == '__main__':
    unittest.main()
