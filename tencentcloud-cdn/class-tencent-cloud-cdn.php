<?php
/*
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
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';
require_once TENCENT_WORDPRESS_CDN_PLUGIN_VENDOR_DIR . 'autoload.php';

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Cdn\V20180606\CdnClient;
use TencentCloud\Cdn\V20180606\Models\PurgeUrlsCacheRequest;
use TencentCloud\Cdn\V20180606\Models\PurgePathCacheRequest;
use TencentCloud\Cdn\V20180606\Models\DescribeDomainsRequest;
use TencentCloud\Cdn\V20180606\Models\DescribePurgeTasksRequest;

class TencentWordpressCDN
{
    private static $initiated = false;
    private static $version = '';
    private static $plugin_type = 'cdn';

    /**
     * 初始化函数 单例模式
     */
    public static function init()
    {
        if (!self::$initiated) {
            self::initHooks();
        }
        if (class_exists('TencentWordpressPluginsSettingActions')) {
            TencentWordpressPluginsSettingActions::init();
        }
    }

    /**
     * 绑定插件在Wordpress中的钩子
     */
    private static function initHooks()
    {
        self::$initiated = true;
        self::$version = get_bloginfo('version');
        // 文章状态修改（新增，修改文章），进行CDN刷新
        add_action('transition_post_status', array('TencentWordpressCDN', 'tcwpcdnRefreshCdnPost'), 10, 3);

        // 发表评论，进行CDN刷新
        add_action('comment_post', array('TencentWordpressCDN', 'tcwpcdnRefreshCdnComment'), 10);

        // 审核评论，进行CDN刷新ßßß
        add_action('comment_unapproved_to_approved', array('TencentWordpressCDN', 'tcwpcdnRefreshCdnCommentApproved'), 10);

        // 管理员插件配置页面
        if (is_admin()) {
            add_action('admin_menu', array('TencentWordpressCDN', 'tcwpcdnAddSettingPage'));
            add_filter('plugin_action_links', array('TencentWordpressCDN', 'tcwpcdnSetPluginActionLinks'), 10, 2);
        }
        // 弹出错误提示
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (!empty($tcwpcdn_options) && isset($tcwpcdn_options['json']) && $tcwpcdn_options['json'] == 'error') {
            add_action(TENCENT_WORDPRESS_CDN_OPTIONS, array('TencentWordpressCDN', 'tcwpcdnAdminNoticeError'));
        }

        // js脚本引入
        add_action('admin_enqueue_scripts', array('TencentWordpressCDN', 'tcwpcdnLoadScriptEnqueue'));

        // 保存COS插件配置信息
        add_action('wp_ajax_save_cdn_options', array('TencentWordpressCDN', 'tcwpcdnSaveOptions'));

        // CDN接口测试
        add_action('wp_ajax_refresh_cdn_information', array('TencentWordpressCDN', 'tcwpcdnTestCdnRequest'));

        add_action('wp_ajax_refresh_cdn_dir', array('TencentWordpressCDN', 'tcwpcdnDirectionFresh'));

        add_action('wp_ajax_refresh_cdn_url', array('TencentWordpressCDN', 'tcwpcdnURLFresh'));

        add_action('wp_ajax_get_refresh_log', array('TencentWordpressCDN', 'tcwpcdnGetRefreshLog'));
    }

    public static function getTencentCloudWordPressStaticData($action)
    {
        $site_id = TencentWordpressPluginsSettingActions::getWordPressSiteID();
        $site_url = TencentWordpressPluginsSettingActions::getWordPressSiteUrl();
        $site_app = TencentWordpressPluginsSettingActions::getWordPressSiteApp();
        $static_data['action'] = $action;
        $static_data['plugin_type'] = self::$plugin_type;
        $static_data['data'] = array(
            'site_id'  => $site_id,
            'site_url' => $site_url,
            'site_app' => $site_app
        );

        $common_option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if ($tcwpcdn_options['customize_secret'] === true && isset($tcwpcdn_options['secret_id']) && isset($tcwpcdn_options['secret_key'])) {
            $secret_id = $tcwpcdn_options['secret_id'];
            $secret_key = $tcwpcdn_options['secret_key'];
        } elseif ($common_option['site_report_on'] === true && isset($common_option['secret_id']) && isset($common_option['secret_key'])) {
            $secret_id = $common_option['secret_id'];
            $secret_key = $common_option['secret_key'];
        } else {
            $secret_id = '';
            $secret_key = '';
        }
        $static_data['data']['uin'] = TencentWordpressPluginsSettingActions::getUserUinBySecret($secret_id, $secret_key);

        $static_data['data']['cust_sec_on'] = ((int)$tcwpcdn_options['customize_secret']) === 1 ? 1 : 2;
        return $static_data;
    }

    /**
     * 开启插件
     */
    public static function tcwpcdnActivatePlugin()
    {
        $init_options = array(
            'version' => TENCENT_WORDPRESS_CDN_VERSION,
            'customize_secret' => false,
            'activation' => false,
            'secret_id' => "",
            'secret_key' => "",
            'error' => '',
            'error_json' => ''
        );
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (empty($tcwpcdn_options)) {
            add_option(TENCENT_WORDPRESS_CDN_OPTIONS, $init_options);
        } else {
            if (true === self::tcwpcdnTestCdnAPI($tcwpcdn_options)) {
                $tcwpcdn_options['activation'] = true;
            }
            $tcwpcdn_options = array_merge($init_options, $tcwpcdn_options);
            update_option(TENCENT_WORDPRESS_CDN_OPTIONS, $tcwpcdn_options);
        }

        $plugin = array(
            'plugin_name' => TENCENT_WORDPRESS_CDN_SHOW_NAME,
            'nick_name' => TENCENT_WORDPRESS_CDN_NICK_NAME,
            'plugin_dir' => TENCENT_WORDPRESS_CDN_RELATIVE_PATH,
            'href' => "admin.php?page=tencent_wordpress_plugin_cdn",
            'activation' => 'true',
            'status' => 'true',
            'download_url' => ''
        );
        // 插件信息存到数据库中，在插件中心中使用
        TencentWordpressPluginsSettingActions::prepareTencentWordressPluginsDB($plugin);

        // 第一次开启插件则生成一个全站唯一的站点id，保存在公共的option中
        TencentWordpressPluginsSettingActions::setWordPressSiteID();
        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('activate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);
    }

    /**
     * 禁止插件
     */
    public static function tcwpcdnDeactivePlugin()
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (!empty($tcwpcdn_options) && isset($tcwpcdn_options['activation'])) {
            $tcwpcdn_options['activation'] = false;
            update_option(TENCENT_WORDPRESS_CDN_OPTIONS, $tcwpcdn_options);
        }
        TencentWordpressPluginsSettingActions::disableTencentWordpressPlugin(TENCENT_WORDPRESS_CDN_SHOW_NAME);

        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('deactivate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);
    }

    /**
     *文章状态更新触发CDN刷新
     */
    public static function tcwpcdnRefreshCdnPost($new_status, $old_status, $post)
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if ((isset($tcwpcdn_options['activation']) && $tcwpcdn_options['activation'] === false)
            || $post->post_type != 'post') {
            return;
        }

        $client = self::getCndClient($tcwpcdn_options);
        if (false === $client) {
            return;
        }

        $url = get_permalink($post->ID);
        $params = array();
        $params['Urls'][] = $url;

        // 发布文章时进行CDN刷新
        if ($new_status == 'publish' && $old_status != 'publish') {
            // 首页
            $params['Urls'][] = home_url();

            if ($categories = wp_get_post_categories($post->ID)) {
                foreach ($categories as $category_id) {
                    // 分类页面
                    $params['Urls'][] = get_category_link($category_id);
                }
            }

            try {
                $req = new PurgeUrlsCacheRequest();
                $params = json_encode($params);
                $req->fromJsonString($params);
                $resp = (array)$client->PurgeUrlsCache($req);
                if (isset($resp['TaskId']) && isset($resp['RequestId'])) {
                    wp_send_json_success();
                }
                return;
            } catch (TencentCloudSDKException $e) {
                $err = array(
                    'ErrorCode' => $e->getErrorCode(),
                    "Message" => $e->getMessage()
                );
                return;
            }
        }

        // 更新文章时进行CDN刷新
        if ($new_status == 'publish' && $old_status == 'publish') {
            try {
                $req = new PurgeUrlsCacheRequest();
                $params = json_encode($params);
                $req->fromJsonString($params);
                $resp = (array)$client->PurgeUrlsCache($req);
                if (isset($resp['TaskId']) && isset($resp['RequestId'])) {
                    wp_send_json_success();
                }
                return;
            } catch (TencentCloudSDKException $e) {
                $err = array(
                    'ErrorCode' => $e->getErrorCode(),
                    "Message" => $e->getMessage()
                );
                return;
            }
        }

        unset($client, $url, $params);
    }

    /**
     * 发布评论/回复评论
     * @param $comment_id
     */
    public static function tcwpcdnRefreshCdnComment($comment_id)
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (empty($tcwpcdn_options) || $tcwpcdn_options['activation'] === false) {
            return;
        }
        $comment = get_comment($comment_id);
        $url = get_permalink($comment->comment_post_ID);
        try {
            $client = self::getCndClient($tcwpcdn_options);
            if (false === $client) {
                $err = array(
                    "Message" => 'create CdnClient failed'
                );
                return;
            }

            $req = new PurgeUrlsCacheRequest();
            $params = array();
            $params['Urls'][] = $url;

            $params = json_encode($params);
            $req->fromJsonString($params);
            $resp = (array)$client->PurgeUrlsCache($req);
            if (isset($resp['TaskId']) && isset($resp['RequestId'])) {
                return;
            }
            return;
        } catch (TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            return;
        }
    }

    /**
     * 评论审核通过
     * @param $comment
     */
    public static function tcwpcdnRefreshCdnCommentApproved($comment)
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (empty($tcwpcdn_options) || $tcwpcdn_options['activation'] === false) {
            return;
        }
        $url = get_permalink($comment->comment_post_ID);

        try {
            $client = self::getCndClient($tcwpcdn_options);
            if (false === $client) {
                $err = array(
                    "Message" => 'create CdnClient failed'
                );
                return;
            }

            $req = new PurgeUrlsCacheRequest();
            $params = array();
            $params['Urls'][] = $url;

            $params = json_encode($params);
            $req->fromJsonString($params);

            $resp = (array)$client->PurgeUrlsCache($req);
            if (!isset($resp['TaskId']) || !isset($resp['RequestId'])) {
                return;
            }

        } catch (TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            return;
        }
    }

    /**
     * 将插件的配置页面加入到设置列表中
     */
    public static function tcwpcdnAddSettingPage()
    {
        TencentWordpressPluginsSettingActions::addTencentWordpressCommonSettingPage();
        $pagehook = add_submenu_page('TencentWordpressPluginsCommonSettingPage', '内容分发网络', '内容分发网络', 'manage_options', 'tencent_wordpress_plugin_cdn', array('TencentWordpressCDN', 'tcwpcdnSettingPage'));
        add_action( 'admin_print_styles-'.$pagehook, array('TencentWordpressCDN', 'tcwpcdnLoadCssForSettingPage'));

    }

    public static function tcwpcdnLoadCssForSettingPage() {
        wp_enqueue_style('tencent_cloud_cdn_admin_css',TENCENT_WORDPRESS_CDN_PLUGIN_CSS_URL.'bootstrap.min.css');
    }

    /**
     * 插件配置信息操作页面
     */
    public static function tcwpcdnSettingPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sorry, you are not allowed to manage options for this site.'));
        }
        include_once TENCENT_WORDPRESS_CDN_PLUGIN_DIR . 'tencent-cloud-cdn-setting-page.php';
    }

    /**
     * 插件列表加入"配置"按钮
     * @param $links string 插件配置页面的链接地址
     * @param $file string 配置页面路径
     * @return mixed
     */
    public static function tcwpcdnSetPluginActionLinks($links, $file)
    {
        if ($file == plugin_basename(TENCENT_WORDPRESS_CDN_PLUGIN_DIR . 'tencentcloud-cdn.php')) {
            $links[] = '<a href="admin.php?page=tencent_wordpress_plugin_cdn">' . __('Settings') . '</a>';
        }
        return $links;
    }

    /**
     * CDN刷新失败
     */
    public static function tcwpcdnAdminNoticeError()
    {
        $class = 'notice notice-error';
        $message = esc_html__('腾讯云CDN刷新失败，请正确设置! ', 'Tencent-Wordpress-CDN');
        $support_url = esc_url('https://www.tencent.com');
        $support_text = esc_html__('获取支持与帮助', 'tencent-wordpress-cdn');
        printf('<div class="%1$s"><p>%2$s <a target="_blank" href="%3$s">%4$s</a></p></div>', esc_attr($class), esc_html($message), $support_url, $support_text);
    }

    /**
     * 加载js脚本
     */
    public static function tcwpcdnLoadScriptEnqueue()
    {
        wp_register_script('back_admin_tcwp_cdn_setting', TENCENT_WORDPRESS_CDN_PLUGIN_JS_URL . 'tcwp_cdn_setting.js', array('jquery'), '2.1', true);
        wp_enqueue_script('back_admin_tcwp_cdn_setting');
    }

    /**
     * 保存配置参数
     */
    public static function tcwpcdnSaveOptions()
    {
        if (empty($_POST) || empty($_POST['action'])) {
            wp_send_json_error();
        }
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (isset($_POST['customize_secret']) && $_POST['customize_secret'] === 'true') {
            $tcwpcdn_options['customize_secret'] = true;
            $tcwpcdn_options['secret_id'] = sanitize_text_field($_POST['secret_id']);
            $tcwpcdn_options['secret_key'] = sanitize_text_field($_POST['secret_key']);
        } else {
            $tcwpcdn_options['customize_secret'] = false;
        }

        if (true === self::tcwpcdnTestCdnAPI($tcwpcdn_options)) {
            $tcwpcdn_options['activation'] = true;
        } else {
            $tcwpcdn_options['activation'] = false;
        }

        update_option(TENCENT_WORDPRESS_CDN_OPTIONS, $tcwpcdn_options);

        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('save_config');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);

        wp_send_json_success();
    }

    /**
     * 一键测试
     */
    public static function tcwpcdnTestCdnRequest()
    {
        if (isset($_POST['customize_secret']) && $_POST['customize_secret'] == 'true') {
            $option = array(
                'customize_secret' => true,
                'secret_id' => sanitize_text_field($_POST['secret_id']),
                'secret_key' => sanitize_text_field($_POST['secret_key'])
            );
        } else {
            $option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        }

        if (true === self::tcwpcdnTestCdnAPI($option)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * 验证CDN刷新接口是否生效
     */
    public static function tcwpcdnTestCdnAPI($option)
    {

        try {
            $client = self::getCndClient($option);

            if (false === $client) {
                $err = array(
                    "Message" => 'create CdnClient failed'
                );
                return false;
            }

            $req = new DescribeDomainsRequest();
            $params = '{}';
            $req->fromJsonString($params);
            $resp = (array)($client->DescribeDomains($req));
            if (!empty($resp) && $resp['TotalNumber'] > 0) {
                return true;
            }
            return false;
        } catch (TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            return false;
        }
    }

    public static function tcwpcdnDirectionFresh()
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (isset($tcwpcdn_options['customize_secret']) && $tcwpcdn_options['customize_secret'] === true) {
            $option = $tcwpcdn_options;
        } else {
            $option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        }

        $flush_type = sanitize_text_field($_POST['flush_type']);
        $fresh_all = sanitize_text_field($_POST['fresh_all']);
        if ($fresh_all === 'true') {
            $dirs = array(
                home_url()
            );
        } else {
            $dirs = sanitize_text_field($_POST['cdn_dir']);
            $dirs = explode(' ', $dirs);
        }

        try {
            $cred = new Credential($option['secret_id'], $option['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cdn.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CdnClient($cred, "", $clientProfile);
            $req = new PurgePathCacheRequest();
            $params['Paths'] = $dirs;
            $params['FlushType'] = $flush_type;
            $params = \GuzzleHttp\json_encode($params);
            $req->fromJsonString($params);
            $resp = ($client->PurgePathCache($req));
            if (isset($resp) && isset($resp->TaskId)) {
                wp_send_json_success();
            }
            wp_send_json_error();
        }
        catch(TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            wp_send_json_error($err);
        }
    }

    public static function tcwpcdnURLFresh()
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (isset($tcwpcdn_options['customize_secret']) && $tcwpcdn_options['customize_secret'] === true) {
            $option = $tcwpcdn_options;
        } else {
            $option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        }
        $fresh_all = sanitize_text_field($_POST['fresh_all']);
        if ($fresh_all === 'true') {
            $urls = array(
                home_url()
            );
        } else {
            $urls = sanitize_text_field($_POST['cdn_url']);
            $urls = explode(' ', $urls);
        }
        try {
            $cred = new Credential($option['secret_id'], $option['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cdn.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CdnClient($cred, "", $clientProfile);

            $req = new PurgeUrlsCacheRequest();
            $params['Urls'] = $urls;
            $params = \GuzzleHttp\json_encode($params);

            $req->fromJsonString($params);
            $resp = ($client->PurgeUrlsCache($req));
            if (isset($resp) && isset($resp->TaskId)) {
                wp_send_json_success();
            }
            wp_send_json_error();
        }
        catch(TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            wp_send_json_error($err);
        }
    }

    public static function tcwpcdnGetRefreshLog()
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (isset($tcwpcdn_options['customize_secret']) && $tcwpcdn_options['customize_secret'] === true) {
            $option = $tcwpcdn_options;
        } else {
            $option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        }
        $purge_type = sanitize_text_field($_POST['purge_type']);
        $start_time = str_replace('T', ' ', sanitize_text_field($_POST['start_time'])) . ":00";
        $end_time = str_replace('T', ' ', sanitize_text_field($_POST['end_time'])) . ":00";
        $offset = isset($_POST['offset']) ? sanitize_text_field($_POST['offset']) : 0;
        $limit = (int)sanitize_text_field($_POST['log_number']);  // 必须为整数类型

        try {

            $cred = new Credential($option['secret_id'], $option['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cdn.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CdnClient($cred, "", $clientProfile);

            $req = new DescribePurgeTasksRequest();

            $params['PurgeType'] = $purge_type;
            $params['StartTime'] = $start_time;
            $params['EndTime'] = $end_time;
            $params['Offset'] = $offset;
            $params['Limit'] = $limit;

            $params = \GuzzleHttp\json_encode($params);
            $req->fromJsonString($params);
            $resp = $client->DescribePurgeTasks($req);
            if (isset($resp) && isset($resp->PurgeLogs)) {
                wp_send_json_success($resp);
            }
            wp_send_json_error();
        }
        catch(TencentCloudSDKException $e) {
            $err = array(
                'ErrorCode' => $e->getErrorCode(),
                "Message" => $e->getMessage()
            );
            wp_send_json_error($err);
        }
    }

    /**
     * 获取CDN客户端对象
     * @param $options array 配置信息
     * @return bool|CdnClient
     */
    public static function getCndClient($options)
    {
        $tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
        if (isset($tcwpcdn_options) && isset($tcwpcdn_options['customize_secret']) &&
            $tcwpcdn_options['customize_secret'] === false) {
            $tcwp_common_options = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
            $options['secret_id'] = $tcwp_common_options['secret_id'];
            $options['secret_key'] = $tcwp_common_options['secret_key'];
        }

        if (empty($options['secret_id']) || empty($options['secret_key'])) {
            return false;
        }
        try {
            $cred = new Credential($options['secret_id'], $options['secret_key']);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cdn.tencentcloudapi.com");

            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            return new CdnClient($cred, "", $clientProfile);
        } catch (TencentCloudSDKException $e) {
            return false;
        }
    }
}
