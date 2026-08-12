<?php
namespace Smackcoders\UCI\Core\Background;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles asynchronous media downloading using Action Scheduler.
 * 
 * To use this, ensure Action Scheduler is bundled or active.
 */
class MediaBackgroundProcessor {
    
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self;
            self::$instance->init();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init() {
        add_action('smack_uci_download_media', array($this, 'process_media_download'), 10, 3);
    }

    /**
     * Schedules a media download job.
     */
    public function schedule_download($image_url, $post_id, $meta_data) {
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('smack_uci_download_media', array(
                'image_url' => $image_url,
                'post_id' => $post_id,
                'meta_data' => $meta_data
            ));
        } else {
            // Fallback to synchronous if Action Scheduler is not available
            $this->process_media_download($image_url, $post_id, $meta_data);
        }
    }

    /**
     * The background worker that actually downloads and attaches the image.
     */
    public function process_media_download($image_url, $post_id, $meta_data) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Simulate secure download
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            // Log failure
            return;
        }

        $file_array = array(
            'name' => basename($image_url),
            'tmp_name' => $tmp
        );

        $id = media_handle_sideload($file_array, $post_id, null);

        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
            return;
        }

        // Attach as featured image if requested
        if (isset($meta_data['featured']) && $meta_data['featured']) {
            set_post_thumbnail($post_id, $id);
        }
    }
}
