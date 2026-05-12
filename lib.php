<?php
function site_config()
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function h($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function site_url($path)
{
    $config = site_config();
    $base = rtrim($config['base_url'], '/');
    return $base . $path;
}

function absolute_base_url()
{
    $config = site_config();
    if ($config['base_url'] !== '' && preg_match('#^https?://#i', $config['base_url'])) {
        return rtrim($config['base_url'], '/');
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $base = rtrim($config['base_url'], '/');
    return $scheme . '://' . $host . $base;
}

function data_path($name)
{
    return __DIR__ . '/data/' . $name;
}

function page_path($name)
{
    return __DIR__ . '/pages/' . $name . '.html';
}

function posts_path()
{
    return __DIR__ . '/posts';
}

function ensure_data_file($name, $default)
{
    $path = data_path($name);
    if (!file_exists($path)) {
        file_put_contents($path, $default);
    }
    return $path;
}

function visitor_count()
{
    $config = site_config();
    $path = ensure_data_file('counter.txt', "314");
    $count = (int) trim((string) file_get_contents($path));
    if (empty($_COOKIE['clay_blog_seen'])) {
        $count++;
        file_put_contents($path, (string) $count, LOCK_EX);
        setcookie('clay_blog_seen', '1', time() + (86400 * (int) $config['visitor_cookie_days']));
    }
    return $count;
}

function read_page($name, $fallbackTitle)
{
    $path = page_path($name);
    if (!file_exists($path)) {
        return array('title' => $fallbackTitle, 'body' => '<p>页面不存在。</p>');
    }
    return array('title' => $fallbackTitle, 'body' => (string) file_get_contents($path));
}

function parse_post_file($path)
{
    $raw = (string) file_get_contents($path);
    $parts = preg_split("/\r?\n----\r?\n/", $raw, 2);
    $header = isset($parts[0]) ? $parts[0] : '';
    $body = isset($parts[1]) ? $parts[1] : $raw;
    $meta = array();
    foreach (preg_split("/\r?\n/", trim($header)) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, ':') === false) {
            continue;
        }
        list($key, $value) = explode(':', $line, 2);
        $meta[strtolower(trim($key))] = trim($value);
    }
    $slug = isset($meta['slug']) && $meta['slug'] !== '' ? $meta['slug'] : basename($path, '.html');
    $stamp = isset($meta['date']) ? strtotime($meta['date']) : filemtime($path);
    return array(
        'title' => isset($meta['title']) ? $meta['title'] : $slug,
        'date' => isset($meta['date']) ? $meta['date'] : date('Y-m-d H:i', $stamp),
        'summary' => isset($meta['summary']) ? $meta['summary'] : '',
        'slug' => $slug,
        'stamp' => $stamp,
        'body' => trim($body),
        'file' => basename($path)
    );
}

function all_posts()
{
    $items = array();
    foreach (glob(posts_path() . '/*.html') as $path) {
        $items[] = parse_post_file($path);
    }
    usort($items, function ($a, $b) {
        if ($a['stamp'] === $b['stamp']) {
            return strcmp($a['slug'], $b['slug']);
        }
        return $a['stamp'] < $b['stamp'] ? 1 : -1;
    });
    return $items;
}

function find_post($slug)
{
    foreach (all_posts() as $post) {
        if ($post['slug'] === $slug) {
            return $post;
        }
    }
    return null;
}

function save_post($fileName, $title, $date, $slug, $summary, $body)
{
    $safe = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($fileName));
    $safe = trim($safe, '-');
    if ($safe === '') {
        $safe = 'post-' . date('Ymd-His');
    }
    $slugSafe = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($slug));
    $slugSafe = trim($slugSafe, '-');
    if ($slugSafe === '') {
        $slugSafe = $safe;
    }
    $path = posts_path() . '/' . $safe . '.html';
    $content = "Title: " . trim($title) . "\n";
    $content .= "Date: " . trim($date) . "\n";
    $content .= "Slug: " . $slugSafe . "\n";
    $content .= "Summary: " . trim($summary) . "\n";
    $content .= "----\n";
    $content .= trim($body) . "\n";
    file_put_contents($path, $content, LOCK_EX);
    return $path;
}

function delete_post($fileName)
{
    $path = posts_path() . '/' . basename($fileName);
    if (file_exists($path)) {
        unlink($path);
    }
}

function post_to_form($post)
{
    return array(
        'file' => $post ? basename($post['file'], '.html') : '',
        'title' => $post ? $post['title'] : '',
        'date' => $post ? date('Y-m-d H:i', $post['stamp']) : date('Y-m-d H:i'),
        'slug' => $post ? $post['slug'] : 'post-' . date('Ymd'),
        'summary' => $post ? $post['summary'] : '',
        'body' => $post ? $post['body'] : "<p>在这里写你的文章内容。</p>"
    );
}

