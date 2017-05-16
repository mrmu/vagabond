<?php
/**
 * Template Name: Edit Project
 *
 * Add by Audi - 2013.06.05
 *
 */ 

get_header();

function getWorkingDays($startDate,$endDate,$holidays)
{
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);


    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;

    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date("N", $startDate);
    $the_last_day_of_week = date("N", $endDate);

    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }

    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
   $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
      $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }

    return $workingDays;
}

$proj_id = $_GET['proj_id'];


$gantti_data = get_post_meta( $proj_id, 'gantti_data', true );
$req_data = get_post_meta( $proj_id, 'req_data', true );

$ary_com_profile = get_option('company_profile');
$com_title = $ary_com_profile['com_title'];
$com_num = $ary_com_profile['com_num'];
$com_adds = $ary_com_profile['com_adds'];

//print_r($req_data);

if (empty($gantti_data[0]['label']) || !is_array($gantti_data))
{
	$dateP0 = date('Y-m-d', strtotime(current_time( 'mysql' )) );
	$dateP1 = date('Y-m-d', strtotime($todate.' +1 day') );
	$dateP2 = date('Y-m-d', strtotime($todate.' +2 day') );
	$dateP3 = date('Y-m-d', strtotime($todate.' +3 day') );
	$dateP4 = date('Y-m-d', strtotime($todate.' +4 day') );

	$gantti_data[] = array(
	  'label' => '需求',
	  'start' => $dateP0, 
	  'end'   => $dateP1
	);

	$gantti_data[] = array(
	  'label' => '視覺',
	  'start' => $dateP1, 
	  'end'   => $dateP2,
	  'class'	=> 'urgent'
	);

	$gantti_data[] = array(
	  'label' => '開發',
	  'start' => $dateP2, 
	  'end'   => $dateP3,
	  'class'	=> 'important'
	);

	$gantti_data[] = array(
	  'label' => '驗收',
	  'start' => $dateP3, 
	  'end'   => $dateP4,
	  'class'	=> 'important'
	);
	update_post_meta( $proj_id, 'gantti_data', $gantti_data );
}else{
	//print_r($gantti_data);
}

// $gantti = new Gantti($gantti_data, array(
//   'title'      => '<div class="btn-group"><button type="button" class="btn btn-xs btn-default zoom"> <span class="glyphicon glyphicon-zoom-in"></span> </button> <button class="btn btn-xs btn-default zoom"> <span class="glyphicon glyphicon-zoom-out"></span> </button></div>',
//   'cellwidth'  => $cellwidth,
//   'cellheight' => 35,
//   'today'      => true
// ));

date_default_timezone_set('UTC');
setlocale(LC_ALL, 'zh_TW');


global $VagabondPM;

//$current_user = wp_get_current_user();

if (isset($_GET['proj_id'])){
	$proj_id = $_GET['proj_id'];
}else{
	wp_die(__('No any project is selected.'), $VagabondPM::SLUG);
}

if ( empty($_POST) || !wp_verify_nonce($_POST['editproj_nonce'],'') )
{
   //print 'Sorry, your nonce did not verify.';
   //exit;
}else{
	//echo $_POST['proj_name'];
	//if(isset($_POST['proj_name'])) $proj_name = $_POST['proj_name'];
	//if(isset($_POST['proj_state'])) $proj_state = $_POST['proj_state'];
	if(isset($_POST['proj_price'])) $proj_price = $_POST['proj_price'];
	if(isset($_POST['proj_no_paid'])) $proj_no_paid = $_POST['proj_no_paid'];
	if(isset($_POST['proj_warranty'])) $proj_warranty = $_POST['proj_warranty'];
	if(isset($_POST['kickoff_start'])) $kickoff_start = $_POST['kickoff_start'];
	if(isset($_POST['kickoff_end'])) $kickoff_end = $_POST['kickoff_end'];
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
	$the_proj['ID'] = $proj_id;
	wp_update_post($the_proj);
	
	update_post_meta($proj_id, 'proj_price', $proj_price);
	update_post_meta($proj_id, 'proj_warranty', $proj_warranty);
	update_post_meta($proj_id, 'kickoff_start', $kickoff_start);
	update_post_meta($proj_id, 'kickoff_end', $kickoff_end);
	update_post_meta($proj_id, 'work_day', $work_day);
	update_post_meta($proj_id, 'proj_file_url', $proj_file_url);
	update_post_meta($proj_id, 'host_info', $host_info);
	update_post_meta($proj_id, 'proj_no_paid', $proj_no_paid);
	update_post_meta($proj_id, 'bill_detail', $bill_detail);
	
}

