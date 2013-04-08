<?php
global $post;
$page_url = get_permalink( $post->ID);
$urls = get_option('url_redirect');
$login = isset($urls['login']) && !empty($urls['login']) ? $urls['login'] : add_query_arg(array('support-action' => 'login', 'ref' => $page_url));
$register = isset($urls['register']) && !empty($urls['register']) ? $urls['register'] : add_query_arg(array('support-action' => 'register', 'ref' => $page_url));
?>

<h1>Restricted Content</h1>
<p>To gain access to our support system, please <a href="<?php echo $login; ?>">login</a> or <a href="<?php echo $register; ?>">register an account</a> with the website.</p>