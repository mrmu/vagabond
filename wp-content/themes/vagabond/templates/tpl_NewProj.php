<?php
/**
 * Template Name: New Project
 *
 * Add by Audi - 2013.06.05
 *
 */ 
//get_header('pmadmin');
get_header();
//$current_user = wp_get_current_user();

if ( empty($_POST) || !wp_verify_nonce($_POST['newproj_nonce'],'') )
{
   //print 'Sorry, your nonce did not verify.';
   //exit;
}else{
	//if(isset($_POST['proj_name'])) $proj_name = $_POST['proj_name'];
	//if(isset($_POST['proj_state'])) $proj_state = $_POST['proj_state'];
	if(isset($_POST['proj_price'])) $proj_price = $_POST['proj_price'];
	if(isset($_POST['proj_no_paid'])) $proj_no_paid = $_POST['proj_no_paid'];
	if(isset($_POST['proj_warranty'])) $proj_warranty = $_POST['proj_warranty'];
	if(isset($_POST['kickoff_date'])) $kickoff_date = $_POST['kickoff_date'];
	if(isset($_POST['work_day'])) $work_day = $_POST['work_day'];
	if(isset($_POST['proj_file_url'])) $proj_file_url = $_POST['proj_file_url'];
	if(isset($_POST['host_info'])) $host_info = $_POST['host_info'];
	if(isset($_POST['bill_detail'])) $bill_detail = $_POST['bill_detail'];
	
	$the_proj = array();
	$the_proj['post_author'] = wp_get_current_user()->ID;
	$the_proj['post_date'] = gmdate("Y:m:d H:i:s", time()+3600*8);
	$the_proj['post_date_gmt'] = gmdate("Y:m:d H:i:s");
	$the_proj['post_modified'] = gmdate("Y:m:d H:i:s", time()+3600*8); 
	$the_proj['post_modified_gme'] = gmdate("Y:m:d H:i:s");		
	$the_proj['post_content'] = (isset($_POST["proj_memo"]))? esc_html($_POST["proj_memo"]) : "";
	$the_proj['post_title'] = (isset($_POST["proj_name"]))? esc_html($_POST["proj_name"]) : "";
	$the_proj['post_name'] = (isset($_POST["proj_name"]))? esc_html($_POST["proj_name"]) : "";
	
	$p_tags	 = (isset($_POST["proj_state"]))? esc_html($_POST["proj_state"]) : "";
	if ($p_tags) {
		$ary_tags = explode(',', $p_tags);
		$ary_ntags = array();
		foreach ($ary_tags as $tag){
			if ($tag){
				$ary_ntags[] = trim($tag);
			}
		}
		$p_tags = join( ",", $ary_ntags );
	}
	$the_proj['tags_input'] = $p_tags;
	$the_proj['post_status'] = "publish";
	$the_proj['post_type'] = "proj";
	//$the_proj['tax_input'] = array( 'proj_cate' => array( 'term', 'term2', 'term3' ) );
	$proj_id = wp_insert_post($the_proj);
	
	add_post_meta($proj_id, 'proj_price', $proj_price);
	add_post_meta($proj_id, 'proj_warranty', $proj_warranty);
	add_post_meta($proj_id, 'kickoff_date', $kickoff_date);
	add_post_meta($proj_id, 'work_day', $work_day);
	add_post_meta($proj_id, 'proj_file_url', $proj_file_url);
	add_post_meta($proj_id, 'host_info', $host_info);
	add_post_meta($proj_id, 'proj_no_paid', $proj_no_paid);
	add_post_meta($proj_id, 'bill_detail', $bill_detail);
	
}

