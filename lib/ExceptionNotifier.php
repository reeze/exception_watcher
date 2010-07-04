<?php
error_reporting(E_ALL);

/**
 * Notify the exception
 */
class ExceptionNotifier 
{
	protected $watcher = null;

	public function notify($watcher) {
		$this->watcher = $watcher;
		$exception = $watcher->exception;

		$message = $exception->getMessage();
		$trace = $exception->getTraceAsString();

		if($this->needRealNotify()) {
			// now we can send the email to user
			// TODO use exception class to certy email
			// Exception => cc to all@gmail.com
			// DbConnectionException => db@gmail.com
			$result = $this->sendMail($watcher->notification_recipient, $watcher->notification_cc, "[Exception.]" . get_class($exception), $message . $trace, array('reeze.xia@gmail.com' => "reeze.xia"));
		}
	}

	public function sendMail($to, $cc, $subject, $body, $from=null, $type='text/html') {
		// maybe the application have already included the swift library
		if(!class_exists('Swift')) {
			require_once EXCEPTION_WATCHER_LIB_DIR . '/../vendor/Swift-4.0.4/swift_required.php';
		}

		$watcher = $this->watcher;

		$host = $watcher->smtp_host;
		$port = $watcher->smtp_port ? $watcher->smtp_port : 25;
		$username = $watcher->smtp_username;
		$password = $watcher->smtp_password;

		$transport = Swift_SmtpTransport::newInstance($host, $port)
			->setUsername($username)
			->setPassword($password);
			
		$mailer = Swift_Mailer::newInstance($transport);
			
		$message = Swift_Message::newInstance($subject)
			->setTo($to)
			->setBody($body)
			->setContentType($type);
			
		$message->setCharset('utf-8');
			
		if($from) {
			$message->setFrom($from);
		}

		if(is_array($cc) && !empty($cc)) {
			$message->setCc($cc);
		}

		
		return $mailer->send($message);
	}

	public function needRealNotify() {
		return true;	
	}
}
