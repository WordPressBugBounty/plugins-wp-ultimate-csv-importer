<?php
/**
 * Safe expression evaluation for import field mapping formulas.
 *
 * Replaces eval() with an allow-listed function approach.
 *
 * @package Smackcoders\UCI\Core
 */

namespace Smackcoders\UCI\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SafeExpressionEvaluator {

	/**
	 * PHP functions permitted inside bracket mapping formulas.
	 *
	 * @var string[]
	 */
	private static $allowed_functions = array(
		'strtoupper',
		'strtolower',
		'trim',
		'ltrim',
		'rtrim',
		'substr',
		'strlen',
		'ucfirst',
		'lcfirst',
		'ucwords',
		'str_replace',
		'str_ireplace',
		'preg_replace',
		'number_format',
		'round',
		'floor',
		'ceil',
		'abs',
		'intval',
		'floatval',
		'sanitize_text_field',
		'wp_strip_all_tags',
		'date',
		'gmdate',
		'implode',
		'explode',
		'json_encode',
		'htmlspecialchars',
		'html_entity_decode',
		'strip_tags',
		'nl2br',
		'md5',
		'sha1',
		'urlencode',
		'rawurlencode',
		'urldecode',
		'rawurldecode',
		'base64_encode',
		'wordwrap',
		'str_pad',
		'str_repeat',
		'mb_strlen',
		'mb_substr',
		'mb_strtoupper',
		'mb_strtolower',
	);

	/**
	 * Tokens that must never appear in a mapping expression.
	 *
	 * @var string[]
	 */
	private static $blocked_tokens = array(
		'eval',
		'assert',
		'create_function',
		'system',
		'exec',
		'shell_exec',
		'passthru',
		'proc_open',
		'popen',
		'pcntl_exec',
		'include',
		'require',
		'include_once',
		'require_once',
		'file_put_contents',
		'fopen',
		'fwrite',
		'unlink',
		'curl_exec',
		'curl_multi_exec',
		'base64_decode',
		'gzinflate',
		'gzuncompress',
		'gzdecode',
		'str_rot13',
		'ReflectionFunction',
		'ReflectionMethod',
		'$_GET',
		'$_POST',
		'$_REQUEST',
		'$_SERVER',
		'$_ENV',
		'GLOBALS',
		'phpinfo',
		'sleep',
		'usleep',
		'die',
		'exit',
	);

	/**
	 * Evaluate a mapping expression safely (replaces evalPhp).
	 *
	 * @param string $expression Expression after placeholder substitution.
	 * @return mixed|null
	 */
	public static function evaluate( $expression ) {
		$expression = trim( (string) $expression );
		if ( $expression === '' ) {
			return null;
		}

		if ( ! self::is_expression_safe( $expression ) ) {
			return null;
		}

		// Literal strings / numbers / booleans.
		if ( self::is_literal( $expression ) ) {
			return self::parse_literal( $expression );
		}

		// Simple function call: name(args).
		if ( preg_match( '/^([A-Za-z_][A-Za-z0-9_]*)\s*\((.*)\)\s*$/s', $expression, $matches ) ) {
			return self::invoke_allowed_function( $matches[1], $matches[2] );
		}

		// Concatenation with dot operator.
		if ( strpos( $expression, '.' ) !== false && ! preg_match( '/[()]/', $expression ) ) {
			return self::evaluate_concatenation( $expression );
		}

		// Ternary: cond ? a : b
		if ( preg_match( '/^(.+?)\s*\?\s*(.+?)\s*:\s*(.+)$/s', $expression, $ternary ) ) {
			$cond = self::evaluate( trim( $ternary[1] ) );
			return $cond ? self::evaluate( trim( $ternary[2] ) ) : self::evaluate( trim( $ternary[3] ) );
		}

		// Arithmetic without function calls.
		if ( preg_match( '/^[0-9+\-*\/()%.\s]+$/', $expression ) ) {
			return self::evaluate_arithmetic( $expression );
		}

		return null;
	}

	/**
	 * Evaluate a return-style expression (replaces eval('return '.$expr.';')).
	 *
	 * @param string $expression
	 * @return mixed|null
	 */
	public static function evaluate_return( $expression ) {
		$expression = trim( (string) $expression );
		if ( $expression === '' ) {
			return null;
		}

		// Strip leading "return" if present.
		$expression = preg_replace( '/^\s*return\s+/i', '', $expression );
		$expression = rtrim( $expression, ';' );

		return self::evaluate( $expression );
	}

