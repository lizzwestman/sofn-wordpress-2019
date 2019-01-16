<?php
/**
 * Fusion-Privacy handler.
 *
 * @package Fusion-Library
 * @since 1.5.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle Privacy related stuff.
 *
 * @since 1.5.2
 */
class Fusion_Privacy {

	/**
	 * The screens where notices will be displayed.
	 *
	 * @access private
	 * @since 1.5.2
	 * @var string
	 */
	private $screens;

	/**
	 * The contents of message.
	 *
	 * @access private
	 * @since 1.5.2
	 * @var string
	 */
	private $message;

	/**
	 * Array of data which is sent to the server.
	 *
	 * @access private
	 * @since 1.5.2
	 * @var string
	 */
	private $server_data;

	/**
	 * Current screen.
	 *
	 * @access private
	 * @since 1.5.2
	 * @var string
	 */
	private $current_screen;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Add the notices.
		add_action( 'admin_init', array( $this, 'display_notice' ) );

		// Handle saving the data via ajax.
		add_action( 'wp_ajax_fusion_dismiss_admin_notice', array( $this, 'dismiss_notice' ) );
	}

	/**
	 * Check if we're on the right screen and display notice.
	 *
	 * @access public
	 * @since 1.5.2
	 * @return void
	 */
	public function display_notice() {
		if ( isset( $_GET['page'] ) ) {
			$this->current_screen = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			$this->screens        = $this->get_allowed_screens();
			$this->server_data    = $this->get_server_data();
			$this->message        = $this->get_message_contents( $this->current_screen );

			if ( class_exists( 'Fusion_Admin_Notice' ) && $this->is_show() && ( isset( $this->current_screen ) && in_array( $this->current_screen, $this->screens ) ) ) {
				new Fusion_Admin_Notice(
					'fusion-privacy-notice',
					$this->message,
					is_super_admin(),
					'info',
					true,
					'user_meta',
					'the-meta'
				);
			}
		}
	}

	/**
	 * Dmismiss notice.
	 *
	 * @access public
	 * @since 1.5.2
	 * @return void
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'fusion_admin_notice', 'nonce' );

		if ( ! empty( $_POST ) && isset( $_POST['data'] ) ) {
			$option = '';
			if ( isset( $_POST['data']['dismissOption'] ) ) {
				$option = sanitize_text_field( wp_unslash( $_POST['data']['dismissOption'] ) );
			} elseif ( isset( $_POST['data']['dismiss-option'] ) ) {
				$option = sanitize_text_field( wp_unslash( $_POST['data']['dismiss-option'] ) );
			}

			$type = '';
			if ( isset( $_POST['data']['dismissType'] ) ) {
				$type = sanitize_text_field( wp_unslash( $_POST['data']['dismissType'] ) );
			} elseif ( isset( $_POST['data']['dismiss-type'] ) ) {
				$type = sanitize_text_field( wp_unslash( $_POST['data']['dismiss-type'] ) );
			}

			switch ( $type ) {
				case 'user_meta':
					// @codingStandardsIgnoreLine WordPress.VIP.RestrictedFunctions.user_meta_update_user_meta
					update_user_meta( get_current_user_id(), $option, true );
					break;
			}
		}

		wp_die();
	}

	/**
	 * Get list of screens where notice should be displayed.
	 *
	 * @access private
	 * @since 1.5.2
	 * @return array
	 */
	private function get_allowed_screens() {
		$screens = array(
			'avada-fusion-patcher',
			'avada-registration',
			'avada-plugins',
			'avada-demos',
		);

		return $screens;
	}

	/**
	 * Array of data which is sent to server.
	 *
	 * @access private
	 * @since 1.5.2
	 * @return array
	 */
	private function get_server_data() {
		global $wp_version;
		$data = array(
			'server' => array(
				'name'  => __( 'PHP Version', 'Avada' ),
				'value' => phpversion(),
			),
			'php' => array(
				'name'  => __( 'Server Software', 'Avada' ),
				'value' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			),
			'wp' => array(
				'name'  => __( 'WordPress Version', 'Avada' ),
				'value' => $wp_version,
			),
			'url' => array(
				'name'  => __( 'Encrypted Site URL', 'Avada' ),
				'value' => md5( site_url() ),
			),
			'token' => array(
				'name'  => __( 'Token', 'Avada' ),
				'value' => Avada()->registration->get_token(),
			),
		);
		return $data;
	}

	/**
	 * Prepare message contents.
	 *
	 * @access private
	 * @since 1.5.2
	 * @param string $page current page slug.
	 * @return string
	 */
	private function get_message_contents( $page ) {
		switch ( $page ) {
			case 'avada-demos':
				$message = sprintf( '<p>%s</p>', esc_html__( 'Following data is sent to a ThemeFusion server located in the US to verify purchase and to ensure that demos are compatible with your install.', 'Avada' ) );
				break;
			case 'avada-registration':
				$message = sprintf( '<p>%s</p>', esc_html__( 'Following data is sent to a ThemeFusion server located in the US to verify purchase.', 'Avada' ) );
				break;
			case 'avada-plugins':
				$message = sprintf( '<p>%s</p>', esc_html__( 'Following data will be sent to a ThemeFusion server located in the US to verify purchase and to ensure that plugins are compatible with your install.', 'Avada' ) );
				break;
			default:
				$message = sprintf( '<p>%s</p>', esc_html__( 'Following data is sent to a ThemeFusion server located in the US to ensure that patches are compatible with your install.', 'Avada' ) );
		}
		$message .= '<table>';
		if ( 'avada-fusion-patcher' !== $page ) {
			$message .= sprintf( '<tr><td>%s:</td><td>%s</td></tr>', $this->server_data['token']['name'], $this->server_data['token']['value'] );
		} else {
			foreach ( $this->server_data as $i => $index ) {
				if ( 'Token' !== $index['name'] ) {
					$message .= sprintf( '<tr><td>%s:</td><td>%s</td></tr>', $index['name'], $index['value'] );
				}
			}
		}

		$message .= '</table>';

		$message .= sprintf( '<p>%s</p>', esc_html__( 'We will never collect any confidential data such as IP, email addresses or usernames.', 'Avada' ) );

		return $message;
	}

	/**
	 * Check if message should be displayed or not?
	 *
	 * @access private
	 * @since 1.5.2
	 * @return bool
	 */
	private function is_show() {

		if ( 'avada-fusion-patcher' === $this->current_screen || ( ( 'avada-registration' === $this->current_screen || 'avada-plugins' === $this->current_screen || 'avada-demos' === $this->current_screen ) && Avada()->registration->is_registered() ) ) {
			return true;
		}

		return false;
	}
}
