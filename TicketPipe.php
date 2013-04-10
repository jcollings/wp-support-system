#!/usr/local/bin/php -q
<?php
define('WP_USE_THEMES', false);
require(dirname(__FILE__).'/../../../wp-blog-header.php');

$email = file_get_contents("php://stdin");
TicketEmail::process_email_ticket($email);
?>