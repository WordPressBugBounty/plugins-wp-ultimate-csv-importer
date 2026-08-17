<?php
/**
 * Plugin deactivation feedback modal and API delivery.
 *
 * @package wp-ultimate-csv-importer
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the deactivation feedback popup on the Plugins screen.
 */
class DeactivationFeedback {

	/**
	 * Smackcoders feedback API endpoint.
	 */
	const FEEDBACK_API_URL = 'https://control.smackcoders.com/api/feedback';

	/**
	 * Nonce action for AJAX and localized script data.
	 */
	const NONCE_ACTION = 'smack-deactivation-feedback-free';

	/**
	 * AJAX action name.
	 */
	const AJAX_ACTION = 'smack_send_deactivation_feedback_free';

	/**
	 * DOM id prefix for modal elements.
	 */
	const DOM_ID_PREFIX = 'smack-deactivation-feedback-free';

	/**
	 * Singleton instance.
	 *
	 * @var DeactivationFeedback|null
	 */
	private static $instance = null;

	/**
	 * Allowed feedback reason keys.
	 *
	 * @var string[]
	 */
	private static $allowed_reasons = array(
		'temporary_testing',
		'not_working',
		'better_alternative',
		'missing_feature',
		'no_longer_need',
		'other',
	);

	/**
	 * Get singleton instance.
	 *
	 * @return DeactivationFeedback
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->register_hooks();
		}

		return self::$instance;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_modal' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( $this, 'ajax_send_feedback' ) );
	}

	/**
	 * Main plugin bootstrap file path.
	 *
	 * @return string
	 */
	private function get_plugin_file() {
		return dirname( __DIR__ ) . '/wp-ultimate-csv-importer.php';
	}

	/**
	 * Whether the current user may use the deactivation feedback feature.
	 *
	 * @return bool
	 */
	private function current_user_can_submit() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		if ( is_multisite() && is_network_admin() && current_user_can( 'manage_network_plugins' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue scripts and styles on the Plugins screen only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'plugins.php' !== $hook_suffix || ! $this->current_user_can_submit() ) {
			return;
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file = $this->get_plugin_file();
		$plugin_data = get_plugin_data( $plugin_file );
		$version     = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0';
		$handle      = 'wsmack-uci-free-deactivation-feedback';

		wp_enqueue_style(
			$handle,
			plugins_url( 'admin/css/deactivation-feedback.css', $plugin_file ),
			array( 'dashicons' ),
			$version
		);

		wp_enqueue_script(
			$handle,
			plugins_url( 'admin/js/deactivation-feedback.js', $plugin_file ),
			array( 'jquery' ),
			$version,
			true
		);

		wp_localize_script(
			$handle,
			'smackDeactivationFeedbackFree',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( self::NONCE_ACTION ),
				'action'         => self::AJAX_ACTION,
				'pluginBasename' => plugin_basename( $plugin_file ),
				'idPrefix'       => self::DOM_ID_PREFIX,
				'strings'        => $this->get_localized_strings(),
			)
		);
	}

	/**
	 * Localized UI strings for JavaScript.
	 *
	 * @return array<string, string>
	 */
	private function get_localized_strings() {
		return array(
			'title'            => __( 'Quick Feedback', 'wp-ultimate-csv-importer' ),
			'description'      => __( 'If you have a moment, please share why you are deactivating WP Ultimate CSV Importer.', 'wp-ultimate-csv-importer' ),
			'otherLabel'       => __( 'Please tell us more (optional)', 'wp-ultimate-csv-importer' ),
			'submitDeactivate' => __( 'Submit & Deactivate', 'wp-ultimate-csv-importer' ),
			'skipDeactivate'   => __( 'Skip & Deactivate', 'wp-ultimate-csv-importer' ),
			'submitting'       => __( 'Sending feedback…', 'wp-ultimate-csv-importer' ),
			'successMessage'   => __( 'Thank you for your feedback! Deactivating the plugin…', 'wp-ultimate-csv-importer' ),
			'ajaxError'        => __( 'Could not send feedback. You can skip deactivation or try again.', 'wp-ultimate-csv-importer' ),
		);
	}

