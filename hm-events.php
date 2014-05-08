<?php
/*
Plugin Name: HM Events
Version: 0.4
Description: Simple event management of single date events.
Plugin URI: http://hatsumatsu.de/
Author: HATSUMATSU
Author URI: http://hatsumatsu.de/
*/

/**
 * Configuration
 *
 */
$hm_events_options = get_option( 'hm_events_options' );


/**
 * I11n
 *
 */
load_plugin_textdomain( 'hm-events', '/wp-content/plugins/hm-events/' );


/**
 * Register events post type and taxonomy
 *
 */
function hm_events_register_data_structure() {
    global $hm_events_options;

    register_taxonomy( 
        $hm_events_options['event_types_slug'], 
        array( 'events' ), 
        array( 
            'hierarchical' => true,
            'labels' => array( 
                'name' => __( 'Event Types', 'hm-events' ), 
                'singular_name' => __( 'Event Type', 'hm-events' )
            ),
            'show_ui' => true 
        ) 
    );

    register_post_type( 
        'events', 
        array( 
            'labels' => array( 
                'name' => __( 'Events', 'hm-events' ), 
                'singular_name' => __( 'Event', 'hm-events' ),
                'add_new_item' => __( 'Add new Event', 'hm-events' ),
                'edit_item' => __( 'Edit Event', 'hm-events' ),
                'new_item' => __( 'Add Event', 'hm-events' ),
                'view_item' => __( 'View Event', 'hm-events' ),
                'search_items' => __( 'Search Event', 'hm-events' )
            ),
            'capability_type' => 'post',
            'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
            'public' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-calendar',
            'taxonomies' => array( $hm_events_options['event_types_slug'] ),
            'rewrite' => array( 'slug' => 'events' ),
            'has_archive' => 'events' 
        )
    );

}

add_action( 'init', 'hm_events_register_data_structure' );


/**
 * Register admin CSS
 *
 */
function hm_events_admin_css() {
    wp_register_style( 'hm-events-style', WP_PLUGIN_URL . '/hm-events/css/hm-events.css' );
    wp_register_style( 'hm-events_jqueryui', WP_PLUGIN_URL . '/hm-events/css/jquery-ui-wordpress-theme/jquery-ui-1.10.4.custom.min.css', false, 0, false );

    wp_enqueue_style( 'hm-events-style' );
    wp_enqueue_style( 'hm-events_jqueryui' );
} 


/**
 * Register admin JS
 *
 */
function hm_events_admin_js() {
    wp_register_script( 'hm-events_datetimepicker', WP_PLUGIN_URL . '/hm-events/js/jquery-ui-timepicker-addon.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider' ), 0 );
    wp_register_script( 'hm-events_js', WP_PLUGIN_URL . '/hm-events/js/hm-events.js', array( 'hm-events_datetimepicker' ), 0 );

    wp_enqueue_script( 'hm-events_datetimepicker' ); 
    wp_enqueue_script( 'hm-events_js' ); 
} 

add_action( 'admin_enqueue_scripts', 'hm_events_admin_css' ); 
add_action( 'admin_enqueue_scripts', 'hm_events_admin_js' ); 


/**
 * Register metabox for events edit page.
 *
 */
function hm_events_add_metabox() {
    global $meta_box;

    add_meta_box( 'hm-events', __( 'Event Date and Time', 'hm-events' ), 'hm_events_show_box', 'events', 'side', 'high' );
}

add_action( 'admin_menu', 'hm_events_add_metabox' );


/**
 * Render metabox no events edit page.
 *
 */
function hm_events_show_box() {
    global $post;

    // Use nonce for verification
    echo '<input type="hidden" name="hm-events_nonce" value="', wp_create_nonce( basename( __FILE__ ) ), '" />';

    echo '<table class="form-table hm-events">';
    echo '<tr><td>';

    // get current post meta data
    $timestamp = get_post_meta( $post->ID, 'hm-events_date', true );
    $date = ( $timestamp ) ? date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) : '';

    echo '<p>';
    echo '<label for="hm-events_date">' . __( 'Date and Time', 'hm-events' ) . '</label> ';
    echo '<input class="hm-events_date" type="text" name="hm-events_date" id="hm-events_date" value="' . $date . '" accept="" />';
                
    echo '<input class="" type="hidden" name="hm-events_dateformat" id="hm-events_dateformat" value="' . get_option( 'date_format' ) . '" accept="" />';
    echo '<input class="" type="hidden" name="hm-events_timeformat" id="hm-events_timeformat" value="' . get_option( 'time_format' ) . '" accept="" />';
    echo '<input class="" type="hidden" name="" id="" value="' . $timestamp . '" accept="" />';

    echo '</p>';

    echo '</td></tr>';
    echo '</table>';
}