function render_post_list($posts, $showSummary)
{
    if (!$posts) {
        return '<p>暂时还没有文章，可以先去后台发布第一篇。</p>';
    }
    $html = '';
    foreach ($posts as $post) {
        $html .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;border:1px solid #808080;background:#efefef;">';
        $html .= '<tr><td style="background:#000080;color:#fff;font:bold 12px Arial;padding:3px 6px;">';
        $html .= '<a href="' . h(site_url('/index.php?post=' . urlencode($post['slug']))) . '" style="color:#fff;text-decoration:none;">' . h($post['title']) . '</a>';
        $html .= '</td></tr><tr><td style="padding:6px;line-height:1.5;">';
        $html .= '<div style="font-size:11px;"><b>日期：</b> ' . h($post['date']) . '</div>';
        if ($showSummary && $post['summary'] !== '') {
            $html .= '<p>' . h($post['summary']) . '</p>';
        }
        $html .= '<a href="' . h(site_url('/index.php?post=' . urlencode($post['slug']))) . '">阅读全文...</a>';
        $html .= '</td></tr></table>';
    }
    return $html;
}

function retro_layout($title, $content, $active)
{
    $config = site_config();
    $counter = visitor_count();
    $posts = all_posts();
    $latest = array_slice($posts, 0, 6);
    $year = date('Y');
    $gifBlue = 'data:image/gif;base64,R0lGODlhCAAIAIEAAAAAAP///wAAAAAAACH5BAEAAAQALAAAAAAIAAgAAAgJAAEIHEiwoMGDCBMqXMiwIQA7';
    $gifYellow = 'data:image/gif;base64,R0lGODlhCAAIAIEAAP//AAAAAP///wAAACH5BAEAAAQALAAAAAAIAAgAAAgJAAEIHEiwoMGDCBMqXMiwIQA7';
    $gifRed = 'data:image/gif;base64,R0lGODlhCAAIAIEAAP8AAAAAAAAAAAAAACH5BAEAAAQALAAAAAAIAAgAAAgJAAEIHEiwoMGDCBMqXMiwIQA7';
    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h($title); ?> - <?php echo h($config['site_name']); ?></title>
<style type="text/css">
body{margin:0;padding:0;background:#c0c0c0;color:#000;font:12px Arial,Helvetica,sans-serif;}
table{border-collapse:collapse;}
a:link,a:visited{color:#00f;text-decoration:underline;}
input,textarea{font:12px Arial,Helvetica,sans-serif;border:2px inset #fff;background:#fff;color:#000;}
.page{width:900px;margin:8px auto;border:2px outset #fff;background:#d4d0c8;}
.layout td{vertical-align:top;}
.navtop{background:#000080;color:#fff;font:bold 12px Arial;padding:4px 6px;border-bottom:2px solid #808080;}
.navtop a{color:#fff;text-decoration:none;padding:2px 8px;border:2px outset #c0c0c0;background:#808080;margin-right:4px;}
.menu{background:#bdbdbd;border-top:1px solid #fff;border-left:1px solid #fff;border-right:1px solid #404040;border-bottom:1px solid #404040;}
.win{background:#c0c0c0;border:2px outset #fff;}
.title{background:#000080;color:#fff;font:bold 12px Arial;padding:3px 6px;}
.box{padding:6px;line-height:1.5;}
.btn{display:inline-block;padding:2px 10px;color:#000!important;text-decoration:none!important;background:#c0c0c0;border-top:2px solid #fff;border-left:2px solid #fff;border-right:2px solid #404040;border-bottom:2px solid #404040;font:12px Arial;}
.small{font-size:11px;}
.counter{font:bold 14px "Courier New",monospace;background:#000;color:#0f0;padding:2px 6px;border:2px inset #fff;letter-spacing:2px;}
.post-body p{margin:0 0 10px 0;}
.post-body pre{font:12px "Courier New",monospace;background:#efefef;border:1px solid #808080;padding:6px;overflow:auto;}
.post-body blockquote{margin:8px 12px;padding:6px;border-left:3px solid #808080;background:#efefef;}
.post-body img{max-width:100%;}
hr{height:1px;border:0;border-top:1px solid #808080;border-bottom:1px solid #fff;}
@media screen and (max-width: 760px){
body{padding:4px;}
.page{width:100%;margin:0;}
.layout,.layout tbody,.layout tr,.layout td{display:block;width:auto!important;}
.navtop a{display:inline-block;margin:2px 2px 0 0;}
input,textarea{width:96%;box-sizing:border-box;}
}
</style>
</head>
<body>
<table class="page" align="center" cellpadding="0" cellspacing="0">
<tr>
<td colspan="3" class="navtop">
<span><?php echo h($config['site_name']); ?></span>
&nbsp;&nbsp;
<a href="<?php echo h(site_url('/index.php')); ?>">首页</a>
<a href="<?php echo h(site_url('/index.php?page=posts')); ?>">文章</a>
<a href="<?php echo h(site_url('/index.php?page=archive')); ?>">归档</a>
<a href="<?php echo h(site_url('/index.php?page=about')); ?>">关于</a>
<a href="<?php echo h(site_url('/index.php?page=links')); ?>">链接</a>
<a href="<?php echo h(site_url('/rss.php')); ?>">RSS</a>
<a href="<?php echo h(site_url('/admin.php')); ?>">后台</a>
</td>
</tr>
<tr class="menu">
<td colspan="3" style="padding:4px 6px;font:11px Arial;">文件&nbsp;&nbsp;编辑&nbsp;&nbsp;查看&nbsp;&nbsp;转到&nbsp;&nbsp;收藏夹&nbsp;&nbsp;选项&nbsp;&nbsp;目录&nbsp;&nbsp;帮助</td>
</tr>
<tr valign="top" class="layout">
<td width="180" style="padding:8px;">
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">导航</td></tr>
<tr><td class="box small">
<div><img src="<?php echo $gifBlue; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/index.php')); ?>">首页</a></div>
<div><img src="<?php echo $gifYellow; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/index.php?page=posts')); ?>">文章列表</a></div>
<div><img src="<?php echo $gifRed; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/index.php?page=archive')); ?>">归档</a></div>
<div><img src="<?php echo $gifBlue; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/index.php?page=about')); ?>">关于我</a></div>
<div><img src="<?php echo $gifYellow; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/index.php?page=links')); ?>">友情链接</a></div>
<div><img src="<?php echo $gifRed; ?>" width="8" height="8" alt=""> <a href="<?php echo h(site_url('/rss.php')); ?>">RSS 订阅</a></div>
</td></tr>
</table>
<br>
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">站点信息</td></tr>
<tr><td class="box small">
<a class="btn" href="<?php echo h(site_url('/index.php?page=posts')); ?>">进入博客</a><br><br>
<b>访问计数器</b><br>
<span class="counter"><?php echo str_pad((string) $counter, 6, '0', STR_PAD_LEFT); ?></span><br><br>
<b>当前栏目：</b><br>
<?php echo h($active); ?><br><br>
<b>电子邮箱：</b><br>
<a href="mailto:<?php echo h($config['email']); ?>"><?php echo h($config['email']); ?></a>
</td></tr>
</table>
<br>
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">小徽章</td></tr>
<tr><td class="box small" align="center">
<div style="margin-bottom:3px;border:1px solid #000;background:#ffffcc;padding:3px;">800x600 效果更佳</div>
<div style="margin-bottom:3px;border:1px solid #000;background:#ccffff;padding:3px;">NETSCAPE 推荐</div>
<div style="margin-bottom:3px;border:1px solid #000;background:#ffdddd;padding:3px;">无数据库</div>
<div style="border:1px solid #000;background:#e8e8e8;padding:3px;">HTML 驱动</div>
</td></tr>
</table>
</td>
<td width="500" style="padding:8px;">
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title"><?php echo h($title); ?></td></tr>
<tr><td class="box post-body"><?php echo $content; ?></td></tr>
</table>
</td>
<td width="220" style="padding:8px;">
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">最新文章</td></tr>
<tr><td class="box small">
<?php if ($latest) { foreach ($latest as $item) { ?>
<div style="margin-bottom:6px;"><a href="<?php echo h(site_url('/index.php?post=' . urlencode($item['slug']))); ?>"><?php echo h($item['title']); ?></a><br><?php echo h(date('Y-m-d', $item['stamp'])); ?></div>
<?php }} else { ?>
还没有文章。
<?php } ?>
</td></tr>
</table>
<br>
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">友情链接</td></tr>
<tr><td class="box small">
<?php foreach ($config['blogroll'] as $link) { ?>
<div><img src="<?php echo $gifBlue; ?>" width="8" height="8" alt=""> <a href="<?php echo h($link['url']); ?>"><?php echo h($link['name']); ?></a></div>
<?php } ?>
</td></tr>
</table>
<br>
<table width="100%" class="win" cellpadding="0" cellspacing="0">
<tr><td class="title">站点说明</td></tr>
<tr><td class="box small">
纯文件博客系统<br>
兼容 PHP 7.2<br>
解压即可运行<br>
不需要 Composer<br>
几乎不依赖 JavaScript
</td></tr>
</table>
</td>
</tr>
<tr>
<td colspan="3" align="center" class="box small" style="border-top:2px groove #fff;">
<b>Best viewed in Netscape Navigator</b><br>
Copyright (c) CLAY, 1998-<?php echo $year; ?>. All rights reserved on the information superhighway.
</td>
</tr>
</table>
</body>
</html>
<?php
}
