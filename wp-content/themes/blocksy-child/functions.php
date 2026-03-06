<?php
// Charger les styles du thème parent + enfant et styles custom.
function mon_theme_enfant_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );

    wp_enqueue_style(
        'global-custom',
        get_stylesheet_directory_uri() . '/assets/global.css',
        array( 'child-style' ),
        '1.0.0'
    );

    // Seulement sur les pages catégories produits.
    if ( is_product_category() ) {
        wp_enqueue_style(
            'producteurs-style',
            get_stylesheet_directory_uri() . '/assets/producteurs.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }

    // Seulement sur la homepage.
    if ( is_front_page() ) {
        wp_enqueue_style(
            'homepage-style',
            get_stylesheet_directory_uri() . '/assets/homepage.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }

    // Partout sur WooCommerce.
    if ( is_woocommerce() ) {
        wp_enqueue_style(
            'woo-custom',
            get_stylesheet_directory_uri() . '/assets/woocommerce.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mon_theme_enfant_enqueue_styles' );
