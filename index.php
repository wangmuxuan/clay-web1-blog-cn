<?php
require __DIR__ . '/lib.php';

$config = site_config();
$posts = all_posts();
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$slug = isset($_GET['post']) ? $_GET['post'] : '';
$title = $config['site_name'];
$content = '';
$active = '首页';

if ($slug !== '') {
    $post = find_post($slug);
    if ($post) {
        $title = $post['title'];
        $active = '文章';
        $content .= '<div class="small"><b>日期：</b> ' . h($post['date']) . '</div><hr>';
        $content .= $post['body'];
        $content .= '<hr><a href="' . h(site_url('/index.php?page=posts')) . '">返回文章列表</a>';
    } else {
        $title = '文章不存在';
        $active = '错误';
        $content = '<p>你访问的文章不存在。</p>';
    }
} elseif ($page === 'posts') {
    $title = '文章列表';
    $active = '文章列表';
    $content = render_post_list($posts, true);
} elseif ($page === 'archive') {
    $title = '归档';
    $active = '归档';
    if ($posts) {
        foreach ($posts as $post) {
            $content .= '<div style="margin-bottom:8px;"><b>' . h(date('Y-m', $post['stamp'])) . '</b> - ';
            $content .= '<a href="' . h(site_url('/index.php?post=' . urlencode($post['slug']))) . '">' . h($post['title']) . '</a>';
            $content .= ' <span class="small">(' . h(date('Y-m-d', $post['stamp'])) . ')</span></div>';
        }
    } else {
        $content = '<p>暂时还没有归档内容。</p>';
    }
} elseif ($page === 'about') {
    $data = read_page('about', '关于');
    $title = $data['title'];
    $active = '关于';
    $content = $data['body'];
} elseif ($page === 'links') {
    $data = read_page('links', '友情链接');
    $title = $data['title'];
    $active = '友情链接';
    $content = $data['body'];
} else {
    $title = '首页';
    $active = '首页';
    $content .= '<p><font face="Times New Roman,Times,serif" size="6"><b>CLAY</b></font><br>';
    $content .= '<b>欢迎来到我的 Web 1.0 风格博客。</b><br>';
    $content .= '这个站点使用轻量的 PHP 文件系统驱动，不用数据库、不用框架，保留了很多老互联网时代的个人主页气质。</p>';
    $content .= '<hr><b>最新文章</b><br><br>';
    $content .= render_post_list(array_slice($posts, 0, (int) $config['posts_per_page']), true);
}

retro_layout($title, $content, $active);
