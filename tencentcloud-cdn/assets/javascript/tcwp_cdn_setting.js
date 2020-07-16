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
jQuery(function ($) {
    // 异步请求地址
    var ajaxUrl = $("#tcwpform_cdn_info_set").data("ajax-url");
    $("#tencent_wordpress_cdn_customize_secret").change(function() {
        var disabled = !($(this).is(':checked'));
        $("#tencent_wordpress_cdn_secret_id").attr('disabled',disabled);
        $("#tencent_wordpress_cdn_secret_key").attr('disabled',disabled);
    });
    // 修改密钥是否可显示
    function change_type(input_element, span_eye) {
        if(input_element[0].type === 'password') {
            input_element[0].type = 'text';
            span_eye.addClass('dashicons-visibility').removeClass('shicons-hidden');
        } else {
            input_element[0].type = 'password';
            span_eye.addClass('shicons-hiddenda').removeClass('dashicons-visibility');
        }
    }
    // 是否可显示SecretId
    $('#cdn_secret_id_change_type').click(function () {
        change_type($('#tencent_wordpress_cdn_secret_id'), $('#cdn_secret_id_change_type'));
    });
    // 是否可现实SecretKey
    $('#cdn_secret_key_change_type').click(function () {
        change_type($('#tencent_wordpress_cdn_secret_key'), $('#cdn_secret_key_change_type'));
    });
    // 判断SecretId不能为空
    $('#tencent_wordpress_cdn_secret_id').blur(function () {
        if ($('#tencent_wordpress_cdn_customize_secret')[0].checked === true && !($('#tencent_wordpress_cdn_secret_id')[0].value)) {
            $('#span_cdn_secret_id')[0].innerHTML = "SecretId的值不能为空";
            $('#span_cdn_secret_id').show();
        } else {
            $('#span_cdn_secret_id').hide();
        }
    });
    //判断SecretKey不能为空
    $('#tencent_wordpress_cdn_secret_key').blur(function () {
        if ($('#tencent_wordpress_cdn_customize_secret')[0].checked === true &&  !($('#tencent_wordpress_cdn_secret_key')[0].value)) {
            $('#span_cdn_secret_key')[0].innerHTML = "secretkey的值不能为空";
            $('#span_cdn_secret_key').show();
        } else {
            $('#span_cdn_secret_key').hide();
        }
    });
    // 保存插件配置信息
    $('#form_cdn_button_save').click(function () {
        var secret_id = $('#tencent_wordpress_cdn_secret_id')[0].value;
        var secret_key = $('#tencent_wordpress_cdn_secret_key')[0].value;
        var customize_secret = $('#tencent_wordpress_cdn_customize_secret')[0].checked;

        if ($('#tencent_wordpress_cdn_customize_secret')[0].checked === true &&  (!secret_id || !secret_key)) {
            alert("SecretId和SecretKey的值都不能为空！");
            return false;
        }
        // 发送ajax请求，保存配置信息
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "save_cdn_options",
                secret_id:secret_id,
                secret_key:secret_key,
                customize_secret:customize_secret,
            },
            success: function(response) {
                if (response.success){
                    $('#span_button_save')[0].innerHTML = "保存成功！";
                } else {
                    $('#span_button_save')[0].innerHTML = "保存失败！";
                }
                $('#span_button_save').show().delay(3000).fadeOut();
                setTimeout(location.reload.bind(location), 3000);
            }
        });
    });
    // 验证CDN接口是否有效
    $('#form_cdn_button_info_refresh').click(function () {
        var secret_id = $('#tencent_wordpress_cdn_secret_id').val();
        var secret_key = $('#tencent_wordpress_cdn_secret_key').val();
        var customize_secret = $('#tencent_wordpress_cdn_customize_secret')[0].checked;
        if ($('#tencent_wordpress_cdn_customize_secret')[0].checked === true &&  (!secret_id || !secret_key)) {
            alert("SecretId和SecretKey的值都不能为空！");
            return false;
        }
        //发生ajax请求，验证接口有效性
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "refresh_cdn_information",
                secret_id:secret_id,
                secret_key:secret_key,
                customize_secret:customize_secret,
            },
            success: function(response) {
                if (response.success){
                    $('#span_cdn_info_refresh')[0].innerHTML = "验证成功！";
                } else {
                    $('#span_cdn_info_refresh')[0].innerHTML = "验证失败，请检查配置参数是否正确！";
                }
                $('#span_cdn_info_refresh').show().delay(3000).fadeOut();
            }
        });
    });

    $('#tencent_wordpress_cdn_config_id').click(function () {
        $('#tencent_wordpress_cdn_config_id').removeClass('active').addClass('active');
        $('#tencent_wordpress_cdn_dirfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_urlfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_fresh_log_id').removeClass('active');

        $('#tencent_wordpress_cdn_config_home').addClass('active show');
        $('#tencent_wordpress_cdn_dirfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_urlfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_fresh_log_home').removeClass('active show');
    });

    $('#tencent_wordpress_cdn_dirfresh_id').click(function () {
        $('#tencent_wordpress_cdn_dirfresh_id').removeClass('active').addClass('active');
        $('#tencent_wordpress_cdn_config_id').removeClass('active');
        $('#tencent_wordpress_cdn_urlfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_fresh_log_id').removeClass('active');

        $('#tencent_wordpress_cdn_dirfresh_home').addClass('active show');
        $('#tencent_wordpress_cdn_urlfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_config_home').removeClass('active show');
        $('#tencent_wordpress_cdn_fresh_log_home').removeClass('active show');
    });

    $('#tencent_wordpress_cdn_urlfresh_id').click(function () {
        $('#tencent_wordpress_cdn_urlfresh_id').removeClass('active').addClass('active');
        $('#tencent_wordpress_cdn_dirfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_config_id').removeClass('active');
        $('#tencent_wordpress_cdn_fresh_log_id').removeClass('active');

        $('#tencent_wordpress_cdn_urlfresh_home').addClass('active show');
        $('#tencent_wordpress_cdn_dirfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_config_home').removeClass('active show');
        $('#tencent_wordpress_cdn_fresh_log_home').removeClass('active show');
    });

    $('#tencent_wordpress_cdn_fresh_log_id').click(function () {
        $('#tencent_wordpress_cdn_fresh_log_id').removeClass('active').addClass('active');
        $('#tencent_wordpress_cdn_dirfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_urlfresh_id').removeClass('active');
        $('#tencent_wordpress_cdn_config_id').removeClass('active');

        $('#tencent_wordpress_cdn_fresh_log_home').addClass('active show');
        $('#tencent_wordpress_cdn_dirfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_urlfresh_home').removeClass('active show');
        $('#tencent_wordpress_cdn_config_home').removeClass('active show');
    });

    $("#switch_fresh_all_dir_id").change(function() {
        var disabled = ($(this).is(':checked'));
        $("#tencent_wordpress_cdn_dir_fresh").attr('disabled',disabled);
    });

    $("#switch_fresh_all_url_id").change(function() {
        var disabled = ($(this).is(':checked'));
        $("#tencent_wordpress_cdn_url_fresh").attr('disabled',disabled);
    });

    $('#form_cdn_button_dirfresh').click(function () {
        var fresh_all = $("#switch_fresh_all_dir_id").is(':checked')
        var cdn_dir = $('#tencent_wordpress_cdn_dir_fresh').val();

        var flush_type = '';
        if ($("#dir_fresh_type_flush").is(":checked")) {
            flush_type = 'flush'
        }
        if ($("#dir_fresh_type_delete").is(":checked")) {
            flush_type = 'delete'
        }

        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "refresh_cdn_dir",
                cdn_dir:cdn_dir,
                flush_type:flush_type,
                fresh_all:fresh_all
            },
            success: function(response) {
                if (response.success){
                    $('#span_cdn_button_dirfresh')[0].innerHTML = "刷新成功！";
                } else {
                    $('#span_cdn_button_dirfresh')[0].innerHTML = response.data.Message;
                }
                $('#span_cdn_button_dirfresh').show().delay(3000).fadeOut();
            },
        });
    });

    $('#form_cdn_button_urlfresh').click(function () {
        var fresh_all = $("#switch_fresh_all_url_id").is(':checked')
        var cdn_url = $('#tencent_wordpress_cdn_url_fresh').val();
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "refresh_cdn_url",
                cdn_url:cdn_url,
                fresh_all:fresh_all
            },
            success: function(response) {
                if (response.success){
                    $('#span_cdn_button_urlfresh')[0].innerHTML = "刷新成功！";
                } else {
                    $('#span_cdn_button_urlfresh')[0].innerHTML = response.data.Message;
                }
                $('#span_cdn_button_urlfresh').show().delay(3000).fadeOut();
            },
        });
    });

    $('#form_cdn_button_refresh_log').click(function () {
        var start_time = $('#quert_start_time_id').val();
        var end_time = $('#quert_end_time_id').val();
        var log_number = $('#select_log_number').val();
        var purge_type = $('#select_flush_type').val();
        if (!start_time || !end_time || !log_number || !purge_type) {
            $('#span_cdn_refresh_log')[0].innerHTML = "开始时间、截止时间、日志数量和刷新方式都不能为空！";
            $('#span_cdn_refresh_log').show().delay(3000).fadeOut();

        }
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "get_refresh_log",
                start_time:start_time,
                end_time:end_time,
                log_number:log_number,
                purge_type:purge_type,
            },
            success: function(response) {
                if (response.success){
                    var table_html = '';
                    var purgelogs = response.data.PurgeLogs;
                    for (var purgelog in purgelogs) {
                        if (purgelog % 2 == 0) {
                            table_html += '<tr scope="row">'
                        } else {
                            table_html += '<tr class="table-active">'
                        }

                        table_html += '<td>' + purgelogs[purgelog].Url + '</td>'
                        table_html += '<td>' + purgelogs[purgelog].CreateTime + '</td>'
                        var flush_status = '';
                        if (purgelogs[purgelog].Status == 'done'){
                            flush_status = "完成";
                        } else  {
                            flush_status = "正在刷新";
                        }
                        table_html += '<td>' + flush_status + '</td>'
                        table_html += '</tr>'
                    }
                    $('#flush_log_table_id').children('tbody').empty().append(table_html)
                    $("#flush_log_block_id").css("display", "block");
                    $("#cdn_refresh_log_count").html("总共" + response.data.TotalCount + "条日志");
                }
            },
        });
    });
});