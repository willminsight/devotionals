<?php
/** Step 1. */
function devotionals_menu() {
    $page_title = __( 'Devotionals Options', 'devotionals' );
    $menu_title = __( 'Devotionals', 'devotionals' );
    $capability = 'manage_options';
    $menu_slug  = 'iflm-devotionals';
    $function   = 'devotionals_options';
    
    add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
}


/** Step 2 (from text above). */
add_action( 'admin_menu', 'devotionals_menu' );
add_action( 'admin_init', 'iflm_devo_settings' );

function iflm_devo_settings() {
	register_setting( 'devotionals_group', 'iflm_devo_slug' );
	register_setting( 'devotionals_group', 'iflm_devo_has_archive' );
	register_setting( 'devotionals_group', 'iflm_devo_bitlyLogin' );
    register_setting( 'devotionals_group', 'iflm_devo_bitlyApiKey' );
    register_setting( 'devotionals_group', 'iflm_devo_enable_biblegateway' );
    register_setting( 'devotionals_group', 'iflm_devo_bibleversion' );
    //register_setting( 'devotionals_group', 'iflm_devo_googleApiKey' );
    register_setting( 'devotionals_group', 'iflm_devo_default_author' );
    
    // Check if the user has submitted the settings (wordpress will add the "settings-updated" $_GET parameter to the url)
    if ( isset( $_GET['settings-updated'] ) ) {
        // Flush the Permalinks cache:
        flush_rewrite_rules();
    }
}



/** Step 3. */
function devotionals_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'devotionals' ) );
	}
?>