	/**
	 * Feedback reason options keyed by slug.
	 *
	 * @return array<string, string>
	 */
	private function get_feedback_reasons() {
		return array(
			'temporary_testing'  => __( 'This is a temporary deactivation for testing.', 'wp-ultimate-csv-importer' ),
			'not_working'        => __( 'Something isn\'t working properly.', 'wp-ultimate-csv-importer' ),
			'better_alternative' => __( 'I found a better alternative.', 'wp-ultimate-csv-importer' ),
			'missing_feature'    => __( 'It\'s missing a specific feature.', 'wp-ultimate-csv-importer' ),
			'no_longer_need'     => __( 'I no longer need the plugin.', 'wp-ultimate-csv-importer' ),
			'other'              => __( 'Other', 'wp-ultimate-csv-importer' ),
		);
	}

	/**
	 * Output modal markup in the admin footer on the Plugins screen.
	 *
	 * @return void
	 */
	public function render_modal() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if (
			! $screen
			|| ! in_array( $screen->id, array( 'plugins', 'plugins-network' ), true )
			|| ! $this->current_user_can_submit()
		) {
			return;
		}

		$prefix  = self::DOM_ID_PREFIX;
		$reasons = $this->get_feedback_reasons();
		?>
		<div id="<?php echo esc_attr( $prefix ); ?>-overlay" class="smack-deactivation-feedback-overlay" hidden aria-hidden="true">
			<div
				id="<?php echo esc_attr( $prefix ); ?>-modal"
				class="smack-deactivation-feedback-modal"
				role="dialog"
				aria-modal="true"
				aria-labelledby="<?php echo esc_attr( $prefix ); ?>-title"
				aria-describedby="<?php echo esc_attr( $prefix ); ?>-description"
				tabindex="-1"
			>
				<button type="button" class="smack-deactivation-feedback-close" aria-label="<?php echo esc_attr__( 'Close', 'wp-ultimate-csv-importer' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'wp-ultimate-csv-importer' ); ?></span>
					<span aria-hidden="true">&times;</span>
				</button>

				<h2 id="<?php echo esc_attr( $prefix ); ?>-title" class="smack-deactivation-feedback-title">
					<?php esc_html_e( 'Quick Feedback', 'wp-ultimate-csv-importer' ); ?>
				</h2>

				<div id="<?php echo esc_attr( $prefix ); ?>-body" class="smack-deactivation-feedback-body">
					<p id="<?php echo esc_attr( $prefix ); ?>-description" class="smack-deactivation-feedback-description">
						<?php esc_html_e( 'If you have a moment, please share why you are deactivating WP Ultimate CSV Importer.', 'wp-ultimate-csv-importer' ); ?>
					</p>

					<fieldset class="smack-deactivation-feedback-reasons">
						<legend class="screen-reader-text"><?php esc_html_e( 'Deactivation reason', 'wp-ultimate-csv-importer' ); ?></legend>
						<?php foreach ( $reasons as $key => $label ) : ?>
							<label class="smack-deactivation-feedback-reason">
								<input
									type="radio"
									name="smack_deactivation_feedback_reason_free"
									value="<?php echo esc_attr( $key ); ?>"
									<?php checked( 'temporary_testing', $key ); ?>
								/>
								<span><?php echo esc_html( $label ); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>

					<div id="<?php echo esc_attr( $prefix ); ?>-other-wrap" class="smack-deactivation-feedback-other-wrap" hidden>
						<label for="<?php echo esc_attr( $prefix ); ?>-comment" class="smack-deactivation-feedback-other-label">
							<?php esc_html_e( 'Please tell us more (optional)', 'wp-ultimate-csv-importer' ); ?>
						</label>
						<textarea
							id="<?php echo esc_attr( $prefix ); ?>-comment"
							class="smack-deactivation-feedback-comment"
							rows="4"
							placeholder="<?php echo esc_attr__( 'Share additional details…', 'wp-ultimate-csv-importer' ); ?>"
						></textarea>
					</div>

