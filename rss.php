<?php
require __DIR__ . '/lib.php';

$config = site_config();
$posts = array_slice(all_posts(), 0, 10);
$site = absolute_base_url();

header('Content-Type: application/rss+xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
<channel>
<title><?php echo h($config['site_name']); ?></title>
<link><?php echo h($site . '/index.php'); ?></link>
<description><?php echo h($config['tagline']); ?></description>
<language>en-us</language>
<?php foreach ($posts as $post) { ?>
<item>
<title><?php echo h($post['title']); ?></title>
<link><?php echo h($site . '/index.php?post=' . urlencode($post['slug'])); ?></link>
<guid><?php echo h($site . '/index.php?post=' . urlencode($post['slug'])); ?></guid>
<pubDate><?php echo date(DATE_RSS, $post['stamp']); ?></pubDate>
<description><?php echo h($post['summary']); ?></description>
</item>
<?php } ?>
</channel>
</rss>