/**
 * Save event data when post is saved.
 *
 * @param   int     $post_id    post ID
 */
function hm_events_save_data( $post_id ) {
    global $post;

    // verify nonce
    if( !wp_verify_nonce( $_POST['hm-events_nonce'], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    // check autosave
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {  
        return $post_id;
    }

    // check permissions
    if( !current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
     
    // delete current event data       
    delete_post_meta( $post_id, 'hm-events_date' );

    // save event data
    if( $_POST['hm-events_date'] ) {
        $date_array = date_parse_from_format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $_POST['hm-events_date'] ); 
        $new = mktime( $date_array['hour'], $date_array['minute'], 0, $date_array['month'], $date_array['day'], $date_array['year'] );
        update_post_meta( $post_id, 'hm-events_date', $new );
    }

}

add_action( 'save_post', 'hm_events_save_data' );


/**
 * Add column to edit events list
 *
 * @param   array   $columns    columns of the edit events list
 * @return  array   $columns  
 */
function hm_events_register_admin_col( $columns ) {  
    $columns[ 'event_date' ] = __( 'Event Date', 'hm-events' ); 

    return $columns;  
}  

add_filter( 'manage_edit-events_columns', 'hm_events_register_admin_col' );  


/**
 * Show event date in custo m column on edit events list
 *
 * @param   int     $column_name    name of the columns to render values in.
 * @param   int     $post_id        post ID
 */
function hm_events_render_admin_col( $column_name, $post_id ) {  
    if ( 'event_date' != $column_name ) {
        return;  
    }
    
    if( get_post_meta( $post_id, 'hm-events_date', true ) ) {   
        $value = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), get_post_meta( $post_id, 'hm-events_date', true ) );  
        echo $value;
    } else {
        _e( 'None', 'hm-events' );
    }

}  

add_action( 'manage_events_posts_custom_column', 'hm_events_render_admin_col', 10, 2 );  


/**
 * Enable sorting by event date on edit events list.
 *
 * @param   array   $columns    columns of the edit events list
 * @return  array   $columns
 */
function hm_events_sortable_admin_col( $columns ) {  
    $columns[ 'event_date' ] = 'event_date';  
    
    return $columns;  
}  

add_filter( 'manage_edit-events_sortable_columns', 'hm_events_sortable_admin_col' );  


/**
 * Sort edit events list by event date.
 *
 * @param   object  $query  current list query
 */
function hm_events_sortby_admin_col( $query ) {  
        
    if( !is_admin() ) {  
        return;  
    }

    $orderby = $query->get( 'orderby' );  

    if( 'event_date' == $orderby ) {  
        $query->set( 'meta_key', 'hm-events_date' );  
        $query->set( 'orderby', 'meta_value' ); 
    }  
} 

add_action( 'pre_get_posts', 'hm_events_sortby_admin_col' );  


/**
 * Register query vars.
 *
 * @param   array   $vars    query vars
 * @return  array   $vars
 */
function hm_events_add_query_vars( $vars ){
  $vars[] = 'passed';
  $vars[] = 'event_year';
  $vars[] = 'event_month';
  $vars[] = 'event_day';

  return $vars;
}

add_filter( 'query_vars', 'hm_events_add_query_vars' );


/**
 * Modify query for event archive pages.
 * Upcoming events / passed events / year, months and day archives.
 *
 * @param   object   $query    current archive page query
 * @return  object   $query
 */
