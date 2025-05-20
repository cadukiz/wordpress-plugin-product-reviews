<?php
/**
 * Plugin Name: Simple Reviews
 * Description: A simple WordPress plugin that registers a custom post type for product reviews and provides REST API support.
 * Version: 1.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

class Simple_Reviews {
    public function __construct() {
        add_action('init', [$this, 'register_product_review_cpt']);   
        add_action('rest_api_init', [$this, 'register_rest_routes']);   
        add_action('init', [$this, 'display_product_reviews_init']);
        
    }

    public function display_product_reviews_init(){
        add_shortcode('product_reviews',[$this,'display_product_reviews']);
    }
    
    public function register_product_review_cpt() {
        register_post_type('product_review', [
            'labels'      => [
                'name'          => 'Product Reviews',
                'singular_name' => 'Product Review'
            ],
            'public'      => true,
            'supports'    => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
        ]);
    }

    public function register_rest_routes() {
        register_rest_route('mock-api/v1', '/sentiment/', [
            'methods'  => 'POST',
            'callback' => [$this, 'analyze_sentiment'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('mock-api/v1', '/review-history/', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_review_history'],
            'permission_callback' => '__return_true',
        ]);
        register_rest_route('mock-api/v1', '/outliers/', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_outliers'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function analyze_sentiment($request) {
        $params = $request->get_json_params();
        $text = isset($params['text']) ? sanitize_text_field($params['text']) : '';
        
        if (empty($text)) {
            return new WP_Error('empty_text', 'No text provided for analysis.', ['status' => 400]);
        }

        $sentiment_scores = ['positive' => 0.9, 'negative' => 0.2, 'neutral' => 0.5];
        $random_sentiment = array_rand($sentiment_scores);
        return rest_ensure_response(['sentiment' => $random_sentiment, 'score' => $sentiment_scores[$random_sentiment]]);
    }

    public function get_review_history() {
        $reviews = get_posts([
            'post_type'      => 'product_review',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        
        $response = [];
        foreach ($reviews as $review) {
            $response[] = [
                'id'       => $review->ID,
                'title'    => $review->post_title,
                'sentiment'=> get_post_meta($review->ID, 'sentiment', true) ?? 'neutral',
                'score'    => get_post_meta($review->ID, 'sentiment_score', true) ?? 0.5,
            ];
        }

        return rest_ensure_response($response);
    }

    public function get_outliers() {
        global $wpdb;
    
        // Get sum and count
        $result = $wpdb->get_row("
            SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,4))) as total_score, COUNT(pm.meta_value) as total_reviews
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'sentiment_score'
              AND p.post_type = 'product_review'
              AND p.post_status = 'publish'
        ");
        $mean = ($result->total_reviews > 0) ? ($result->total_score / $result->total_reviews) : 0;
    
        // Get all scores
        $scores = $wpdb->get_results("
            SELECT p.ID, p.post_title, pm.meta_value as score
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = 'sentiment_score'
              AND p.post_type = 'product_review'
              AND p.post_status = 'publish'
        ");
    
        $score_values = array_map(function($row) { return floatval($row->score); }, $scores);
        $variance = 0.0;
        foreach ($score_values as $score) {
            $variance += pow($score - $mean, 2);
        }
        $stddev = (count($score_values) > 0) ? sqrt($variance / count($score_values)) : 0;
    
        // Find outliers
        $outliers = [];
        foreach ($scores as $row) {
            if (abs(floatval($row->score) - $mean) > $stddev) {
                $outliers[] = [
                    'id' => $row->ID,
                    'title' => $row->post_title,
                    'score' => floatval($row->score),
                    'sentiment' => get_post_meta($row->ID, 'sentiment', true) ?? 'neutral',
                ];
            }
        }
    
        return rest_ensure_response([
            'mean' => $mean,
            'stddev' => $stddev,
            'outliers' => $outliers,
        ]);
    }
  
    

    public function display_product_reviews() {
        $reviews = get_posts([
            'post_type'      => 'product_review',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $output = '<style>
            .review-positive { color: green; font-weight: bold; }
            .review-negative { color: red; font-weight: bold; }
        </style>';

        $output .= '<ul>';
        foreach ($reviews as $review) {
            $sentiment = get_post_meta($review->ID, 'sentiment', true) ?? 'neutral';
            $class = ($sentiment === 'positive') ? 'review-positive' : (($sentiment === 'negative') ? 'review-negative' : '');
            $output .= "<li class='$class'>{$review->post_title} (Sentiment: $sentiment)</li>";
        }
        $output .= '</ul>';

        return $output;
    }
}

new Simple_Reviews();
