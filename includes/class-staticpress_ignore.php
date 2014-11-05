<?php

class staticpress_ignore
{
	const FNM_PATHNAME = 1;
	const FNM_NOESCAPE = 2;
	const FNM_PERIOD   = 4;
	const FNM_CASEFOLD = 16;

	private $options;

	public function __construct( $options )
	{
		$this->options = $options;
		add_filter( 'StaticPress::get_url', array( $this, 'get_url' ) );
	}

	public static function plugin_basename()
	{
		return plugin_basename( dirname( dirname( __FILE__ ) ) . '/plugin.php' );
	}

	public function get_url( $url )
	{
		$options  = $this->options;
		$patterns = isset( $options['ignore_pattern'] ) ? $options['ignore_pattern'] : '';
		$patterns = preg_split( '/\r?\n/', $patterns, -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $patterns as $pattern ) {
			if ( self::fnmatch( $pattern, $url ) ) {
				return;
			}
		}
		return $url;
	}

	private static function fnmatch( $pattern, $string, $flags = 0 )
	{
		if ( function_exists( 'fnmatch' ) ) {
			return fnmatch( $pattern, $string, $flags );
		}
		return self::pcre_match( $pattern, $string, $flags );
	}

	/**
	 * @link http://php.net/manual/ja/function.fnmatch.php#100207
	 */
	private static function pcre_fnmatch( $pattern, $string, $flags = 0 )
	{
		$modifiers  = null;
		$transforms = array(
		    '\*'   => '.*',
		    '\?'   => '.',
		    '\[\!' => '[^',
		    '\['   => '[',
		    '\]'   => ']',
		    '\.'   => '\.',
		    '\\'   => '\\\\',
		);

		// Forward slash in string must be in pattern:
		if ( $flags & self::FNM_PATHNAME ) {
			$transforms['\*'] = '[^/]*';
		}

		// Back slash should not be escaped:
		if ( $flags & self::FNM_NOESCAPE ) {
			unset($transforms['\\']);
		}

		// Perform case insensitive match:
		if ( $flags & self::FNM_CASEFOLD ) {
			$modifiers .= 'i';
		}

		// Period at start must be the same as pattern:
		if ( $flags & self::FNM_PERIOD ) {
			if ( strpos( $string, '.' ) === 0 && strpos( $pattern, '.' ) !== 0 ) {
				return false;
			}
		}

		$pattern = '#^'
		    . strtr( preg_quote( $pattern, '#' ), $transforms )
		    . '$#'
		    . $modifiers;

		return (boolean)preg_match( $pattern, $string );
	}
}
