<?php
session_start();
require __DIR__ . '/lib.php';

$config = site_config();
$message = '';

if (isset($_GET['logout'])) {
    unset($_SESSION['clay_admin']);
    header('Location: ' . site_url('/admin.php'));
    exit;
}

if (isset($_POST['password'])) {
    if ((string) $_POST['password'] === (string) $config['admin_password']) {
        $_SESSION['clay_admin'] = 1;
        header('Location: ' . site_url('/admin.php'));
        exit;
    }
    $message = '密码错误。';
}

if (empty($_SESSION['clay_admin'])) {
    $content = '';
    if ($message !== '') {
        $content .= '<p><b>' . h($message) . '</b></p>';
    }
    $content .= '<p>请输入后台密码进入博客管理页面。</p>';
    $content .= '<form method="post" action="' . h(site_url('/admin.php')) . '">';
    $content .= '密码<br><input type="password" name="password" size="30"><br><br>';
    $content .= '<input class="btn" type="submit" value="登录">';
    $content .= '</form>';
    retro_layout('后台登录', $content, '后台');
    exit;
}

if (isset($_POST['save_post'])) {
    $original = isset($_POST['original_file']) ? basename($_POST['original_file']) : '';
    save_post(
        isset($_POST['file']) ? $_POST['file'] : '',
        isset($_POST['title']) ? $_POST['title'] : '',
        isset($_POST['date']) ? $_POST['date'] : date('Y-m-d H:i'),
        isset($_POST['slug']) ? $_POST['slug'] : '',
        isset($_POST['summary']) ? $_POST['summary'] : '',
        isset($_POST['body']) ? $_POST['body'] : ''
    );
    $newFile = preg_replace('/[^a-z0-9\-]+/i', '-', strtolower(isset($_POST['file']) ? $_POST['file'] : ''));
    $newFile = trim($newFile, '-');
    if ($newFile !== '' && $original !== '' && $original !== ($newFile . '.html')) {
        delete_post($original);
    }
    $message = '文章已保存。';
}

if (isset($_GET['delete'])) {
    delete_post($_GET['delete']);
    $message = '文章已删除。';
}

$edit = null;
if (isset($_GET['edit'])) {
    $editPath = posts_path() . '/' . basename($_GET['edit']);
    if (file_exists($editPath)) {
        $edit = parse_post_file($editPath);
    }
}
$form = post_to_form($edit);
$posts = all_posts();

$content = '';
if ($message !== '') {
    $content .= '<p><b>' . h($message) . '</b></p>';
}
$content .= '<p><a class="btn" href="' . h(site_url('/admin.php?logout=1')) . '">退出登录</a></p>';
$content .= '<table width="100%" cellpadding="0" cellspacing="0"><tr valign="top">';
$content .= '<td width="58%">';
$content .= '<b>写文章 / 编辑文章</b><br><br>';
$content .= '<form method="post" action="' . h(site_url('/admin.php')) . '">';
$content .= '<input type="hidden" name="save_post" value="1">';
$content .= '<input type="hidden" name="original_file" value="' . h($form['file'] . '.html') . '">';
$content .= '文件名<br><input type="text" name="file" size="30" value="' . h($form['file']) . '"><br><br>';
$content .= '标题<br><input type="text" name="title" size="50" value="' . h($form['title']) . '"><br><br>';
$content .= '日期<br><input type="text" name="date" size="30" value="' . h($form['date']) . '"><br><br>';
$content .= '别名 Slug<br><input type="text" name="slug" size="30" value="' . h($form['slug']) . '"><br><br>';
$content .= '摘要<br><textarea name="summary" cols="48" rows="3">' . h($form['summary']) . '</textarea><br><br>';
$content .= '正文 HTML<br><textarea name="body" cols="48" rows="15">' . h($form['body']) . '</textarea><br><br>';
$content .= '<input class="btn" type="submit" value="保存文章">';
$content .= '</form>';
$content .= '</td>';
$content .= '<td width="4%">&nbsp;</td>';
$content .= '<td width="38%">';
$content .= '<b>现有文章</b><br><br>';
if ($posts) {
    foreach ($posts as $post) {
        $content .= '<div style="margin-bottom:8px;border-bottom:1px dotted #808080;padding-bottom:6px;">';
        $content .= '<a href="' . h(site_url('/index.php?post=' . urlencode($post['slug']))) . '">' . h($post['title']) . '</a><br>';
        $content .= '<span class="small">' . h($post['file']) . '</span><br>';
        $content .= '<a href="' . h(site_url('/admin.php?edit=' . urlencode($post['file']))) . '">编辑</a> | ';
        $content .= '<a href="' . h(site_url('/admin.php?delete=' . urlencode($post['file']))) . '" onclick="return confirm(\'确定删除这篇文章吗？\');">删除</a>';
        $content .= '</div>';
    }
} else {
    $content .= '还没有文章。';
}
$content .= '</td></tr></table>';

retro_layout('后台管理', $content, '后台');