function hm_events_modify_query( $query ) {
    global $hm_events_options;

    $today = mktime ( null, null, null, date( 'm' ), date( 'd' ), date( 'y' ) );

    if( $query->query_vars[ 'post_type' ] == 'events' || isset( $query->query_vars[ $hm_events_options['events_per_page'] ] ) ) {
        
        $query->query_vars[ 'posts_per_page' ] = $hm_events_options['events_per_page'];

        /* only show upcoming events */
        $query->query_vars[ 'meta_key' ] = 'hm-events_date';

        $query->query_vars[ 'meta_value' ] = $today;
        $query->query_vars[ 'orderby' ] = 'meta_value';

        /* passed events */
        if( $query->query_vars[ 'passed' ] ) {

            $query->query_vars[ 'meta_compare' ] = '<';
            $query->query_vars[ 'order' ] = 'DESC';

        } else {

            $query->query_vars[ 'meta_compare' ] = '>=';
            $query->query_vars[ 'order' ] = 'ASC';

        }

        /* by event year */
        if( $query->query_vars[ 'event_year' ] ) {

            $year = $query->query_vars[ 'event_year' ];

            // reset meta query
            $query->query_vars[ 'meta_value' ] = null;
            $query->query_vars[ 'meta_compare' ] = null;

            $query->query_vars[ 'meta_query' ] = array( 
                array(
                    'key' => 'hm-events_date',
                    'value' => array( mktime( 0, 0, 1, 1, 1, $year ), mktime( 23, 59, 59, 12, 31, $year ) ),
                    'compare' => 'BETWEEN',
                    'type' => 'numeric'
                )
            );

            /* by year + month */
            if( $query->query_vars[ 'event_month' ] ) {

                $month = $query->query_vars[ 'event_month' ];

                $query->query_vars[ 'meta_query' ] = array( 
                    array(
                        'key' => 'hm-events_date',
                        'value' => array( mktime( 0, 0, 1, $month, 1, $year ), mktime( 23, 59, 59, $month, 31, $year ) ),
                        'compare' => 'BETWEEN',
                        'type' => 'numeric'
                    )
                );

                /* by year + month + day */
                if( $query->query_vars[ 'event_day' ] ) {

                    $day = $query->query_vars[ 'event_day' ];

                    $query->query_vars[ 'meta_query' ] = array( 
                        array(
                            'key' => 'hm-events_date',
                            'value' => array( mktime( 0, 0, 0, $month, $day, $year ), mktime( 23, 59, 59, $month, $day, $year ) ),
                            'compare' => 'BETWEEN',
                            'type' => 'numeric'
                        )
                    );

                }

            }

        }

    }

    /* show all events on search results */
    if( is_search() && $query->query_vars[ 'post_type' ] == 'events' ) {

        $query->query_vars[ 'posts_per_page' ] = -1;

        $query->query_vars[ 'meta_key' ] = 'hm-events_date';
        $query->query_vars[ 'meta_value' ] = 0;
        $query->query_vars[ 'meta_compare' ] = '>';

        $query->query_vars[ 'orderby' ] = 'meta_value';
        $query->query_vars[ 'order' ] = 'DESC';

    }

    return $query;

}

if( !is_admin() ) { 
    add_filter( 'pre_get_posts', 'hm_events_modify_query' );
}


/**
 * Register rewrite rules for event archive pages
 * events/{passed}/{year}/{month}/{day}/{page}/{pagenumber}/
 *
 */
