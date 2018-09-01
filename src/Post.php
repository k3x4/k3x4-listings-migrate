<?php

namespace k3x4\ListingsMigrate;

class Post{

    public function __construct(){
        add_filter('wp_import_posts', [$this, 'filterPosts'], 10, 1);
        //add_filter('wp_import_post_data_raw', [$this, 'migratePost'], 10, 1);
        add_filter('wp_import_post_terms', [$this, 'mapCategory'], 10, 3);
        add_filter('wp_import_post_comments', '__return_null', 10, 3);
        add_filter('wp_import_post_meta', '__return_null', 10, 3);
    }

    public function filterPosts($posts){
        $filter = [
            'listing',
            //'attachment'
        ];
        $posts = array_filter($posts, function($post) use ($filter){
            return in_array($post['post_type'], $filter);
        });

        return $posts;
    }

    public function migratePost($post){
        return $post;
    }

    public function mapCategory($terms, $post_id, $post){
        if(!$terms){
            return $terms;
        }

        $filter = [
            'pointfinderltypes',
        ];
        $terms = array_filter($terms, function($term) use ($filter){
            return in_array($term['domain'], $filter);
        });

        $map = [
            'pointfinderltypes' => 'listing_category'
        ];

        //var_dump($terms);exit();

        array_walk($terms, function(&$item, $key) use ($map){
            if($key == 'domain' && $item){
                $item = $map[$item];
            }
        });

        return $terms;
    }

}