<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package casting_kaya
 */

get_header(); ?>
	<div class="fullwidth mid-content"> <!-- Middle content align -->
		<?php 
		echo '<div class="taxonomy-content-wrapper kaya-post-content-wrapper">';
			echo '<ul class="column-extra">';
				$term = get_term_by('slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
				$taxonomy = get_queried_object();
				while ( have_posts() ) : the_post();
					kaya_get_template_part( 'pods-taxonomy-view-style' );
				endwhile; // End of the loop.
			echo '</ul>';
		echo '</div>'; ?>
	</div> <!-- End -->
<?php get_footer(); ?>