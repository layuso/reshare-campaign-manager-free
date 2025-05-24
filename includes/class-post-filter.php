<?php
namespace RCM;

if (!defined('ABSPATH')) exit;

class Post_Filter {

    public static function query_posts($args = []) {
        $query_args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($args['search'])) $query_args['s'] = sanitize_text_field($args['search']);
        if (!empty($args['tags'])) $query_args['tag'] = implode(',', (array) $args['tags']);
        if (!empty($args['categories'])) $query_args['category_name'] = implode(',', (array) $args['categories']);
        if (!empty($args['author'])) $query_args['author'] = absint($args['author']);
        if (!empty($args['date_start']) || !empty($args['date_end'])) {
            $query_args['date_query'] = [];
            if (!empty($args['date_start'])) $query_args['date_query'][] = ['after' => sanitize_text_field($args['date_start'])];
            if (!empty($args['date_end'])) $query_args['date_query'][] = ['before' => sanitize_text_field($args['date_end'])];
        }

        $query = new \WP_Query($query_args);
        return $query->posts;
    }
}

