<?php defined('SAFE_CONST') or exit('Access Invalid!');?>
<div class="page">
	<div class="fixed-bar">
		<div class="item-title">
			<h3>手机支付</h3>
			<ul class="tab-base">
				<li><a href="<?php echo urlAdmin('mb_payment', 'payment_list');?>"><span>列表</span></a></li>
				<li><a class="current"><span><?php echo $lang['nc_edit'];?></span></a></li>
			</ul>
		</div>
	</div>
	<div class="fixed-empty"></div>
	<form id="post_form" method="post" enctype="multipart/form-data" name="form1" action="<?php echo urlAdmin('mb_payment', 'payment_save');?>">
		<input type="hidden" name="payment_id"
			value="<?php echo $output['payment']['payment_id'];?>" /> <input
			type="hidden" name="payment_code"
			value="<?php echo $output['payment']['payment_code'];?>" />

		<table class="table tb-type2 nobdb">
			<tbody>
				<tr class="noborder">
					<td class="vatop rowform"><?php echo $output['payment']['payment_name'];?></td>
					<td class="vatop tips"></td>
				</tr>
        <?php if ($output['payment']['payment_code'] == 'alipay') { ?>
        <tr>
					<td colspan="2" class="required"><label class="validation">支付宝账号:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_account"
						id="alipay_account"
						value="<?php echo isset($output['payment']['payment_config']['alipay_account']) ? $output['payment']['payment_config']['alipay_account'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">交易安全校验码（key）:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_key" id="alipay_key"
						value="<?php echo isset($output['payment']['payment_config']['alipay_key']) ? $output['payment']['payment_config']['alipay_key'] : 0;?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">合作者身份（partner
							ID）:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_partner"
						id="alipay_partner"
						value="<?php echo isset($output['payment']['payment_config']['alipay_partner']) ? $output['payment']['payment_config']['alipay_partner'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
        <?php } ?>
        <?php if ($output['payment']['payment_code'] == 'alipay_native') { ?>
        <tr>
					<td colspan="2" class="required"><label class="validation">支付宝账号:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_account"
						id="alipay_account"
						value="<?php echo isset($output['payment']['payment_config']['alipay_account']) ? $output['payment']['payment_config']['alipay_account'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>

				<tr>
					<td colspan="2" class="required"><label class="validation">交易安全校验码（key）:</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_key" id="alipay_key"
						value="<?php echo isset($output['payment']['payment_config']['alipay_key']) ? $output['payment']['payment_config']['alipay_key'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>

				<tr>
					<td colspan="2" class="required"><label class="validation">合作者身份(partner
							ID):</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="alipay_partner"
						id="alipay_partner"
						value="<?php echo isset($output['payment']['payment_config']['alipay_partner']) ? $output['payment']['payment_config']['alipay_partner'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
        <?php } ?>
        <?php if ($output['payment']['payment_code'] == 'wxpay') { ?>
        <tr>
					<td colspan="2" class="required"><label class="validation">APP唯一凭证(appid):</label>
					</td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="wxpay_appid"
						id="wxpay_appid"
						value="<?php echo isset($output['payment']['payment_config']['wxpay_appid']) ? $output['payment']['payment_config']['wxpay_appid'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips">APP唯一凭证，需要到微信开放平台进行申请</td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">商户号（mch_id）:
					</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="wxpay_partnerid"
						id="wxpay_partnerid"
						value="<?php echo isset($output['payment']['payment_config']['wxpay_partnerid']) ? $output['payment']['payment_config']['wxpay_partnerid'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>

				<tr>
					<td colspan="2" class="required"><label class="validation">商户密钥(APIKEY/partnerkey):
					</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="wxpay_partnerkey"
						id="wxpay_partnerkey"
						value="<?php echo isset($output['payment']['payment_config']['wxpay_partnerkey']) ? $output['payment']['payment_config']['wxpay_partnerkey'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips">到微信商户平台(账户设置-安全设置-API安全)进行设置</td>
				</tr>
        <?php } ?>
        <?php if ($output['payment']['payment_code'] == 'wxpay_jsapi') { ?>
        <tr>
					<td colspan="2" class="required"><label class="validation">APP唯一凭证(appid):</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="appId" id="appId"
						value="<?php echo isset($output['payment']['payment_config']['appId']) ? $output['payment']['payment_config']['appId'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">应用密钥(appsecret):
					</label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="appSecret" id="appSecret"
						value="<?php echo isset($output['payment']['payment_config']['appSecret']) ? $output['payment']['payment_config']['appSecret'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">微信支付商户号(partner
							ID): </label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="partnerId" id="partnerId"
						value="<?php echo isset($output['payment']['payment_config']['partnerId']) ? $output['payment']['payment_config']['partnerId'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				<tr>
					<td colspan="2" class="required"><label class="validation">API密钥: </label></td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform"><input name="apiKey" id="apiKey"
						value="<?php echo isset($output['payment']['payment_config']['apiKey']) ? $output['payment']['payment_config']['apiKey'] : '';?>"
						class="txt" type="text"></td>
					<td class="vatop tips"></td>
				</tr>
				
				<tr>
					<td colspan="2" class="required"><label class="validation">apiclient_cert证书: </label></td>
				</tr>
                <tr class="noborder">
					<td class="vatop rowform">
						<?php echo isset($output['payment']['payment_config']['apiclientcert']) ? $output['payment']['payment_config']['apiclientcert'] : '';?>
						<span class="type-file-box">
						<input type='text' name='textfield' id='textfield1' class='type-file-text' />
						<input type='button' name='button' id='button1' value='' class='type-file-button' />
						<input name="apiclientcert" type="file" class="type-file-file" id="apiclientcert" size="30" hidefocus="true" nc_type="change_apiclientcert">
						</span></td>
					<td class="vatop tips"></td>
				</tr>
                <tr>
					<td colspan="2" class="required"><label class="validation">apiclient_key证书: </label></td>
				</tr>
                <tr class="noborder">
					<td class="vatop rowform">
                    	<?php echo isset($output['payment']['payment_config']['apiclientkey']) ? $output['payment']['payment_config']['apiclientkey'] : '';?>
						<span class="type-file-box">
						<input type='text' name='textfield' id='textfield2' class='type-file-text' />
						<input type='button' name='button' id='button1' value='' class='type-file-button' />
						<input name="apiclientkey" type="file" class="type-file-file" id="apiclientkey" size="30" hidefocus="true" nc_type="change_apiclientkey">
						</span>
                    </td>
					<td class="vatop tips"></td>
				</tr>
        <?php } ?>
        <tr>
					<td colspan="2" class="required">启用:</td>
				</tr>
				<tr class="noborder">
					<td class="vatop rowform onoff"><label for="payment_state1"
						class="cb-enable <?php if($output['payment']['payment_state'] == '1'){ ?>selected<?php } ?>"><span><?php echo $lang['nc_yes'];?></span></label>
						<label for="payment_state2"
						class="cb-disable <?php if($output['payment']['payment_state'] == '0'){ ?>selected<?php } ?>"><span><?php echo $lang['nc_no'];?></span></label>
						<input type="radio"
						<?php if($output['payment']['payment_state'] == '1'){ ?>
						checked="checked" <?php }?> value="1" name="payment_state"
						id="payment_state1"> <input type="radio"
						<?php if($output['payment']['payment_state'] == '0'){ ?>
						checked="checked" <?php }?> value="0" name="payment_state"
						id="payment_state2"></td>
					<td class="vatop tips"></td>
				</tr>
			</tbody>
			<tfoot>
				<tr class="tfoot">
					<td colspan="15"><a href="JavaScript:void(0);" class="btn"
						id="btn_submit"><span><?php echo $lang['nc_submit'];?></span></a></td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>
