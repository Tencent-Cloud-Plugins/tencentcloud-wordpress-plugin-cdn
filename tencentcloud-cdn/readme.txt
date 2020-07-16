=== 腾讯云内容分发管理（CDN） ===
Contributors: 腾讯云中小企业产品中心（SMB Product Center of Tencent Cloud）
Tags: 腾讯云wordpress, CDN, 内容分发网络, 腾讯云
Donate link: https://cloud.tencent.com/
Requires at least: 5.0
Tested up to: 5.4.1
Requires PHP: 5.6
Stable tag: 1.0.0
License: Apache 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

== Description ==

<strong>腾讯云内容分发管理插件是自动刷新腾讯云CDN缓存的插件。</strong>

<strong>主要功能：</strong>
* 1、发布或更新文章时，将自动刷新文章页、首页、分类列表页、标签列表页相关URL缓存;
* 2、发表评论或评论被审批后，将自动刷新文章页CDN缓存;
* 3、新增手动刷新所有URL的功能
* 4、新增自定义URL刷新功能
* 5、新增自动刷新日志功能

== Installation ==

* 1、把tencentcloud-cdn文件夹上传到/wp-content/plugins/目录下<br />
* 2、在后台插件列表中激活tencentcloud-cdn<br />
* 3、在《tencentcloud-cdn设置》菜单中输入腾讯云相关参数信息<br />

== Frequently Asked Questions ==

= 如何获得腾讯云CDN密钥？ =

腾讯云CDN 密钥获得地址：https://console.cloud.tencent.com/cam/capi

= 为什么刷新CDN缓存失败=

请先查看腾讯云CDN后台管理中的刷新日志，如果没有相应日志，请禁用所有其它插件试试。

== Screenshots ==

1. screenshot-1.png

== Changelog ==

= 1.0.0 =
* 1、发布或更新文章时，将自动刷新文章页、首页、分类列表页、标签列表页相关URL缓存;
* 2、发表评论或评论被审批后，将自动刷新文章页CDN缓存;

= 1.0.1 =
* 1、新增手动刷新所有URL的功能
* 2、新增自定义URL刷新功能
* 3、新增自动刷新日志功能