					<div id="<?php echo esc_attr( $prefix ); ?>-error" class="smack-deactivation-feedback-error" hidden role="alert"></div>

					<div class="smack-deactivation-feedback-actions">
						<button type="button" class="button button-primary smack-deactivation-feedback-submit">
							<span class="smack-deactivation-feedback-submit-text"><?php esc_html_e( 'Submit & Deactivate', 'wp-ultimate-csv-importer' ); ?></span>
							<span class="spinner smack-deactivation-feedback-spinner" aria-hidden="true"></span>
						</button>
						<button type="button" class="button button-secondary smack-deactivation-feedback-skip">
							<?php esc_html_e( 'Skip & Deactivate', 'wp-ultimate-csv-importer' ); ?>
						</button>
					</div>
				</div>

				<div id="<?php echo esc_attr( $prefix ); ?>-success" class="smack-deactivation-feedback-success" hidden>
					<div class="smack-deactivation-feedback-success-icon" aria-hidden="true">
						<span class="dashicons dashicons-yes-alt"></span>
					</div>
					<p class="smack-deactivation-feedback-success-message">
						<?php esc_html_e( 'Thank you for your feedback! Deactivating the plugin…', 'wp-ultimate-csv-importer' ); ?>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX handler: send deactivation feedback via the Smackcoders API.
	 *
	 * @return void
	 */
	public function ajax_send_feedback() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! $this->current_user_can_submit() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		$reason_key = isset( $_POST['feedback_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['feedback_reason'] ) ) : '';
		$comment    = isset( $_POST['feedback_comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback_comment'] ) ) : '';

		if ( ! empty( $reason_key ) && ! in_array( $reason_key, self::$allowed_reasons, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid feedback reason.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		$sent = $this->send_feedback_to_api( $reason_key, $comment );

		if ( ! $sent ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not send feedback. You can skip deactivation or try again.', 'wp-ultimate-csv-importer' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Thank you for your feedback! Deactivating the plugin…', 'wp-ultimate-csv-importer' ),
			)
		);
	}

	/**
	 * Send deactivation feedback to the Smackcoders API.
	 *
	 * @param string $feedback_reason Selected reason slug.
	 * @param string $comment         Additional comment.
	 * @return bool
	 */
	private function send_feedback_to_api( $feedback_reason, $comment ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		global $wp_version;

		$plugin_file    = $this->get_plugin_file();
		$plugin_data    = get_plugin_data( $plugin_file );
		$plugin_version = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';

		$payload = array(
			'sku'               => 'wp-ultimate-csv-importer',
			'plugin'            => 'wp-ultimate-csv-importer',
			'edition'           => 'free',
			'feedback_reason'   => sanitize_text_field( $feedback_reason ),
			'comment'           => sanitize_textarea_field( $comment ),
			'plugin_version'    => sanitize_text_field( $plugin_version ),
			'wordpress_version' => sanitize_text_field( $wp_version ),
			'php_version'       => sanitize_text_field( PHP_VERSION ),
			'submitted_at'      => gmdate( 'Y-m-d H:i:s' ),
		);

		$response = wp_safe_remote_post(
			self::FEEDBACK_API_URL,
			array(
				'timeout'     => 15,
				'redirection' => 0,
				'headers'     => array(
					'Accept'       => 'application/json',
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'body'        => wp_json_encode( $payload ),
				'data_format' => 'body',
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );

		if ( $response_code >= 200 && $response_code < 300 ) {
			return true;
		}

		$decoded = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		return is_array( $decoded ) && ! empty( $decoded['success'] );
	}
}
