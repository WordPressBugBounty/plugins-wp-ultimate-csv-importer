<?php
/**
 * WP Ultimate CSV Importer.
 *
 * WP Ultimate CSV Importer plugin file.
 *
 * @package   Smackcoders\UCI\Core
 * @copyright Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: WP Ultimate CSV Importer
 * Version:     8.0.1
 * Plugin URI:  https://www.smackcoders.com/wp-ultimate-csv-importer-pro.html
 * Description: Seamlessly create posts, custom posts, pages, media, SEO and more from your CSV data with ease.
 * Author:      Smackcoders
 * Author URI:  https://www.smackcoders.com/wordpress.html
 * Text Domain: wp-ultimate-csv-importer
 * Domain Path: /languages
 * License:     GPL v3
 * Requires PHP: 7.4
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Load plugin translations for PHP UI strings.
 */
function uci_load_textdomain() {
	load_plugin_textdomain(
		'wp-ultimate-csv-importer',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\uci_load_textdomain' );


$extension_uploader = glob( __DIR__ . '/extensionUploader/*.php');
foreach ($extension_uploader as $extension_upload_value) {
	include_once($extension_upload_value);
}
$upload_modules = glob( __DIR__ . '/uploadModules/*.php');
foreach ($upload_modules as $upload_module_value) {
	include_once($upload_module_value);
}
$extension_uploader = glob( __DIR__ . '/extensionUploader/*.php');
foreach ($extension_uploader as $extension_upload_value) {
	include_once($extension_upload_value);
}		

$upload_modules = glob( __DIR__ . '/uploadModules/*.php');
foreach ($upload_modules as $upload_module_value) {
	include_once($upload_module_value);
}

$extension_modules = glob( __DIR__ . '/extensionModules/*.php');
foreach ($extension_modules as $extension_module_value) {
	include_once($extension_module_value);
}

$manager_extension = glob( __DIR__ . '/managerExtensions/*.php');
foreach ($manager_extension as $manager_extension_value) {
	include_once($manager_extension_value);
}

$import_extensions = glob( __DIR__ . '/importExtensions/*.php');
foreach ($import_extensions as $import_extension_value) {
	include_once($import_extension_value);
}

require_once __DIR__ . '/includes/WpucsvHooks.php';

$export_extensions = glob( __DIR__ . '/exportExtensions/*.php');
foreach ($export_extensions as $export_extension_value) {
	include_once($export_extension_value);
}
include_once('SaveMapping.php');
include_once('MediaHandling.php');
include_once('ImportConfiguration.php');
include_once('Dashboard.php');
include_once('DragandDropExtension.php');
include_once('controllers/SendPassword.php');
include_once('controllers/SupportMail.php');
include_once('controllers/HelperExtension.php');
include_once('controllers/NeedHelperExtension.php');
include_once('controllers/Security.php');

class UCICore{

	

	protected static $instance = null;
	private static $table_instance = null;
	private static $validate_file = null;
	private static $desktop_upload_instance = null;
	private static $url_upload_instance = null;
	private static $ftp_upload_instance = null;
	private static $xml_instance = null;
	protected static $mapping_instance = null;
	private static $extension_instance = null;
	private static $save_mapping_instance = null;
	private static $plugin_instance = null;
	private static $import_config_instance = null;
	private static  $dashboard_instance = null;
	private static $drag_drop_instance = null;
	private static $log_manager_instance = null;
	private static $medi_instance = null;
	private static $db_optimizer = null;
	private static $send_password = null ; 
	private static $security = null ;
	private static $support_instance = null ;
	private static $helper_instance = null ;
	private static $need_helper_instance = null ;
	private static $uninstall = null ;
	private static $install = null ;
	private static $export_instance = null ;
	public static $en_instance = null ;
	public static $en_CA_instance = null ;
	public static $en_GB_instance = null ;
	public static $italy_instance = null ;
	public static $france_instance = null ;
	public static $german_instance = null ;
	public static $spanish_instance = null;
	public static $russian_instance = null;
	public static $portuguese_instance = null;
	public static $turkish_instance = null;
	public static $nz_instance = null;
	public static $pl_instance = null;
	public static $aus_instance = null;
	public static $enpi_instance = null;
	public static $japanese_instance = null;
	public static $dutch_instance = null;
	public static $en_ZA_instance = null;
	public static $tamil_instance = null;
	public static $arabic_instance = null;
	public static $persian_instance = null;
	public static $chinese_instance = null;
	private static $addon_instance = null;
	public $version = '8.0.1';

	/**
	 * UCICore Instance
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function show_admin_menus(){
		if( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$role = ( array ) $user->roles;
		} 
		
		if(!empty($role) && in_array( 'administrator' , $role)){
			if ( is_user_logged_in() &&  current_user_can('manage_options') ) {
				add_action('admin_menu',array(__CLASS__,'testing_function'));
			}
		}

		$first_activate = get_option("WP_ULTIMATE_CSV_FIRST_ACTIVATE");
		if($first_activate == 'On'){
			delete_option("WP_ULTIMATE_CSV_FIRST_ACTIVATE");	
			wp_redirect(admin_url().'admin.php?page=wp-addons-page');
			exit;
		}
	}

	public static function admin_body_class($classes) {
		$classes .= ' smack-csv-importer';
		return $classes;
	}

	public static function render_main_page() {
		echo '<div id="wp-csv-importer-admin"></div>';
	}

	public static function csv_register_importers() {
		// Stub to prevent fatal error
	}

	public function __construct() {
		add_action('init', array(__CLASS__, 'show_admin_menus'));
		//action to register in wordpress tools
		add_action('admin_init', array(__CLASS__, 'csv_register_importers'));
		// WordPress 7.0 AI: increase timeout for AI generation during import.
		add_filter('wp_ai_client_default_request_timeout', function ($timeout) {
			return 120;
		});
		$current_date_and_time = date("Y-m-d H:i:s");
		$nextnoticedate =get_option('close_date');
		if(!empty($nextnoticedate)){
			$nextnotice=strtotime("+3 day", strtotime($nextnoticedate));
		}

		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_all_assets'));
		do_action('sm_uci_load_addon_features');
		do_action('sm_uci_register_addon_schedulers');
		add_filter('admin_body_class', array(__CLASS__, 'admin_body_class'));
		$this->init_review_notice();

		self::$en_instance = \Smackcoders\UCI\Core\LangEN::getInstance();
		self::$italy_instance = \Smackcoders\UCI\Core\LangIT::getInstance();
		self::$france_instance = \Smackcoders\UCI\Core\LangFR::getInstance();
		self::$german_instance = \Smackcoders\UCI\Core\LangGE::getInstance();
		self::$spanish_instance = \Smackcoders\UCI\Core\LangES::getInstance();
		self::$japanese_instance = \Smackcoders\UCI\Core\LangJA::getInstance();
		self::$dutch_instance = \Smackcoders\UCI\Core\LangNL::getInstance();
		self::$russian_instance = \Smackcoders\UCI\Core\LangRU::getInstance();
		self::$portuguese_instance = \Smackcoders\UCI\Core\LangPT::getInstance();
		self::$turkish_instance = \Smackcoders\UCI\Core\LangTR::getInstance();
		self::$en_CA_instance = \Smackcoders\UCI\Core\LangEN_CA::getInstance();
		self::$en_GB_instance = \Smackcoders\UCI\Core\LangEN_GB::getInstance();
		self::$en_ZA_instance = \Smackcoders\UCI\Core\LangEN_ZA::getInstance();
		self::$validate_file = \Smackcoders\UCI\Core\ValidateFile::getInstance();
	}

	public function init_review_notice() {
    add_action('admin_notices', [$this, 'render_review_notice']);
    add_action('admin_init', [$this, 'handle_review_notice_actions']);
}

public function render_review_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $activation_time = get_option('wcsv_activation_time');
    $dismissed       = get_option('wcsv_review_dismissed');
    $later           = get_option('wcsv_review_later');

    if (!$activation_time) {
        update_option('wcsv_activation_time', time());  
        return;
    }

    if ((time() - $activation_time) < 7 * DAY_IN_SECONDS) {
        return;
    }

    if ($dismissed) {
        return;
    }

    if ($later && (time() - $later) < 7 * DAY_IN_SECONDS) {
        return;
    }
    ?>
    <div class="notice notice-success is-dismissible">
        <h2><?php esc_html_e('Loving WP Ultimate CSV Importer? 💙', 'wp-ultimate-csv-importer'); ?></h2>
        <p>
            <?php esc_html_e('We’d be so grateful if you could share your experience in a quick review. It only takes a minute, and it really helps us reach more WordPress users like you.', 'wp-ultimate-csv-importer'); ?>
        </p>
        <p>
            <a href="https://wordpress.org/support/plugin/wp-ultimate-csv-importer/reviews/?filter=5"
               target="_blank" class="button button-primary"><?php echo esc_html__( "⭐ Sure, I’ll Rate It", 'wp-ultimate-csv-importer' ); ?></a>
            <a href="<?php echo esc_url(add_query_arg('wcsv_review_later', '1')); ?>" class="button"><?php echo esc_html__( 'Maybe Later', 'wp-ultimate-csv-importer' ); ?></a>
            <a href="<?php echo esc_url(add_query_arg('wcsv_review_dismiss', '1')); ?>" class="button"><?php echo esc_html__( 'No, Thanks', 'wp-ultimate-csv-importer' ); ?></a>
        </p>
    </div>
    <?php
}

public function handle_review_notice_actions() {
	$pro_plugins = [
		'wp-ultimate-csv-importer-pro/wp-ultimate-csv-importer-pro.php'               => 'com.smackcoders.csvimporternewpro.menu',
		'wp-importer-custom-fields-basic-pro/wp-importer-custom-fields-basic-pro.php' => 'com.smackcoders.csvimporternewcustom.menu',
		'wordpress-importer-wpml-pro/wordpress-importer-wpml-pro.php'                 => 'com.smackcoders.csvimporternewwpml.menu'
	];

	foreach($pro_plugins as $plugin => $menu_slug){
		if(is_plugin_active($plugin)){
			$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
			if ($page === 'com.smackcoders.csvimporternew.menu') {
				wp_safe_redirect(admin_url('admin.php?page='.$menu_slug));
				exit;
			}
		}
	}
    if (isset($_GET['wcsv_review_dismiss'])) {
        update_option('wcsv_review_dismissed', 1);
        wp_redirect(remove_query_arg('wcsv_review_dismiss'));
        exit;
    }

    if (isset($_GET['wcsv_review_later'])) {
        update_option('wcsv_review_later', time());
        wp_redirect(remove_query_arg('wcsv_review_later')); 
        exit;
    }
}

    public static function enqueue_all_assets() {
        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
        $single_import_state = get_option('sm_uci_pro_settings');
        $single_import = isset($single_import_state['singleimport']) ? $single_import_state['singleimport'] : '';

        if (strpos($page, 'com.smackcoders.csvimporternew.menu') === false && $page !== 'wp-addons-page') {
            return;
        }
        $plugin_instance = self::getInstance();
        $plugin_slug = 'wp-ultimate-csv-importer';
        $upload = wp_upload_dir();
        $upload_base_url = $upload['baseurl'];
        $upload_url = $upload_base_url . '/smack_uci_uploads/imports';

        // Common scripts
        wp_enqueue_script('jquery-ui-droppable');
        wp_register_script('popper-js', plugins_url( 'assets/js/deps/popper.js', __FILE__), array('jquery'));
        wp_enqueue_script('popper-js');
        wp_register_script('bootstrap-js', plugins_url( 'assets/js/deps/bootstrap.min.js', __FILE__), array('jquery'));
        wp_enqueue_script('bootstrap-js');
        wp_register_script('main-js', plugins_url( 'assets/js/deps/main.js', __FILE__), array('jquery'));
        wp_enqueue_script('main-js');

        // React app scripts
        // Allow Pro addons to enqueue their scripts and styles
        do_action('sm_uci_enqueue_scripts');
        do_action('sm_uci_enqueue_styles');

        if ($page === 'com.smackcoders.csvimporternew.menu' || $single_import == 'true') {
            $react_js_path = plugin_dir_path(__FILE__) . 'assets/js/admin-v6.1.js';
            $react_js_version = file_exists($react_js_path) ? (string) filemtime($react_js_path) : $plugin_instance->version;
            wp_register_script('react-js', plugins_url('assets/js/admin-v6.1.js', __FILE__), array('wp-element', 'wp-components', 'wp-i18n', 'jquery'), $react_js_version);
            wp_enqueue_script('react-js');
        }

        // Common styles
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_bootstrap-css'), plugins_url( 'assets/css/deps/bootstrap.min.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_filepond-css'), plugins_url( 'assets/css/deps/filepond.min.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_react-datepicker-css'), plugins_url( 'assets/css/deps/react-datepicker.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_react-toastify-css'), plugins_url( 'assets/css/deps/ReactToastify.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_csv-importer-css'), plugins_url( 'assets/css/deps/csv-importer-free.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_csv-importer-roboto-css'), plugins_url( 'assets/css/deps/csv-importerfree-roboto.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_csv-importer-poppins-css'), plugins_url( 'assets/css/deps/csv-importerfree-poppins.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_style-css'), plugins_url('assets/css/deps/style.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_style-poppins-css'), plugins_url('assets/css/deps/style-poppins.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_style-roboto-css'), plugins_url('assets/css/deps/style-roboto.css', __FILE__));
        wp_enqueue_style(wp_unique_handle($plugin_slug . '_react-confirm-alert-css'), plugins_url('assets/css/deps/react-confirm-alert.css', __FILE__));

        // Language localization script
        // Use the admin/user locale for admin UI.
        $language = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
        $user_id = get_current_user_id();
        $contents = array();
        if($language == 'it_IT'){
            $contents = \Smackcoders\UCI\Core\LangIT::getInstance()->contents();
        } elseif($language == 'fr_FR' || $language == 'fr_BE'){
            $contents = \Smackcoders\UCI\Core\LangFR::getInstance()->contents();
        } elseif($language == 'de_DE' || $language == 'de_AT'){
            $contents = \Smackcoders\UCI\Core\LangGE::getInstance()->contents();
        } elseif ($language == 'es_ES') {
            $contents = \Smackcoders\UCI\Core\LangES::getInstance()->contents();
        } elseif ($language == 'en_CA') {
            $contents = \Smackcoders\UCI\Core\LangEN_CA::getInstance()->contents();
        } elseif ($language == 'en_GB') {
            $contents = \Smackcoders\UCI\Core\LangEN_GB::getInstance()->contents();
        } elseif ($language == 'tr_TR') {
            $contents = \Smackcoders\UCI\Core\LangTR::getInstance()->contents();
        } elseif ($language == 'en_NZ') {
            $contents = \Smackcoders\UCI\Core\LangNZ::getInstance()->contents();
        } elseif ($language == 'pl_PL') {
            $contents = \Smackcoders\UCI\Core\LangPL::getInstance()->contents();
        } elseif ($language == 'en_AU') {
            $contents = \Smackcoders\UCI\Core\LangAUS::getInstance()->contents();
        } elseif ($language == 'art_xpirate') {
            $contents = \Smackcoders\UCI\Core\LangPI::getInstance()->contents();
        } elseif ($language == 'en_ZA') {
            $contents = \Smackcoders\UCI\Core\LangEN_ZA::getInstance()->contents();
        } elseif ($language == 'ru_RU') {
            $contents = \Smackcoders\UCI\Core\LangRU::getInstance()->contents();
        } elseif($language == 'pt_BR') {
            $contents = \Smackcoders\UCI\Core\LangPT::getInstance()->contents();
        } elseif (strpos($language, 'ja') === 0) {
            $contents = \Smackcoders\UCI\Core\LangJA::getInstance()->contents();
        } elseif ($language == 'nl_NL') {
            $contents = \Smackcoders\UCI\Core\LangNL::getInstance()->contents();
        } elseif ($language == 'ta_IN') {
            $contents = \Smackcoders\UCI\Core\LangTA::getInstance()->contents();
        } elseif ($language == 'ar') {
            $contents = \Smackcoders\UCI\Core\LangAR::getInstance()->contents();
        } elseif ($language == 'fa_IR') {
            $contents = \Smackcoders\UCI\Core\LangFA::getInstance()->contents();
        } elseif ($language == 'zh_CN') {
            $contents = \Smackcoders\UCI\Core\LangZH::getInstance()->contents();
        } else {
            $contents = \Smackcoders\UCI\Core\LangEN::getInstance()->contents();
        }
        
        $response = wp_json_encode($contents);

        wp_enqueue_style('font-awesome-all', plugins_url('assets/css/deps/font-awesome-all.css', __FILE__));

        wp_localize_script('react-js', 'wpr_object', array(
            'file' => $response,
            'imagePath' => plugins_url('/assets/images/', __FILE__),
            'logfielpath' => $upload_url
        ));

        wp_localize_script('react-js', 'smack_nonce_object', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smack-ultimate-csv-importer'),
        ));

    }


	public static function smack_enqueue_scripts() {
		// This function is no longer used. Assets are enqueued in enqueue_all_assets().
	}

	public static function csv_enqueue_function(){
		// This function is no longer used. Assets are enqueued in enqueue_all_assets().
	}

	/**
	 * Generates unique key for each file.
	 * @param string $value - filename
	 * @return string hashkey
	 */
	public function convert_string2hash_key($value) {
		$file_name = hash_hmac('md5', "$value" . time() , 'secret');
		return $file_name;
	}


	/**
	 * Creates a folder in uploads.
	 * @return string path to that folder
	 */
	public function create_upload_dir($mode = null)
    {
		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		if(!is_dir($upload_dir)){
			return false;
        } else {
			$upload_dir = $upload_dir . '/smack_uci_uploads/imports/';
			if (!is_dir($upload_dir)) {
				wp_mkdir_p($upload_dir);
				chmod($upload_dir, 0755);

				$index_php_file = $upload_dir . 'index.php';
				if (!file_exists($index_php_file)) {
					$file_content = '<?php' . PHP_EOL . '?>';
					file_put_contents($index_php_file, $file_content);
				}
			}
			if($mode != 'CLI')
            {
				chmod($upload_dir, 0777);
			}
			return $upload_dir;
		}
	}
	
	public function delete_image_schedule()
	{

		global $wpdb;
		$wpdb->get_results("DELETE FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager");
	}

	public function image_schedule()
	{

		global $wpdb;
		$get_result = $wpdb->get_results("SELECT DISTINCT post_id FROM {$wpdb->prefix}ultimate_csv_importer_shortcode_manager", ARRAY_A);
		$records = array_column($get_result, 'post_id');

		foreach ($records as $title => $id) {
			$core_instance = CoreFieldsImport::getInstance();
			$post_id = $core_instance->image_handling($id);
		}
	}

	public function admin_bar_menu(){
		global $wp_admin_bar;
		$wp_admin_bar->add_menu( array(
			'id'     => 'debug-bar',
			'href' => admin_url().'admin.php?page=com.smackcoders.csvimporternew.menu',
			'parent' => 'top-secondary',
			'title'  => apply_filters( 'debug_bar_title', __( 'Maintenance Mode', 'wp-ultimate-csv-importer' ) ),
			'meta'   => array( 'class' => 'smack-main-mode' ),
		) );
	}

	public function activate_maintenance_mode() { 		
		global $maintainance_text;
		$maintainance_text = __( 'Site is under maintenance mode. Please wait a few minutes!', 'wp-ultimate-csv-importer' );
		if(!current_user_can('manage_options')) {
?> 
			<div class="main-mode-front"> <span> <?php echo esc_html($maintainance_text); ?> </span> </div> 
<?php }
	} 
	public static function testing_function (){
		remove_menu_page('com.smackcoders.csvimporternew.menu');
		$my_page = add_menu_page(
			__( 'Ultimate CSV Importer Free', 'wp-ultimate-csv-importer' ),
			__( 'Ultimate CSV Importer Free', 'wp-ultimate-csv-importer' ),
			'manage_options',
			'com.smackcoders.csvimporternew.menu',array(__CLASS__,'render_main_page'),plugins_url("assets/images/wp-ultimate-csv-importer.png",__FILE__));
		add_submenu_page(
			'com.smackcoders.csvimporternew.menu',
			__( 'Manage Addons', 'wp-ultimate-csv-importer' ),
			'<span style="color:#00a699">' . esc_html__( 'Manage Addons', 'wp-ultimate-csv-importer' ) . '</span>',
			'manage_options',
			'wp-addons-page',
			array(__CLASS__,'importer_addons_page')
		);
	}

	public static function importer_addons_page(){		
		wp_register_script('script_csv_importer_recommend_addon',plugins_url( 'assets/js/deps/recommendedAddons.js', __FILE__), array('jquery'));
		
			/* Create Nonce */
			$secure_uniquekey_csv = array(
				'url' => admin_url('admin-ajax.php') ,
				'nonce' => wp_create_nonce('smack-ultimate-csv-importer'),
				'imagePath' => plugins_url('/assets/images/', __FILE__)
			);
		   
			wp_localize_script('script_csv_importer_recommend_addon', 'smack_nonce_object', $secure_uniquekey_csv);
			wp_enqueue_script('script_csv_importer_recommend_addon');
		include_once('recommended-addons.php');		
	}

	public static function importer_pro_page() {
		wp_enqueue_style('com.smackcoders.smackcsvfont-awesome-css', plugins_url( 'assets/css/deps/font-awesome-all.css', __FILE__));	
		include_once('upgrade-to-pro.php');
	}

	public static	function importer_hireus_page() {
		wp_enqueue_style('com.smackcoders.smackcsvfont-awesome-css', plugins_url( 'assets/css/deps/font-awesome-all.css', __FILE__));			
		include_once('hire-us.php');
	}
}

// class_alias('Smackcoders\UCI\Core\UCICore', 'Smackcoders\UCI\Core\SmackCSV');
// class_alias('Smackcoders\UCI\Core\UCICore', 'Smackcoders\UCI\Core\SmackCSV');

include_once('Plugin.php');
// class_alias('Smackcoders\UCI\Core\Plugin', 'Smackcoders\UCI\Core\Plugin');
include_once('extensionModules/MappingExtension.php');
include_once('SmackCSVImporterInstall.php');
include_once('languages/LangIT.php');
include_once('languages/LangEN.php');
include_once('languages/LangGE.php');
include_once('languages/LangFR.php');
include_once('languages/LangRU.php');
include_once('languages/LangPT.php');
include_once('languages/LangTR.php');
include_once('languages/LangNZ.php');
include_once('languages/LangPL.php');
include_once('languages/LangAUS.php');
include_once('languages/LangPI.php');
include_once('languages/LangES.php');
include_once('languages/LangJA.php');
include_once('languages/LangNL.php');
include_once('languages/LangenGB.php');
include_once('languages/LangenCA.php');
include_once('languages/LangenZA.php');
include_once('languages/LangTA.php');
include_once('languages/LangAR.php');
include_once('languages/LangFA.php');
include_once('languages/LangZH.php');
include_once('Tables.php');
include_once('SmackCSVImporterUninstall.php');
include_once('InstallAddons.php');
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}
if (is_plugin_active('wp-ultimate-csv-importer/wp-ultimate-csv-importer.php')) {	
	global $csv_class;
	$csv_class = new UCICore();
	// For CLI
	include_once('SingleImportExport.php');
	include_once('SmackcliHandler.php');
	$singlecsv_class = new \Smackcoders\UCI\Core\SingleImportExport();
																
}

$activate_plugin = new SmackCSVInstall();
$deactive_plugin = SmackUCIUnInstall::getInstance();
register_activation_hook( __FILE__, array($activate_plugin,'install'));
register_deactivation_hook(__FILE__, array($deactive_plugin, 'unInstall'));
add_action( 'plugins_loaded', 'Smackcoders\\UCI\\Core\\onpluginsload' );

namespace Smackcoders\UCI\Core;

function onpluginsload(){
	loadbasic();
	$ucisettings = get_option('sm_uci_pro_settings');
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$role = ( array ) $user->roles;
	} 
		if(!empty($role) && in_array( 'administrator' , $role ) ){
		if ( is_user_logged_in() &&  current_user_can('manage_options') ) {
			loadbasic();
		}
	}

}
add_action('admin_head', 'Smackcoders\\UCI\\Core\\disable_admin_notices_on_plugin_page');