$proj_title = get_the_title($proj_id);
$content_post = get_post($proj_id);
$proj_memo = $content_post->post_content;

$proj_price = get_post_meta($proj_id, 'proj_price', true);
$proj_warranty = get_post_meta($proj_id, 'proj_warranty', true);
$kickoff_start = get_post_meta($proj_id, 'kickoff_start', true);
$kickoff_end = get_post_meta($proj_id, 'kickoff_end', true);
$work_day = get_post_meta($proj_id, 'work_day', true);
$proj_file_url = get_post_meta($proj_id, 'proj_file_url', true);
$host_info = get_post_meta($proj_id, 'host_info', true);
$proj_no_paid = get_post_meta($proj_id, 'proj_no_paid', true);
$bill_detail = get_post_meta($proj_id, 'bill_detail', true);

$tag_names = wp_get_post_tags( $proj_id, array( 'fields' => 'names' ) );
$proj_state = join( ",", $tag_names );

?>
<style>
.rborder1{border-right:1px solid #eeeeee;}
</style>

<div class="wrap">
	<form name="frm_editproj" action="" method="POST">
		<input type="hidden" name="editproj_nonce">
		<h1><?php _e('Project Info', $VagabondPM::SLUG); ?></h1>
		<h3><?php _e('General Settings', $VagabondPM::SLUG);?></h3>

		<div class="row">
			<div class="col-sm-12">
				
				<div class="row">
					<div class="col-sm-12">
						<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><?php _e('Title',$VagabondPM::SLUG); ?></span>
							<input type="text" name="proj_name" id="proj_name" class="form-control" placeholder="<?php _e('project title',$VagabondPM::SLUG); ?>" value="<?php echo $proj_title;?>">
						</div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><?php _e('Start', $VagabondPM::SLUG);?></span>
								<input type="text" class="form-control date" name="kickoff_start" id="kickoff_start" value="<?php echo $kickoff_start;?>"> 
							</div>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><?php _e('End', $VagabondPM::SLUG);?></span>
								<input type="text" class="form-control date" name="kickoff_end" id="kickoff_end" value="<?php echo $kickoff_end;?>"> 
							</div>
						</div>
					</div>
					<div class="col-sm-1">
						<div class="form-group">
							<div class="input-group">
								<button type="button" id="btn_cal_bdays" class="btn btn-default">試排</button>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<input type="text" class="form-control" name="work_day" id="work_day" value="<?php echo $work_day;?>">
								<span class="input-group-addon"><?php _e('Days', $VagabondPM::SLUG);?></span>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<?php _e('Warranty', $VagabondPM::SLUG); ?>
							</div>
							<div class="panel-body">
								<div class="row">

									<div class="col-sm-4">
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><?php _e('Start', $VagabondPM::SLUG);?></span>
												<input type="text" class="form-control date" name="warranty_start" id="warranty_start" value="<?php echo $warranty_start;?>"> 
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<div class="input-group">
												<span class="input-group-addon"><?php _e('End', $VagabondPM::SLUG);?></span>
												<input type="text" class="form-control date" name="warranty_end" id="warranty_end" value="<?php echo $warranty_end;?>"> 
											</div>
										</div>
									</div>
									<div class="col-sm-4">
										<div class="form-group">
											<div class="input-group">
												<input type="text" class="form-control" name="warranty_days" id="warranty_days" value="<?php echo $warranty_days;?>">
												<span class="input-group-addon"><?php _e('Days', $VagabondPM::SLUG);?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div> <!-- end of row -->

			</div>
			<!--
			<div class="col-sm-3">
				<div class="progress">
					<div class="progress-bar progress-bar-success" style="width: 35%">
					<span>20,000</span>
					</div>
					<div class="progress-bar progress-bar-danger" style="width: 65%">
					<span>30,000</span>
					</div>
				</div>
			</div>
			-->
		</div> <!-- end of row -->

<!-- Gantt -->
		<h3><?php _e('Gantt Chart', $VagabondPM::SLUG);?></h3>
		<div class="row">
			<div class="col-sm-12">
				<div id="gantti_canvas"></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-10" id="g_items">
			<?php
			if ($gantti_data){
				foreach ($gantti_data as $itm){
				?>
				<div class="row">
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default g_remove" type="button"><span class="glyphicon glyphicon-remove"></span></button>
								</span>
								<input type="text" name="g_phase[]" class="g_phase form-control" placeholder="<?php _e('Phase', $VagabondPM::SLUG);?>" value="<?php echo $itm['label'];?>">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								<input type="text" name="g_start[]" class="g_start form-control date" value="<?php echo $itm['start'];?>">

								<span class="input-group-addon"><?php _e('To', $VagabondPM::SLUG);?></span>
								<input type="text" name="g_end[]" class="g_end form-control date" value="<?php echo $itm['end'];?>">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<select name="g_class[]" class="g_class form-control">
								<option><?php _e('Normal', $VagabondPM::SLUG);?></option>
								<option value="important" <?php echo ($itm['class']=='important')?'selected':'';?>><?php _e('Important', $VagabondPM::SLUG);?></option>
								<option value="urgent" <?php echo ($itm['class']=='urgent')?'selected':'';?>><?php _e('Urgent', $VagabondPM::SLUG);?></option>
							</select>
						</div>
					</div>
				</div>
				<?php
				}
			}
			?>	
				<div class="row">
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default g_remove" type="button"><span class="glyphicon glyphicon-remove"></span></button>
								</span>
								<input type="text" name="g_phase[]" class="g_phase form-control" placeholder="<?php _e('Phase', $VagabondPM::SLUG);?>">
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								<input type="text" name="g_start[]" class="form-control date g_start">
	
								<span class="input-group-addon"><?php _e('To', $VagabondPM::SLUG);?></span>
								<input type="text" name="g_end[]" class="form-control date g_end">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<select name="g_class[]" class="g_class form-control">
								<option><?php _e('Normal', $VagabondPM::SLUG);?></option>
								<option value="important"><?php _e('Important', $VagabondPM::SLUG);?></option>
								<option value="urgent"><?php _e('Urgent', $VagabondPM::SLUG);?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="btn-group">
					<button type="button" id="btn_add_g_item" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span></button>
					<button type="button" id="btn_sav_g_item" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span></button>
				</div>

			</div>
		</div><!-- row-->

<!-- Requirements/Spec -->
		<h3><?php _e('Requirements/Spec', $VagabondPM::SLUG);?></h3>
		<div class="row">
			<div class="col-sm-10" id="req_item">
			<?php
			if ($req_data){
				foreach ($req_data as $itm){
				?>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default req_remove" type="button"><span class="glyphicon glyphicon-remove"></span></button>
								</span>
								<input class="form-control req_title" name="req_title[]" placeholder="<?php _e('Title', $VagabondPM::SLUG);?>" value="<?php echo $itm['req_title'];?>">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								<input class="form-control date req_date" name="req_date[]" value="<?php echo $itm['req_date'];?>">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-usd"></span></span>
								<input class="form-control req_pay_price" name="req_pay_price[]" placeholder="<?php _e('Charge', $VagabondPM::SLUG); ?>" value="<?php echo $itm['req_pay_price'];?>">
							</div>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<select class="form-control req_pay_times" name="req_pay_times[]">
								<option value="oneoff" <?php echo ($itm['req_pay_times']=='oneoff')?'selected':'';?>><?php _e('One Off');?></option>
								<option value="monthly" <?php echo ($itm['req_pay_times']=='monthly')?'selected':'';?>><?php _e('Monthly');?></option>
								<option value="yearly" <?php echo ($itm['req_pay_times']=='yearly')?'selected':'';?>><?php _e('Yearly');?></option>
							</select>
						</div>
					</div>
				</div>

				<?php
				}
			}
			?>
				<div class="row">
					<div class="col-sm-4">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-btn">
									<button class="btn btn-default req_remove" type="button"><span class="glyphicon glyphicon-remove"></span></button>
								</span>
								<input class="form-control req_title" name="req_title[]" placeholder="<?php _e('Title', $VagabondPM::SLUG);?>">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								<input class="form-control date req_date" name="req_date[]">
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon"><span class="glyphicon glyphicon-usd"></span></span>
								<input class="form-control req_pay_price" name="req_pay_price[]" placeholder="<?php _e('Charge', $VagabondPM::SLUG); ?>">
							</div>
						</div>
					</div>
					<div class="col-sm-2">
						<div class="form-group">
							<select class="form-control req_pay_times" name="req_pay_times[]">
								<option value="oneoff"><?php _e('One Off');?></option>
								<option value="monthly"><?php _e('Monthly');?></option>
								<option value="yearly"><?php _e('Yearly');?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-2">
				<div class="btn-group">
					<button type="button" id="btn_add_req_item" class="btn btn-default"><span class="glyphicon glyphicon-plus"></span></button>
					<button type="button" id="btn_sav_req_item" class="btn btn-primary"><span class="glyphicon glyphicon-ok"></span></button>
				</div>
			</div>
		</div>

<!-- Contract -->
		<h3><?php _e('Contract', $VagabondPM::SLUG);?></h3>
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('甲方', $VagabondPM::SLUG);?></span>
						<input type="text" class="form-control" name="contract_pa_name" id="contract_pa_name" value="<?php echo $contract_pa_name;?>" placeholder="<?php _e('Customer Title', $VagabondPM::SLUG);?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('乙方', $VagabondPM::SLUG);?></span>
						<input type="text" class="form-control" name="contract_pb_name" id="contract_pb_name" value="<?php echo $com_title;?>" placeholder="<?php _e('My Company Name', $VagabondPM::SLUG);?>"> 
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact Person', $VagabondPM::SLUG);?></span>
						<input type="text" class="form-control" name="contract_cont_person_a" id="contract_cont_person_a" value="<?php echo $contract_cont_person_a;?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact Person', $VagabondPM::SLUG);?></span>
						<input type="text" class="form-control" name="contract_cont_person_b" id="contract_cont_person_b" value="<?php echo $contract_cont_person_b;?>">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact Tel', $VagabondPM::SLUG);?></span>
						<input type="tel" class="form-control" name="contract_cont_tel_a" id="contract_cont_tel_a" value="<?php echo $contract_cont_tel_a;?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact Tel', $VagabondPM::SLUG);?></span>
						<input type="tel" class="form-control" name="contract_cont_tel_b" id="contract_cont_tel_b" value="<?php echo $contract_cont_tel_b;?>">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact E-mail', $VagabondPM::SLUG);?></span>
						<input type="email" class="form-control" name="contract_cont_email_a" id="contract_cont_email_a" value="<?php echo $contract_cont_email_a;?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Contact E-mail', $VagabondPM::SLUG);?></span>
						<input type="email" class="form-control" name="contract_cont_email_b" id="contract_cont_email_b" value="<?php echo $contract_cont_email_b;?>">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Address', $VagabondPM::SLUG);?></span>
						<input type="address" class="form-control" name="contract_cont_adds_a" id="contract_cont_adds_a" value="<?php echo $contract_cont_adds_a;?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Address', $VagabondPM::SLUG);?></span>
						<input type="address" class="form-control" name="contract_cont_adds_b" id="contract_cont_adds_b" value="<?php echo $com_adds;?>">
					</div>
				</div>
			</div>
		</div>
<!-- Company Tax ID -->
		<div class="row">
			<div class="col-xs-6 rborder1">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Tax ID', $VagabondPM::SLUG);?></span>
						<input type="address" class="form-control" name="contract_cont_taxid_a" id="contract_cont_taxid_a" value="<?php echo $contract_cont_taxid_a;?>"> 
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><?php _e('Tax ID', $VagabondPM::SLUG);?></span>
						<input type="address" class="form-control" name="contract_cont_taxid_b" id="contract_cont_taxid_b" value="<?php echo $com_num;?>">
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<div class="form-group">
					<textarea class="form-control" name="contract_memo" placeholder="需求備註"></textarea>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">
				<div class="form-group">
					<textarea class="form-control" name="contract_desc" placeholder="合約內容"></textarea>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-3">
				<div class="checkbox">
					<input type="checkbox" name="contract_need_payway" checked>
					加入付款方式
				</div>
			</div>
			<div class="col-sm-3">
				<div class="checkbox">
					<input type="checkbox" name="contract_need_contract" checked>
					加入合約內容
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-6">
				<button id="gen_contract" type="button" class="btn-primary"><?php _e('Generate/Renew Contract', $VagabondPM::SLUG); ?></button>
				<?php
				$contract_url = get_post_meta( $proj_id, 'contract_url', true ); 
				if ($contract_url) echo '<a href="'.$contract_url.'">下載合約</a>';
				?>
			</div>
		</div>
<!-- -->
		<h3><?php _e('Events', $VagabondPM::SLUG);?></h3>
		<div class="row">
			<div class="col-sm-4">
				<div class="form-group">
					<select class="form-control">
						<option>其他</option>
						<option>付款</option>
						<option>結案</option>
						<option>簽約</option>
					</select>
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
					<input type="text" class="form-control" name="" placeholder="Title">
				</div>
			</div>
			<div class="col-sm-4">
				<div class="form-group">
				</div>
			</div>
		</div>
		<h3><?php _e('Todo List', $VagabondPM::SLUG);?></h3>


			<ul>
			狀態：<input type="text" name="proj_state" id="proj_state" value="<?php echo $proj_state;?>" placeholder="例：洽談、開工、驗收..等，以,區隔" style="width:210px">
			報價：<input type="text" name="proj_price" id="proj_price" value="<?php echo $proj_price;?>">
			應收帳款：<input type="text" name="proj_no_paid" id="proj_no_paid" value="<?php echo $proj_no_paid;?>">
			</ul>

		<input type="submit" class="btn btn-primary" value="確定">
		<?php wp_nonce_field('','editproj_nonce'); ?>
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

function calculateDays( d0, d1 )
{
    var ndays = 1 + Math.round((d1.getTime()-d0.getTime())/(24*3600*1000));
    var nsaturdays = Math.floor((ndays + d0.getDay()) / 7);
    return ndays - 2*nsaturdays + (d0.getDay()==7) - (d1.getDay()==6);
}

$(function(){
	update_gantti();

	function update_gantti()
	{
		$.ajax({
			async: true, //mimic POST use false
			type: 'POST',
			url: '<?php echo $ajax_url;?>',
			data: {
				action: 'update_gantti',
				post_id: <?php echo $proj_id;?>,
				cellwidth: 25,
				cellheight: 35
			},
			dataType: 'html',
			success: function(res) {
				//console.log(res);
				$('#gantti_canvas').html(res);
			},
			error:function (xhr, ajaxOptions, thrownError){
				alert(ajaxOptions+':'+thrownError);
			}
		});
	}

	$('#btn_cal_bdays').on('click', function(){
		if ($('#kickoff_start').val() && $('#kickoff_end').val())
		{
			$('#work_day').val( calculateDays( new Date($('#kickoff_start').val()), new Date($('#kickoff_end').val())) );
		}
	});

	$('#btn_add_g_item').on('click', function(){
		$('.date').removeClass('hasDatepicker').attr('id','');
		$('#g_items').append($('#g_items .row:last').clone(true, true));
		$('.date').datepicker(dp_opt);
	});

	$('#btn_add_req_item').on('click', function(){
		$('.date').removeClass('hasDatepicker').attr('id','');
		$('#req_item').append($('#req_item .row:last').clone(true, true));
		$('.date').datepicker(dp_opt);
	});

	$('#gen_contract').on('click', function(){
		$.ajax({
			async: true, //mimic POST use false
			type: 'POST',
			url: '<?php echo $ajax_url;?>',
			data: {
				action: 'gen_contract',
				post_id: <?php echo $proj_id;?>
			},
			dataType: 'json',
			success: function(res) {
				//console.log(res);
				if (res.code=='1'){
					window.open(res.url, '_blank');
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				alert(ajaxOptions+':'+thrownError);
			}
		});
	});

	$('#btn_sav_g_item').on('click', function(){
		$.ajax({
			async: true, //mimic POST use false
			type: 'POST',
			url: '<?php echo $ajax_url;?>',
			data: {
				action: 'sav_gantt_func',
				post_id: <?php echo $proj_id;?>,
				g_phase: $('input:text.g_phase').serialize(),
				g_start: $('input:text.g_start').serialize(),
				g_end: $('input:text.g_end').serialize(),
				g_class: $('select.g_class').serialize()
			},
			dataType: 'json',
			success: function(res) {
				//console.log(res.text);
				if (res.code=='1'){
					alert(res.text);
					//window.location.reload();
				}
			},
			error:function (xhr, ajaxOptions, thrownError){
				alert(ajaxOptions+':'+thrownError);
			}
		});
	});	

	$('#btn_sav_req_item').on('click', function(){
		console.log($('input:text.req_title').serialize());
		$.ajax({
			async: true, //mimic POST use false
			type: 'POST',
			url: '<?php echo $ajax_url;?>',
			data: {
				action: 'sav_req_func',
				post_id: <?php echo $proj_id;?>,
				req_title: $('input:text.req_title').serialize(),
				req_date: $('input:text.req_date').serialize(),
				req_pay_price: $('input:text.req_pay_price').serialize(),
				req_pay_times: $('select.req_pay_times').serialize()
			},
			dataType: 'json',
			success: function(res) {
				console.log(res.text);
				if (res.code=='1'){
					alert(res.text);
					//window.location.reload();
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
	$('.req_remove').on('click', function(){
		$(this).parent().parent().parent().parent().parent().remove();
	})


});

</script>
<?php
get_footer();
?>