?>
<style>
	h1,h2,h3,h4,h5,a,input {font-family: "微軟正黑體", "新細明體", Arial;}
	h1{font-size: 36px;}
	h2{font-size: 25px;}
	p{line-height:120%; margin:10px 0; font-size:15px;}
	#BEST .TOP .IMG.two_col{width: 487px;}
	
	.reveal-modal {width: 800px; margin-left:-440px; top:60px;}
	.IMG{cursor: pointer;}
	a.button, a.button:link, a.button:visited, .button{
		border-radius: 3px;
		font-size: 15px;
		border-color: #29ba8c;
		font-weight: bold;
		color: #fff;
		background-color:#497;
		text-shadow: rgba(0,0,0,0.3) 0 -1px 0;
		padding: 3px 8px;
		text-decoration:none;
	}
	a.button, a.button:link, a.button:visited{
		background-color:#888;
	}
	.setting_fd{
		border: 1px solid #ccc;
		padding: 10px;
		float: left;
		font-size: 15px;
	}
	a.ads_tit:link, a.ads_tit:visited, a.ads_tit{
		font-size:18px; font-weight:bold; text-decoration:none; background-color:#378; color:#fff; border-radius:50px;
	}
	.bp{display:none;}
	.preview1{margin: 5px 0; width:1000px; height:110px; border:1px solid; overflow:hidden;}
	.preview2{margin: 5px 0; width:1000px; height:500px; border:1px solid; overflow:hidden;}
	
	.tab{
		padding:5px; border:1px solid;
	}
</style>

<div class="wrap">
	<form name="frm_newproj" action="" method="POST">
		<input type="hidden" name="newproj_nonce">
		<h1>新增專案</h1>
		<br>
		<h3>專案資訊</h3>
			<ul>
			名稱：<input type="text" name="proj_name" id="proj_name">
			狀態：<input type="text" name="proj_state" id="proj_state" placeholder="例：洽談、開工、驗收..等，以,區隔" style="width:210px">
			報價：<input type="text" name="proj_price" id="proj_price">
			應收帳款：<input type="text" name="proj_no_paid" id="proj_no_paid">
			</ul>
		<h3>時程</h3>
			<ul>
				<div>開始日：<input type="date" name="kickoff_date" id="kickoff_date"> 
				預計總工作天：<input type="text" name="work_day" id="work_day">天，
				結案後保固<input type="text" name="proj_warranty" id="proj_warranty">年
				</div>
				<div style="margin: 20px 0 10px 0;">
					<a id="tab_g_preview" class="tab" href="javascript:void();">甘特圖</a> 
					<a id="tab_g_setting" class="tab" href="javascript:void();">細部設定</a>
				</div>
				<div id="gantt_setting" style="display:none;">
					分析：<input type="text" value="">天 註記：<input type="text">
							<select>
								<option value="ganttRed">紅</option>
								<option value="ganttGreen">綠</option>
								<option value="ganttBlue">藍</option>
								<option value="ganttOrange">橘</option>
							</select>
					
				</div>
				<div id="gantt_preview" style="display:none">
					<div class="gantt"></div>
				</div>
				
			</ul>
		<h3>合約</h3>
			<ul>
				<a href="javascript:void();" class="button">自動生成及預覽</a>
			</ul>
		<h3>專案檔案目錄</h3>
			<ul>Link to Dropbox: <input type="url" name="proj_file_url" id="proj_file_url" style="width:500px"></ul>
		<h3>主機資訊</h3>
			<ul><textarea cols="100" rows="10" name="host_info" id="host_info"></textarea></ul>
		<h3>報價單明細</h3>
			<ul><textarea cols="100" rows="10" name="bill_detail" id="bill_detail"></textarea></ul>
		<h3>備註</h3>
		<ul><textarea cols="100" rows="5" name="proj_memo" id="proj_memo"></textarea></ul>
		<input type="submit" class="" value="確定">
		<?php wp_nonce_field('','newproj_nonce'); ?>
	</form>
</div>

<script>
<?php
	//[ag]added by audi -2013/05/08- 若無ssl，為配合後台有ssl，需將https的admin-ajax.php的網址替換為http，以避免ajax跨域錯誤
	$ajax_url = admin_url( 'admin-ajax.php' );
	if (!is_ssl() && 'https' == substr($ajax_url, 0, 5)) {
		$ajax_url = 'http'.substr($ajax_url, 5);
	}
?>
</script>
<?php
get_footer();
?>