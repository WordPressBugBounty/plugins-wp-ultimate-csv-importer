<?php
/**
 * Plugin deactivation feedback modal and email delivery.
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
	 * Feedback email recipient.
	 */
	const EMAIL_RECIPIENT = 'support@smackcoders.com';

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
	 * AJAX handler: send deactivation feedback via email.
	 *
	 * @return void
	 */
	public function ajax_send_feedback() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! $this->current_user_can_submit() ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'wp-ultimate-csv-importer' ),
				),
				403
			);
		}

		$reason_key = isset( $_POST['feedback_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['feedback_reason'] ) ) : '';
		$comment    = isset( $_POST['feedback_comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback_comment'] ) ) : '';

		if ( ! empty( $reason_key ) && ! in_array( $reason_key, self::$allowed_reasons, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid feedback reason.', 'wp-ultimate-csv-importer' ),
				),
				400
			);
		}

		$reasons         = $this->get_feedback_reasons();
		$feedback_reason = '';

		if ( ! empty( $reason_key ) && isset( $reasons[ $reason_key ] ) ) {
			$feedback_reason = $reasons[ $reason_key ];
		}

		$sent = $this->send_feedback_email( $feedback_reason, $comment );

		if ( ! $sent ) {
			wp_send_json_error(
				array(
					'message' => __( 'Could not send feedback. You can skip deactivation or try again.', 'wp-ultimate-csv-importer' ),
				),
				500
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Thank you for your feedback! Deactivating the plugin…', 'wp-ultimate-csv-importer' ),
			)
		);
	}

	/**
	 * Send feedback email.
	 *
	 * @param string $feedback_reason Selected reason label.
	 * @param string $comment         Additional comment.
	 * @return bool
	 */
	private function send_feedback_email( $feedback_reason, $comment ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		global $wp_version;

		$plugin_file    = $this->get_plugin_file();
		$plugin_data    = get_plugin_data( $plugin_file );
		$current_user   = wp_get_current_user();
		$plugin_version = ! empty( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';
		$site_url       = site_url();
		$datetime       = wp_date( 'Y-m-d H:i:s T' );
		$subject        = '[WP Ultimate CSV Importer] Plugin Deactivation Feedback';

		$body_lines = array(
			__( 'Plugin Deactivation Feedback', 'wp-ultimate-csv-importer' ),
			'',
			__( 'Feedback Reason:', 'wp-ultimate-csv-importer' ) . ' ' . ( $feedback_reason ? $feedback_reason : __( 'Not specified', 'wp-ultimate-csv-importer' ) ),
			__( 'Additional Comment:', 'wp-ultimate-csv-importer' ) . ' ' . ( $comment ? $comment : __( 'None', 'wp-ultimate-csv-importer' ) ),
			'',
			__( 'Site URL:', 'wp-ultimate-csv-importer' ) . ' ' . $site_url,
			__( 'Plugin Version:', 'wp-ultimate-csv-importer' ) . ' ' . $plugin_version,
			__( 'WordPress Version:', 'wp-ultimate-csv-importer' ) . ' ' . $wp_version,
			__( 'PHP Version:', 'wp-ultimate-csv-importer' ) . ' ' . PHP_VERSION,
			__( 'User ID:', 'wp-ultimate-csv-importer' ) . ' ' . (int) $current_user->ID,
			__( 'User Name:', 'wp-ultimate-csv-importer' ) . ' ' . $current_user->display_name,
			__( 'User Email:', 'wp-ultimate-csv-importer' ) . ' ' . $current_user->user_email,
			__( 'Date & Time:', 'wp-ultimate-csv-importer' ) . ' ' . $datetime,
		);

		$message = implode( "\n", $body_lines );
		$headers = $this->build_mail_headers( $current_user->display_name, $current_user->user_email );

		return $this->dispatch_feedback_mail( self::EMAIL_RECIPIENT, $subject, $message, $headers );
	}

	/**
	 * Build email headers with a valid From address.
	 *
	 * @param string $reply_name  Reply-to display name.
	 * @param string $reply_email Reply-to email address.
	 * @return string[]
	 */
	private function build_mail_headers( $reply_name, $reply_email ) {
		$site_name   = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$from_email  = $this->get_sender_email();
		$from_header = sprintf(
			'From: %s <%s>',
			$this->format_mail_name( $site_name ),
			$from_email
		);

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			$from_header,
		);

		$reply_email = sanitize_email( $reply_email );
		if ( ! empty( $reply_email ) ) {
			$headers[] = sprintf(
				'Reply-To: %s <%s>',
				$this->format_mail_name( $reply_name ),
				$reply_email
			);
		}

		return $headers;
	}

	/**
	 * Resolve a valid sender email for mail headers and PHPMailer.
	 *
	 * @return string
	 */
	private function get_sender_email() {
		if ( $this->has_smtp_configuration() && defined( 'SMACK_UCI_SMTP_USER' ) && SMACK_UCI_SMTP_USER ) {
			$smtp_user = sanitize_email( SMACK_UCI_SMTP_USER );
			if ( is_email( $smtp_user ) ) {
				return $smtp_user;
			}
		}

		$admin_email = sanitize_email( get_option( 'admin_email' ) );
		if ( is_email( $admin_email ) ) {
			return $admin_email;
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$host = $host ? preg_replace( '/[^a-z0-9.-]/i', '', $host ) : 'example.com';

		return 'noreply@' . $host;
	}

	/**
	 * Send mail using direct SMTP or wp_mail().
	 *
	 * @param string   $to      Recipient email.
	 * @param string   $subject Email subject.
	 * @param string   $message Email body.
	 * @param string[] $headers Email headers.
	 * @return bool
	 */
	private function dispatch_feedback_mail( $to, $subject, $message, $headers ) {
		if ( $this->has_valid_smtp_configuration() && ! $this->is_sendmail_available() ) {
			if ( $this->attempt_direct_smtp_mail( $to, $subject, $message, $headers ) ) {
				return true;
			}
		} elseif ( $this->is_sendmail_available() ) {
			if ( $this->attempt_wp_mail( $to, $subject, $message, $headers ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Send mail via a standalone PHPMailer SMTP connection.
	 *
	 * @param string   $to      Recipient email.
	 * @param string   $subject Email subject.
	 * @param string   $message Email body.
	 * @param string[] $headers Email headers.
	 * @return bool
	 */
	private function attempt_direct_smtp_mail( $to, $subject, $message, $headers ) {
		if ( ! class_exists( '\PHPMailer\PHPMailer\PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
		}

		$mail = new \PHPMailer\PHPMailer\PHPMailer( true );

		try {
			$this->apply_smtp_config( $mail );
			$mail->isHTML( false );
			$mail->addAddress( $to );
			$mail->Subject = $subject;
			$mail->Body    = $message;

			foreach ( $headers as $header ) {
				if ( 0 === stripos( $header, 'Reply-To:' ) && preg_match( '/<([^>]+)>/', $header, $matches ) ) {
					$reply_email = sanitize_email( $matches[1] );
					if ( is_email( $reply_email ) ) {
						$reply_name = trim( preg_replace( '/<[^>]+>/', '', substr( $header, 9 ) ) );
						$reply_name = trim( $reply_name, "\" \t" );
						$mail->addReplyTo( $reply_email, $reply_name );
					}
				}
			}

			$mail->send();

			return true;
		} catch ( \Exception $exception ) {
			return false;
		}
	}

	/**
	 * Attempt to send mail through wp_mail().
	 *
	 * @param string   $to      Recipient email.
	 * @param string   $subject Email subject.
	 * @param string   $message Email body.
	 * @param string[] $headers Email headers.
	 * @return bool
	 */
	private function attempt_wp_mail( $to, $subject, $message, $headers ) {
		$on_phpmailer_init = function ( $phpmailer ) {
			$phpmailer->CharSet = 'UTF-8';
		};

		add_action( 'phpmailer_init', $on_phpmailer_init, PHP_INT_MAX );

		$sent = wp_mail( $to, $subject, $message, $headers );

		remove_action( 'phpmailer_init', $on_phpmailer_init, PHP_INT_MAX );

		return (bool) $sent;
	}

	/**
	 * Whether SMTP constants are available.
	 *
	 * @return bool
	 */
	private function has_smtp_configuration() {
		return defined( 'SMACK_UCI_SMTP_HOST' ) && SMACK_UCI_SMTP_HOST;
	}

	/**
	 * Whether SMTP constants contain real credentials.
	 *
	 * @return bool
	 */
	private function has_valid_smtp_configuration() {
		if ( ! $this->has_smtp_configuration() ) {
			return false;
		}

		if ( ! defined( 'SMACK_UCI_SMTP_USER' ) || ! SMACK_UCI_SMTP_USER || ! defined( 'SMACK_UCI_SMTP_PASS' ) || ! SMACK_UCI_SMTP_PASS ) {
			return false;
		}

		if ( $this->has_placeholder_smtp_credentials() ) {
			return false;
		}

		return is_email( SMACK_UCI_SMTP_USER );
	}

	/**
	 * Whether wp-config.php still contains example SMTP placeholder values.
	 *
	 * @return bool
	 */
	private function has_placeholder_smtp_credentials() {
		if ( ! defined( 'SMACK_UCI_SMTP_USER' ) || ! defined( 'SMACK_UCI_SMTP_PASS' ) ) {
			return false;
		}

		$user = strtolower( (string) SMACK_UCI_SMTP_USER );
		$pass = strtolower( (string) SMACK_UCI_SMTP_PASS );

		$placeholder_users = array(
			'your-email@smackcoders.com',
			'user@example.com',
			'yourname@smackcoders.com',
		);

		$placeholder_passwords = array(
			'your-app-password',
			'app-password',
			'password',
			'your-password',
		);

		if ( in_array( $user, $placeholder_users, true ) || in_array( $pass, $placeholder_passwords, true ) ) {
			return true;
		}

		return ( 0 === strpos( $user, 'your-' ) || 0 === strpos( $pass, 'your-' ) );
	}

	/**
	 * Whether the server has a sendmail-compatible binary.
	 *
	 * @return bool
	 */
	private function is_sendmail_available() {
		$sendmail_path = ini_get( 'sendmail_path' );

		if ( is_string( $sendmail_path ) && preg_match( '/^(\S+)/', $sendmail_path, $matches ) ) {
			return file_exists( $matches[1] );
		}

		return file_exists( '/usr/sbin/sendmail' ) || file_exists( '/usr/bin/sendmail' );
	}

	/**
	 * Apply SMTP settings to a PHPMailer instance.
	 *
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
	 * @return void
	 */
	private function apply_smtp_config( $phpmailer ) {
		if ( ! $this->has_smtp_configuration() ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host = SMACK_UCI_SMTP_HOST;
		$phpmailer->Port = defined( 'SMACK_UCI_SMTP_PORT' ) ? (int) SMACK_UCI_SMTP_PORT : 587;

		if ( defined( 'SMACK_UCI_SMTP_USER' ) && SMACK_UCI_SMTP_USER ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = SMACK_UCI_SMTP_USER;
			$phpmailer->Password = defined( 'SMACK_UCI_SMTP_PASS' ) ? SMACK_UCI_SMTP_PASS : '';
		}

		if ( defined( 'SMACK_UCI_SMTP_SECURE' ) && SMACK_UCI_SMTP_SECURE ) {
			$phpmailer->SMTPSecure = SMACK_UCI_SMTP_SECURE;
		}

		$from_email = $this->get_sender_email();
		$from_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		try {
			$phpmailer->setFrom( $from_email, $from_name, false );
		} catch ( \Exception $exception ) {
			// Ignore invalid From address; PHPMailer will fall back to its defaults.
		}
	}

	/**
	 * Format a display name for mail headers.
	 *
	 * @param string $name Display name.
	 * @return string
	 */
	private function format_mail_name( $name ) {
		$name = wp_strip_all_tags( (string) $name );
		$name = str_replace( array( "\r", "\n" ), '', $name );
		$name = trim( $name );

		if ( '' === $name ) {
			return 'WordPress';
		}

		if ( preg_match( '/[^\w \-\.]/', $name ) ) {
			return '"' . addcslashes( $name, '"\\' ) . '"';
		}

		return $name;
	}
}