<script>
$(document).ready(function(){
	$("#apiclientcert").change(function(){
		$("#textfield1").val($(this).val());
	});
	$("#apiclientkey").change(function(){
		$("#textfield2").val($(this).val());
	});
	$('#post_form').validate({
        errorPlacement: function(error, element){
			var error_td = element.parent('dd').children('span.err');
            error_td.append(error);
        },
		<?php if ($output['payment']['payment_code'] == 'alipay') { ?>
        rules : {
            alipay_account : {
                required   : true
            },
            alipay_key : {
                required   : true
            },
            alipay_partner : {
                required   : true
            }
        },
        messages : {
            alipay_account  : {
                required : '支付宝账号不能为空'
            },
            alipay_key  : {
                required : '交易安全校验码不能为空'
            },
            alipay_partner  : {
                required : '合作者身份不能为空'
            }
        }
		<?php } ?>
		<?php if ($output['payment']['payment_code'] == 'alipay_native') { ?>
        rules : {
            alipay_account : {
                required   : true
            },
            alipay_key : {
                required   : true
            },
            alipay_partner : {
                required   : true
            }
        },
        messages : {
            alipay_account  : {
                required : '<i class="fa fa-exclamation-circle"></i>支付宝账号不能为空'
            },
            alipay_key  : {
                required : '商户方私钥不能为空'
            },
            alipay_partner  : {
                required : '<i class="fa fa-exclamation-circle"></i>合作者身份不能为空'
            }
        }
		<?php } ?>
		<?php if ($output['payment']['payment_code'] == 'wxpay') { ?>
        rules : {
            wxpay_key : {
                required   : true
            },
            wxpay_partner : {
                required   : true
            }
        },
        messages : {
            wxpay_key  : {
                required : '交易安全校验码不能为空'
            },
            wxpay_partner  : {
                required : '合作者身份不能为空'
            }
        }
		<?php } ?>
		<?php if ($output['payment']['payment_code'] == 'wxpay_jsapi') { ?>
        rules : {
            appId : {
                required   : true
            },
            appSecret : {
                required   : true
            },
            partnerId : {
                required   : true
            },
            apiKey : {
                required   : true
            }
        },
        messages : {
            appId  : {
                required : '不能为空'
            },
            appSecret  : {
                required : '不能为空'
            },
            partnerId  : {
                required : '不能为空'
            },
            partnerId  : {
                apiKey : '不能为空'
            }
        }
		<?php } ?>
    });

    $('#btn_submit').on('click', function() {
        $('#post_form').submit();
    });
});
</script>
