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

    .select_region_style {
        width: 225px;
    }

    .flush_log_block {
        margin-top: 20px;
        display: none;
    }
    .fresh_all_dir_switch {
        margin-top: 18px;
        padding-right:40px;
    }
    .fresh_all_url_switch {
        margin-top: 18px;
        padding-right:40px;
    }
    .fresh_all_dir_lable {
        padding-top: 3px;
    }
    .fresh_dir_textarea {
        margin-left: 15px;
    }
    .show_log_count {
        margin-left: 10px;
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
<div class="wrap">
    <div class="bs-docs-section">
        <div class="row">
            <div class="col-lg-12">
                <div class="page-header ">
                    <h1 id="forms">腾讯云内容分发网络（CDN）插件</h1>
                </div>
                <p>自动刷新腾讯云CDN缓存的插件</p>
            </div>
        </div>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a id="tencent_wordpress_cdn_config_id" class="nav-link active" data-toggle="tab"
                   href="#tencent_wordpress_cdn_config_home">插件配置</a>
            </li>
            <li class="nav-item">
                <a id="tencent_wordpress_cdn_dirfresh_id" class="nav-link" data-toggle="tab"
                   href="#tencent_wordpress_cdn_dirfresh_home">目录刷新</a>
            </li>
            <li class="nav-item">
                <a id="tencent_wordpress_cdn_urlfresh_id" class="nav-link" data-toggle="tab"
                   href="#tencent_wordpress_cdn_urlfresh_home">URL刷新</a>
            </li>
            <li class="nav-item">
                <a id="tencent_wordpress_cdn_fresh_log_id" class="nav-link" data-toggle="tab"
                   href="#tencent_wordpress_cdn_fresh_log_home">刷新日志</a>
            </li>
        </ul>

        <div id="myTabContent" class="tab-content">
            <!-- 保存插件配置 -->
            <div class="tab-pane fade active show" id="tencent_wordpress_cdn_config_home">
                <div class="postbox">
                    <div class="row">
                        <div class="col-lg-9">
                            <form id="tcwpform_cdn_info_set" data-ajax-url="<?php echo $ajax_url ?>" name="tcwpcosform"
                                  method="post"
                                  class="bs-component">
                                <!-- Setting Option no_local_file-->
                                <div class="row form-group">
                                    <label class="col-form-label col-lg-2 tencent_cdn_secret_lable" for="inputDefault"><h5>自定义密钥</h5></label>

                                    <div class="custom-control custom-switch div_custom_switch_padding_top">
                                        <input name="customize_secret" type="checkbox" class="custom-control-input"
                                               id="tencent_wordpress_cdn_customize_secret"
                                            <?php
                                            if (isset($tcwpcdn_options)
                                                && isset($tcwpcdn_options['customize_secret'])
                                                && $tcwpcdn_options['customize_secret'] === true) {
                                                echo 'checked="true"';
                                            }
                                            ?>
                                        >
                                        <label class="custom-control-label"
                                               for="tencent_wordpress_cdn_customize_secret">为本插件就配置不同于全局腾讯云密钥的单独密钥</label>
                                    </div>

                                </div>
                                <!-- Setting Option SecretId-->
                                <div class="form-group">
                                    <label class="col-form-label col-lg-2" for="inputDefault"><h5>SecretId</h5></label>
                                    <input id="tencent_wordpress_cdn_secret_id" name="secret_id" type="password"
                                           class="col-lg-5 is-invalid"
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
                                    <label class="col-form-label col-lg-2" for="inputDefault"><h5>SecretKey</h5></label>
                                    <input id="tencent_wordpress_cdn_secret_key" name="secret_key" type="password"
                                           class="col-lg-5 is-invalid"
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
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>接口测试</h5></label>
                                <button id="form_cdn_button_info_refresh" type="button" class="btn btn-primary">一键测试
                                </button>
                                <span id="span_cdn_info_refresh" class="invalid-feedback offset-lg-2"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tencent_wordpress_cdn_dirfresh_home">
                <!-- 目录刷新 -->
                <div class="postbox">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2 tencent_cdn_secret_lable" for="inputDefault"><h5>全站刷新</h5></label>
                                <div class="custom-control custom-switch fresh_all_dir_switch">
                                    <input type="checkbox" class="custom-control-input" name="switch_fresh_all_dir"
                                           id="switch_fresh_all_dir_id">
                                    <label class="custom-control-label fresh_all_dir_lable" for="switch_fresh_all_dir_id">开启全站点目录的CDN刷新</label>
                                </div>
                            </div>
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2 tencent_cdn_secret_lable" for="inputDefault"><h5>选择刷新方式</h5></label>
                                <div class="custom-control custom-radio fresh_all_dir_switch">
                                    <input type="radio" id="dir_fresh_type_flush" name="dir_fresh_radio"
                                           class="custom-control-input" checked="">
                                    <label class="custom-control-label fresh_all_dir_lable" for="dir_fresh_type_flush">刷新变更资源</label>
                                </div>
                                <div class="custom-control custom-radio fresh_all_dir_switch">
                                    <input type="radio" id="dir_fresh_type_delete" name="dir_fresh_radio"
                                           class="custom-control-input">
                                    <label class="custom-control-label fresh_all_dir_lable" for="dir_fresh_type_delete">刷新全部资源</label>
                                </div>
                            </div>
                            <div class="form-group">
                                    <textarea class="col-lg-12 fresh_dir_textarea" id="tencent_wordpress_cdn_dir_fresh" name="url_fresh"
                                              rows="20"
                                              placeholder="输入需要刷新目录的URL (需要http://或https://)
一行一个，例如: http://www.test.com/test/
单日最多刷新100个境内URL
单日最多刷新100个境外URL"
                                    ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saving Options-->
                <button id="form_cdn_button_dirfresh" type="button" class="btn btn-primary">提交并刷新</button>
                <span id="span_cdn_button_dirfresh" class="invalid-feedback offset-lg-1"></span>
            </div>

            <div class="tab-pane fade" id="tencent_wordpress_cdn_urlfresh_home">
                <!-- URL刷新 -->
                <div class="postbox">
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2 tencent_cdn_secret_lable" for="inputDefault"><h5>全站刷新</h5></label>
                                <div class="custom-control custom-switch fresh_all_url_switch">
                                    <input type="checkbox" class="custom-control-input" name="switch_fresh_all_url"
                                           id="switch_fresh_all_url_id">
                                    <label class="custom-control-label fresh_all_dir_lable" for="switch_fresh_all_url_id">开启全站点URL的CDN刷新</label>
                                </div>
                            </div>
                            <!-- URL文本输入框-->
                            <div class="form-group">
                                    <textarea class="col-lg-12 fresh_dir_textarea" id="tencent_wordpress_cdn_url_fresh"
                                              name="url_fresh" rows="20"
                                              placeholder="请在此输入需要刷新的URL (需要http://或https://)
一行一个，例如: http://www.test.com/test.html
暂不支持提交包含通配符的URL刷新任务
单日最多刷新10000个境内URL
单日最多刷新10000个境外URL"
                                    ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saving Options-->
                <button id="form_cdn_button_urlfresh" type="button" class="btn btn-primary">提交并刷新</button>
                <span id="span_cdn_button_urlfresh" class="invalid-feedback offset-lg-1"></span>
            </div>

            <div class="tab-pane fade" id="tencent_wordpress_cdn_fresh_log_home">
                <div class="postbox">
                    <div class="row">
                        <div class="col-lg-9">
                            <!-- setting start time-->
                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>开始时间</h5></label>
                                <input type="datetime-local" id="quert_start_time_id" name="quert_start_time" value=""/>
                            </div>
                            <!-- setting end time-->
                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>截止时间</h5></label>
                                <input type="datetime-local" id="quert_end_time_id" name="quert_end_time" value="0000-00-00 00:00:00"/>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>显示日志数量</h5></label>

                                <select id="select_log_number" name="region" class="select_region_style">
                                    <option value="">请选择日志条数</option>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20" selected="selected">20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
                                </select>
                            </div>
                            <div>
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>刷新方式</h5></label>
                                <select id="select_flush_type" name="region" class="select_region_style">
                                    <option value="">选择刷新方式</option>
                                    <option value="path">目录刷新</option>
                                    <option value="url">URL刷新</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                <button id="form_cdn_button_refresh_log" type="button" class="btn btn-primary">查询日志</button>
                <span id="span_cdn_refresh_log" class="invalid-feedback offset-lg-1"></span>
                <div class="postbox flush_log_block" id="flush_log_block_id">
                    <div>
                        <table id="flush_log_table_id" class="table table-hover col-lg-7">
                            <thead>
                            <tr>
                                <th scope="col">刷新记录</th>
                                <th scope="col">刷新时间</th>
                                <th scope="col">状态</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div class="bs-component show_log_count">
                            <p id="cdn_refresh_log_count" class="text-muted "></p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div>
                    <br>
                    <div class="setting_page_footer">
                        <a href="https://openapp.qq.com/docs/Wordpress/cdn.html" target="_blank">文档中心</a> | <a
                                href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-cdn" target="_blank">GitHub</a> | <a
                                href="https://da.do/y0rp" target="_blank">反馈建议</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(function ($) {
        var date = new Date();
        var cur_year = date.getFullYear();
        var cur_month ="";
        var cur_Date ="";
        var cur_Hours ="";
        var cur_Minutes ="";
        if (1 + date.getMonth() < 10) {
            cur_month = '0' + (date.getMonth() + 1);
        } else {
            cur_month = date.getMonth() + 1;
        }
        if (date.getDate() < 10) {
            cur_Date = '0' + date.getDate();
        } else {
            cur_Date = date.getDate();
        }
        if (date.getHours() < 10) {
            cur_Hours = '0' + date.getHours();
        } else {
            cur_Hours = date.getHours();
        }
        if (date.getMinutes() < 10) {
            cur_Minutes = '0' + date.getMinutes();
        } else {
            cur_Minutes = date.getMinutes();
        }

        var start_str = cur_year + "-" + cur_month + "-" + cur_Date+ "T" +  "01" + ":" + "00" ;
        var end_str = cur_year + "-" + cur_month + "-" + cur_Date + "T" +  cur_Hours + ":" + cur_Minutes;
        $('#quert_start_time_id').val(start_str)
        $('#quert_end_time_id').val(end_str)
    })

</script>

