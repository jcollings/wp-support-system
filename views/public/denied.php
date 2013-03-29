<?php
$login_url = get_option('support_login_url');
$login = isset($login_url['login_url']) && !empty($login_url['login_url']) ? $login_url['login_url'] : add_query_arg('support-action', 'login');
$register_url = get_option('support_register_url');
$register = isset($register_url['register_url']) && !empty($register_url['register_url']) ? $register_url['register_url'] : add_query_arg('support-action', 'login');
?>

<h1>Restricted Content</h1>
<p>To gain access to our support system, please <a href="<?php echo $login; ?>">login</a> or <a href="<?php echo $register; ?>">register an account</a> with the website.</p>