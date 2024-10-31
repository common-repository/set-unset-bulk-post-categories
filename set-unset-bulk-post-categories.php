<?php
/**
Plugin Name: Set Unset Bulk Post Categories
Description: Allows user to set the desired categories as well as unset the categories of all the posts in a bulk without editing the posts itself.
Version:     1.2.1
Author:      Param Themes
Author URI:  http://www.paramthemes.com
Domain Path: /languages
Text Domain: set-unset-bulk-post-categories

@package   MyPackage

Set Bulk Post Categories is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Set Bulk Post Categories is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Set Bulk Categories. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
 */


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_set_unset_bulk_post_categories() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once __DIR__ . '/appsero/src/Client.php';
    }

    $client = new Appsero\Client( 'e7de8ebc-ae45-4e35-ae2d-1509fb7de49a', 'Set Unset Bulk Post Categories', __FILE__ );

    // Active insights
    $client->insights()->init();

    // Active automatic updater
    $client->updater();

}

appsero_init_tracker_set_unset_bulk_post_categories();


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $query,$paged;

// include stylesheet,script.
add_action( 'admin_print_styles', 'ecpt_plugin_stylesheet' );
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
if ( ! defined( 'ECPT_PLUGIN_PATH' ) ) {
	define( 'ECPT_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
}
/**
 * Define plugin stylesheet.
 */
function ecpt_plugin_stylesheet() {
	wp_enqueue_style( 'myCSS', ECPT_PLUGIN_PATH . '/css/style.css' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-css', ECPT_PLUGIN_PATH . '/css/jquery-ui.css' );
	wp_enqueue_script( 'v2plugin', ECPT_PLUGIN_PATH . '/js/v2plugin.js' );
}
add_action( 'admin_menu', 'ecpt_menu_page' );

// Set The Wrap.
/**
 * Define plugin category.
 */
function ecpt_setcats() {
	echo '<div class="wrap">';
}

// delete and edit post.
/**
 * Delete post.
 *
 * @param string $link 'Delete This'.
 *   The flag handler to check.
 * @param string $before ''.
 *   The entity type to check against.
 * @param string $after ''.
 *   The bundle to check against.
 *
 * @return string
 *   Whether the given flag is applicable to the given bundle and entity type.
 */
function ecpt_wp_delete_post_link( $link = 'Delete This', $before = '', $after = '' ) {
	$admin_url = admin_url( '/post.php?action=delete&amp;post=' );
	global $post;

	if ( 'post' === $post->post_type ) {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
	} else {
		if ( ! current_user_can( 'delete_post', $post->ID ) ) {
			return;
		}
	}
	$link = "<a href='" . wp_nonce_url( $admin_url . $post->ID, 'delete-post_' . $post->ID ) . "'>" . $link . '</a>';
	echo $before . $link . $after;
}

// plugin set menu name and theme option page.
/**
 * Define ecpt_menu_page.
 */
function ecpt_menu_page() {
	add_menu_page( 'Set Unset Bulk Post Categories', 'Set Unset Bulk Post Categories', 'manage_options', 'set-unset-bulk-post-categories', 'ecpt_custom_function', 'dashicons-menu', 6 );
}

add_filter( 'post_updated_messages', 'ecpt_show_update_message', 11 );
/**
 * Message.
 *
 * @param string $messages '', notifications to be displayed.
 */
function ecpt_show_update_message( $messages ) {
	$message = esc_html( $_COOKIE['wdm_server_response'] );
	$status  = esc_html( $_COOKIE['wdm_server_response_status'] );

	/*
	 We have to append the notification message for each WordPress default status message, for our post type. This has to be done, because we can't be sure of the status message which will be displayed by WordPress.The message which will be displayed will be dependent on the success of the operation performed.
	For example, since we are updating the post, a post update "success" or "failure" notification might be displayed. Since we aren't sure which of the two will be displayed, we have to append our message to either of the notifications, to display it.
	*/
	foreach ( $messages['product'] as $key => $single_message ) {
		if ( 'success' == $status ) {
			$messages['product'][ $key ] = $single_message . '</div><div id="message" class="updated notice notice-success is-dismissible"><p>' . $message . '</p></div>';
		} else {
				$messages['product'][ $key ] = $single_message . '</div><div id="message" class=" notice notice-error is-dismissible"><p>' . $message . '</p></div>';
		}
	}
	return $messages;
}

// main page function / display table data.
/**
 * Define plugin custom.
 */
function ecpt_custom_function() {
	// get category for user selected new category for post.
	$ptcategory = sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'ptcategory' ) ) );
	$stcategory = sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'setcategory' ) ) );
	if ( isset( $_POST['setcategory'] ) ) {
		if ( isset( $_POST['ptcategory'] ) ) { ?>
			<div class="updated notice" style=" width: 97%;float: right;">
				<p>Post has been Saved</p>
			</div>
			<?php
			global $catigory;
			$catids = $_POST['ptcategory'];
			foreach ( $catids as $catid ) {
				$pids[]     = substr( $catid, strpos( $catid, '-' ) + 1 );
				$pid        = array_unique( $pids );
				$catigory[] = $catid;
			}
			$new   = array();
			$count = 0;
			foreach ( $catigory as $key => $value ) {
				$val = explode( '-', $value );
				$k   = $val[1];
				if ( array_key_exists( $k, $new ) ) {
					$new[ $k ][ $count ] = $val[0];
				} else {
					$new[ $k ]           = array();
					$new[ $k ][ $count ] = $val[0];
				}
					$count++;
			}
			foreach ( $new as $key => $value ) {  // get $key as post_id & values as category_id.
				wp_set_post_categories( $key, $new[ $key ], false );
			}
		}
	}
	$ecpt_url = admin_url( 'admin.php?page=set-unset-bulk-post-categories' );
		print "<h1 class='v2'>Set Unset Bulk Post Categories</h1>";
	?>
		<form id="myForm" action ="<?php echo esc_attr( $ecpt_url ); ?>" method ="post">
		<div class="tablenav top">
			<div class="alignleft actions">

				 <input type="text" id="startdate"   name="startdate"  value="
				 <?php
					$sdate11 = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'sdate' ) ) );
					$sdate33 = sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'startdate' ) ) );
					if ( ! empty( $sdate11 ) ) {
						echo $sdate = $sdate11; } else {
						echo $sdate = $sdate33;}
						?>
						" placeholder=" Start Date" >
				 <input type="text"  id ="enddate" name="enddate"  value="
				 <?php
					$edate11 = sanitize_text_field( wp_unslash( filter_input( INPUT_GET, 'edate' ) ) );
					if ( ! empty( $edate11 ) ) {
						echo $edate = preg_replace( '( [^0-9/] )', '', esc_attr( wp_unslash( $edate11 ) ) );
					} else {
						echo $edate = preg_replace( '([^0-9/])', '', sanitize_text_field( wp_unslash( filter_input( INPUT_POST, 'enddate' ) ) ) );}
					?>
					" placeholder="End Date">

				<?php
				// get data forform submit.
					$author_p = sanitize_text_field( filter_input( INPUT_POST, 'author' ) );
					$author_g = sanitize_text_field( filter_input( INPUT_GET, 'author' ) );
				if ( isset( $author_p ) ) {
					 $selectednumber1                  = $author_p;
						$selected1[ $selectednumber1 ] = 'selected';
				}
				$get_new_new_arr = get_option( 'pt_option' );
				?>
				<select name='author' value="Author" id="location">
					<option value= "" selected="selected">Select Author</option>
					<?php
					if ( ! empty( $_POST['submit'] ) ) {

						( isset( $author_p ) ) ? $author = $author_p : $author = 0;

					} else {

						( isset( $author_g ) ) ? $author = $author_g : $author = 0;
					}
					?>
					<?php
					$users = get_users();
					foreach ( $users as $user ) {
						?>
						<option value='<?php echo $user->ID; ?>'
													<?php
													if ( $author == $user->ID ) {
														echo 'selected';}
													?>
							>
							<?php echo $user->display_name; ?></option>

				<?php } ?>
				</select>
					<?php
					$category_p = sanitize_text_field( $_POST['ptcategory'] );
					$category_g = sanitize_text_field( $_GET['cat'] );
					if ( isset( $category_p ) ) {
						  $selectednumber2             = $category_p;
						$selected2[ $selectednumber2 ] = 'selected';
					}
					?>
				<select name='ptcategory'>
				<option value= "">Select Category</option>
				<?php
				if ( ! empty( $_POST['submit'] ) ) {
					( isset( $category_p ) ) ? $category_p = $category_p : $category_p = 'Select Category';

				} else {

					( isset( $category_g ) ) ? $category_p = $category_g : $category_p = 'Select Category';
				}

				?>
					<?php
					$args       = array(
						'orderby' => 'name',
					);
					$categories = get_categories( $args );
					foreach ( $categories as $category ) {
						?>
						<option value='<?php echo $category->name; ?>'
													<?php
													if ( $category_p === $category->name ) {
														echo 'selected';}
													?>
							>
								<?php echo $category->name; ?></option>
						<?php
					}
					wp_reset_postdata();
					?>
				</select>
				<input type="hidden" name="paged" value="0" />
				<input type="submit" name="submit" id="btnget" class="button action" value="Filter">

				<input type="button" id="btn" class="button action" value="Clear" onclick="window.location.replace('<?php echo $ecpt_url; ?>')">
			</div>
		</form>
				<?php
				$draft = '';
				// wp-query for fetch data in database.
				if ( isset( $_POST['submit'] ) ) {
					$paged   = sanitize_text_field( filter_input( INPUT_GET, 'paged' ) );
					$sdate   = preg_replace( '([^0-9/])', '', filter_input( INPUT_POST, 'startdate' ) );
					$edate   = preg_replace( '([^0-9/])', '', filter_input( INPUT_POST, 'enddate' ) );
					$draft   = sanitize_text_field( filter_input( INPUT_POST, 'author' ) );
					$catgory = sanitize_text_field( filter_input( INPUT_POST, 'ptcategory' ) );
					if ( null != $sdate ) {
						$sdates = date( 'Y-m-d', strtotime( $sdate ) );
					}
					if ( null != $edate ) {
						$edatee = date( 'Y-m-d', strtotime( $edate ) );
					}
						$args = array(
							'post_type'      => 'post',
							'author'         => $draft,
							'posts_per_page' => 10,
							'category_name'  => $catgory,
							'paged'          => $paged,
							'date_query'     => array(
								array(
									'after'     => $sdates,
									'before'    => $edatee,
									'inclusive' => true,
								),

							),
						);
						$query = new WP_Query( $args );
						wp_reset_query();
				} else {

						$paged = $_GET['paged'];
					if ( '' != $_GET['author'] ) {
						$args = array(
							'post_type'      => 'post',
							'author'         => $_GET['author'],
							'posts_per_page' => 10,
							'orderby'        => 'title',
							'paged'          => $paged,
						);
					}
					if ( '' != $_GET['cat'] ) {
						$args = array(
							'post_type'      => 'post',
							'category_name'  => $_GET['cat'],
							'posts_per_page' => 10,
							'orderby'        => 'title',
							'paged'          => $paged,
						);

					}
					if ( '' != $_GET['sdate'] || '' != $_GET['edate'] ) {
						if ( null != $_GET['sdate'] ) {
							$sdate1 = date( 'Y-m-d', strtotime( $_GET['sdate'] ) );
						}
						if ( null != $_GET['edate'] ) {
							$edate1 = date( 'Y-m-d', strtotime( $_GET['edate'] ) );
						}

						$args = array(
							'post_type'      => 'post',
							'posts_per_page' => 10,
							'orderby'        => 'title',
							'paged'          => $paged,
							'date_query'     => array(
								array(
									'after'     => $sdate1,
									'before'    => $edate1,
									'inclusive' => true,
								),

							),
						);

					}

							  $query = new WP_Query( $args );

				}

							ecpt_pagination( $query->max_num_pages, $draft, $catgory, $sdate, $edate );
				?>
		</div>
		<!-- table display the post -->
		<form action="" enctype="multipart/form-data" method="post" >
		<div class="wrap">
			<table  id="example" class="wp-list-table widefat fixed striped posts" cellspacing="0">
				<thead>
					  <tr>
						<th>Title</th>
						<th>Author</th>
						<th>Categories</th>
						<th>Date</th>
					  </tr>
				</thead>
				<tbody id="the-list">
					<?php
					while ( $query->have_posts() ) {
						global $post;
						$query->the_post();
						$args                = array(
							'hide_empty' => 0,
							'pad_counts' => false,
							'orderby'    => 'title',
						);
							$category_detail = get_the_category( $post->ID );// $post->ID
						foreach ( $category_detail as $cd ) {
							$category = get_the_category( $post->ID );
						}
						echo '<td class="title column-title has-row-actions column-primary page-title" data-colname="Title">' . get_the_title( $post->ID );
						echo '</br>';
						echo '</br>';
						echo '<div class="row-actions">';
						echo '<span class="trash">';
						echo '<b>' . ecpt_wp_delete_post_link( 'Delete' ) . '</b>' . '&nbsp' . '&nbsp'; // link to delete the post.
						echo '</span>';
						echo '<b>' . edit_post_link( 'Edit', '', '', $post->ID ) . '</b>' . '<br />'; // link to edit the post.
						echo '</div>';
						echo '</td>';
						echo '<td class="author column-author" data-colname="Author">' . get_the_author() . '</td>';
						$categories = get_categories( $args );
						// $category = get_the_category($post->ID);
						$a = array();
						$b = array();
						foreach ( $categories as $ptcategory ) {
							$a[] = $ptcategory->term_taxonomy_id;
						}
						foreach ( $category as $cato ) {
							$b[] = $cato->term_taxonomy_id;
						}
						$results = array_merge( array_diff( $a, $b ) );
						$result  = array_unique( $results );
						echo '<td class="categories column-categories" data-colname="Categories">';
						foreach ( $result as $resu ) { // check to see if the category has been already assigned or not & chekbox is set 'unchecked' if true.
							$ancestors = get_ancestors( $resu, 'category' );
							if ( $ancestors ) {
								echo "<span class='parents'>" . get_category_parents( $resu, false, ' &raquo; ' ) . '</span>' . "<span class='child'>" . '<strong>' . get_cat_name( $resu ) . '</strong>' . '</span>' . ": <input type='checkbox' name='ptcategory[]' value='" . $resu . '-' . $post->ID . "'>" . '<br />';
							} else {
								echo "<input type='checkbox' name='ptcategory[]' value='" . $resu . '-' . $post->ID . "'>" . '&nbsp;' . '<strong>' . get_cat_name( $resu ) . '</strong>' . '<br />';
							}
						}
						$res = array_intersect( $a, $b );
						foreach ( $res as $re ) {      // check to see if the category has been already assigned & chekbox is set checked if true.
							$r          = $re;
							$ancestors1 = get_ancestors( $r, 'category' );
							if ( $ancestors1 ) {
								echo "<span class='parents'>" . get_category_parents( $r, false, ' &raquo; ' ) . '</span>' . "<span class='child'>" . '<strong>' . get_cat_name( $r ) . '</strong>' . '</span>' . ": <input type='checkbox' name='ptcategory[]' value='" . $r . '-' . $post->ID . "' checked>" . '<br />';
							} else {
								echo "<input type='checkbox' name='ptcategory[]' value='" . $r . '-' . $post->ID . "' checked>" . '&nbsp;' . '<strong>' . get_cat_name( $r ) . '</strong>' . '<br />';
							}
						}
						echo '</td>';
						echo '<td class="date column-date" data-colname="Date">' . get_the_date() . '</td>';
						 echo '</tr>';
					}
					?>
				</tbody>
			</table>
			</div>
			<?php

							ecpt_pagination( $query->max_num_pages, $draft, $catgory, $sdate, $edate );

			?>
		<?php
		echo '<br /><br />';
		echo '<input type="submit" name="setcategory" class="button action" value="Submit">';
		echo '</form>';
}
/**
 * Pagination post.
 *
 * @param string $pages ''.
 *   The flag handler to check.
 * @param string $draft ''.
 *   The entity type to check against.
 * @param string $catgory ''.
 *   The bundle to check against.
 * @param string $sdate ''.
 *   The bundle to check against.
 * @param string $edate ''.
 *   The bundle to check against.
 * @param int    $range 3.
 *   The bundle to check against.
 */
