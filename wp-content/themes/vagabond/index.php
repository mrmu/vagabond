<?php
/**
 *
	Ideas:
	合約：自動生成
	報價

	時程規劃
	case based?
	依過往版型設計、功能、時程來試算報價、時程
 */

global $VagabondPM;

get_header(); 
?>

<div id="primary" class="site-content">
	<div id="content" role="main">
	<?php 
	if ( have_posts() ) :
		while ( have_posts() ) : 
			the_post(); 
			get_template_part( 'content', get_post_format() ); 
		endwhile; ?>

		<?php twentytwelve_content_nav( 'nav-below' ); ?>

	<?php else : ?>

		<article id="post-0" class="post no-results not-found">

		<?php if ( current_user_can( 'edit_posts' ) ) :
			// Show a different message to a logged-in user who can add posts.
		?>
			<header class="entry-header">
				<h1 class="entry-title"><?php _e( 'No posts to display', $VagabondPM::SLUG ); ?></h1>
			</header>

			<div class="entry-content">
				<p><?php printf( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', $VagabondPM::SLUG ), admin_url( 'post-new.php' ) ); ?></p>
			</div>

		<?php else :
			// Show the default message to everyone else.
		?>
			<header class="entry-header">
				<h1 class="entry-title"><?php _e( 'Nothing Found', $VagabondPM::SLUG ); ?></h1>
			</header>

			<div class="entry-content">
				<p><?php _e( 'Apologies, but no results were found. Perhaps searching will help find a related post.', $VagabondPM::SLUG ); ?></p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->
		<?php endif; // end current_user_can() check ?>

		</article><!-- #post-0 -->

	<?php endif; // end have_posts() check ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php //get_sidebar(); ?>
<?php get_footer(); ?>