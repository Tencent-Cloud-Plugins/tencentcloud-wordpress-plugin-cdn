<?php
/**
 * Plugin Name: tencentcloud-cdn
 * Plugin URI: https://openapp.qq.com/
 * Description: 通过腾讯云内容分发网络服务自动刷新网站的变化内容，提升用户的浏览体验。
 * Version: 1.0.2
 * Author: 腾讯云
 * Author URI: https://cloud.tencent.com/
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// Check that the file is not accessed directly.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

defined('TENCENT_WORDPRESS_CDN_VERSION') or define('TENCENT_WORDPRESS_CDN_VERSION', '1.0.2');
defined('TENCENT_WORDPRESS_CDN_NAME') or define('TENCENT_WORDPRESS_CDN_NAME', 'tencentcloud-cdn');

defined('TENCENT_WORDPRESS_CDN_PLUGIN_DIR') or define('TENCENT_WORDPRESS_CDN_PLUGIN_DIR', plugin_dir_path(__FILE__));
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR', TENCENT_WORDPRESS_CDN_PLUGIN_DIR . 'common' . '/');
defined('TENCENT_WORDPRESS_CDN_PLUGIN_INC_DIR') or define('TENCENT_WORDPRESS_CDN_PLUGIN_INC_DIR', TENCENT_WORDPRESS_CDN_PLUGIN_DIR . 'includes' . '/');
defined('TENCENT_WORDPRESS_CDN_PLUGIN_VENDOR_DIR') or define('TENCENT_WORDPRESS_CDN_PLUGIN_VENDOR_DIR', TENCENT_WORDPRESS_CDN_PLUGIN_INC_DIR . 'vendor' . '/');

defined('TENCENT_WORDPRESS_PLUGINS_URL') or define('TENCENT_WORDPRESS_PLUGINS_URL', plugins_url() . '/');
defined('TENCENT_WORDPRESS_CDN_PLUGIN_URL') or define('TENCENT_WORDPRESS_CDN_PLUGIN_URL', TENCENT_WORDPRESS_PLUGINS_URL . basename(__DIR__) . '/');

defined('TENCENT_WORDPRESS_CDN_PLUGIN_ASSETS_URL') or define('TENCENT_WORDPRESS_CDN_PLUGIN_ASSETS_URL', TENCENT_WORDPRESS_CDN_PLUGIN_URL . 'assets' . '/');
defined('TENCENT_WORDPRESS_CDN_PLUGIN_JS_URL') or define('TENCENT_WORDPRESS_CDN_PLUGIN_JS_URL', TENCENT_WORDPRESS_CDN_PLUGIN_ASSETS_URL . 'javascript' . '/');
defined('TENCENT_WORDPRESS_CDN_PLUGIN_CSS_URL') or define('TENCENT_WORDPRESS_CDN_PLUGIN_CSS_URL', TENCENT_WORDPRESS_CDN_PLUGIN_ASSETS_URL . 'css' . '/');

defined('TENCENT_WORDPRESS_PLUGINS_COMMON_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_URL', TENCENT_WORDPRESS_CDN_PLUGIN_URL . 'common' . '/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL', TENCENT_WORDPRESS_PLUGINS_COMMON_URL . 'css' . '/');

defined('TENCENT_WORDPRESS_CDN_OPTIONS') or define('TENCENT_WORDPRESS_CDN_OPTIONS', 'tencent_wordpress_cdn_options');
defined('TENCENT_WORDPRESS_CDN_ADMIN_AJAX') or define('TENCENT_WORDPRESS_CDN_ADMIN_AJAX', 'admin-ajax.php');

defined('TENCENT_WORDPRESS_CDN_SHOW_NAME') or define('TENCENT_WORDPRESS_CDN_SHOW_NAME', 'tencentcloud-cdn');
defined('TENCENT_WORDPRESS_CDN_NICK_NAME') or define('TENCENT_WORDPRESS_CDN_NICK_NAME', '腾讯云内容分发网络（CDN）插件');
defined('TENCENT_WORDPRESS_CDN_RELATIVE_PATH') or define('TENCENT_WORDPRESS_CDN_RELATIVE_PATH', basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__));

require_once TENCENT_WORDPRESS_CDN_PLUGIN_DIR . 'class-tencent-cloud-cdn.php';
register_activation_hook(__FILE__, array('TencentWordpressCDN', 'tcwpcdnActivatePlugin'));
register_deactivation_hook(__FILE__, array('TencentWordpressCDN', 'tcwpcdnDeactivePlugin'));
add_action('init', array('TencentWordpressCDN', 'init'));
