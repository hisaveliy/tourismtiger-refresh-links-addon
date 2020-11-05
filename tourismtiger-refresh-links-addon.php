<?php

/*
Plugin Name: TourismTiger Refresh Links Add-on
Plugin URI: https://www.tourismtiger.com
Description: Replaces all the http links related to its domain to https, if the domain is based on https.
Version: 1.0.0
Author: TourismTiger
Author URI: https://www.tourismtiger.com
Text Domain: https-links
Domain Path: /lang
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('Https_Links') ) :


class Https_Links
{
    protected static $instance = null;
    protected static $site_url = null;
    protected static $https_link = 0;
    protected static $needle = null;

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
            self::$site_url = get_bloginfo('url');

            if ( strpos(self::$site_url, 'https://' ) === 0) :
                self::$https_link = 1;
                self::$needle = str_replace('https://', 'http://', self::$site_url );
            endif;
        }

        return self::$instance;
    }

    /**
     * Fields constructor.
     */
    function __construct () {
        add_action('init', __CLASS__ . '::refresh_links');
        add_action('admin_bar_menu', __CLASS__ . '::admin_bar_menu', 500);
    }


    /**
     *
     */
    public static function refresh_links() {

        if (isset($_GET['refresh_links'])) :
            $links_number = self::refresh_links_processing();

            show_notice( sprintf(__('%d links have been successfully refreshed!', 'tourismtiger-theme'), $links_number ), 'success' );
        endif;

    }


    public static function refresh_links_processing(){
        global $wpdb;
        $resp = 0;

        $query = "SELECT * FROM " . $wpdb->posts . " WHERE post_content LIKE '%".self::$needle."%'";
        $links = $wpdb->query( $query );

        $query = "SELECT * FROM " . $wpdb->postmeta . " WHERE meta_value LIKE '%".self::$needle."%'";
        $links_meta = $wpdb->query( $query );

        if ( $links ) :
            $query = "UPDATE {$wpdb->posts} 
                    SET 
                        post_content = REPLACE( post_content, '". self::$needle ."', '". self::$site_url ."' )
                    WHERE
                        post_content LIKE '%".self::$needle."%'";
            $resp += $wpdb->query( $query );
        endif;

        if ( $links_meta ) :
            $query = "UPDATE {$wpdb->postmeta} 
                    SET 
                        meta_value = REPLACE( meta_value, '". self::$needle ."', '". self::$site_url ."' )
                    WHERE
                        meta_value LIKE '%".self::$needle."%'";
            $resp += $wpdb->query( $query );
        endif;

        return $resp;
    }


    /**
     * @param $wp_admin_bar
     */
    public static function admin_bar_menu( $wp_admin_bar ) {
        $item = (object) array(
            'slug' => 'refresh_links',
            'name' => __('Refresh links', 'tourismtiger-theme'),
        );

        $title = sprintf(
            '<span class="ab-label"><span class="screen-reader-text">%1$s</span>%2$s</span>',
            $item->name,
            esc_html( $item->name )
        );

        $wp_admin_bar->add_menu(
            array(
                'id'    => 'refresh_links',
                'title' => $title,
                'href'  => esc_url( add_query_arg( 'refresh_links', 1, remove_query_arg( 'paged' ) ) ),
                'meta'  => array( 'title' => $item->name ),
            )
        );
    }
}

Https_Links::instance();

endif;
