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
        var secret_id = $('#tencent_wordpress_cdn_secret_id')[0].value;
        var secret_key = $('#tencent_wordpress_cdn_secret_key')[0].value;
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
});