<?php

global $VagabondPM;

if ( post_password_required() )
	return;
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-title">
			<?php
				printf( 
					_n( '1 comment：', '%1$s comments：', get_comments_number(), $VagabondPM::SLUG ),
					number_format_i18n( get_comments_number() ), 
					'<span>' . get_the_title() . '</span>' 
				);
			?>
		</h2>

		<ol class="commentlist">
			<?php wp_list_comments( array( 'callback' => array($VagabondPM, 'vaga_comment'), 'style' => 'ol' ) ); ?>
		</ol><!-- .commentlist -->

		<?php 
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there Issues to navigate through ?>
			<nav id="comment-nav-below" class="navigation" role="navigation">
				<h1 class="assistive-text section-heading">
					<?php _e( 'Comment navigation', $VagabondPM::SLUG ); ?>
				</h1>
				<div class="nav-previous">
					<?php previous_comments_link( __( '&larr; Older Issues', $VagabondPM::SLUG ) ); ?>
				</div>
				<div class="nav-next">
					<?php next_comments_link( __( 'Newer Issues &rarr;', $VagabondPM::SLUG ) ); ?>
				</div>
			</nav>
		<?php endif;?>

		<?php
		/* If there are no comments and comments are closed, let's leave a note.
		 * But we only want the note on posts and pages that had comments in the first place.
		 */
		if ( ! comments_open() && get_comments_number() ) : ?>
			<p class="nocomments"><?php _e( 'Issues are closed.', $VagabondPM::SLUG ); ?></p>
		<?php endif; ?>

	<?php endif; // have_comments() ?>

	<?php 
	$comment_args = array(
		'title_reply' => __('Comments', $VagabondPM::SLUG),
		'label_submit' => __('Reply', $VagabondPM::SLUG),
		'logged_in_as' => '',
		'fields' => apply_filters( 'comment_form_default_fields', 
			array(
				'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Your Name', $VagabondPM::SLUG ) . '</label> ' . ( $req ? '<span>*</span>' : '' ) .
							'<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',   
				'email'  => '<p class="comment-form-email">'.'<label for="email">'.__('Your Email', $VagabondPM::SLUG).'</label>'.($req?'<span>*</span>':'').
							'<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' />'.'</p>',
				'url'    => '' 
			)
		),
		'comment_field' => '<p>' .
					'<label for="comment">' . __( 'Comment Description:', $VagabondPM::SLUG ) . '</label>' .
					'<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea>' .	 
					'</p>',
		'comment_notes_after' => '',	 
	);
	comment_form($comment_args); 
	?>

</div><!-- #comments .comments-area -->