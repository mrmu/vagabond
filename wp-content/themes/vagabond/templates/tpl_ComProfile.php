<?php
/**
 * Template Name: Company Profile
 *
 * Add by Audi - 2016.04.01
 *
 */ 

get_header();

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'zh_TW');

global $VagabondPM;

$ary_com_profile = array();
if ( empty($_POST) || !wp_verify_nonce($_POST['comprofile_nonce'],'') )
{
   //print 'Sorry, your nonce did not verify.';
   //exit;
}
else
{
	if(isset($_POST['com_title'])) $com_title = $_POST['com_title'];
	if(isset($_POST['bank_title'])) $bank_title = $_POST['bank_title'];
	if(isset($_POST['bank_account'])) $bank_account = $_POST['bank_account'];
	if(isset($_POST['bank_num'])) $bank_num = $_POST['bank_num'];
	if(isset($_POST['bank_com_title'])) $bank_com_title = $_POST['bank_com_title'];

	if(isset($_POST['com_num'])) $com_num = $_POST['com_num'];
	if(isset($_POST['com_adds'])) $com_adds = $_POST['com_adds'];


	$ary_com_profile['com_title'] = $com_title;
	$ary_com_profile['bank_title'] = $bank_title;
	$ary_com_profile['bank_account'] = $bank_account;
	$ary_com_profile['bank_num'] = $bank_num;
	$ary_com_profile['bank_com_title'] = $bank_com_title;

	$ary_com_profile['com_num'] = $com_num;
	$ary_com_profile['com_adds'] = $com_adds;

	update_option( 'company_profile', $ary_com_profile );
}

$ary_com_profile = get_option('company_profile');
//print_r($ary_com_profile);
$com_title = $ary_com_profile['com_title'];
$bank_title = $ary_com_profile['bank_title'];
$bank_account = $ary_com_profile['bank_account'];
$bank_num = $ary_com_profile['bank_num'];
$bank_com_title = $ary_com_profile['bank_com_title'];
$com_num = $ary_com_profile['com_num'];
$com_adds = $ary_com_profile['com_adds'];

?>
<style>
.rborder1{border-right:1px solid #eeeeee;}
</style>

<div class="wrap">
	<form name="frm_editproj" action="" method="POST">
		<input type="hidden" name="editproj_nonce">
		<h1><?php _e('Company Profile', $VagabondPM::SLUG); ?></h1>
		<h3><?php _e('General Settings', $VagabondPM::SLUG);?></h3>

		<div class="row">
			<div class="col-sm-12">
				
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('Company Title',$VagabondPM::SLUG); ?></span>
							<input type="text" name="com_title" id="com_title" class="form-control" placeholder="<?php _e('company title',$VagabondPM::SLUG); ?>" value="<?php echo $com_title;?>">
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('銀行名稱',$VagabondPM::SLUG); ?></span>
							<input type="text" name="bank_title" id="bank_title" class="form-control" placeholder="<?php _e('bank title',$VagabondPM::SLUG); ?>" value="<?php echo $bank_title;?>">
						</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('銀行帳號',$VagabondPM::SLUG); ?></span>
							<input type="text" name="bank_account" id="bank_account" class="form-control" placeholder="<?php _e('bank account',$VagabondPM::SLUG); ?>" value="<?php echo $bank_account;?>">
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('銀行代號',$VagabondPM::SLUG); ?></span>
							<input type="text" name="bank_num" id="bank_num" class="form-control" placeholder="<?php _e('bank number',$VagabondPM::SLUG); ?>" value="<?php echo $bank_num;?>">
						</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('戶名',$VagabondPM::SLUG); ?></span>
							<input type="text" name="bank_com_title" id="bank_com_title" class="form-control" placeholder="<?php _e('bank company title',$VagabondPM::SLUG); ?>" value="<?php echo $bank_com_title;?>">
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('統一編號',$VagabondPM::SLUG); ?></span>
							<input type="text" name="com_num" id="com_num" class="form-control" placeholder="<?php _e('company number',$VagabondPM::SLUG); ?>" value="<?php echo $com_num;?>">
						</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('公司地址',$VagabondPM::SLUG); ?></span>
							<input type="text" name="com_adds" id="com_adds" class="form-control" placeholder="<?php _e('bank company title',$VagabondPM::SLUG); ?>" value="<?php echo $com_adds;?>">
						</div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<input type="submit" class="btn btn-primary" value="確定">
		<?php wp_nonce_field('','comprofile_nonce'); ?>
	</form>
</div>

<script>
<?php
	//[ag]added by audi -2013/05/08- 若無ssl，為配合後台有ssl，需將https的admin-ajax.php的網址替換為http，以避免ajax跨域錯誤
	$ajax_url = admin_url( 'admin-ajax.php' );
	if (!is_ssl() && 'https' == substr($ajax_url, 0, 5)) {
		$ajax_url = 'http'.substr($ajax_url, 5);
	}

	global $ajax_url;
?>
$(function(){

	$('#btn_add_g_item').on('click', function(){
		$('.date').removeClass('hasDatepicker').attr('id','');
		$('#g_items').append($('#g_items .row:last').clone());
		$('.date').datepicker(dp_opt);
	});

	$('#btn_sav_g_item').on('click', function(){
		$.ajax({
			async: true, //mimic POST use false
			type: 'POST',
			url: '<?php echo $ajax_url;?>',
			data: {
				action: 'sav_gantt_func',
				g_phase: $('input:text.g_phase').serialize(),
				g_start: $('input:text.g_start').serialize(),
				g_end: $('input:text.g_end').serialize(),
				g_class: $('input:text.g_class').serialize()
			},
			dataType: 'JSON',
			success: function(res) {
				console.log(res.text);
				if (res.code=='1'){
					//do sth when success...
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				alert(ajaxOptions+':'+thrownError);
			}
		});
	});	




	$('.g_remove').on('click', function(){
		$(this).parent().parent().parent().parent().parent().remove();
	});


});

</script>
<?php
get_footer();
?>