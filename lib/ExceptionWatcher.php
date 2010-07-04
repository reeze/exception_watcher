<?php

define('EXCEPTION_WATCHER_LIB_DIR', dirname(__FILE__));

require_once EXCEPTION_WATCHER_LIB_DIR . "/ExceptionNotifier.php";


/**
 * Exception Watcher
 * 
 * @author reeze<reeze.xia@gmail.com>
 */
class ExceptionWatcher
{
	/**
	 * Singleton instance
	 */
	private static $instance;

	/**
	 * Save the previous exception handler
	 */
	private $orig_handler;

	/**
	 * initialized
	 */
	private $initialized = false;

	private $options = array();

	private $defaultOptions = array(
		// used for application save extra information like user id request url etc
		// this callback should return an array
		'extraParamGetterCallback' => '',
		'saveType' => 'sqlite',
		'sqliteOptions' => array(
			'dbPath' => './dbfile.sqlite3',
		),
	);

	/**
	 * catched exception
	 */
	public $exception = null;

	private function __construct()
	{
	}

	public function getInstance() {
		if(!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * register the exception error handler
	 */
	public function startWatching($options=array()) 
	{
		if(!$this->initialized) {
			$this->options = $options;
			$this->orig_handler = set_exception_handler(array($this, "exceptionHandler"));
			$this->notifier = new ExceptionNotifier();

			set_error_handler(array($this, "errorHandler"));

			$this->initialized = true;
		}
	}

	public function exceptionHandler($exception)
	{
		restore_error_handler();
		restore_exception_handler();

		$this->exception = $exception;

		try {
			$this->notifier->notify($this);
		}
		catch(Exception $e) {
			// if debug exception watcher
			if($this->debug) {
				echo "ExceptionWatcher have problem\n";	
			}

			echo $e->getMessage();
			// we always write our own exceptions to log file
		}

		// call the orig_exception_handler, we just want to do exception 
		// handling silently
		if($this->orig_handler) {
			call_user_func($this->orig_handler, $exception);
		}
	}

	public function errorHandler() {
		var_dump(func_get_args());	
	}

	/* get the options */
	public function __get($name) {
		if(isset($this->options[$name])) {
			return $this->options[$name];
		}
		else {
			return isset($this->defaultOptions[$name]) ? $this->defaultOptions[$name] : null;
		}
	}
}