<div class="wrap">
    <h1><?php _e('Devotionals Settings', 'devotionals'); ?></h1>
	<p><?php _e('These are general options for custom post type of Devotionals.', 'devotionals'); ?></p>

    <form method="post" action="options.php">
        <?php
          settings_fields('devotionals_group');
          do_settings_sections('devotionals_group');
        ?>

        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_slug">
                  <strong><?php _e('Permalink Slug', 'devotionals'); ?>:</strong>
                </label>
              </th>
              <td>
                <input type="text" name="iflm_devo_slug" value="<?php echo esc_attr( get_option('iflm_devo_slug') ); ?>" size="25" />
                  <?php 
                    if (get_option('iflm_devo_slug') != '') { 
                        $saved_slug =  get_option('iflm_devo_slug'); 
                    } else {
                        $saved_slug = "devotionals";
                    }
                    $feed_url = get_site_url() . "/" . $saved_slug . "/feed/";
                    $archive_url = get_site_url() . "/" . $saved_slug . "/";
                  ?>

                  <small>
                  <ul>
                      <li><?php _e('This determines the URL "folder" for Devotional Archives and RSS Feed.', 'devotionals'); ?></li>
                      <li><?php _e('Archives will be located at this URL: ', 'devotionals'); ?> <a href="<?php echo $archive_url; ?>" target="blank"><?php echo $archive_url; ?></a></li>
                      <li><?php _e('RSS Feed will be located at this URL: ', 'devotionals'); ?> <a href="<?php echo $feed_url; ?>" target="blank"><?php echo $feed_url; ?></a></li>
                  </ul>
                  </small>
              </td>
            </tr>
              
             <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_has_archive">
                  <strong><?php _e('Use Standard Archives?', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <select name="iflm_devo_has_archive" id="iflm_devo_has_archive">
                    <option value="true" <?php if(get_option('iflm_devo_has_archive') === 'true') echo 'selected="selected"' ?>><?php _e('True', 'devotionals'); ?></option>
                    <option value="false" <?php if(get_option('iflm_devo_has_archive') != 'true') echo 'selected="selected"' ?>><?php _e('False', 'devotionals'); ?></option>
                </select>

                  <small>
                  <ul>
                      <li><?php _e('A TRUE setting will use a system generated <strong>default archives page</strong>.', 'devotionals'); ?></li>
                      <li><?php _e('A FALSE setting will <strong>require a custom page</strong> saved at this URL: ', 'devotionals'); ?> <a href="<?php echo $archive_url; ?>" target="blank"><?php echo $archive_url; ?></a>.</li>
                  </ul>
                  </small>
              </td>
            </tr>

              
            <tr valign="top">
              <td colspan="2" style="padding: 0 0 1em 0;">
                <hr>
                <h3><?php _e('Default RSS Bible version (optional)', 'devotionals'); ?></h3>
                <p><?php _e('Your Devotionals RSS feed will attempt to add a link to biblegateway.com around the main scripture reference at the top of the devotional. When enabled, you can set which version/translation that biblegateway.com should display by default.', 'devotionals'); ?></p>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_enable_biblegateway">
                  <strong><?php _e('Enable biblegateway.com in RSS feed?:', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <select name="iflm_devo_enable_biblegateway" id="iflm_devo_enable_biblegateway">
                    <option value="true" <?php if(get_option('iflm_devo_enable_biblegateway') != 'false') echo 'selected="selected"' ?>><?php _e('Enable', 'devotionals'); ?></option>
                    <option value="false" <?php if(get_option('iflm_devo_enable_biblegateway') == 'false') echo 'selected="selected"' ?>><?php _e('Disable', 'devotionals'); ?></option>
                </select>
                <?php
                /* Hide or show extra options based on true/false selection above. */
                echo "
                <script>
                var \$j = jQuery.noConflict();
                \$j(function() {
                    \$j( '#iflm_devo_enable_biblegateway' ).on('change', function() { 
                        if( this.value == 'true' ){
                            \$j('#devo_biblegateway_options').removeClass('hidden');
                        } else {
                            \$j('#devo_biblegateway_options').addClass('hidden');
                            \$j('#iflm_devo_bibleversion').val('');
                        }
                    });
                });
                </script>
                "; 
                /* END: Hide or show extra options based on true/false selection above. */
                ?>
              </td>
            </tr>  
            <tr id="devo_biblegateway_options" valign="top" <?php if(get_option('iflm_devo_enable_biblegateway') == 'false') echo 'class="hidden"' ?>>
              <th scope="row">
                <label for="iflm_devo_bibleversion">
                  <strong><?php _e('Default Version:', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <input type="text" id="iflm_devo_bibleversion" name="iflm_devo_bibleversion" value="<?php echo esc_attr( get_option('iflm_devo_bibleversion') ); ?>" size="25" />
                  <small>
                  <ul>
                      <li><?php _e("When this setting if left blank, your user's biblegateway.com preferences will determine what version is displayed." , 'devotionals'); ?></li>
                      <li><?php _e('This setting can always be overridden on individual devotionals by simply adding a version to your scripture reference (e.g.: John 1:1 NLT).', 'devotionals'); ?></li>
                  </ul>
                  </small>
              </td>
            </tr>  
            
            
              
            <tr valign="top">
              <td colspan="2" style="padding: 0 0 1em 0;">
                <hr>
                <h3><?php _e('Tweet This (optional)', 'devotionals'); ?></h3>
                <p><?php _e('Shorten the devotional URL in the shared tweet that is generated by the "Tweet This" button.', 'devotionals'); ?></p>
              </td>
            </tr>
              
            <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_bitlyApiKey">
                  <strong><?php _e('Bit.ly Login:', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <input type="text" name="iflm_devo_bitlyLogin" value="<?php echo esc_attr( get_option('iflm_devo_bitlyLogin') ); ?>" size="25" />
                  <small>
                  <ul>
                      <li><a href="https://bitly.com/a/sign_up" target="_blank"><?php _e('Create an account at bit.ly', 'devotionals'); ?></a> <?php _e('then enter your bit.ly login name above', 'devotionals'); ?>.</li>
                  </ul>
                  </small>
              </td>
            </tr>  
            <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_bitlyApiKey">
                  <strong><?php _e('Bit.ly API Key:', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <input type="text" name="iflm_devo_bitlyApiKey" value="<?php echo esc_attr( get_option('iflm_devo_bitlyApiKey') ); ?>" size="50" />
                  <small>
                  <ul>
                      <li><a href="https://app.bitly.com/Ba54fL4Q5lh/bitlinks/2jtHbE4?actions=accountMain&actions=settings&actions=advancedSettings&actions=apiSupport" target="_blank"><?php _e('Generate your API key from bit.ly', 'devotionals'); ?></a> <?php _e('(under Account > Advanced Settings > API Support)', 'devotionals'); ?>.</li>
                  </ul>
                  </small>
              </td>
            </tr>


            <?php
            /* Try to override the logged in user as author with stored meta data */
            if (get_option('iflm_devo_default_author') != '') { 
                $saved_default_author =  get_option('iflm_devo_default_author'); 
            } else {
                $saved_default_author = "";
            }

            if($saved_default_author !== "") {
                echo "
                <script>
                var \$j = jQuery.noConflict();
                \$j(function() {
                    \$j('#iflm_devo_default_author').val('$saved_default_author');
                });
                </script>
                "; 
            }
            /* END: Try to override the logged in user as author with stored meta data */
            ?>

            <tr valign="top">
              <th scope="row">
                <label for="iflm_devo_default_author">
                  <strong><?php _e('Default Author:', 'devotionals'); ?></strong>
                </label>
              </th>
              <td>
                <?php wp_dropdown_users( array( 'name' => 'iflm_devo_default_author', 'who' => 'authors' ) ); ?>
                  <small>
                  <ul>
                      <li><?php _e('Choose an existing author to default to for all NEW devotionals.', 'devotionals'); ?></li>
                      <li><a href="/wp-admin/user-edit.php?user_id=<?php echo $saved_default_author; ?>&wp_http_referer=%2Fwp-admin%2Fusers.php" target="_blank"><?php _e("Set the chosen author's Twitter Username ", 'devotionals'); ?></a> <?php _e(" to their twitter handle (e.g: chuckswindoll for Charles R. Swindoll).", 'devotionals'); ?></li>
                  </ul>
                  </small>
              </td>
            </tr>

          </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}
?>