function hm_events_rewrite_rules() {
    global $hm_events_options;

    /* passed events */
    add_rewrite_rule(
        'events/passed/?$',
        'index.php?post_type=events&passed=passed',
        'top'
    );

    /* passed events paged */
    add_rewrite_rule(
        'events/passed/page/([0-9]+)/?$',
        'index.php?post_type=events&passed=passed&paged=$matches[1]',
        'top'
    );

    /* passed events by year */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]',
        'top'
    );

    /* passed events by year paged */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/page/([0-9]+)/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]&paged=$matches[2]',
        'top'
    );

    /* passed events by month */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/([0-9]{1,2})/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]&event_month=$matches[2]',
        'top'
    );

    /* passed events by month paged */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/([0-9]{1,2})/page/([0-9]+)/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]&event_month=$matches[2]&paged=$matches[3]',
        'top'
    );

    /* passed events by day */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]&event_month=$matches[2]&event_day=$matches[3]',
        'top'
    );

    /* passed events by day paged */
    add_rewrite_rule(
        'events/passed/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/([0-9]+)/?$',
        'index.php?post_type=events&passed=passed&event_year=$matches[1]&event_month=$matches[2]&event_day=$matches[3]&paged=$mathes[4]',
        'top'
    );

    /* by year */
    add_rewrite_rule(
        'events/([0-9]{4})/?$',
        'index.php?post_type=events&event_year=$matches[1]',
        'top'
    );

    /* by year paged */
    add_rewrite_rule(
        'events/([0-9]{4})/page/([0-9]+)/?$',
        'index.php?post_type=events&event_year=$matches[1]&paged=$matches[2]',
        'top'
    );

    /* by month */
    add_rewrite_rule(
        'events/([0-9]{4})/([0-9]{1,2})/?$',
        'index.php?post_type=events&event_year=$matches[1]&event_month=$matches[2]',
        'top'
    );

    /* by month paged */
    add_rewrite_rule(
        'events/([0-9]{4})/([0-9]{1,2})/page/([0-9]+)/?$',
        'index.php?post_type=events&event_year=$matches[1]&event_month=$matches[2]&paged=$matches[3]',
        'top'
    );

    /* by day */
    add_rewrite_rule(
        'events/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$',
        'index.php?post_type=events&event_year=$matches[1]&event_month=$matches[2]&event_day=$matches[3]',
        'top'
    );

    /* by day paged */
    add_rewrite_rule(
        'events/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/([0-9]+)/?$',
        'index.php?post_type=events&event_year=$matches[1]&event_month=$matches[2]&event_day=$matches[3]&paged=$mathes[4]',
        'top'
    );

    /* event type + passed events */
    add_rewrite_rule(
        $hm_events_options['event_types_slug'] . '/([a-zA-z_-]+)/passed/?$',
        'index.php?' . $hm_events_options['event_types_slug'] . '=$matches[1]&passed=passed',
        'top'
    );

}

add_action( 'init', 'hm_events_rewrite_rules' );


/**
 * Register rewrite rules for event archive pages
 * events/{passed}/{year}/{month}/{day}/{page}/{pagenumber}/
 *
 * @param   array   $query_vars     currently registered query vars
 * @return  array   $query_vars
 */
function hm_events_register_query_vars( $query_vars ) {
    
    $query_vars[] = 'passed';

    $query_vars[] = 'event_year';
    $query_vars[] = 'event_month';
    $query_vars[] = 'event_day';

    return $query_vars;
}

add_filter( 'query_vars', 'hm_events_register_query_vars' );


/**
 * Template tag: Display event date based on WP's global date & time format
 *
 * @param   string  $date_format    PHP date format. default is WP's global date/time format.
 */
function the_event_date( $date_format = '' ) {
    $event_date = get_event_date( $date_format );

    echo $event_date;
}


/**
 * Template tag: Get event date based on WP's global date & time format
 *
 * @param   string  $date_format    PHP date format. default is WP's global date/time format.
 * @return  string  $date_format
 */
function get_event_date( $date_format = '' ) {
    global $post;

    if( !$date_format ) { 
        $date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' ); 
    }

    $event_date = date( $date_format, get_post_meta( $post->ID, 'hm-events_date', true) );

    return $event_date;
}

/**
 * Template tag: Display event archive nav basd on event years
 *
 */
function get_event_archive_nav() {
    global $wpdb,
           $wp_query;

    $years = $wpdb->get_col( "SELECT YEAR( FROM_UNIXTIME( meta_value ) ) FROM $wpdb->postmeta WHERE meta_key = 'hm-events_date' ORDER BY meta_value DESC" );
    $years = array_unique( $years, SORT_REGULAR );

    if( $years ) {
        $baseURL = get_bloginfo( 'url' );
        $current = ( get_query_var( 'event_year' ) ) ? get_query_var( 'event_year' ) : 0;

        echo '<nav class="hm-events hm-events-archive-nav">';
        echo '<ul>';

        foreach( $years as $year ) {
            $current_class = ( $current == $year ) ? ' class="current"' : '';

            echo '<li' . $current_class . '>';
            echo '<a href="' . $baseURL . '/events/' . $year . '/">';
            echo $year;
            echo '</a>';
            echo '</li>';
        }

        echo '</ul>';
        echo '</nav>';
    }
}

require_once( 'option-page.php' );