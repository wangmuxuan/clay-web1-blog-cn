# CLAY 的 Web1.0 个人博客系统

一个复古风格的 PHP 个人博客系统，视觉灵感来自 1998 到 2001 年之间的网页：

- Netscape Navigator
- GeoCities
- Yahoo 1998
- Windows 95 UI
- 早期互联网门户网站

这个项目的目标不是现代化工程，而是：

- 文件少
- 易看懂
- 易维护
- 无数据库
- 无依赖安装
- 解压即可运行

适合普通 Apache 虚拟主机，兼容 PHP 7.2。

## 功能特性

- 首页
- 文章列表
- 文章详情页
- 归档页
- About 页面
- Links 页面
- RSS 输出
- visitor counter
- 单密码后台
- 每篇文章一个独立文件
- 自动扫描 `posts/` 生成列表
- 手机 / 电脑自适应

## 项目结构

```text
index.php           前台入口
admin.php           后台管理
config.php          站点配置
lib.php             公共函数
rss.php             RSS 输出
posts/              文章目录
pages/              About / Links 页面
data/               计数器等运行数据
```

## 运行环境

- PHP 7.2 或更高
- Apache 优先
- 普通虚拟主机即可
- 不需要 MySQL
- 不需要 Composer
- 不需要 Node.js

## 安装方法

### 方法一：上传压缩包

1. 将项目压缩包上传到网站根目录
2. 解压文件
3. 确认首页可访问

如果你的域名直接指向当前目录，首页通常就是：

```text
https://你的域名/
```

后台地址通常是：

```text
https://你的域名/admin.php
```

### 方法二：上传文件

1. 将以下文件和目录全部上传到网站根目录
2. 保持目录结构不变

需要上传的内容：

- `index.php`
- `admin.php`
- `config.php`
- `lib.php`
- `rss.php`
- `posts/`
- `pages/`
- `data/`

## 首次使用

打开：

```text
/admin.php
```

默认管理员密码是：

```text
changeme
```

这个密码在 `config.php` 里修改：

```php
'admin_password' => 'changeme',
```

上线前一定建议改掉。

## 站点配置

配置文件：

- `config.php`

你可以修改这些内容：

### 站点名称

```php
'site_name' => "CLAY 的个人博客",
```

### 站点副标题

```php
'tagline' => '一个运行在信息高速公路上的复古个人网站。',
```

### 邮箱

```php
'email' => 'clay@internetmail.example',
```

### 后台密码

```php
'admin_password' => 'changeme',
```

### 每页文章数量

```php
'posts_per_page' => 5,
```

### 友情链接

```php
'blogroll' => array(
    array('name' => '复古门户在线', 'url' => 'https://example.com')
)
```

## `base_url` 怎么设置

如果你的博客安装在网站根目录：

```php
'base_url' => '',
```

如果你的博客安装在子目录，比如：

```text
https://example.com/blog/
```

那么改成：

```php
'base_url' => '/blog',
```

如果你想让 RSS 里输出完整地址，也可以直接写完整域名：

```php
'base_url' => 'https://example.com/blog',
```

## 如何发布文章

打开后台：

```text
/admin.php
```

填写这些字段：

- 文件名：文章文件名，不用带 `.html`
- 标题：文章标题
- 日期：显示日期，例如 `2026-05-12 18:30`
- 别名 Slug：文章 URL 标识
- 摘要：文章列表页显示的简介
- 正文 HTML：文章正文内容

点击“保存文章”即可。

## 文章存储格式

每篇文章都是一个独立文件，放在：

- `posts/`

例如：

```text
posts/my-first-post.html
```

文件格式如下：

```html
Title: 我的第一篇文章
Date: 2026-05-12 18:30
Slug: wo-de-di-yi-pian-wen-zhang
Summary: 这是一篇测试文章。
----
<p>这里是正文。</p>
<p>可以直接写 HTML。</p>
```

说明：

- `Title` 是标题
- `Date` 是日期
- `Slug` 是文章链接标识
- `Summary` 是摘要
- `----` 下面就是正文

## 正文支持什么格式

当前版本正文直接使用 HTML。

可以写：

- `<p>`
- `<br>`
- `<b>`
- `<i>`
- `<a>`
- `<img>`
- `<blockquote>`
- `<pre>`

示例：

```html
<p>这是一段文字。</p>
<blockquote>这是一段引用。</blockquote>
<pre>这是一段代码。</pre>
```

## 页面内容在哪里改

### 关于页面

文件：

- `pages/about.html`

### 友情链接页面

文件：

- `pages/links.html`

你可以直接用任意文本编辑器修改它们。

## RSS 地址

RSS 文件：

- `rss.php`

访问地址通常是：

```text
https://你的域名/rss.php
```

## 访问计数器

访客计数文件在：

- `data/counter.txt`

说明：

- 第一次访问时会自动创建
- 使用 Cookie 防止同一用户短时间重复累加
- 如果要重置计数，直接修改这个文件内容即可

## 后台说明

后台入口：

- `admin.php`

功能包括：

- 登录
- 新增文章
- 编辑文章
- 删除文章
- 查看当前已有文章

这个后台故意保持简单，不做复杂权限系统，也不依赖数据库。

## 手机与电脑自适应

这个版本保留了桌面端 900px 的 Web1.0 三栏布局，同时加了轻量的响应式规则：

- 电脑端保持复古三栏
- 手机端自动改成单栏
- 输入框与文本域自动缩放
- 导航按钮自动换行

所以它既保留老网页气质，也能正常在手机里浏览。

## GitHub 使用建议

如果你准备上传到 GitHub，建议保留这些文件：

- `index.php`
- `admin.php`
- `config.php`
- `lib.php`
- `rss.php`
- `posts/`
- `pages/`
- `data/`
- `README.md`

运行过程中会变化的文件主要是：

- `data/counter.txt`

因此仓库里一般不需要频繁提交这个计数器文件。

## 常见问题

### 1. 页面出现乱码怎么办？

请确认：

- 服务器和文件都使用 UTF-8
- 虚拟主机没有强制改成其他编码
- 编辑器保存时不要用 ANSI 或 GBK

### 2. 后台打不开怎么办？

请确认：

- 访问的是 `/admin.php`
- 主机支持 PHP
- `config.php` 没有写错

### 3. 文章不显示怎么办？

请确认：

- 文章文件在 `posts/` 目录
- 文件扩展名是 `.html`
- 文件头格式正确
- `----` 分隔线存在

### 4. RSS 链接不对怎么办？

请检查 `config.php` 中的 `base_url` 设置。

### 5. 共享主机上能跑吗？

可以。这个项目就是按共享主机思路做的。

## 安全提醒

这是一个轻量个人博客系统，不是复杂 CMS。

建议你至少做这几件事：

- 修改默认后台密码
- 不要把后台密码设得太简单
- 定期备份 `posts/`、`pages/`、`config.php`
- 如果主机支持，给后台目录加额外访问限制

## 备份建议

建议重点备份这些内容：

- `posts/`
- `pages/`
- `config.php`
- `data/counter.txt`

## 适合谁使用

这个项目适合：

- 想要一个简单博客的人
- 想做 Web1.0 / 复古风网站的人
- 使用虚拟主机的人
- 不想装数据库和依赖的人
- 希望源码几年后依然容易维护的人

## 开源许可

本项目使用 MIT License。

许可文件见：

- `LICENSE`
