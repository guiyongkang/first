<script type="text/javascript">
$(document).ready(function(){
    $("#submitBtn").click(function(){
        $("#add_form").submit();
    });
});
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_wechat_api'];?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
  <input type="hidden" name="form_submit" value="ok" />
  <input type="hidden" name="wid" value="<?php echo $output['api_account']['wechat_id'];?>" />
    <table class="table tb-type2">
      <tbody>
		<tr>
          <td colspan="2" class="required"><label for="token"><?php echo $lang['wechat_api_url']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">http://<?php echo $_SERVER['HTTP_HOST'];?>/api/index.php?act=weixin&wsn=<?php echo $output['api_account']['wechat_sn'];?></td>
        </tr>
      	<tr>
          <td colspan="2" class="required"><label for="token"><?php echo $lang['wechat_token']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2"><input type="text" class="txt" value="<?php echo $output['api_account']['wechat_token'];?>" readonly="readonly" /></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="type"><?php echo $lang['wechat_type']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform" colspan="2">
           <?php foreach($lang['wechat_type_name'] as $key=>$value){?>
           	<input type="radio" name="type" value="<?php echo $key;?>" id="type_<?php echo $key;?>"<?php echo $output['api_account']['wechat_type']==$key ? ' checked' : '';?> /><label for="wechat_type_<?php echo $key;?>"><?php echo $value;?></label>&nbsp;&nbsp;
           <?php }?>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="appid"><?php echo $lang['wechat_appid']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="appid" name="appid" value="<?php echo $output['api_account']['wechat_appid'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="appsecret"><?php echo $lang['wechat_appsecret'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="appsecret" name="appsecret" value="<?php echo $output['api_account']['wechat_appsecret'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>   
        <tr>
          <td colspan="2" class="required"><label for="name"><?php echo $lang['wechat_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="name" name="name" value="<?php echo $output['api_account']['wechat_name'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="email"><?php echo $lang['wechat_email'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="email" name="email" value="<?php echo $output['api_account']['wechat_email'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="preid"><?php echo $lang['wechat_preid'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="preid" name="preid" value="<?php echo $output['api_account']['wechat_preid'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="account"><?php echo $lang['wechat_account'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="account" name="account" value="<?php echo $output['api_account']['wechat_account'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="encodingtype"><?php echo $lang['wechat_encodingtype'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
           <select name="encodingtype">
           <?php foreach($lang['wechat_encodingtype_name'] as $k=>$v){?>
           	<option value="<?php echo $k;?>"<?php echo $output['api_account']['wechat_encodingtype']==$k ? ' selected' : '';?>><?php echo $v;?></option>
           <?php }?>
           </select>
          </td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label for="encoding"><?php echo $lang['wechat_encoding'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input id="encoding" name="encoding" value="<?php echo $output['api_account']['wechat_encoding'];?>" class="txt" type="text"></td>
          <td class="vatop tips"></td>
        </tr>     
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2" ><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
