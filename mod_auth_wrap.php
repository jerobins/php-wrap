<?php

define(WRAPSCRIPT, 'wrap-auth.pl');

function auth_wrap_translate_uri($uri) {
   $parts = explode('/', $uri);
   for ($i = 0; $i < count($parts); $i++) {
      $parts[$i] = rawurlencode($parts[$i]);
   }
   return implode('/', $parts);
}

function auth_wrap_siteurl() {

   // protocol 
   if(isset($_SERVER['HTTPS']) and ($_SERVER['HTTPS'] == "on")) {
      $mysite = "https://";
   } else {
      $mysite = "http://";
   }
      
   // host            
   $mysite .= $_SERVER['HTTP_HOST'];
   
   // path
   $path = dirname($_SERVER['SCRIPT_NAME']);
   if($path != "/") {
      $mysite .= auth_wrap_translate_uri($path);
      $mysite .= '/';
   }
    
   return($mysite);
}

function auth_wrap_do_webauth() {
   $myReturnURL = auth_wrap_siteurl();
   $cookieHeaderString = "WRAP_REFERER=$myReturnURL; "
                     . "path=/; domain=.ncsu.edu";
   header("Set-Cookie: $cookieHeaderString");
   $authLink = "https://webauth.ncsu.edu/wrap-bin/was16.cgi";
   header("Location: $authLink");
   exit(0);
}

function auth_wrap_exec() {

   if ( ! isset($_COOKIE['WRAP_REFERER']) 
        or ! isset($_COOKIE['WRAP16']) ) {

      auth_wrap_do_webauth();

   } else {
   
      $sugar = $_SERVER['HTTP_COOKIE'];
      $spice = $_SERVER['REMOTE_ADDR'];

      exec(WRAPSCRIPT . " \"$sugar\" $spice", $result, $rc);

      if ( $rc == 1 ) {
         $userid = $result[0];
         $_SERVER['WRAP_USERID'] = $userid;
         $_SERVER['REMOTE_USER'] = $userid;
         $_SERVER['PHP_AUTH_USER'] = $userid;
      } elseif ( $rc == 2 ) {
         // expired
         auth_wrap_do_webauth();
      }

      // if they have a WRAP_REFERER cookie, but fail to login, oh well
   }

   return;
}

function auth_wrap_fail() {
   header("HTTP/1.1 401 Access Denied");
   header("Status: 401 Access Denied");
   exit();
}

function require_wrap_user($user) {

   if ( ! isset($_SERVER['WRAP_USERID']) ) {
      auth_wrap_fail();
   }

   if ( is_array($user) ) {
      if ( ! in_array($_SERVER['WRAP_USERID'], $user) ) {
         auth_wrap_fail();
      }
   }

   if ( is_string($user) ) {
      if ( strcmp($user, 'valid-user') != 0 ) {
         if ( strcmp($user, $_SERVER['WRAP_USERID']) != 0 ) {
            auth_wrap_fail();
         }
      }
   }
   return;
}

function request_wrap_user($user) {
   $rc = False;

   if ( isset($_SERVER['WRAP_USERID']) ) {
      if ( is_array($user) ) {
         if ( in_array($_SERVER['WRAP_USERID'], $user) ) {
            $rc = True;
         }
      }

      if ( is_string($user) ) {
         if ( strcmp($user, 'valid-user') == 0 ) {
            $rc = True;
         } elseif ( strcmp($user, $_SERVER['WRAP_USERID']) == 0 ) {
            $rc = True;
         }
      }
   }

   return $rc;
}

auth_wrap_exec();

?>
