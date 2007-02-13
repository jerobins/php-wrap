<?php

include_once('mod_auth_wrap.php');
require_wrap_user('valid-user');

# to get here, the user must be WRAPped

$VALIDUSERS  = array('user1', 'user2');
$AUTHUSER = request_wrap_user($VALIDUSERS);

if ( $AUTHUSER ) {
   # do special stuff
} else {
   # all other users
}

?>
