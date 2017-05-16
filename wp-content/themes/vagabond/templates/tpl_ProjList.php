<?php
/**
 * Template Name: Project List
 *
 * Add by Audi - 2013.06.05
 *
 */ 
get_header(); ?>
<style>

.pin{float:left; margin:5px; padding:5px; border:1px solid; width:22%; height:200px;
	background: #FEFEFE;
	border: 1px solid #bbb;
	box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
	background: -webkit-linear-gradient(45deg, #FFF, #f8f8f8);
	-webkit-transition: all .2s ease;
}

.set_item{
	padding:5px 0;
}

</style>
	<div class="wrap">
		<ul id="columns">
		<?php
		$pages_to_ids = get_option("vpm_page_ids");
		$page_id_pj_edit = $pages_to_ids['Edit Project'];

		$ori_query_obj = $wp_query;
		$wp_query = null;
		$wp_query = new WP_Query();
		$wp_query->query('post_type=proj&posts_per_page=100&paged='.$paged);
		while ($wp_query->have_posts()) :
			$wp_query->the_post();
			$edit_url = add_query_arg( 'proj_id', $wp_query->post->ID, get_permalink( $page_id_pj_edit ) );
		?>
				<li class="pin">
					<h5> 
						<a href="<?php echo $edit_url;?>"><?php the_title(); ?></a>
					</h5>

					<div class="set_item">狀態: <?php the_tags('', ', ', ''); ?>  </div>
					<div class="set_item">預算: <?php echo get_post_meta($post->ID, 'proj_price', true); ?>  </div>
					<div class="set_item">
						<a href="<?php the_permalink(); ?>">加入任務</a>
						<a id="del_proj" data-proj_id="<?php the_ID();?>" href="javascript:void();">刪除</a>
					</div>
					<ul style="list-style-type:circle; margin:10px 0 0 22px;">
					<?php 
					$defaults = array(
						'author_email' => '',
						'ID' => '',
						'karma' => '',
						'number' => '',
						'offset' => '',
						'orderby' => '',
						'order' => 'DESC',
						'parent' => '',
						'post_id' => get_the_ID(),
						'post_author' => '',
						'post_name' => '',
						'post_parent' => '',
						'post_status' => '',
						'post_type' => '',
						'status' => '',
						'type' => '',
						'user_id' => '',
						'search' => '',
						'count' => false,
						'meta_key' => '',
						'meta_value' => '',
						'meta_query' => '',
					);
					$comments = get_comments( $defaults );
					foreach($comments as $comment) {
						$title = get_comment_meta($comment->comment_ID, 'title', true);
						$pstate = get_comment_meta($comment->comment_ID, 'pstate', true);
						$rating = get_comment_meta($comment->comment_ID, 'rating', true);
						echo '<li class="'.$pstate.' '.$rating.'"><a title="'.$comment->comment_content.'">'.$title.'</a></li>';
					}
					?>
					</ul>
				</li>
		<?php endwhile; ?>
		<div style="clear:both;"></div>
		<div class="posts_navi">
		<?php previous_posts_link('上一頁'); ?> <?php next_posts_link('下一頁'); ?>
		</div>

		<?php
		$wp_query = null;
		$wp_query = $ori_query_obj;
		?>
		</ul>
	</div>

<?php get_footer(); ?>