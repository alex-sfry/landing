<?php

function add_scripts_and_styles()
{
    // wp_register_script('jquery', get_template_directory_uri() . '/assets/js/jquery.js', array(), null, true);
    wp_enqueue_script('swiper-scripts', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '1.0', true);
    wp_enqueue_script('main',  get_template_directory_uri() . '/assets/js/main.min.js', array('swiper-scripts'), '1.0', true);

    wp_enqueue_style('swiper-styles', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '1.0', 'all');
    wp_enqueue_style('main', get_template_directory_uri() . '/assets/css/style.css', array('swiper-styles'), '1.0', 'all');
    wp_enqueue_style('theme_info', get_template_directory_uri() . '/style.css', array(), '1.0', 'all');
}

add_action('wp_enqueue_scripts', 'add_scripts_and_styles');
add_theme_support('custom-logo');
 
