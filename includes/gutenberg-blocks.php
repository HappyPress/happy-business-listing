<?php

// Register custom Gutenberg block
function hbl_register_gutenberg_blocks() {
    // Register block script
    wp_register_script(
        'hbl-block',
        plugins_url('/assets/js/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch')
    );

    // Register block type
    register_block_type('hbl/business-block', array(
        'editor_script' => 'hbl-block',
    ));
}
add_action('init', 'hbl_register_gutenberg_blocks');

// Enqueue block editor assets
function hbl_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'hbl-block-editor',
        plugins_url('/assets/js/block-editor.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch')
    );
}
add_action('enqueue_block_editor_assets', 'hbl_enqueue_block_editor_assets');
?>
