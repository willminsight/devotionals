<?php
/*
Plugin Name:		IFLM Devotionals
Plugin URI:			https://github.com/willminsight/devotionals
GitHub Plugin URI:	willminsight/devotionals
GitHub Plugin URI:	https://github.com/willminsight/devotionals
Description:	Create devotional posts on your ministry blog, including standard or custom archives and RSS Feed. You can also optionally include tweetable quotes.
Version:		1.2.7
Author:			Insight for Living Ministries
Author URI:		https://insight.org/
License:		GPL2
License URI:	https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:	devotionals
Domain Path:	/languages/


IFLM Devotionals is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
IFLM Devotionals is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Devotionals. If not, see {URI to Plugin License}.
*/


include 'inc/iflm_options.php';
include 'inc/bitly-url.php';

function iflm_devo_setup_post_type() {
    // Internationalization:
    load_plugin_textdomain('devotionals', false, basename( dirname( __FILE__ ) ) . '/languages/' );
    
    
	/* plugin options values: */
	
    // rewrite permalink slug
    if (get_option('iflm_devo_slug') != '') { 
        $saved_slug =  get_option('iflm_devo_slug'); 
    } else {
        $saved_slug = "devotionals";
    }
	
    // has_archive and feed rewrite rule
    if( get_option('iflm_devo_has_archive') === 'true' ){
      	$has_archive = true;
    } 
    else {
		$has_archive = false;
        add_rewrite_rule( '^'.$saved_slug.'/feed/?', 'index.php?feed=feed&post_type=iflm_devotional', 'top' );
	}
    
    $rewrite = array( 'slug' => $saved_slug, 'feed' => true);
	
    
	
   // set up labels
	$labels = array(
 		'name'                     => __( 'Devotionals', 'devotionals' ),
    	'singular_name'            => __( 'Devotional', 'devotionals' ),
    	'add_new'                  => __( 'Add New Devotional', 'devotionals' ),
    	'add_new_item'             => __( 'Add New Devotional', 'devotionals' ),
    	'edit_item'                => __( 'Edit Devotional', 'devotionals' ),
    	'new_item'                 => __( 'New Devotional', 'devotionals' ),
    	'all_items'                => __( 'All Devotionals', 'devotionals' ),
    	'view_item'                => __( 'View Devotional', 'devotionals' ),
    	'search_items'             => __( 'Search Devotionals', 'devotionals' ),
    	'not_found'                => __( 'No Devotionals Found', 'devotionals' ),
    	'not_found_in_trash'       => __( 'No Devotionals found in Trash', 'devotionals' ), 
    	'parent_item_colon'        => '',
        'menu_name'                => __( 'Devotionals', 'devotionals' ),
    );
    //register post type
	register_post_type( 'iflm_devotional', array(
		'labels' 				=> $labels,
		'has_archive' 			=> $has_archive,
 		'public' 				=> true,
		'supports' 				=> array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'taxonomies' 			=> array( 'post_tag', 'category' ),	
		'exclude_from_search'	=> false,
		'show_ui'				=> true,
		'menu_position' 		=> 5,
        'menu_icon'             => 'dashicons-book-alt',
		'capability_type' 		=> 'post',
		'rewrite' 				=> $rewrite,
		)
	);
    
    
	
}
add_action( 'init', 'iflm_devo_setup_post_type' );
 
