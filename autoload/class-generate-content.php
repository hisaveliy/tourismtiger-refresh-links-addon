<?php

namespace TourismTiger\Refresh_Links;

use WP_Query;

class Generate_Content
{
    public static function get_content_for_blog_posts(){
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                global $post;

                if ( !$post->post_content ) :
                    $post_id = $post->ID;

                    ob_start();

                    include WP_CONTENT_DIR. "/themes/tourismtiger-theme/template-parts/post.php";

                    $pattern = '/<style\b[^>]*>(.*?)<\/style>/is';
                    $html = preg_replace($pattern, '', ob_get_clean());
                    $html = str_replace('&nbsp;', '', $html);

                    $pattern = '/\s{2,}/';
                    $replacement = "";
                    $html = preg_replace($pattern, $replacement, $html); // &#8211

                    $pattern = ' &#8211';
                    $html = str_replace($pattern, '', $html);
                    $html = str_replace('&nbsp;', '', $html);

                    $content = strip_tags($html);

                    $updated_post = array(
                        'ID'           => $post_id,
                        'post_content' => $content,
                    );

                    $result = wp_update_post($updated_post);
                endif;
            }
            wp_reset_postdata();
        }
    }
}
