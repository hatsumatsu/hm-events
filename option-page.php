<?php

/**
 * Plugin uninstall function
 *
 */
function hm_events_delete_plugin_options() {
    delete_option( 'hm_events_options' );
}

register_uninstall_hook( __FILE__, 'hm_events_delete_plugin_options' );


/**
 * Template tag: display event date based on WP's global date & time format
 *
 */
function hm_events_register_options() {

    $tmp = get_option( 'hm_events_options' );
    if( ( $tmp['chk_default_options_db'] == '1' ) || ( !is_array( $tmp ) ) ) {
        
        delete_option( 'hm_events_options' ); 
        
        $arr = array(   
            'events_per_page' => _( 'Number of events to show on archive pages', 'hm-events' ),
            'event_types_slug' => _( 'Slug for Event Types', 'hm-events' )
        );

        update_option( 'hm_events_options', $arr );
    }
}

register_activation_hook( __FILE__, 'hm_events_register_options' );


/**
 * Register settings
 *
 */
function hm_events_init(){
    register_setting( 'hm_events_plugin_options', 'hm_events_options', 'hm_events_validate_options' );
}

add_action( 'admin_init', 'hm_events_init' );


/**
 * Register options page
 *
 */
function hm_events_register_options_page() {
    add_options_page( 'Events', 'Events', 'manage_options', __FILE__, 'hm_events_render_option_page' );
}

add_action( 'admin_menu', 'hm_events_register_options_page' );


/**
 * Render options page
 *
 */
function hm_events_render_option_page() {
    ?>
    <div class="wrap">
        
        <h2><?php _e( 'HM Events Plugin Options', 'hm-events' ); ?></h2>

        <!-- Beginning of the Plugin Options Form -->
        <form method="post" action="options.php">
            <?php settings_fields( 'hm_events_plugin_options' ); ?>
            <?php $options = get_option( 'hm_events_options' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Events per Page', 'hm-events' ); ?></th>
                    <td>
                        <input type="text" size="57" name="hm_events_options[events_per_page]" value="<?php echo $options['events_per_page']; ?>" />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php _e( 'Event Types Slug', 'hm-events' ); ?></th>
                    <td>
                        <input type="text" size="57" name="hm_events_options[event_types_slug]" value="<?php echo $options['event_types_slug']; ?>" />
                    </td>
                </tr>

            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
            </p>
        </form>

    </div>
    <?php   
}

/**
 * Validate options
 *
 */
function hm_events_validate_options( $input ) {
    return $input;
}