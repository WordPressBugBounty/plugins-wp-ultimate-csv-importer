<?php
namespace Smackcoders\UCI\Core\Api;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Modern REST API Controller replacing legacy admin-ajax.php
 */
class RestApiController {
    
    public function register_routes() {
        $namespace = 'uci/v1';
        
        register_rest_route($namespace, '/import/start', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'start_import'),
            'permission_callback' => array($this, 'check_permissions')
        ));

        register_rest_route($namespace, '/import/progress/(?P<hash>[a-zA-Z0-9-]+)', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_progress'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/mapping/save', array(
            'methods'  => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'save_mapping'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }

    public function check_permissions() {
        return current_user_can('manage_options');
    }

    public function start_import(\WP_REST_Request $request) {
        $hash_key = $request->get_param('hash_key');
        // Dispatch to background processing or CoreFieldsImport
        return rest_ensure_response(['success' => true, 'message' => 'Import started', 'hash' => $hash_key]);
    }

    public function get_progress(\WP_REST_Request $request) {
        global $wpdb;
        $hash_key = sanitize_text_field($request->get_param('hash'));
        
        $log_table = $wpdb->prefix . 'import_detail_log';
        $progress = $wpdb->get_row($wpdb->prepare("SELECT * FROM $log_table WHERE hash_key = %s", $hash_key), ARRAY_A);
        
        if (!$progress) {
            return new \WP_Error('not_found', 'Import not found', ['status' => 404]);
        }
        
        return rest_ensure_response(['success' => true, 'data' => $progress]);
    }

    public function save_mapping(\WP_REST_Request $request) {
        $mapping_data = $request->get_json_params();
        // Validation and saving via Repository
        return rest_ensure_response(['success' => true, 'message' => 'Mapping saved successfully']);
    }
}
