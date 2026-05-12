<?php
require __DIR__ . '/lib.php';

function rss_xml($text)
{
    return htmlspecialchars((string) $text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function rss_cdata($text)
{
    $text = (string) $text;
    return '<![CDATA[' . str_replace(']]>', ']]]]><![CDATA[>', $text) . ']]>';
}

function rss_summary($post)
{
    if (!empty($post['summary'])) {
        return $post['summary'];
    }
    $plain = trim(strip_tags($post['body']));
    if (function_exists('mb_substr')) {
        return mb_substr($plain, 0, 140, 'UTF-8');
    }
    return substr($plain, 0, 140);
}

$config = site_config();
$posts = array_slice(all_posts(), 0, 10);
$site = absolute_base_url();
$feedUrl = $site . '/rss.php';
$homeUrl = $site . '/index.php';
$lastBuild = count($posts) ? $posts[0]['stamp'] : time();

header('Content-Type: application/rss+xml; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo rss_xml($config['site_name']); ?></title>
<link><?php echo rss_xml($homeUrl); ?></link>
<description><?php echo rss_xml($config['tagline']); ?></description>
<language>zh-cn</language>
<lastBuildDate><?php echo gmdate(DATE_RSS, $lastBuild); ?></lastBuildDate>
<pubDate><?php echo gmdate(DATE_RSS, $lastBuild); ?></pubDate>
<ttl>60</ttl>
<generator>CLAY Web1 Blog RSS</generator>
<atom:link href="<?php echo rss_xml($feedUrl); ?>" rel="self" type="application/rss+xml" />
<?php foreach ($posts as $post) { ?>
<?php $postUrl = $homeUrl . '?post=' . urlencode($post['slug']); ?>
<item>
<title><?php echo rss_xml($post['title']); ?></title>
<link><?php echo rss_xml($postUrl); ?></link>
<guid isPermaLink="true"><?php echo rss_xml($postUrl); ?></guid>
<pubDate><?php echo gmdate(DATE_RSS, $post['stamp']); ?></pubDate>
<description><?php echo rss_cdata(rss_summary($post)); ?></description>
</item>
<?php } ?>
</channel>
</rss>
