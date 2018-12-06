<?php
/**
 * @since 2/21/08
 * @package MiddMedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
/**
 * An ErrorPrinter for custom error pages
 *
 * @since 2/21/08
 * @package MiddMedia
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
class MiddMediaErrorPrinter {

	/**
 	 * @var object  $instance;
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;
	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the
	 * ClassName::instance() method.
	 *
	 * @return object
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance))
			self::$instance = new MiddMediaErrorPrinter;

		return self::$instance;
	}

	/**
	 * @var array $userAgentFilters;
	 * @access private
	 * @since 2/26/08
	 */
	private $userAgentFilters;

	/**
	 * Constructor
	 *
	 * @return void
	 * @access private
	 * @since 2/26/08
	 */
	private function __construct () {
		$this->userAgentFilters = array();
	}

	/**
	 * Add a user agent string and an array of matching codes. If the user agent
	 * matches the string and the code or exception class is in the list, the exception
	 * will not be logged. This can be used to prevent misbehaving bots and web
	 * crawlers from filling the logs with repeated invalid requests.
	 *
	 * @param string $userAgent
	 * @param optional array $codesOrExceptionClasses If empty, no matches to the user agent will be logged.
	 * @return void
	 * @access public
	 * @since 2/26/08
	 */
	public function addUserAgentFilter ($userAgent, $codesOrExceptionClasses = array()) {
		$userAgent = trim($userAgent);
		ArgumentValidator::validate($userAgent, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($codesOrExceptionClasses,
			ArrayValidatorRuleWithRule::getRule(OrValidatorRule::getRule(
				NonzeroLengthStringValidatorRule::getRule(),
				IntegerValidatorRule::getRule())));

		$this->userAgentFilters[$userAgent] = $codesOrExceptionClasses;
	}

	/**
	 * Answer true if the Exception should be logged
	 *
	 * @param object Exception $e
	 * @param int $code
	 * @return boolean
	 * @access private
	 * @since 2/26/08
	 */
	private function shouldLogException (Exception $e, $code) {
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$userAgent = trim($_SERVER['HTTP_USER_AGENT']);
			if (array_key_exists($userAgent, $this->userAgentFilters)) {
				if (!count($this->userAgentFilters[$userAgent]))
					return false;
				if (in_array($code, $this->userAgentFilters[$userAgent]))
					return false;
				if (in_array(get_class($e), $this->userAgentFilters[$userAgent]))
					return false;
			}
		}

		return true;
	}

	/**
	 * Handle an Exception
	 *
	 * @param object Exception $e
	 * @parma int $code The HTTP status code to use.
	 * @return void
	 * @access public
	 * @since 2/26/08
	 * @static
	 */
	public static function handleException (Exception $e, $code) {
		$printer = self::instance();
		$printer->handleAnException($e, $code);
	}

	/**
	 * Handle an Exception
	 *
	 * @param object Exception $e
	 * @parma int $code The HTTP status code to use.
	 * @return void
	 * @access public
	 * @since 2/26/08
	 */
	public function handleAnException (Exception $e, $code) {
		ArgumentValidator::validate($code, IntegerValidatorRule::getRule());
		if (!headers_sent())
			header('HTTP/1.1 '.$code.' '.self::getCodeString($code));
		$this->printException($e, $code);
		if ($this->shouldLogException($e, $code))
			HarmoniErrorHandler::logException($e);
	}


	/**
	 * Print out a custom error page for an exception with the HTTP status code
	 * specified
	 *
	 * @param object Exception $e
	 * @param int $code
	 * @return void
	 * @access private
	 * @since 2/21/08
	 */
	private function printException (Exception $e, $code) {
		// Debugging mode for development, rethrow the exception
		if (defined('DISPLAY_ERROR_BACKTRACE') && DISPLAY_ERROR_BACKTRACE) {
			throw $e;
		}

		// Normal production case
		else {
			$message = HtmlString::getSafeHtml($e->getMessage());
			$codeString = self::getCodeString($code);
			$errorString = _('Error');
			if ($this->shouldLogException($e, $code))
				$logMessage = _('This error has been logged.');
			else
				$logMessage = '';
			print <<< END
<html>
	<head>
		<title>$code $codeString</title>
		<style>
			body {
				background-color: #FFF8C6;
				font-family: Verdana, sans-serif;
			}

			.header {
				height: 65px;
				border-bottom: 1px dotted #333;
			}
			.middmedia_name {
				font-family: Tahoma, sans-serif;
				font-variant: small-caps;
				font-weight: bold;
				font-size: 60px;
				color: #333333;

				float: left;
			}

			.error {
				font-size: 20px;
				font-weight: bold;
				float: left;
				margin-top: 40px;
				margin-left: 20px;
			}

			blockquote {
 				margin-bottom: 50px;
				clear: both;
			}
		</style>
	</head>
	<body>
		<div class='header'>
			<div class='middmedia_name'>MiddMedia</div>
			<div class='error'>$errorString</div>
		</div>
		<blockquote>
			<h1>$codeString</h1>
			<p>$message</p>
		</blockquote>
		<p>$logMessage</p>
	</body>
</html>
END;
		}
	}

	/**
	 * Answer a string that matches the HTTP error code given.
	 *
	 * @param int $code
	 * @return string
	 * @access public
	 * @since 2/21/08
	 * @static
	 */
	public static function getCodeString ($code) {
		switch ($code) {
			case 400:
				return _('Bad Request');
			case 401:
				return _('Unauthorized');
			case 402:
				return _('Payment Required');
			case 403:
				return _('Forbidden');
			case 404:
				return _('Not Found');
			case 405:
				return _('Method Not Allowed');
			case 406:
				return _('Not Acceptable');
			case 407:
				return _('Proxy Authentication Required');
			case 408:
				return _('Request Timeout');
			case 409:
				return _('Conflict');
			case 410:
				return _('Gone');
			case 411:
				return _('Length Required');
			case 412:
				return _('Precondition Failed');
			case 413:
				return _('Request Entity Too Large');
			case 414:
				return _('Request-URI Too Long');
			case 415:
				return _('Unsupported Media Type');
			case 416:
				return _('Requested Range Not Satisfiable');
			case 417:
				return _('Expectation Failed');
			case ($code > 400 && $code < 500):
				return _('Client Error');

			case 500:
				return _('Internal Server Error');
			case 501:
				return _('Not Implemented');
			case 502:
				return _('Bad Gateway');
			case 503:
				return _('Service Unavailable');
			case 505:
				return _('Gateway Timeout');
			case 505:
				return _('HTTP Version Not Supported');

			case ($code > 500 && $code < 600):
				return _('Server Error');

			default:
				return _('Error');
		}
	}

}