function iflm_devo_install() {
    // trigger our function that registers the custom post type
    iflm_devo_setup_post_type();
 
    // clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'iflm_devo_install' );



/**
 * Add Twitter handle/username to User Contact Information
 *
 * @param $user_contact
 *
 * @return array
 */
function user_contact_add_twitter( $user_contact ) {
	$user_contact['twitter'] = __( 'Twitter Username' );
	return $user_contact;
}
add_filter( 'user_contactmethods', 'user_contact_add_twitter' );



/**
 * Adds a meta box to the post editing screen
 */
function iflm_devo_custom_meta() {
    add_meta_box( 'iflm_devo_meta', __( 'Devotional Extras', 'devotionals' ), 'iflm_devo_meta_callback', 'iflm_devotional', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'iflm_devo_custom_meta' );


/**
 * Outputs the content of the meta box
 */
function iflm_devo_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'iflm_devo_nonce' );
    $iflm_devo_stored_meta = get_post_meta( $post->ID );

    /* Try to override the logged in user as author with stored meta data */
    if (get_option('iflm_devo_default_author') != '') { 
        $saved_default_author =  get_option('iflm_devo_default_author'); 
    } else {
        $saved_default_author = ""; // this value will default to the logged in user as author of the devotional.
    }

    if($saved_default_author !== "") {
        echo "
        <script>
        $(function() {
            $('#post_author').val('$saved_default_author');
            $('#post_author_override').val('$saved_default_author');
        });
        </script>
        "; 
    }
    /* END: Try to override the logged in user as author with stored meta data */
    ?>

    <!-- scripture -->
    <div class="iflm-row-content">
        <label for="iflm_devo-scripture"><?php _e( 'Scripture Reference:', 'devotionals' )?></label>
		<div class="iflm-row-input">
			<input type="text" name="iflm_devo-scripture" id="iflm_devo-scripture" placeholder="Acts 6:1-3" value="<?php if ( isset ( $iflm_devo_stored_meta['iflm_devo-scripture'] ) ) echo $iflm_devo_stored_meta['iflm_devo-scripture'][0]; ?>" />
		</div>
		<div class="clear"></div>
    </div>

    <!-- footnote -->
	<?php 
	if ( isset ( $iflm_devo_stored_meta['iflm_devo_footnote'] ) ) $saved_value = $iflm_devo_stored_meta['iflm_devo_footnote'][0];
	$settings = array('textarea_name' => 'iflm_devo_footnote', 'media_buttons' => false);
	?>
	<div class="iflm-row-content">
		<label for="iflm_devo_footnote"><?php _e( 'Footnote:', 'iflm_devo-textdomain' )?></label>
		<div class="iflm-row-input">
			<?php wp_editor( $saved_value, 'iflm_devo_footnote', $settings ); ?>
		</div>
		<div class="clear"></div>
    </div>

    <!-- optional twitter quote -->
	<h3 class="iflm-heading"><?php _e( 'Optional Tweetable Pull Quote', 'iflm_devo-textdomain' )?></h3>

	<?php
	// Pull bit.ly Login and API Key from Plugin Settings
    $login = get_option('iflm_devo_bitlyLogin');
    $apiKey = get_option('iflm_devo_bitlyApiKey');
	
	if($login == '' || $apiKey == '') {
    ?>
		<p style="color: red;"><?php _e( 'Your bit.ly Login and API Key are not defined. If you want to have tweetable pull quotes associated with your devotionals <strong>with shortened URLs</strong>, please visit the Settings > Devotionals admin page and enter your bit.yl information.', 'iflm_devo-textdomain' )?></p>
	<?php
    }
    ?>
    
    <!-- tweet_quote -->
	<div class="iflm-row-content">
        <label for="iflm_devo-tweet_quote"><?php _e( 'Quote:', 'devotionals' )?></label>
		<div class="iflm-row-input">
			<textarea name="iflm_devo-tweet_quote" id="iflm_devo-tweet_quote"><?php if ( isset ( $iflm_devo_stored_meta['iflm_devo-tweet_quote'] ) ) echo $iflm_devo_stored_meta['iflm_devo-tweet_quote'][0]; ?></textarea>
		</div>
		<div class="clear"></div>
    </div>

    <!-- tweet quote author -->
	<?php 
		if ( isset ( $iflm_devo_stored_meta['iflm_devo-tweet_quote_author'] ) ) {
			$saved_value = $iflm_devo_stored_meta['iflm_devo-tweet_quote_author'][0];
		} else {
            if($saved_default_author == "") {
                $author_id=$post->post_author;
            } else {
                $author_id = $saved_default_author;
            }
			$saved_value = get_the_author_meta( 'display_name', $author_id );
		}
	?>
	<div class="iflm-row-content">
        <label for="iflm_devo-tweet_quote_author"><?php _e( 'Quote Author:', 'devotionals' )?></label>
        <div class="iflm-row-input">
			<input type="text" name="iflm_devo-tweet_quote_author" id="iflm_devo-tweet_quote_author" value="<?php echo $saved_value ?>" />
		</div>
		<div class="clear"></div>
    </div>

	<?php 
		if ( isset ( $iflm_devo_stored_meta['iflm_devo-tweet_quote_author_username'] ) ) {
			$saved_value = $iflm_devo_stored_meta['iflm_devo-tweet_quote_author_username'][0];
		} else {
			if($saved_default_author == "") {
                $author_id=$post->post_author;
            } else {
                $author_id = $saved_default_author;
            }
			$saved_value = get_the_author_meta( 'twitter', $author_id );
		}
	?>
	<div class="iflm-row-content">
        <label for="iflm_devo-tweet_quote_author_username"><?php _e( 'Quote Author\'s Twitter Username:', 'devotionals' )?></label>
        <div class="iflm-row-input">
			<input type="text" name="iflm_devo-tweet_quote_author_username" id="iflm_devo-tweet_quote_author_username" value="<?php echo $saved_value ?>" />
			<p class="howto"><?php _e( 'Enter the quote author\'s Twitter username (without @)', 'devotionals' )?></p>
		</div>
		<div class="clear"></div>
    </div>

        
    <?php
} //end of function





