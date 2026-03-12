<?php
// Charger les styles du thème parent + enfant et styles custom.
function mon_theme_enfant_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );

    wp_enqueue_style(
        'global-custom',
        get_stylesheet_directory_uri() . '/assets/css/global.css',
        array( 'child-style' ),
        '1.0.0'
    );

    // Seulement sur les pages catégories produits.
    if ( is_product_category() ) {
        wp_enqueue_style(
            'producteurs-style',
            get_stylesheet_directory_uri() . '/assets/css/producteurs.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }

        // Seulement sur les pages producteur (taxonomy)
    if (is_tax('producteur')) {
        wp_enqueue_style(
            'page-producteur-style',
            get_stylesheet_directory_uri() . '/assets/css/page-producteur.css',
            array('child-style'),
            '1.0.0'
        );
        
        // Charger aussi les styles des cards produits
        wp_enqueue_style(
            'producteurs-style',
            get_stylesheet_directory_uri() . '/assets/css/producteurs.css',
            array('child-style'),
            '1.0.1'
        );
    }

    // Seulement sur la homepage.
    if ( is_front_page() ) {
        wp_enqueue_style(
            'homepage-style',
            get_stylesheet_directory_uri() . '/assets/css/homepage.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }

    // Partout sur WooCommerce.
    if ( is_woocommerce() ) {
        wp_enqueue_style(
            'woo-custom',
            get_stylesheet_directory_uri() . '/assets/css/woocommerce.css',
            array( 'child-style' ),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mon_theme_enfant_enqueue_styles' );

// Image de fallback personnalisée pour les produits.
add_filter( 'woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder' );

function custom_woocommerce_placeholder( $src ) {
	return get_stylesheet_directory_uri() . '/assets/images/product-placeholder.webp';
}
