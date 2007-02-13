#!/usr/bin/perl

use POSIX;
use Wrap::Cookie;

ReadPublicKeyFile('/path/to/wrap16.pub');

($sugar, $ip) = @ARGV;

my $rc = 0;
my $cookie = new Wrap::Cookie $sugar;

if ($cookie->Authentic) {
   if ( $cookie->affil == "ncsu.edu" ) {
      $curtime = mktime(localtime());
      if ( $cookie->expdate > $curtime ) {
         if ( $cookie->onproxy == 'Y') { 
            print $cookie->userid;
            $rc = 1;
         } else {
            if ( $cookie->address == $ip ) {
               print $cookie->userid;
               $rc = 1;
            }
         }
      } else {
         $rc = 2;
      }
   }
}
exit($rc);
