<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>
	</div><!-- #main .wrapper -->
	<footer id="colophon" role="contentinfo">
		<div class="site-info">
			2013 - Audi Lu
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
<?php
date_default_timezone_set('Asia/Taipei');

$kickoff_date = strtotime('2013-06-26');

$data = array(
	array(
		'name'	=>	"第一階段",
		'desc'	=>	"分析",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".$kickoff_date.'000'.")/",
			'to'	=> "/Date(".strtotime('+5 days', $kickoff_date).'000'.")/",
			'label'	=> "需求蒐集", 
			'customClass'	=> "ganttRed"
			)
		)
	),
	array(
		'name'	=>	"",
		'desc'	=>	"架構",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+1 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+5 days', $kickoff_date).'000'.")/",
			'label'	=> "規劃架構", 
			'customClass'	=> "ganttRed"
			)
		)
	),
	array(
		'name'	=>	"第二階段",
		'desc'	=>	"版型設計",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+5 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+10 days', $kickoff_date).'000'.")/",
			'label'	=> "設計提案", 
			'customClass'	=> "ganttGreen"
			)
		)
	),
	array(
		'name'	=>	"",
		'desc'	=>	"版型客製",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+10 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+25 days', $kickoff_date).'000'.")/",
			'label'	=> "前端開發", 
			'customClass'	=> "ganttGreen"
			)
		)
	),
	array(
		'name'	=>	"第三階段",
		'desc'	=>	"功能開發",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+10 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+30 days', $kickoff_date).'000'.")/",
			'label'	=> "後端及外掛開發", 
			'customClass'	=> "ganttOrange"
			)
		)
	),
	array(
		'name'	=>	"最終階段",
		'desc'	=>	"培訓",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+30 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+35 days', $kickoff_date).'000'.")/",
			'label'	=> "製發使用手冊", 
			'customClass'	=> "ganttBlue"
			)
		)
	),
	array(
		'name'	=>	"",
		'desc'	=>	"上線",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+35 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+36 days', $kickoff_date).'000'.")/",
			'label'	=> "試部署", 
			'customClass'	=> "ganttBlue"
			)
		)
	),
	array(
		'name'	=>	"",
		'desc'	=>	"驗收",
		'values'	=>	array(
			array(
			'id'	=> "t01",
			'from'	=> "/Date(".strtotime('+36 days', $kickoff_date).'000'.")/",
			'to'	=> "/Date(".strtotime('+39 days', $kickoff_date).'000'.")/",
			'label'	=> "驗收及微調", 
			'customClass'	=> "ganttBlue"
			)
		)
	),
);
$json = json_encode($data);

//若無ssl，為配合後台有ssl，需將https的admin-ajax.php的網址替換為http，以避免ajax跨域錯誤
$ajax_url = admin_url( 'admin-ajax.php' );
if (!is_ssl() && 'https' == substr($ajax_url, 0, 5)) {
	$ajax_url = 'http'.substr($ajax_url, 5);
}
?>

<script src="<?php echo get_stylesheet_directory_uri(); ?>/js/jquery.fn.gantt.js"></script>
<script>

	$(function() {
		"use strict";
		$('#del_proj').on('click', function(){
			if (confirm('確定刪除此專案嗎？') ) {
				var this_proj_block = $(this).parent().parent();
				$.ajax({
					type: 'POST',
					url: '<?php echo $ajax_url;?>',
					data:{
						action: 'del_proj_ajax', 
						proj_id: $(this).data('proj_id')
					},
					success: function(rst) {
						if (rst!='fail'){
							this_proj_block.hide('fast');
						}else{
							alert('刪除動作失敗，請再試一次。');
						}
					},
					error:function (xhr, ajaxOptions, thrownError){
						alert('傳送請求失敗，請再試一次。');
					}
				});
			}
		});
/*
		$(".gantt").popover({
			selector: ".bar",
			title: "I'm a popover",
			content: "And I'm the content of said popover.",
			trigger: "hover"
		});
*/
		//prettyPrint();

		$('#tab_g_preview').click(function(){
			$('#gantt_setting').hide();
			$('#gantt_preview').show();
			$(".gantt").gantt({
				source: <?php echo $json;?>,
				months: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
				navigate: "scroll",
				scale: "days", //weeks
				maxScale: "months",
				minScale: "days",
				itemsPerPage: 10,
				onItemClick: function(data) {
					//alert("Item clicked - show some details");
				},
				onAddClick: function(dt, rowId) {
					//alert("Empty space clicked - add an item!");
				},
				onRender: function() {
					//if (window.console && typeof console.log === "function") {
					//	console.log("chart rendered");
					//}
				}
			});
		});
		$('#tab_g_setting').click(function(){
			$('#gantt_preview').hide();
			$('#gantt_setting').show();
		});
	});

</script>

</body>
</html>