function ecpt_pagination( $pages = '', $draft, $catgory, $sdate, $edate, $range = 3 ) {

	global $paged;
	 $paged     = esc_html( $_GET['paged'] );
	 $args      = array(
		 'post_type'      => 'post',
		 'posts_per_page' => 5,
		 'orderby'        => 'title',
		 'paged'          => $paged,
	 );
	 $query     = new WP_Query( $args );
	 $showitems = ( $range * 2 ) + 1;
	 if ( empty( $paged ) ) {
		 $paged = 1;
	 }
	 if ( '' === $pages ) {
		 global $wp_query;
		 $pages = $wp_query->max_num_pages;
		 if ( ! $pages ) {
			 $pages = 1;
		 }
	 }
	 if ( 1 !== $pages ) {

		 echo '<div class="tablenav1">';
		 echo '<div class="pagination"><span>Page ' . intval( $paged ) . ' of ' . intval( $pages ) . '</span>';
		 if ( $paged > 2 && $paged > $range + 1 && $showitems < $pages ) {
			 echo "<a href='" . esc_html( get_pagenum_link( 1 ) ) . "'>&laquo; First</a>";
		 }
		 if ( $paged > 1 && $showitems < $pages ) {
			 echo "<a href='" . esc_html( get_pagenum_link( $paged - 1 ) ) . "'>&lsaquo; Previous</a>";
		 }
		 for ( $i = 1; $i <= $pages; $i++ ) {
			 if ( 1 !== $pages && ( $pages <= $showitems || ! ( $i >= $paged + $range + 1 || $i <= $paged - $range - 1 ) ) ) {

				 if ( '' != $draft || '' != $catgory || '' != $sdate || '' != $edate ) {
						echo ( $paged == $i ) ? '<span class="current">' . $i . '</span>' : "<a href='" . get_pagenum_link( $i ) . '&author=' . $draft . '&cat=' . $catgory . '&sdate=' . $sdate . '&edate=' . $edate . "' class=\"inactive\">" . $i . '</a>';
				 } else {
					 echo ( $paged == $i ) ? '<span class="current">' . $i . '</span>' : "<a href='" . get_pagenum_link( $i ) . "' class=\"inactive\">" . $i . '</a>';
				 }
			 }
		 }
		 if ( $paged < $pages && $showitems < $pages ) {
			 echo '<a href="' . esc_html( get_pagenum_link( $paged + 1 ) ) . '">Next &rsaquo;</a>';
		 }
		 if ( $paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages ) {
			 echo "<a href='" . esc_html( get_pagenum_link( $pages ) ) . "'>Last &raquo;</a>";
		 }
		 echo "</div>\n";
		 echo "</div>\n";
	 }
}
?>
