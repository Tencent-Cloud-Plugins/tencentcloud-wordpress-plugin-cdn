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
// Check that the file is not accessed directly.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}
$tcwpcdn_options = get_option(TENCENT_WORDPRESS_CDN_OPTIONS);
$ajax_url = admin_url(TENCENT_WORDPRESS_CDN_ADMIN_AJAX);
?>
<!--add style file-->
<link rel="stylesheet" type="text/css" href="<?php echo TENCENT_WORDPRESS_CDN_PLUGIN_CSS_URL; ?>bootstrap.min.css">
<style type="text/css">
    .dashicons {
        vertical-align: middle;
        position: relative;
        right: 30px;
    }

    .setting_page_footer {
        text-align: center;
        line-height: 50px;
    }
    .tencent_cdn_secret_lable {
        padding-left:30px;
    }

    .div_custom_switch_padding_top {
        padding-top: 15px;
        padding-left: 75px
    }
</style>
<!--TencentCloud COS Plugin Setting Page-->
<?php
    if (isset($tcwpcdn_options['activation']) && $tcwpcdn_options['activation'] === true) {
        $notice = '腾讯云内容分发管理（CDN）插件生效中';
    } elseif (isset($tcwpcdn_options['activation']) && $tcwpcdn_options['activation'] === false) {
        $notice = '腾讯云内容分发管理（CDN）插件开启中';
    } else {
        $notice = '腾讯云内容分发管理（CDN）插件已安装';
    }
    echo '<div id="message" class="updated notice is-dismissible" style="margin-bottom: 1%;margin-left:0%;"><p>' . $notice . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">忽略此通知。</span></button></div>';
?>
<div class="bs-docs-section">
    <div class="row">
        <div class="col-lg-12">
            <div class="page-header ">
                <h1 id="forms">腾讯云内容分发网络（CDN）插件</h1>
            </div>
            <p>自动刷新腾讯云CDN缓存的插件</p>
        </div>
    </div>
    <div class="postbox">
        <div class="row">
            <div class="col-lg-9">
                <form id="tcwpform_cdn_info_set" data-ajax-url="<?php echo $ajax_url ?>" name="tcwpcosform" method="post"
                      class="bs-component">
                    <!-- Setting Option no_local_file-->
                    <div class="row form-group">
                        <label class="col-form-label col-lg-2 tencent_cdn_secret_lable" for="inputDefault">自定义密钥</label>

                        <div class="custom-control custom-switch div_custom_switch_padding_top">
                            <input name="customize_secret" type="checkbox" class="custom-control-input" id="tencent_wordpress_cdn_customize_secret"
                                <?php
                                if (isset($tcwpcdn_options)
                                    && isset($tcwpcdn_options['customize_secret'])
                                    && $tcwpcdn_options['customize_secret'] === true) {
                                    echo 'checked="true"';
                                }
                                ?>
                            >
                            <label class="custom-control-label" for="tencent_wordpress_cdn_customize_secret">为本插件就配置不同于全局腾讯云密钥的单独密钥</label>
                        </div>

                    </div>
                    <!-- Setting Option SecretId-->
                    <div class="form-group">
                        <label class="col-form-label col-lg-2" for="inputDefault">SecretId</label>
                        <input id="tencent_wordpress_cdn_secret_id" name="secret_id" type="password" class="col-lg-5 is-invalid"
                               placeholder="SecretId"
                            <?php
                            if (!isset($tcwpcdn_options) || !isset($tcwpcdn_options['customize_secret'])
                                || $tcwpcdn_options['customize_secret'] === false) {
                                echo 'disabled="true"';
                            }
                            ?>
                               value="<?php if (isset($tcwpcdn_options) && isset($tcwpcdn_options['secret_id'])) {
                                   echo esc_attr($tcwpcdn_options['secret_id']);
                               } ?>">

                        <span id="cdn_secret_id_change_type" class="dashicons dashicons-hidden"></span>
                        <span id="span_cdn_secret_id" class="invalid-feedback offset-lg-2"></span>
                    </div>
                    <!-- Setting Option SecretKey-->
                    <div class="form-group">
                        <label class="col-form-label col-lg-2" for="inputDefault">SecretKey</label>
                        <input id="tencent_wordpress_cdn_secret_key" name="secret_key" type="password" class="col-lg-5 is-invalid"
                               placeholder="SecretKey"
                            <?php
                            if (!isset($tcwpcdn_options) || !isset($tcwpcdn_options['customize_secret'])
                                || $tcwpcdn_options['customize_secret'] === false) {
                                echo 'disabled="true"';
                            }
                            ?>
                               value="<?php if (isset($tcwpcdn_options) && isset($tcwpcdn_options['secret_key'])) {
                                   echo esc_attr($tcwpcdn_options['secret_key']);
                               } ?>">
                        <span id="cdn_secret_key_change_type" class="dashicons dashicons-hidden"></span>
                        <span id="span_cdn_secret_key" class="invalid-feedback offset-lg-2"></span>
                        <div class="offset-lg-2">
                            <p>访问 <a href="https://console.qcloud.com/cam/capi" target="_blank">密钥管理</a>获取
                                SecretId和SecretKey或通过"新建密钥"创建密钥串</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Saving Options-->
    <button id="form_cdn_button_save" type="button" class="btn btn-primary">保存配置</button>
    <span id="span_button_save" class="invalid-feedback offset-lg-2"></span>
    <hr class="my-4">
    <div class="row">
        <div class="col-lg-9">
            <form id="wpcosform_cdn_info_refresh" name="tcwpcosform_cos_info_replace" method="post"
                  class="bs-component">
                <div class="form-group">
                    <label class="col-form-label col-lg-2" for="inputDefault">接口测试</label>
                    <button id="form_cdn_button_info_refresh" type="button" class="btn btn-primary">一键测试</button>
                    <span id="span_cdn_info_refresh" class="invalid-feedback offset-lg-2"></span>
                </div>
            </form>
        </div>
    </div>
    <br>
    <div class="setting_page_footer">
        <a href="https://openapp.qq.com/" target="_blank">文档中心</a> | <a href="https://github.com/Tencent-Cloud-Plugins/" target="_blank">GitHub</a> | <a
                href="https://support.qq.com/product/164613" target="_blank">反馈建议</a>
    </div>
</div>
