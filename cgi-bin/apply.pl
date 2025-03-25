#! /usr/bin/perl -w
#
# apply.pl: process a TVS membership application form
#
# Last change: 11/21/19
#
# Note: in theory a user could submit a form that has no email, mailing address, or phone, so that we
# would have no way to contact him.  The script doesn't check for that because it doesn't seem likely
# that users would actually do this and expect to hear from us.
#

use CGI;


# Variables that control how this script runs:

my $from = 'webmaster@trivalleystargazers.org';         # Who all email messages appear to come from
my @handlers = ('membership@trivalleystargazers.org', 'treasurer@trivalleystargazers.org');     # The people who handle membership applications
my $sendmail = "/usr/sbin/sendmail -t -i";
my $spam_reader = 'spam@trivalleystargazers.org';       # The person that gets email about spam
my $testing = 0;                                        # 0 for production, 1 for testing


# Extract data from form fields

my $query = new CGI;

my $name = $query->param('name');             # Person's name
my $address = $query->param('address');       # Person's street address
my $city = $query->param('city');             # Person's city
my $state = $query->param('state');           # Person's state
my $zip = $query->param('zip');               # Person's zip code
my $phone = $query->param('phone');           # Person's phone number
my $email = $query->param('email');           # Person's email
my $comments = $query->param('comments');     # Person's comments, if any
my $blank = $query->param('blank');           # Blank field for spambot detection
my $preset = $query->param('preset');         # Non-blank field for spambot detection
my $new = $query->param('new');               # Flag for returning vs. new members

# Compose the message body

my $eol;
if ( $testing ) {
    $eol = "<br>";
}
else {
    $eol = "\n";
}

my $time = localtime;
my $body = "The following membership application data was received at iPage on $time:$eol$eol";
$body = "${body}Name:         $name $eol";
$body = "${body}Address:      $address $eol";
$body = "${body}City:         $city $eol";
$body = "${body}State:        $state $eol";
$body = "${body}Zip:          $zip $eol";
$body = "${body}Phone:        $phone $eol";
$body = "${body}Email:        $email $eol";
if ( $new eq "Yes" ) {
    $body = "${body}Status:       New member $eol";
}
else {
    $body = "${body}Status:       Returning member $eol";
}
$body = "${body}Comments:$eol$eol$comments $eol";


# Check for spambots.  Die if the blank or preset fields are wrong.

if ($blank ne "" || $preset ne "Preset" || $new eq "unset") {
    spam("Probable spam because fields are wrong: blank = $blank, preset = $preset, new = $new.${eol}Full message follows:$eol$eol$body");
    die("Spam: field error: blank = $blank, preset = $preset.");
}


# Check for all blank fields.  This could be a spambot, but I've never seen it
# happen.  More likely the user just hit the submit button by mistake.  In any
# case we warn the user, but we don't annoy the spm reader, since it doesn't
# happen often enough to be a concern.

if ( $name eq "" && $address eq "" && $city eq "" && $state eq "" && $zip eq ""
     && $phone eq "" && $email eq "" && $comments eq "") {

    ### spam("All fields blank.  Could be user error or spam.");
    print "Content-type: text/html\n\n";
    print "<h1 style='text-align:center;'>Blank message ignored</h1>";
    exit;
}


# Check for valid email address.

$email =~ s/^\s+|\s+$//g;           # trim leading and trailing blanks...
if ( $email =~ m/^[ ]*$/ ) {        # ... so old test might be redundant now
    $email = "";
} 
elsif ( $email !~ m/^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/ ) {
    spam("Invalid email ($email) could be spam.${eol}Full message follows:$eol$eol$body");
    print "Content-type: text/html\n\n";
    print "<h1 style='text-align:center;'>Invalid email address: $email</h1>";
    exit;
}


if ( $testing ) {
    print "Content-type: text/html\n\n";
    print "This is just a test<br>";
    print "$body";
    exit;
}


# Find who to send the message to.

my $to;
if ($phone eq "Hilary") {			# Test messages always go to me
    @handlers = ('hilary@muffycat.org',  'hdjones@pacbell.net');  # It's OK to hardwire these addresses
}


# Send message.  Note that we don't CC back to the user.  That would reveal the our email address,
# allowing him to spam us.  The message must also not appear to come from the applicant, since
# that makes it look like we are sending out spam with a forged return address.  In turn that
# can cause our site to be blacklisted.

foreach my $to (@handlers) {

    open(SENDMAIL, "|$sendmail") or die "Cannot open $sendmail. $!";

    print SENDMAIL "From: $from\n";
    print SENDMAIL "To: $to\n";
    print SENDMAIL "Reply-to: $email\n";
    print SENDMAIL "Subject: TVS membership application\n";
    print SENDMAIL "Content-Type: text/html; charset=ISO-8859-1\n";
    print SENDMAIL "\n";		# Tell sendmail that headers are done and message body follows

    print SENDMAIL "<pre>$body</pre>\n";
    close(SENDMAIL);
}



# Send user to the PayPal page to make sure he pays his dues too
print "Location: http://$ENV{'HTTP_HOST'}/pay.shtml?applicant=true\n\n";


# Function to notify the spam reader when the script detects spam

sub spam {
    my $msg = shift;

    if ( $testing ) {
      print "Content-type: text/html\n\n";
      print "Testing function spam:$eol";
      print "$msg";
      exit;
    }

    open(SENDMAIL, "|$sendmail") or die "Cannot open $sendmail. $!";

    print SENDMAIL "From: $from\n";
    print SENDMAIL "To: $spam_reader\n";
    print SENDMAIL "Subject: Spam TVS membership application\n";
    print SENDMAIL "Content-Type: text/html; charset=ISO-8859-1\n";
    print SENDMAIL "\n";		# Tell sendmail that headers are done and message body follows

    print SENDMAIL "<pre>$msg</pre>\n";
    close(SENDMAIL);
    return;
}

