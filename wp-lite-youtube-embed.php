<?php

/**
 * Plugin Name: Lite Youtube Embed
 * Description: A plugin to display a Lite Youtube Embed module.
 * Author: Pure Dazzle
 * Author URI: https://puredazzle.se/
 * Text Domain: lite-youtube-embed
 * Version: 1.1.0
 * Requires PHP: 8.2
 * Requires at least: 6.5
 */

declare(strict_types=1);

namespace LiteYoutubeEmbed;

defined('ABSPATH') || exit;

class Plugin
{
    public function __construct()
    {
        add_action('wp_footer', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_post_lite_youtube_clear_oembed_cache', [$this, 'handle_clear_cache']);
        add_filter(
            'oembed_dataparse',
            [$this, 'add_div_around_youtube_embeds'],
            99,
            4,
        );

        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_filter('mce_external_plugins', [$this, 'register_tinymce_plugin']);
        add_action('admin_init', [$this, 'enqueue_admin_assets']);
    }

    public function register_settings_page(): void
    {
        add_options_page(
            'Lite YouTube Embed',
            'Lite YouTube Embed',
            'manage_options',
            'lite-youtube-embed',
            [$this, 'render_settings_page'],
        );
    }

    public function render_settings_page(): void
    {
        $cleared = isset($_GET['cache-cleared']) && $_GET['cache-cleared'] === '1';
?>

        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($cleared): ?>

                <div class="notice notice-success is-dismissible">
                    <p>oEmbed cache cleared successfully.</p>
                </div>

            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('lite_youtube_clear_oembed_cache'); ?>

                <input type="hidden" name="action" value="lite_youtube_clear_oembed_cache" />
                <p>This will force WordPress to re-fetch all oEmbed data on the next page load.</p>

                <?php submit_button('Clear oEmbed Cache', 'secondary'); ?>
            </form>
        </div>

<?php
    }

    public function handle_clear_cache(): void
    {
        check_admin_referer('lite_youtube_clear_oembed_cache');

        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_oembed_%'"
        );

        $wpdb->query(
            "DELETE pm FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key LIKE '_oembed_%'"
        );

        $wpdb->query(
            "DELETE FROM {$wpdb->posts} WHERE post_type = 'oembed_cache'"
        );

        wp_cache_flush();

        wp_safe_redirect(
            add_query_arg('cache-cleared', '1', admin_url('options-general.php?page=lite-youtube-embed'))
        );
        exit;
    }

    public function add_div_around_youtube_embeds(string $html, array|object $data, string $url): string
    {
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            $matches = [];
            preg_match('/(?:youtube\.com\/.*v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
            $videoId = $matches[1] ?? '';

            $html = sprintf(
                '<lite-youtube videoid="%s" posterquality="maxresdefault"></lite-youtube>',
                $videoId,
            );
        }

        return $html;
    }

    public function enqueue_assets(): void
    {
        if (
            wp_get_environment_type() === 'local' &&
            is_array(@wp_remote_get('http://localhost:5174/'))
        ) {
            wp_enqueue_script_module('vite', 'http://localhost:5174/@vite/client');
            wp_enqueue_script_module('lite-youtube-embed', 'http://localhost:5174/resources/js/index.js', ['vite']);
        } elseif (file_exists(__DIR__ . '/build/index.js')) {
            wp_enqueue_script_module('lite-youtube-embed', plugin_dir_url(__FILE__) . 'build/index.js', version: '1.1.0');
            wp_enqueue_style('lite-youtube-embed', plugin_dir_url(__FILE__) . 'build/index.css', ver: '1.1.0');
        }
    }


    public function enqueue_block_assets(): void
    {
        wp_enqueue_script(
            'lite-youtube-embed',
            plugin_dir_url(__FILE__) . 'build/index.js',
            ver: '1.1.0'
        );

        wp_enqueue_style(
            'lite-youtube-embed',
            plugin_dir_url(__FILE__) . 'build/index.css',
            ver: '1.1.0',
        );
    }

    public function enqueue_admin_assets(): void
    {
        // Only needed in classic editor context, but safe to always add in admin
        add_action('admin_head', function (): void {
            printf(
                '<script>var liteYouTubeAssets = %s;</script>',
                wp_json_encode([
                    'js'  => plugin_dir_url(__FILE__) . 'build/index.js',
                    'css' => plugin_dir_url(__FILE__) . 'build/index.css',
                ])
            );
        });
    }

    public function register_tinymce_plugin(array $plugins): array
    {
        $plugins['lite_youtube'] = plugin_dir_url(__FILE__) . 'public/tinymce-plugin.js';
        return $plugins;
    }
}

new Plugin();