/**
 * Saves the custom meta input
 */
function iflm_devo_meta_save( $post_id ) {
 
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'iflm_devo_nonce' ] ) && wp_verify_nonce( $_POST[ 'iflm_devo_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    // Checks for input and sanitizes/saves if needed
    if( isset( $_POST[ 'iflm_devo-scripture' ] ) ) {
        update_post_meta( $post_id, 'iflm_devo-scripture', sanitize_text_field( $_POST[ 'iflm_devo-scripture' ] ) );
    }
	
	if( isset( $_POST[ 'iflm_devo_footnote' ] ) ) {
    	update_post_meta( $post_id, 'iflm_devo_footnote', $_POST[ 'iflm_devo_footnote' ] );
	}
	
	if( isset( $_POST[ 'iflm_devo-tweet_quote' ] ) ) {
    	update_post_meta( $post_id, 'iflm_devo-tweet_quote', $_POST[ 'iflm_devo-tweet_quote' ] );
	}
	
	if( isset( $_POST[ 'iflm_devo-tweet_quote_author' ] ) ) {
    	update_post_meta( $post_id, 'iflm_devo-tweet_quote_author', sanitize_text_field( $_POST[ 'iflm_devo-tweet_quote_author' ] ) );
	}
	
	if( isset( $_POST[ 'iflm_devo-tweet_quote_author_username' ] ) ) {
    	update_post_meta( $post_id, 'iflm_devo-tweet_quote_author_username', sanitize_text_field( $_POST[ 'iflm_devo-tweet_quote_author_username' ] ) );
	}
	
	if( isset( $_POST[ 'iflm_devo-shortURL' ] ) ) {
    	update_post_meta( $post_id, 'iflm_devo-shortURL', sanitize_text_field( $_POST[ 'iflm_devo-shortURL' ] ) );
	}
 
}
add_action( 'save_post', 'iflm_devo_meta_save' );




/**
 * ADMIN: ADD/EDIT CPT -- Adds the admin stylesheet when appropriate
 */
function iflm_devo_admin_styles(){
    global $typenow;
    if( $typenow == 'iflm_devotional' ) {
        wp_enqueue_style( 'iflm_devo_meta_box_styles', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'iflm_devo_admin_styles' );



/* DISPLAY DEVOTIONAL: Display the extra custom fields surrounding the_content() using the standard single.php template. */
function iflm_devo_content( $content ) {
	if ( is_singular('iflm_devotional') ) {
		// get custom fields' data for feed content
		$iflm_devo_stored_meta = get_post_meta( get_the_ID() );
		$scripture = $iflm_devo_stored_meta['iflm_devo-scripture'][0];
		$footnote = $iflm_devo_stored_meta['iflm_devo_footnote'][0];
		
		$tweet_quote = $iflm_devo_stored_meta['iflm_devo-tweet_quote'][0];
        $tweet_quote_author = $iflm_devo_stored_meta['iflm_devo-tweet_quote_author'][0];
        $tweet_quote_author_username = $iflm_devo_stored_meta['iflm_devo-tweet_quote_author_username'][0];
	
		if($scripture != '') {
			$content = "<p class='scripture'>$scripture</p>\n" . $content;
		}
		
		if( $tweet_quote != '' && $tweet_quote_author != '' ){
			
			// This is the URL you want to shorten
			$longURL = get_permalink( get_the_ID() );
            $shortURL = $longURL;
            
            // Pull bit.ly Login and API Key from Plugin Settings
            $login = get_option('iflm_devo_bitlyLogin');
            $apiKey = get_option('iflm_devo_bitlyApiKey');
            
            
            if($login != '' && $apiKey != '') {
                $shortURL = get_bitly_short_url($longURL,$login,$apiKey);
                if (strpos($shortURL, "http://bit.ly") !== 0) {
                    // use long URL in tweet text
                    $shortURL = $longURL;
                }
		    }
            

            $tweet_this_button_text = __( 'Tweet This', 'devotionals' );
			$tweet_quote_html = '
				<div class="tweet-this">
					<p>'.$tweet_quote.'</p>
					<span class="tweet-attr">&mdash; <span>'.$tweet_quote_author.'</span></span>';
            if( $tweet_quote_author_username != '' ){
                $tweet_quote_html .= '
                    <a class="button tweet-button" href="https://twitter.com/intent/tweet?text='.urlencode ($tweet_quote).'&via='.$tweet_quote_author_username.'&url='.$shortURL.'" target="_blank"><span class="fa fa-twitter fa-lg"></span>'. $tweet_this_button_text . '</a>';
            }
            $tweet_quote_html .= '
                </div>';

			$content = $content . "\n\n$tweet_quote_html";
		}
		
		if($footnote != '') {
			$content = $content . "\n<div class='footnote'><p>$footnote</p></div>";
		}
        
        // Add Post Meta Data
		
	}
	return $content;
}
add_filter( 'the_content', 'iflm_devo_content' );

function enqueue_iflm_devo_styles() {
     if ( is_singular('iflm_devotional') ) {
        //load style dependencies
        wp_enqueue_style( 'tweet-this', plugin_dir_url( __FILE__ ) . 'css/tweet-this.css' );
     }
    
}
add_action( 'wp_enqueue_scripts', 'enqueue_iflm_devo_styles' );



/* FEED: Alter RSS Feed Content same as page content */
function iflm_devo_content_feed( $content ) {
	$iflm_devo_post_type = get_post_type( get_the_ID() );
	if ( $iflm_devo_post_type == 'iflm_devotional') {
		
		// get custom fields' data for feed content
		$iflm_devo_stored_meta = get_post_meta( get_the_ID() );
		$scripture = $iflm_devo_stored_meta['iflm_devo-scripture'][0];
		$footnote = $iflm_devo_stored_meta['iflm_devo_footnote'][0];
        $enable_biblegateway = get_option('iflm_devo_enable_biblegateway');

        // replace 'MESSAGE' with 'MSG' (reftagger on website uses 'MESSAGE' as the version name):
        $scripture_editted = str_replace("MESSAGE","MSG",$scripture);
        
        /* 
         * Add biblegateway.com links to scripture passages 
        */
        if( $enable_biblegateway == 'true' ) { // only add the biblegateway.com link ($tmp_passage) if enabled:

            // explode $scripture_editted into an array:
            $scriptures = explode('; ', $scripture_editted);

            // loop through array, combining verses in same book (example: Ephesians 5:25; 6:4):
            $i = 0;
            foreach ($scriptures as $passage){
                if(preg_match("/^[0-9]+:[0-9]+/", $passage)) {
                    //echo $passage . '--';
                    $passage = $scriptures[($i-1)] . '; ' . $scriptures[$i];
                    $scriptures[($i-1)] = $passage;
                    unset($scriptures[$i]);
                }
                $i++;
            }

            // create 'search' url variable and optionally 'version' url variable for biblegateway.com:
            if (get_option('iflm_devo_bibleversion') != '') { 
                $saved_bibleversion =  get_option('iflm_devo_bibleversion'); 
            } else {
                $saved_bibleversion = "";
            }


            $arr_scripture_editted = array();
            foreach ($scriptures as $passage){
                $tmp_passage = preg_replace("/(.*) ([A-Z]+)$/", "$1&version=$2", $passage);
                if( strpos($tmp_passage,"version=") == '' & $saved_bibleversion !== '' ) {
                    $tmp_passage = $passage . "&version=" . $saved_bibleversion;
                }

                $tmp_passage = preg_replace("/\s/", "%20", $tmp_passage);
                $tmp_passage = '<a href="https://www.biblegateway.com/passage/?search=' . $tmp_passage . '" target="_blank">' . $passage . '</a>';
                $arr_scripture_editted[] = $tmp_passage;
            } 
            
            // Implode array into A HREF wrapped html string:
            $scripture_editted = implode('; ', $arr_scripture_editted);
        
        }
        /* 
         * END: Add biblegateway.com links to scripture passages 
        */
        
        
		if($scripture_editted != '') {
			$content = "<blockquote>\n\t<p class='text-center'>$scripture_editted</p>\n</blockquote>\n" . $content;
		}
		
		if($footnote != '') {
			$content = $content . "<div class='footnote'>\n\t<p>$footnote</p>\n</div>";
		}
		
	}
	return $content;
}
add_filter( 'the_content_feed', 'iflm_devo_content_feed');




/* ARCHIVE: Display listings of devotionals */
function namespace_add_custom_types( $query ) {
  if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
    $query->set( 'post_type', array(
     'post', 'nav_menu_item', 'iflm-devotional'
		));
	  return $query;
	}
}
add_filter( 'pre_get_posts', 'namespace_add_custom_types' );

?>