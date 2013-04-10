#!/usr/local/bin/php -q
<?php
define('WP_USE_THEMES', false);
require(dirname(__FILE__).'/../../../wp-blog-header.php');

$email_addr = 'james@jclabs.co.uk'; // where the email will be sent
$subject = 'Piped:'; // the subject of the email being sent
$email_msg = file_get_contents("php://stdin");
$matches = array();
$msg = '';

$to = '';
$from = '';
$subject = '';
$message = '';

if(preg_match('/\nTo:(.*?)\n/i', $email_msg, $matches)){
	$to = $matches[1];	
}

if(preg_match('/\nFrom:(.*?)\n/i', $email_msg, $matches)){
	$from = $matches[1];	
}

if(preg_match('/\nSubject:(.*?)\n/i', $email_msg, $matches)){
	$subject = $matches[1];	
}

$message = substr($email_msg, strpos($email_msg, "\n\n"));

$msg = "
To: $to,
From: $from,
Subject: $subject,
Message: $message,
";

TicketModel::insert_ticket($subject, $message, 0);

// send a copy of the email to your account
mail($email_addr, $subject, $msg);
?>