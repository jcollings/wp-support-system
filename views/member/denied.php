<?php
$urls = get_option('url_redirect');
$login = isset($urls['login']) && !empty($urls['login']) ? $urls['login'] : add_query_arg('support-action', 'login');
$register = isset($urls['register']) && !empty($urls['register']) ? $urls['register'] : add_query_arg('support-action', 'register');
?>

<h1>Restricted Content</h1>
<p>To gain access to our support system, please <a href="<?php echo $login; ?>">login</a> or <a href="<?php echo $register; ?>">register an account</a> with the website.</p>