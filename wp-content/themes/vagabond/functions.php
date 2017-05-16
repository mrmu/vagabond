<?php
/*
	Vagabond - project management theme

	author: audi lu (mrmu.com.tw)
	version: 0.1
	last update: 2014/3
*/


//http://stackoverflow.com/questions/13340216/html-generated-microsoft-word-document-with-header-footer-and-watermark
if( !class_exists( 'WordWriter' ) ) 
{
	class WordWriter {
		function start() {
			ob_start();
			echo '<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40">';
		}
		function save($path) {	 
			echo "</html>";
			$data = ob_get_contents();
			ob_end_clean();
			$this->wirtefile ($path,$data);
		}
		function wirtefile ($fn,$data) {
			$fp=fopen($fn,"wb");
			fwrite($fp,$data);
			fclose($fp);
		}
	}
}

if( !class_exists( 'VagabondPM' ) ) 
{
	class VagabondPM 
	{
		const SLUG = 'VagabondPM';

		private $_version;
		
		//--------------------------------------------------------------------------------------------/
		// Constructor
		//--------------------------------------------------------------------------------------------/
		function __construct() 
		{
			// Initialization
			$this->init();

			add_action( 'init', array($this, 'reg_display_scripts_and_styles') );
			add_action( 'init', array($this, 'reg_admin_scripts_and_styles') );

			add_action( 'init', array($this, 'reg_post_type') );
			add_action( 'after_setup_theme', array($this, 'vaga_theme_setup') );
			add_filter( 'query_vars', array($this, 'add_get_vars') );

			add_filter( 'comment_text', array($this, 'modify_comment') );
			add_action( 'comment_form_logged_in_after', array($this, 'additional_comment_fields' ) );
			add_action( 'comment_form_after_fields', array($this, 'additional_comment_fields' ) );
			add_action( 'comment_post', array($this, 'save_comment_meta_data' ) );

			add_action( 'admin_menu', array($this, 'create_proj_metas', 0 ) );

			add_action( 'wp_ajax_del_proj_ajax', array($this, 'del_proj_ajax' ) ); 
			add_action( 'wp_ajax_nopriv_del_proj_ajax', array($this, 'del_proj_ajax' ) );

			add_action( 'wp_ajax_gen_contract', array($this, 'gen_contract' ) ); 
			add_action( 'wp_ajax_nopriv_gen_contract', array($this, 'gen_contract' ) );

			add_action( 'wp_ajax_update_gantti', array($this, 'update_gantti' ) ); 
			add_action( 'wp_ajax_nopriv_update_gantti', array($this, 'update_gantti' ) );
		}

		private function init() 
		{
			global $wpdb;
			
			$this->_version = "1.0";
			$installed_ver = get_option( self::SLUG.'_version' ); 
			
			if( $installed_ver != $this->plugin_version ) 
			{
				// [TODO] 版本不同時要作的事..
				update_option( self::SLUG.'_version', $this->plugin_version );
			}
		}

		function update_gantti()
		{
			require('lib/gantti.php'); 
			$post_id = esc_attr( $_POST['post_id'] );
			$cellwidth = esc_attr( $_POST['cellwidth'] );
			$cellheight = esc_attr( $_POST['cellheight'] );

			$gantti_data = get_post_meta( $post_id, 'gantti_data', true );
			$gantti = new Gantti($gantti_data, array(
			  // 'title'      => '
					// 	  	<div class="btn-group">
					// 	  		<button data-pid="'.$post_id.'" class="btn btn-xs btn-default zoom"> 
					// 	  			<span class="glyphicon glyphicon-zoom-in"></span> 
					// 	  		</button> 
					// 	  		<button data-pid="'.$post_id.'" class="btn btn-xs btn-default zoom"> 
					// 	  			<span class="glyphicon glyphicon-zoom-out"></span> 
					// 	  		</button>
					// 	  	</div>',
			  'cellwidth'  => $cellwidth,
			  'cellheight' => $cellheight,
			  'today'      => true
			));

			echo $gantti;

			die();			
		}

		function gen_contract()
		{
			global $wpdb;
			$post_id = esc_attr( $_POST['post_id'] );
			$upload_dir = wp_upload_dir();

			$file_dir = $upload_dir['basedir'].'/contracts/'.$post_id.'.doc';
			$file_url = $upload_dir['baseurl'].'/contracts/'.$post_id.'.doc';

			$arg = array(
				'post_id' => $post_id,
				'need_contract' => true,
				'need_payway' => true
			);

			$this->write_to_word($file_dir, $arg);
			update_post_meta( $post_id, 'contract_url', $file_url );

			$rtn_ary = array('code'=>'1', 'url'=>$upload_dir['baseurl'].'/contracts/'.$post_id.'.doc');
			echo json_encode($rtn_ary, JSON_FORCE_OBJECT);

			die();
		}

		//--------------------------------------------------------------------------------------------/
		// create_proj_metas - Project MetaBox
		//--------------------------------------------------------------------------------------------/		
		function create_proj_metas() {
			add_meta_box(array($this, 'proj_status'), __('Project State', self::SLUG), 'proj_status_ui', 'proj', 'normal', 'high', null);
		}

		//--------------------------------------------------------------------------------------------/
		// proj_state_ui - Project Status MetaBox UI
		//--------------------------------------------------------------------------------------------/		
		function proj_status_ui()
		{
			global $post;
			$entitle_cnt = get_post_meta( $post->ID, 'proj_status', true );
		?>
			<th scope="row"><?php _e('Status：', 'VagabondPM');?></th>
				<td>
					<label for="proj_status">
						<input id="proj_status" type="text" size="75" name="proj_status" value="<?php echo $entitle_cnt;?>" />
					</label>
				</td>
			</tr>
		<?php
		}

		//--------------------------------------------------------------------------------------------/
		// reg_post_type - register custom post type
		//--------------------------------------------------------------------------------------------/		
		function reg_post_type()
		{
			$labels = array(
				'name' => __( 'Projects', self::SLUG ),
				'singular_name' => __( 'Project', self::SLUG )
			);			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'has_archive' => true,
				'rewrite' => array('slug' => 'proj'),
				//'menu_icon' => get_bloginfo('stylesheet_directory').'/images/vaga_icon.png',
				'taxonomies' => array('proj_cate', 'post_tag')
				//'exclude_from_search' => true
			);
			register_post_type( 'proj', $args);

			// Reg Custom Taxonomy
			$args = array(
			  'label'=> __('Project Category', self::SLUG ),
			  'hierarchical'=>true
			);
			register_taxonomy('proj_cate', 'proj', $args);

		}

		//--------------------------------------------------------------------------------------------/
		// vaga_theme_setup - i18n
		//--------------------------------------------------------------------------------------------/		
		function vaga_theme_setup()
		{
		    load_theme_textdomain(self::SLUG, get_stylesheet_directory().'/languages');
		}

		//--------------------------------------------------------------------------------------------/
		// add_get_vars - 
		//--------------------------------------------------------------------------------------------/
		function add_get_vars($public_query_vars) 
		{
			$public_query_vars[] = 'proj_id'; 
			return $public_query_vars;
		}

		//--------------------------------------------------------------------------------------------/
		// modify_comment - 
		//--------------------------------------------------------------------------------------------/
		function modify_comment( $text ){

			if ( $commenttitle = get_comment_meta( get_comment_ID(), 'title', true ) ) {
				$commenttitle = '<div style="float:left"><strong>' . esc_attr( $commenttitle ) . '</strong>：';
				$text = $commenttitle . $text;
			}

			if ( $commentrating = get_comment_meta( get_comment_ID(), 'rating', true ) ) {
				$text = '<div class="comment_line '.strtolower($commentrating).'">'.$text.'</div>';
			}
			/*
			if ( $commentstate = get_comment_meta( get_comment_ID(), 'pstate', true ) ) {
				$commentstate = '['.$commentstate.']</div><div style="clear:both;"></div>';
				$text = $text . $commentstate;
			}
			*/
			return $text;
		}

		//--------------------------------------------------------------------------------------------/
		// additional_comment_fields - 
		//--------------------------------------------------------------------------------------------/
		function additional_comment_fields () {
		?>
			<style>
				.commentrating{float:left;}
			</style>
			
			
			<p>
				<div><?php _e('Comment Title: ', self::SLUG); ?><input id="title" name="title" type="text" size="30" /></div>
				<label style="float:left"><?php _e('Priority: ', self::SLUG); ?></label>
				<span class="commentratingbox">
					<label class="commentrating"><input type="radio" name="rating" id="rating" value="high"><?php _e('High: ', self::SLUG); ?></label>
					<label class="commentrating"><input type="radio" name="rating" id="rating" value="medium" checked><?php _e('Medium: ', self::SLUG); ?></label>
					<label class="commentrating"><input type="radio" name="rating" id="rating" value="low"><?php _e('Low: ', self::SLUG); ?></label>
				</span>
			</p>
			<div style="clear:both;"></div>
			<!--
			<p>
				<label style="float:left">狀態：</label>
				<span class="commentratingbox">
					<label class="commentrating"><input type="radio" name="pstate" id="pstate" value="open" checked>處理中</label>
					<label class="commentrating"><input type="radio" name="pstate" id="pstate" value="close">已解決</label>
				</span>
			</p>
			<div style="clear:both;"></div>
			-->
		<?php
		}

		//--------------------------------------------------------------------------------------------/
		// save_comment_meta_data - 
		//--------------------------------------------------------------------------------------------/
		function save_comment_meta_data( $comment_id ) 
		{
			if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ){
				$title = wp_filter_nohtml_kses($_POST['title']);
				add_comment_meta( $comment_id, 'title', $title );
			}
			
			if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') ){
				$rating = wp_filter_nohtml_kses($_POST['rating']);
				add_comment_meta( $comment_id, 'rating', $rating );
			}

			//if ( ( isset( $_POST['pstate'] ) ) && ( $_POST['pstate'] != '') ){
			//	$pstate = wp_filter_nohtml_kses($_POST['pstate']);
			//	add_comment_meta( $comment_id, 'pstate', $pstate );
			//}
		}

		//--------------------------------------------------------------------------------------------/
		// del_proj_ajax - Delete Project by AJAX
		//--------------------------------------------------------------------------------------------/		
		function del_proj_ajax() {
			if (isset($_POST['proj_id'])){
				if (!wp_delete_post( $_POST['proj_id'] )){
					//echo 'fail';
				}
			}
			die();
		}

		//--------------------------------------------------------------------------------------------/
		// reg_display_scripts_and_styles - 在前台註冊js及css
		//--------------------------------------------------------------------------------------------/
		function reg_display_scripts_and_styles() 
		{
			if (!is_admin())
			{
				wp_register_style( 'jquery-ui-datepicker-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/smoothness/jquery-ui.css');
				wp_enqueue_style('jquery-ui-datepicker-style' );

				wp_deregister_script('jquery');
				wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js', false, '1.9.1');
				wp_enqueue_script('jquery');
				wp_register_script('blocksit', get_stylesheet_directory_uri().'/js/blocksit.min.js', false, '1.0.1', true);
				wp_enqueue_script('blocksit');

				wp_register_script('jquery-ui-all', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js', false, '1.10.1', true);

				wp_deregister_script('jquery-ui-core');
				wp_register_script('jquery-ui-core', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-datepicker');
				wp_register_script('jquery-ui-datepicker', false, array('jquery-ui-all'));

				wp_enqueue_script('jquery-ui-widget');	// jqery ui core
				wp_enqueue_script('jquery-ui-mouse');	// jqery ui core
				wp_enqueue_script('jquery-ui-datepicker');	// jqery ui core

				/*
				wp_register_script('jquery-ui-all', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js', false, '1.8.12', true);
				wp_deregister_script('jquery-ui-core');
				wp_register_script('jquery-ui-core', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-core');
				wp_register_script('jquery-effects-core', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-blind');
				wp_register_script('jquery-effects-blind', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-bounce');
				wp_register_script('jquery-effects-bounce', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-clip');
				wp_register_script('jquery-effects-clip', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-drop');
				wp_register_script('jquery-effects-drop', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-explode');
				wp_register_script('jquery-effects-explode', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-fade');
				wp_register_script('jquery-effects-fade', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-fold');
				wp_register_script('jquery-effects-fold', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-highlight');
				wp_register_script('jquery-effects-highlight', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-pulsate');
				wp_register_script('jquery-effects-pulsate', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-scale');
				wp_register_script('jquery-effects-scale', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-shake');
				wp_register_script('jquery-effects-shake', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-slide');
				wp_register_script('jquery-effects-slide', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-effects-transfer');
				wp_register_script('jquery-effects-transfer', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-accordion');
				wp_register_script('jquery-ui-accordion', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-autocomplete');
				wp_register_script('jquery-ui-autocomplete', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-button');
				wp_register_script('jquery-ui-button', false, array('jquery-ui-all'));

				wp_deregister_script('jquery-ui-dialog');
				wp_register_script('jquery-ui-dialog', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-draggable');
				wp_register_script('jquery-ui-draggable', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-droppable');
				wp_register_script('jquery-ui-droppable', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-mouse');
				wp_register_script('jquery-ui-mouse', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-position');
				wp_register_script('jquery-ui-position', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-progressbar');
				wp_register_script('jquery-ui-progressbar', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-resizable');
				wp_register_script('jquery-ui-resizable', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-selectable');
				wp_register_script('jquery-ui-selectable', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-slider');
				wp_register_script('jquery-ui-slider', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-sortable');
				wp_register_script('jquery-ui-sortable', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-tabs');
				wp_register_script('jquery-ui-tabs', false, array('jquery-ui-all'));
				wp_deregister_script('jquery-ui-widget');
				wp_register_script('jquery-ui-widget', false, array('jquery-ui-all'));
				*/

				$this->load_file( self::SLUG . '-bootstrap-style', '/bootstrap-dist/css/bootstrap.min.css');
				$this->load_file( self::SLUG . '-bootstrap-theme-style', '/bootstrap-dist/css/bootstrap-theme.min.css');
				$this->load_file( self::SLUG . '-gantt-style', '/css/gantt.style.css');
				
				$this->load_file( self::SLUG . '-php-gantt-style', '/css/gantti.css');

				$this->load_file( self::SLUG . '-display-script', '/bootstrap-dist/js/bootstrap.min.js', true, true ); //put in footer
				$this->load_file( self::SLUG . '-custom-script', '/js/custom.js', true, true ); //put in footer
			}
		}

		//--------------------------------------------------------------------------------------------/
		// reg_admin_scripts_and_styles - 在admin init 註冊js及css
		//--------------------------------------------------------------------------------------------/
		function reg_admin_scripts_and_styles() 
		{
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			//$this->load_file( self::SLUG . '-admin-script', '/js/admin.js', true, true ); //put in footer
			//$this->load_file( self::SLUG . '-admin-style', '/css/admin.css' );
		} 

		static function vaga_comment( $comment, $args, $depth ) 
		{
			$GLOBALS['comment'] = $comment;

			switch ( $comment->comment_type ) :
				case 'pingback' :
				case 'trackback' :
				// Display trackbacks differently than normal comments.
			?>
			<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'twentytwelve' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?></p>
			<?php
					break;
				default :
				// Proceed with normal comments.
				global $post;
			?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<header class="comment-meta comment-author vcard">
						<?php
							echo get_avatar( $comment, 44 );
							printf( '<cite class="fn">%1$s %2$s</cite>',
								get_comment_author_link(),
								// If current post author is also comment author, make it known visually.
								( $comment->user_id === $post->post_author ) ? '<span> ' . __( 'Post author', 'twentytwelve' ) . '</span>' : ''
							);
							printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
								esc_url( get_comment_link( $comment->comment_ID ) ),
								get_comment_time( 'c' ),
								/* translators: 1: date, 2: time */
								sprintf( __( '%1$s at %2$s', 'twentytwelve' ), get_comment_date(), get_comment_time() )
							);
						?>
					</header><!-- .comment-meta -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentytwelve' ); ?></p>
					<?php endif; ?>

					<section class="comment-content comment">
						<?php comment_text(); ?>
						<?php edit_comment_link( __( 'Edit', 'twentytwelve' ), '<p class="edit-link">', '</p>' ); ?>
					</section><!-- .comment-content -->

					<div class="reply">
						<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'twentytwelve' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
					</div><!-- .reply -->
				</article><!-- #comment-## -->
			<?php
				break;
			endswitch; // end comment_type check
		}

		function get_post_abs($len=55)
		{
			global $post;
			$text = $post->post_content; 
			$text = strip_shortcodes( $text );
			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$excerpt_length = apply_filters('excerpt_length', $len);
			$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
			$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
			$the_excerpt = $text; 
			return $the_excerpt;
		}

		function get_post_1st_img($post_content='')
		{
			$first_img = '';
			$thumbnail = '';
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);
			$first_img = $matches [1][0];
			$thumbnail = $first_img;
			return $thumbnail;
		}

		//--------------------------------------------------------------------------------------------/
		// load_file - 載入及註冊 scripts 及 styles.
		//
		// @name		註冊ID
		// @file_path	檔案路徑
		// @is_script	是否為javascript
		// @is_footer	是否將javasrcipt放至footer
		//--------------------------------------------------------------------------------------------/
		private function load_file( $name, $file_path, $is_script = false, $is_footer = false )
		{

			$url = get_stylesheet_directory_uri().$file_path;
			$file = get_stylesheet_directory().$file_path;

			//$url = plugins_url($file_path, __FILE__);
			//$file = plugin_dir_path(__FILE__) . $file_path;
			
			if( file_exists( $file ) ) 
			{
				if( $is_script ) {
					wp_register_script( $name, $url, array('jquery'), false, $is_footer );
					wp_enqueue_script( $name );
				} 
				else 
				{
					$url .= '?'.filemtime($file);
					wp_register_style( $name, $url );
					wp_enqueue_style( $name );
				}
			}
		}

		//--------------------------------------------------------------------------------------------/
		// write_to_word - 輸出成Word檔 (.doc)
		//
		// @file_path	檔案路徑
		//--------------------------------------------------------------------------------------------/
		function write_to_word($file_path, $arg='') 
		{
			if (!$file_path){
				return false;
			}
			if (!$arg){
				return false;
			}

			// 專案資訊
			$post_id = $arg['post_id'];
			$proj_title = get_the_title($post_id);
			$kickoff_start = get_post_meta($post_id, 'kickoff_start', true);
			$kickoff_end = get_post_meta($post_id, 'kickoff_end', true);
			$work_day = get_post_meta($post_id, 'work_day', true);
			$req_data = get_post_meta( $post_id, 'req_data', true );

			// 需求規格明細
			$price_tot = 0;
			$spec_list = '';
			$no = 1;
			if ($req_data){
				foreach ($req_data as $itm){
					$price_tot += $itm['req_pay_price'];
					$spec_list .= '
						<tr>
							<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">'.$no.'</font></td>
							<td style="width:30%;"><font face="微軟正黑體,新細明體,times,serif">'.$itm["req_title"].'</font></td>
							<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">1</font></td>
							<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">式</font></td>
							<td style="width:20%; text-align:right;"><font face="微軟正黑體,新細明體,times,serif">'.$itm["req_pay_price"].' 元</font></td>
							<td style="width:20%; text-align:right;"><font face="微軟正黑體,新細明體,times,serif">'.$itm["req_pay_price"].' 元</font></td>
						</tr>
					';
					$no++;
				}
			}
			$price_tax = $price_tot * 0.05; //營業稅
			$price_tot_w_tax = $price_tot * 1.05; //含稅總價
			$ph1_price = round($price_tot_w_tax * 20/100); //訂金(含稅)
			$ph2_price = $price_tot_w_tax - $ph1_price; //尾款(含稅)

			// 乙方公司資料
			$ary_com_profile = get_option('company_profile');
			$com_title = $ary_com_profile['com_title'];
			$bank_title = $ary_com_profile['bank_title'];
			$bank_account = $ary_com_profile['bank_account'];
			$bank_num = $ary_com_profile['bank_num'];
			$bank_com_title = $ary_com_profile['bank_com_title'];
			$com_num = $ary_com_profile['com_num'];
			$com_adds = $ary_com_profile['com_adds'];

			if ($arg['need_contract'])
			{
				$contract_content = "<li>合約內容：<ol><li>乙方依約需於結案時交付網站完整程式。</li><li>一年完整保固。</li><li>本專案開始前，甲乙雙方各自擁有之特有技術、營業秘密、專利權、著作權及任何其他無形資產及特有權利均歸甲、乙方各自所有；本專案內容依甲方需求規劃、開發之平台及軟體，甲方得無償使用，且可進行所有權、專利權、著作權、等其它智慧財產權申請。</li><li>保密義務：甲乙雙方對他方之營業秘密或協力廠商之相關訊息，任一方對他方均負有保密之義務。</li><li>乙方之維運服務期程係以驗收完成後起算。</li><li>準據法及管轄法院：本報價單視同合約具有同等法律效力。本報價單之準據法為中華民國法律，凡本報價單未約定之其它事項，均應依中華民國法律之規定。任何與本報價單有關或因本報價單而引起之爭議，雙方同意以台灣士林地方法院為第一審管轄法院。</li></ol></li>";

				// 要做 minify，不然格式可能跑掉
				// $contract_content = "
				// 	<li>合約內容：
				// 		<ol>
				// 			<li>乙方依約需於結案時交付網站完整程式。</li>
				// 			<li>一年完整保固。</li>
				// 			<li>本專案開始前，甲乙雙方各自擁有之特有技術、營業秘密、專利權、著作權及任何其他無形資產及特有權利均歸甲、乙方各自所有；本專案內容依甲方需求規劃、開發之平台及軟體，甲方得無償使用，且可進行所有權、專利權、著作權、等其它智慧財產權申請。</li>
				// 			<li>保密義務：甲乙雙方對他方之營業秘密或協力廠商之相關訊息，任一方對他方均負有保密之義務。</li>
				// 			<li>乙方之維運服務期程係以驗收完成後起算。</li>
				// 			<li>準據法及管轄法院：本報價單視同合約具有同等法律效力。本報價單之準據法為中華民國法律，凡本報價單未約定之其它事項，均應依中華民國法律之規定。任何與本報價單有關或因本報價單而引起之爭議，雙方同意以台灣士林地方法院為第一審管轄法院。</li>
				// 		</ol>
				// 	</li>
				// ";
			}

			if ($arg['need_payway'])
			{
				$payway_desc = '<li>付款方式：<ul><li>專案成立簽約款：支付總價金額之20 %，新台幣 '.$ph1_price.' 元整 (含稅)。</li><li>專案上線驗收尾款：支付總價金額之80%，新台幣 '.$ph2_price.' 元整 (含稅)。</li></ul></li><li>依付款作業流程，階段成立開立發票請款：<ul><li>專案成立簽約款：開立發票請款，14日電匯入帳。</li><li>專案上線驗收完成後：開立發票請款，14日內電匯入帳。</li></ul></li>';

				// 要做 minify，不然格式可能跑掉
				// $payway_desc = '
				// 	<li>付款方式：
				// 		<ul>
				// 			<li>專案成立簽約款：支付總價金額之20 %，新台幣 '.($price_tot * 20/100).' 元整 (含稅)。</li>
				// 			<li>專案上線驗收尾款：支付總價金額之80%，新台幣 '.($price_tot * 80/100).' 元整 (含稅)。</li>
				// 		</ul>
				// 	</li>
				// 	<li>依付款作業流程，階段成立開立發票請款：
				// 		<ul>
				// 			<li>專案成立簽約款：開立發票請款，14日電匯入帳。</li>
				// 			<li>專案上線驗收完成後：開立發票請款，14日內電匯入帳。</li>
				// 		</ul>
				// 	</li>
				// ';
			}

			$html = '
			<style>
			v\:* {behavior:url(#default#VML);}
			o\:* {behavior:url(#default#VML);}
			w\:* {behavior:url(#default#VML);}
			.shape {behavior:url(#default#VML);}
			</style>
			<style>
			@page
			{
			    mso-page-orientation: portrait;
			    size:21cm 29.7cm;    margin:1cm 1cm 1cm 1cm;
			}
			@page Section1 {
			    mso-header-margin:.5in;
			    mso-footer-margin:.5in;
			    mso-header: h1;
			    mso-footer: f1;
			}
			p.MsoFooter, li.MsoFooter, div.MsoFooter
			{
			    margin:0in;
			    margin-bottom:.0001pt;
			    mso-pagination:widow-orphan;
			    tab-stops:center 3.0in center 3.0in;
			    font-size:12.0pt;
			}
			table#headerfooter
			{
			    margin:0in 0in 0in 900in;
			    width:1px;
			    height:1px;
			    overflow:hidden;
			}
			div.Section1 {
				page:Section1; 
			}

			table{
				font-size:15px;
				width: 100%;
				border: 0px solid;
				margin: 0 auto;
				background-color:#888888; 
			}
			td{
				border:0px solid;
				background-color:#ffffff;
				padding:8px 5px;
			}
			li{line-height: 130%;}
			ol,ul,li,td{
				word-wrap: break-word;
				word-break: break-all;
			}
			</style>
			<xml>
				<w:WordDocument>
				<w:View>Print</w:View>
				<w:Zoom>100</w:Zoom>
				<w:DoNotOptimizeForBrowser/>
				</w:WordDocument>
			</xml>
			</head>

			<body>
			<div class="Section1">
			  <table id="headerfooter" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td>
			      <div style="mso-element:header" id="h1" >
			        <!-- HEADER-tags -->
			            <p class=MsoHeader >'.$com_title.' - 估價單</p>
			        <!-- end HEADER-tags -->
			      </div>
			    </td>
			    <td>
			      <div style="mso-element:footer" id="f1">
			        <p class=MsoFooter>
			          <span style="mso-tab-count:2"></span>
			          <span style="mso-field-code: PAGE ">
			           <span style="mso-no-proof:yes"></span> from 
			           <span style="mso-field-code: NUMPAGES "></span>
			           <!-- end FOOTER-tags -->
			          </span>
			        </p>
			      </div>
			    </td>
			    </tr>
			  </table>

			<table cellspacing=1 cellpadding=1>
				<tr><td colspan="2"> 
					<span style="font-size:20px;">
						<font face="微軟正黑體,新細明體,times,serif">
						專案名稱：'.$proj_title.'
						</font>
					</span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<span style="font-size:15px;">
						<font face="微軟正黑體,新細明體,times,serif">
						(製表日期：'.current_time( 'mysql' ).')
						</font>
					</span>
				</td></tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">甲方：XX 股份有限公司</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">乙方：'.$com_title.'</font></td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">聯絡人：</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">聯絡人：</font></td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">電話：</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">電話：</font></td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">E-Mail：</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">E-Mail：</font></td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">地址：</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">地址：'.$com_adds.'</font></td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">統一編號：</font></td>
					<td><font face="微軟正黑體,新細明體,times,serif">統一編號：'.$com_num.'</font></td>
				</tr>
				<tr>
					<td colspan="2">
						<div><font face="微軟正黑體,新細明體,times,serif">需求規格及報價：</font></div>
						<table cellspacing=1 cellpadding=1>
							<tr>
								<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">編號</font></td>
								<td style="width:30%;"><font face="微軟正黑體,新細明體,times,serif">項目</font></td>
								<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">數量</font></td>
								<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">單位</font></td>
								<td style="width:20%;"><font face="微軟正黑體,新細明體,times,serif">單價</font></td>
								<td style="width:20%;"><font face="微軟正黑體,新細明體,times,serif">總價</font></td>
							</tr>
							'.$spec_list.'
							<tr>
								<td style="width:10%;"><font face="微軟正黑體,新細明體,times,serif">備註</font></td>
								<td style="width:50%;" colspan="3">
									<font face="微軟正黑體,新細明體,times,serif">
									<ol>
										<li>開發方式：PHP / WordPress</li>
										<li>預估工時：'.$work_day.'個工作日</li>
										<li>預估開發期程：'.$kickoff_start.' 至 '.$kickoff_end.'</li>
										<li>一年保固：保固指原本規格需求內之功能確認可正常運作使用。</li>
									</ol>
									</font>
								</td>
								<td style="width:20%; text-align:right; vertical-align:center;">
									<font face="微軟正黑體,新細明體,times,serif"><b>
									合計：<br><br>
									營業稅：<br><br>
									總價：<br><br>
									</b></font>
								</td>
								<td style="width:20%; text-align:right; vertical-align:center;">
									<font face="微軟正黑體,新細明體,times,serif">
									<b>
									'.$price_tot.' 元<br><br>
									'.$price_tax.' 元<br><br>
									'.$price_tot_w_tax.' 元<br><br>
									</b>
									</font>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<font face="微軟正黑體,新細明體,times,serif">
					<ul>
						<li>此報價單14天有效。</li>
						'.$payway_desc.'
						'.$contract_content.'
						<li>如無異議，請於用印後回傳並寄回正本，正本一式兩份，由雙方各執一份為憑。</li>
					</ul>
					</font>
					</td>
				</tr>
				<tr>
					<td><font face="微軟正黑體,新細明體,times,serif">甲方代表人簽名：</font><br><br><br><br><br><br><br><br></td>
					<td><font face="微軟正黑體,新細明體,times,serif">乙方代表人簽名：</font><br><br><br><br><br><br><br><br></td>
				</tr>
			</table>
			</div>
			</body>
			'; 

			$wordname = $file_path; 

			$word = new WordWriter(); 

			$word->start(); 
			echo $html; 
			$word->save($wordname); 

			// header('Content-Description: File Transfer');
			// header('Content-Type: application/msword');
			// header("Content-Disposition: attachment; filename='{$wordname}'");
			// header('Content-Transfer-Encoding: binary');
			// header('Expires: 0');
			// header('Cache-Control: must-revalidate');
			// header('Pragma: public');
			// header('Content-Length: ' . filesize($wordname));
			// ob_clean();
			// //ob_flush();
			// flush(); 
			// readfile($wordname);
		}
	}//end of class
}

/*
if (!function_exists(fb_likewidget)) {
	add_action('twentytwelve_credits', 'fb_likewidget', 99);
	function fb_likewidget(){
		?>
		<div style="float:left;" class="fb-like" data-href="http://facebook.com/mrmuStudio" data-send="false" data-width="450" data-show-faces="true"></div>
		<?php
	}
}
*/

if( class_exists( 'VagabondPM' ) ) {
	global $VagabondPM;
	$VagabondPM = new VagabondPM();
}

//sav_gantt_func - WP AJAX
if (!function_exists(sav_req_func))
{
	function sav_req_func()
	{
		global $wpdb;
		$post_id = esc_attr( $_POST['post_id'] );
		$req_title = esc_attr($_POST['req_title']);
		$req_date = esc_attr($_POST['req_date']);
		$req_pay_price = esc_attr($_POST['req_pay_price']);
		$req_pay_times = esc_attr($_POST['req_pay_times']);

		$req_title = str_replace( 'req_title%5B%5D=', '', $req_title);
		$ary_req_title = explode( '&amp;', urldecode($req_title));

		$req_date = str_replace( 'req_date%5B%5D=', '', $req_date);
		$ary_req_date = explode( '&amp;', urldecode($req_date));

		$req_pay_price = str_replace( 'req_pay_price%5B%5D=', '', $req_pay_price);
		$ary_req_pay_price = explode( '&amp;', urldecode($req_pay_price));

		$req_pay_times = str_replace( 'req_pay_times%5B%5D=', '', $req_pay_times);
		$ary_req_pay_times = explode( '&amp;', urldecode($req_pay_times));

		$req_data = array();

		$i=0;
		foreach ($ary_req_title as $p) {
			if ($p)
			{
				$req_data[] = array(
					'req_title' => $p,
					'req_date' => $ary_req_date[$i], 
					'req_pay_price'   => $ary_req_pay_price[$i], 
					'req_pay_times'	=> $ary_req_pay_times[$i], 
				);
				$i++;
			}
		}

		update_post_meta( $post_id, 'req_data', $req_data );

		$rtn_ary = array('code'=>'1', 'text'=>'更新成功。');

		echo json_encode($rtn_ary, JSON_FORCE_OBJECT);
		die();
	}
	add_action( 'wp_ajax_sav_req_func', 'sav_req_func' ); // 針對已登入的使用者
	add_action( 'wp_ajax_nopriv_sav_req_func', 'sav_req_func' ); // 針對未登入的使用者
}

//sav_gantt_func - WP AJAX
if (!function_exists(sav_gantt_func))
{
	function sav_gantt_func()
	{
		global $wpdb;
		$post_id = esc_attr( $_POST['post_id'] );
		$g_phase = esc_attr($_POST['g_phase']);
		$g_start = esc_attr($_POST['g_start']);
		$g_end = esc_attr($_POST['g_end']);
		$g_class = esc_attr($_POST['g_class']);

		$g_phase = str_replace( 'g_phase%5B%5D=', '', $g_phase);
		$ary_g_phase = explode( '&amp;', urldecode($g_phase));

		$g_start = str_replace( 'g_start%5B%5D=', '', $g_start);
		$ary_g_start = explode( '&amp;', urldecode($g_start));

		$g_end = str_replace( 'g_end%5B%5D=', '', $g_end);
		$ary_g_end = explode( '&amp;', urldecode($g_end));

		$g_class = str_replace( 'g_class%5B%5D=', '', $g_class);
		$ary_g_class = explode( '&amp;', urldecode($g_class));

		$gantti_data = array();

		$i=0;
		foreach ($ary_g_phase as $p) {
			if ($p)
			{
				$gantti_data[] = array(
					'label' => $p,
					'start' => $ary_g_start[$i], 
					'end'   => $ary_g_end[$i], 
					'class'	=> $ary_g_class[$i], 
				);
				$i++;
			}
		}

//print_r($g_class);
		update_post_meta( $post_id, 'gantti_data', $gantti_data );

		$rtn_ary = array('code'=>'1', 'text'=>'更新成功。');

		echo json_encode($rtn_ary, JSON_FORCE_OBJECT);
		die();
	}
	add_action( 'wp_ajax_sav_gantt_func', 'sav_gantt_func' ); // 針對已登入的使用者
	add_action( 'wp_ajax_nopriv_sav_gantt_func', 'sav_gantt_func' ); // 針對未登入的使用者
}
		
?>