	/**
	 * @param string $expression
	 * @return bool
	 */
	public static function is_expression_safe( $expression ) {
		$expression = (string) $expression;

		if ( preg_match( '/[`$]|->|::|\\\\/', $expression ) ) {
			return false;
		}

		foreach ( self::$blocked_tokens as $token ) {
			if ( stripos( $expression, $token ) !== false ) {
				return false;
			}
		}

		// Block preg_replace with /e modifier.
		if ( preg_match( '/preg_replace\s*\([^)]*\/e[\'"]/i', $expression ) ) {
			return false;
		}

		// Every function call must be on the allow-list.
		if ( preg_match_all( '/\b([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $expression, $calls ) ) {
			foreach ( $calls[1] as $fn ) {
				if ( ! in_array( strtolower( $fn ), self::$allowed_functions, true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $expression
	 * @return bool
	 */
	private static function is_literal( $expression ) {
		if ( is_numeric( $expression ) ) {
			return true;
		}
		if ( in_array( strtolower( $expression ), array( 'true', 'false', 'null' ), true ) ) {
			return true;
		}
		if ( ( str_starts_with( $expression, '"' ) && str_ends_with( $expression, '"' ) )
			|| ( str_starts_with( $expression, "'" ) && str_ends_with( $expression, "'" ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $expression
	 * @return mixed
	 */
	private static function parse_literal( $expression ) {
		if ( is_numeric( $expression ) ) {
			return strpos( $expression, '.' ) !== false ? (float) $expression : (int) $expression;
		}
		$lower = strtolower( $expression );
		if ( $lower === 'true' ) {
			return true;
		}
		if ( $lower === 'false' ) {
			return false;
		}
		if ( $lower === 'null' ) {
			return null;
		}
		if ( strlen( $expression ) >= 2 ) {
			$quote = $expression[0];
			if ( ( $quote === '"' || $quote === "'" ) && $expression[ strlen( $expression ) - 1 ] === $quote ) {
				return stripcslashes( substr( $expression, 1, -1 ) );
			}
		}
		return $expression;
	}

	/**
	 * @param string $fn_name
	 * @param string $args_string
	 * @return mixed|null
	 */
	private static function invoke_allowed_function( $fn_name, $args_string ) {
		$fn_name = strtolower( $fn_name );
		if ( ! in_array( $fn_name, self::$allowed_functions, true ) || ! function_exists( $fn_name ) ) {
			return null;
		}

		$args = self::parse_argument_list( $args_string );
		if ( null === $args ) {
			return null;
		}

		try {
			return call_user_func_array( $fn_name, $args );
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Parse a comma-separated argument list respecting quoted strings.
	 *
	 * @param string $args_string
	 * @return array|null
	 */
	private static function parse_argument_list( $args_string ) {
		$args_string = trim( $args_string );
		if ( $args_string === '' ) {
			return array();
		}

		$args   = array();
		$buffer = '';
		$depth  = 0;
		$quote  = null;
		$len    = strlen( $args_string );

		for ( $i = 0; $i < $len; $i++ ) {
			$ch = $args_string[ $i ];

			if ( null !== $quote ) {
				$buffer .= $ch;
				if ( $ch === $quote && ( $i === 0 || $args_string[ $i - 1 ] !== '\\' ) ) {
					$quote = null;
				}
				continue;
			}

			if ( $ch === '"' || $ch === "'" ) {
				$quote   = $ch;
				$buffer .= $ch;
				continue;
			}

			if ( $ch === '(' ) {
				++$depth;
				$buffer .= $ch;
				continue;
			}
			if ( $ch === ')' ) {
				--$depth;
				$buffer .= $ch;
				continue;
			}

			if ( $ch === ',' && 0 === $depth ) {
				$args[] = self::evaluate( trim( $buffer ) );
				$buffer = '';
				continue;
			}

			$buffer .= $ch;
		}

		if ( null !== $quote || $depth !== 0 ) {
			return null;
		}

		$args[] = self::evaluate( trim( $buffer ) );
		return $args;
	}

	/**
	 * @param string $expression
	 * @return string|null
	 */
	private static function evaluate_concatenation( $expression ) {
		$parts = preg_split( '/\s*\.\s*/', $expression );
		if ( ! is_array( $parts ) ) {
			return null;
		}

		$result = '';
		foreach ( $parts as $part ) {
			$val = self::evaluate( trim( $part ) );
			if ( null === $val ) {
				return null;
			}
			$result .= (string) $val;
		}
		return $result;
	}

	/**
	 * @param string $expression
	 * @return float|int|null
	 */
	private static function evaluate_arithmetic( $expression ) {
		if ( ! preg_match( '/^[0-9+\-*\/()%.\s]+$/', $expression ) ) {
			return null;
		}
		try {
			if ( class_exists( '\NXP\MathExecutor' ) ) {
				$executor = new \NXP\MathExecutor();
				return $executor->execute( $expression );
			}
		} catch ( \Throwable $e ) {
			return null;
		}
		return null;
	}
}
