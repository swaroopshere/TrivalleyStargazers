#!/usr/bin/perl
use strict;
use warnings;
use Fcntl qw(:flock);
my $body = do { local $/; <STDIN> };
open(my $out, '>>', $ENV{TVS_TEST_MAIL_LOG}) or die $!;
flock($out, LOCK_EX) or die $!;
print {$out} "=== MESSAGE ===\n$body\n";
close($out);
sleep($ENV{TVS_TEST_MAIL_DELAY} || 0);
exit($ENV{TVS_TEST_MAIL_EXIT} || 0);
