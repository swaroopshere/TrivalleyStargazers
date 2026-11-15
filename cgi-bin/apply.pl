#!/usr/bin/perl
use strict;
use warnings;
use CGI;
use Digest::SHA qw(sha256_hex);
use Fcntl qw(:DEFAULT :flock);
use File::Spec;
use File::Path qw(make_path);
use IO::Handle;

# Configuration is supplied by the server, never by form fields.
my $sendmail = $ENV{TVS_MEMBERSHIP_SENDMAIL} || '/usr/sbin/sendmail';
my $state_dir = $ENV{TVS_MEMBERSHIP_STATE_DIR}
    || File::Spec->catdir(File::Spec->tmpdir(), "tvs-membership-$<");
my $payment = 'https://www.trivalleystargazers.org/pay.shtml?applicant=true';
my $ttl = 3600;
my $timeout = $ENV{TVS_MEMBERSHIP_MAIL_TIMEOUT} || 20;
my @fields = qw(name address city state zip phone email comments blank preset new);

sub escape_html {
    my ($text) = @_;
    $text =~ s/&/&amp;/g;
    $text =~ s/</&lt;/g;
    $text =~ s/>/&gt;/g;
    $text =~ s/"/&quot;/g;
    return $text;
}

sub response {
    my ($status, $title, $message) = @_;
    print "Status: $status\r\nContent-Type: text/html; charset=UTF-8\r\n";
    print "Cache-Control: no-store\r\n\r\n";
    print '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>',
        escape_html($title), '</title></head><body><h1>', escape_html($title),
        '</h1><p>', escape_html($message), '</p>',
        '<p><a href="/membership.shtml">Return to the membership form</a></p>',
        '<p><a href="mailto:secretary@trivalleystargazers.org">Contact the TVS secretary</a></p></body></html>';
    exit;
}

sub received {
    print "Status: 303 See Other\r\nLocation: $payment\r\n";
    print "Content-Type: text/html; charset=UTF-8\r\nCache-Control: no-store\r\n\r\n";
    print '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Application received</title></head><body>',
        '<h1>Your membership application has been received</h1><p>Please do not submit it again.</p>',
        '<p><a href="', $payment, '">Continue to payment</a></p></body></html>';
    exit;
}

if (($ENV{REQUEST_METHOD} || '') ne 'POST') {
    print "Allow: POST\r\n";
    response('405 Method Not Allowed', 'Please use the membership form',
        'Applications must be submitted from the membership form. No application was sent.');
}
if (($ENV{CONTENT_LENGTH} || 0) > 65536) {
    response('413 Content Too Large', 'Application too large', 'Please shorten the comments and try again.');
}
my $query = CGI->new;
my %data;
for my $field (@fields) {
    $data{$field} = scalar($query->param($field)) // '';
    $data{$field} =~ s/^\s+|\s+$//g;
}
if ($data{blank} ne '' || $data{preset} ne 'Preset'
        || ($data{new} ne 'Yes' && $data{new} ne 'No')) {
    response('400 Bad Request', 'Application could not be submitted',
        'Please return to the membership form, enable JavaScript, and try again. No application was sent.');
}
if (!grep { length $data{$_} } qw(name address city state zip phone email comments)) {
    response('400 Bad Request', 'Blank application', 'Please fill out your membership information. No application was sent.');
}
# Preserve optional email, while accepting plus-addresses and modern top-level domains.
if ($data{email} ne '' && $data{email} !~ /^[A-Za-z0-9.!#\$%&'*+\/=?^_`{|}~-]+\@[A-Za-z0-9.-]+\.[A-Za-z]{2,63}$/) {
    response('400 Bad Request', 'Invalid email address', 'Please correct your email address. No application was sent.');
}

# A private, persistent fingerprint prevents retries across tabs and browsers from
# sending more email. Store no applicant information in the state files.
my @identity = map { $_ eq 'email' ? lc($data{$_}) : $data{$_} }
    qw(name address city state zip phone email comments new);
my $fingerprint = sha256_hex(join('', map { length($_) . ':' . $_ } @identity));
my $state;
my $state_ok = eval {
    make_path($state_dir, {mode => 0700}) unless -d $state_dir;
    die "unsafe state directory" if -l $state_dir || !-d $state_dir;
    my @dir_stat = stat($state_dir);
    die "unsafe state directory permissions" if $dir_stat[4] != $< || ($dir_stat[2] & 0077);
    my $path = File::Spec->catfile($state_dir, "$fingerprint.state");
    die "unsafe state file" if -l $path;
    sysopen($state, $path, O_RDWR | O_CREAT, 0600) or die "state open failed";
    1;
};
if (!$state_ok) {
    warn "TVS membership: state unavailable\n";
    response('503 Service Unavailable', 'Application service temporarily unavailable',
        'No application was sent. Please try again later or contact the TVS secretary.');
}
if (!flock($state, LOCK_EX | LOCK_NB)) {
    response('409 Conflict', 'Application is being processed',
        'An identical application is already being processed. Please wait for the original submission; do not submit it again.');
}
my $record = do { local $/; <$state> } || '';
if ($record =~ /^(\d+) (sent|pending)\n/ && time - $1 < $ttl) {
    if ($2 eq 'sent') {
        warn "TVS membership: duplicate suppressed\n";
        received();
    }
    response('503 Service Unavailable', 'Please check your application status',
        'An identical submission was started, but receipt could not be confirmed. Please contact the TVS secretary before submitting again.');
}
sub save_state {
    my ($status) = @_;
    seek($state, 0, 0) or die "state seek failed";
    truncate($state, 0) or die "state truncate failed";
    print {$state} time . " $status\n" or die "state write failed";
    $state->flush or die "state flush failed";
}

my $status = $data{new} eq 'Yes' ? 'New member' : 'Returning member';
my $body = "The following membership application data was received at iPage on " . localtime() . ":\n\n";
for my $field (qw(name address city state zip phone email)) {
    $body .= sprintf("%-14s%s\n", ucfirst($field) . ':', $data{$field});
}
$body .= "Status:       $status\nComments:\n\n$data{comments}\n";
my ($mail, $pid);
my $queued = eval {
    save_state('pending');
    local $SIG{ALRM} = sub { die "mail timeout\n" };
    local $SIG{PIPE} = 'IGNORE';
    alarm($timeout);
    $pid = open($mail, '|-', $sendmail, '-t', '-i') or die "mail open failed\n";
    print {$mail} "From: webmaster\@trivalleystargazers.org\n",
        "To: membership\@trivalleystargazers.org, treasurer\@trivalleystargazers.org\n",
        ($data{email} ne '' ? "Reply-To: $data{email}\n" : ''),
        "Subject: TVS membership application\n",
        "Content-Type: text/html; charset=ISO-8859-1\n\n",
        '<pre>', escape_html($body), "</pre>\n" or die "mail write failed\n";
    close($mail) or die "mail handoff failed\n";
    undef $pid;
    alarm(0);
    save_state('sent');
    1;
};
alarm(0);
if (!$queued) {
    # A timeout or failed exit may still have queued mail. Retain pending state
    # and ask for a status check rather than risk duplicate delivery on retry.
    if ($pid) { kill 'KILL', $pid; waitpid($pid, 0); }
    warn "TVS membership: mail handoff not confirmed\n";
    response('503 Service Unavailable', 'Please check your application status',
        'We could not confirm receipt. Please contact the TVS secretary before submitting again; your application may already have been received.');
}
warn "TVS membership: mail queued\n";
received();