function disable_admin_notices_on_plugin_page() {

    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    if ($page === 'com.smackcoders.csvimporternew.menu') {
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}


namespace Smackcoders\UCI\Core;

function wp_unique_handle($handle) {
    return $handle;
}	

function loadbasic(){
	$plugin_pages = ['com.smackcoders.csvimporternew.menu'];
	include __DIR__ . '/wp-csv-hooks.php';
	global $plugin_ajax_hooks;
	global $smackCLI;

	$request_page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
	$request_action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']): '';	

	if ($smackCLI || (in_array($request_page, $plugin_pages) || in_array($request_action, $plugin_ajax_hooks))) {					
		
		UCICore::getInstance();			

		// Initialize singleton classes to register AJAX hooks
		\Smackcoders\UCI\Core\SaveMapping::getInstance();
		\Smackcoders\UCI\Core\MediaHandling::getInstance();
		\Smackcoders\UCI\Core\ImportConfiguration::getInstance();
		\Smackcoders\UCI\Core\Dashboard::getInstance();
		\Smackcoders\UCI\Core\DragandDropExtension::getInstance();
		\Smackcoders\UCI\Core\SendPassword::getInstance();
		\Smackcoders\UCI\Core\SupportMail::getInstance();
		\Smackcoders\UCI\Core\HelperExtension::getInstance();
		\Smackcoders\UCI\Core\NeedHelperExtension::getInstance();
		\Smackcoders\UCI\Core\Security::getInstance();
		
		\Smackcoders\UCI\Core\MappingExtension::getInstance();
		\Smackcoders\UCI\Core\ExportExtension::getInstance();
		\Smackcoders\UCI\Core\LogManager::getInstance();
		\Smackcoders\UCI\Core\DesktopUpload::getInstance();
		\Smackcoders\UCI\Core\FtpUpload::getInstance();
		\Smackcoders\UCI\Core\UrlUpload::getInstance();
		\Smackcoders\UCI\Core\XmlHandler::getInstance();
		\Smackcoders\UCI\Core\InstallAddons::getInstance();
		\Smackcoders\UCI\Core\SingleImportExport::getInstance();
		\Smackcoders\UCI\Core\ImportHelpers::getInstance();
	}	
}

?>