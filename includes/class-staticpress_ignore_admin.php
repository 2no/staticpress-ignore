<?php

class staticpress_ignore_admin
{
	const OPTION_KEY   = 'staticpress_ignore';
	const OPTION_PAGE  = 'staticpress_ignore';
	const TEXT_DOMAIN  = 'staticpress_ignore';

	const NONCE_ACTION = 'ignore_update_options';
	const NONCE_NAME   = '_wpnonce_ignore_update_options';

	public static $debug_mode = false;

	private $options;
	private $plugin_basename;
	private $admin_action;

	public function __construct()
	{
		$this->options = $this->get_option();
		$this->plugin_basename = staticpress_ignore::plugin_basename();
		add_action( 'StaticPress::options_save', array( $this, 'options_save' ) );
		add_action( 'StaticPress::options_page', array( $this, 'options_page' ) );
		add_action( 'StaticPress::admin_head',   array( $this, 'admin_head'   ) );
	}

	public function admin_head()
	{
?>

<style type="text/css">
#ignore_pattern { width: 35em; }
</style>
<?php
	}

	public static function option_keys()
	{
		return array(
			'ignore_pattern' => '除外パターン',
		);
	}

	public static function get_option()
	{
		$options = get_option( self::OPTION_KEY );
		foreach ( array_keys( self::option_keys() ) as $key ) {
			if ( ! isset( $options[$key] ) || is_wp_error( $options[$key] ) ) {
				$options[$key] = '';
			}
		}
		return $options;
	}

	//**************************************************************************************
	// Add Admin Menu
	//**************************************************************************************
	public function options_save()
	{
		$option_keys   = $this->option_keys();
		$this->options = $this->get_option();

		$iv = new InputValidator( 'POST' );
		$iv->set_rules( self::NONCE_NAME, 'required' );

		// Update options
		if ( ! is_wp_error( $iv->input( self::NONCE_NAME ) ) && check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME ) ) {
			// Get posted options
			$fields = array_keys( $option_keys );
			foreach ( $fields as $field ) {
				$iv->set_rules( $field, array( 'trim', 'esc_html' ) );
			}
			$options = $iv->input( $fields );
			$err_message = '';
			foreach ( $option_keys as $key => $field ) {
				if ( is_wp_error( $options[$key] ) ) {
					$error_data = $options[$key];
					$err = '';
					foreach ( $error_data->errors as $errors ) {
						foreach ( $errors as $error ) {
							$err .= ( ! empty( $err ) ? '<br />' : '') . __( 'Error! : ', self::TEXT_DOMAIN );
							$err .= sprintf(
								__( str_replace( $key, '%s', $error ), self::TEXT_DOMAIN ),
								$field
							);
						}
					}
					$err_message .= ( ! empty( $err_message ) ? '<br />' : '' ) . $err;
				}
				if ( ! isset( $options[$key] ) || is_wp_error( $options[$key] ) ) {
					$options[$key] = '';
				}
			}
			if ( self::$debug_mode && function_exists( 'dbgx_trace_var' ) ) {
				dbgx_trace_var( $options );
			}

			// Update options
			if ( $this->options !== $options ) {
				update_option( self::OPTION_KEY, $options );
				printf(
					'<div id="message" class="updated fade"><p><strong>%s</strong></p></div>'."\n",
					empty( $err_message ) ? __( 'Done!', self::TEXT_DOMAIN ) : $err_message
				);
				$this->options = $options;
			}
			unset( $options );
		}
	}

	public function options_page()
	{
		$options = $this->get_option();
?>
		<div class="wrap">
		<h2>StaticPress 除外設定</h2>
		<form method="post" action="<?php echo $this->admin_action; ?>">
		<?php echo wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME, true, false ) . "\n"; ?>
                <table class="form-table"><tbody><tr>
			<th><label for="ignore_pattern">除外パターン</label></th>
			<td>
				<textarea name="ignore_pattern" id="ignore_pattern" rows="6"><?php
					echo esc_html( $options['ignore_pattern'] );
				?></textarea>
				<p class="description">書き出したくないファイルやディレクトリを一行ずつ追加してください。</p>
			</td>
                </tr></tbody></table>
		<?php submit_button(); ?>
		</form>
		</div>
<?php
	}
}
