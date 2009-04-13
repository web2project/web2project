<?php /* $Id$ $URL$ */
/**
 *    @package web2project
 *    @subpackage mail
 */

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *    This class encapsulates the PHP mail() function.
 *
 *    @version    1.0
 *    Example
 *    <code>
 *    include "libmail.php";
 *
 *    $m = new Mail; // create the mail
 *    $m->From( "leo@isp.com" );
 *    $m->To( "destination@somewhere.fr" );
 *    $m->Subject( "the subject of the mail" );
 *
 *    $message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
 *    $m->Body( $message);    // set the body
 *    $m->Cc( "someone@somewhere.fr");
 *    $m->Bcc( "someoneelse@somewhere.fr");
 *    $m->Priority(4) ;    // set the priority to Low
 *    $m->Attach( "/home/leo/toto.gif", "image/gif" ) ;    // attach a file of type image/gif
 *    $m->Send();    // send the mail
 *    echo "the mail below has been sent:<br><pre>", $m->Get(), "</pre>";
 *    </code>
 *    @author    Leo West - lwest@free.fr
 *    @author    Emiliano Gabrielli - emiliano.gabrielli@dearchitettura.com
 *    @author    Pedro Azevedo - pedroa@web2project.net
 */

require_once($AppUI->getLibraryClass('PHPMailer/class.phpmailer'));

class Mail extends PHPMailer {
	/**
	 *    list of To addresses
	 *    @var    array
	 */
	var $ato = array();
	/**
	 *    @var    array
	 */
	var $acc = array();
	/**
	 *    @var    array
	 */
	var $abcc = array();
	/**
	 *    paths of attached files
	 *    @var array
	 */

	/**
	 *    character set of message
	 *    @var string
	 */
	var $receipt = false;
	var $useRawAddress = true;
	var $defer;

	/**
	 *    Mail constructor
	 */
	function Mail() {
		$this->autoCheck(true);
		$this->defer = w2PgetConfig('mail_defer');
		$this->canEncode = function_exists('imap_8bit') && 'us-ascii' != $this->charset;
		$this->hasMbStr = function_exists('mb_substr');

		$this->Mailer = (w2PgetConfig('mail_transport', 'php') == 'smtp' ? 'smtp' : 'mail');
		$this->Port = w2PgetConfig('mail_port', '25');
		$this->Host = w2PgetConfig('mail_host', 'localhost');
		$this->Hostname = w2PgetConfig('mail_host', 'localhost');
		$this->SMTPAuth = w2PgetConfig('mail_auth', false);
		$this->SMTPSecure = w2PgetConfig('mail_secure', '');
		$this->SMTPDebug = w2PgetConfig('mail_debug', false);
		$this->Username = w2PgetConfig('mail_user');
		$this->Password = w2PgetConfig('mail_pass');
		$this->Timeout = w2PgetConfig('mail_timeout', 0);
		$this->CharSet = isset($GLOBALS['locale_char_set']) ? w2PcheckCharset(strtolower($GLOBALS['locale_char_set'])) : 'us-ascii';
		$this->Encoding = $this->Charset != 'us-ascii' ? '8bit' : '7bit';
		//The from clause is fixed for all emails so that the users do not reply to one another
		$this->From(w2PgetConfig('admin_username') . '@' . w2PgetConfig('site_domain'), w2PgetConfig('company_name'));
	}

	/**
	 *    activate or desactivate the email addresses validator
	 *
	 *    ex: autoCheck( TRUE ) turn the validator on
	 *    by default autoCheck feature is on
	 *
	 *    @param boolean    $bool set to TRUE to turn on the auto validation
	 *    @access public
	 */
	function autoCheck($bool) {
		$this->checkAddress = (bool)$bool;
		return true;
	}

	/**
	 *    Define the subject line of the email
	 *    @param string $subject any monoline string
	 */
	function Subject($subject, $charset = '') {
		$this->Subject = w2PgetConfig('email_prefix') . ' ' . $subject;
		return true;
	}

	/**
	 *    set the sender of the mail
	 *    @param string $from should be an email address
	 */
	function From($from, $fromname = '') {
		if (!is_string($from)) {
			return false;
		}
		$this->From = $from;
		$this->FromName = $fromname;
		if ($this->receipt) {
			$this->ConfirmReadingTo($from);
		}
		return true;
	}

	/**
	 *    set the Reply-to header
	 *    @param string $email should be an email address
	 */
	function ReplyTo($address) {
		if (!is_string($address)) {
			return false;
		}
		$this->AddReplyTo($address);
		if ($this->receipt) {
			$this->ConfirmReadingTo($address);
		}
		return true;
	}

	/**
	 *    add a receipt to the mail ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined)
	 *    when the receiver opens the message.
	 *    @warning this functionality is *not* a standard, thus only some mail clients are compliants.
	 */
	function Receipt() {
		$this->receipt = true;
		return true;
	}

	/**
	 *    set the mail recipient
	 *
	 *    The optional reset parameter is useful when looping through records to send individual mails.
	 *    This prevents the 'to' array being continually stacked with additional addresses.
	 *
	 *    @param string $to email address, accept both a single address or an array of addresses
	 *    @param boolean $reset resets the current array
	 */
	function To($to, $reset = false) {
		if (is_array($to)) {
			$this->ato = $to;
		} else {
			if ($this->useRawAddress) {
				if (preg_match("/^(.*)\<(.+)\>$/D", $to, $regs)) {
					$to = $regs[2];
				}
			}
			if ($reset) {
				unset($this->ato);
				$this->ato = array();
			}
			$this->ato[] = $to;
		}

		if ($this->checkAddress == true) {
			$this->CheckAdresses($this->ato);
		}
		
		foreach ($this->ato as $to_address) {
			if (strpos($to_address, '<') !== false) {
				preg_match('/^.*<([^@]+\@[a-z0-9\._-]+)>/i', $to_address, $matches);
				if (isset($matches[1])) {
					$to_address = $matches[1];
				}
			}
			$this->AddAddress($to_address);
		}		
		return true;
	}

	/**
	 *    Cc()
	 *    set the CC headers ( carbon copy )
	 *    $cc : email address(es), accept both array and string
	 */
	function Cc($cc) {
		if (is_array($cc)) {
			$this->acc = $cc;
		} else {
			$this->acc = explode(',', $cc);
		}

		if ($this->checkAddress == true) {
			$this->CheckAdresses($this->acc);
		}

		foreach ($this->acc as $cc_address) {
			if (strpos($cc_address, '<') !== false) {
				preg_match('/^.*<([^@]+\@[a-z0-9\._-]+)>/i', $cc_address, $matches);
				if (isset($matches[1])) {
					$cc_address = $matches[1];
				}
			}
			$this->AddCC($cc_address);
		}		
		
		return true;
	}

	/**
	 *    set the Bcc headers ( blank carbon copy ).
	 *    $bcc : email address(es), accept both array and string
	 */
	function Bcc($bcc) {
		if (is_array($bcc)) {
			$this->abcc = $bcc;
		} else {
			$this->abcc = explode(',', $bcc);
		}

		if ($this->checkAddress == true) {
			$this->CheckAdresses($this->abcc);
		}

		foreach ($this->abcc as $bcc_address) {
			if (strpos($bcc_address, '<') !== false) {
				preg_match('/^.*<([^@]+\@[a-z0-9\._-]+)>/i', $bcc_address, $matches);
				if (isset($matches[1])) {
					$bcc_address = $matches[1];
				}
			}
			$this->AddCC($bcc_address);
		}		
		
		return true;
	}

	/**
	 *        set the body (message) of the mail
	 *        define the charset if the message contains extended characters (accents)
	 *        default to us-ascii
	 *        $mail->Body( "m?l en fran?ais avec des accents", "iso-8859-1" );
	 */
	function Body($body, $charset = '') {
		$this->Body = w2PHTMLDecode($body);

		if (!empty($charset)) {
			@($this->charset = strtolower($charset));
			if ($this->charset != 'us-ascii') {
				$this->Encoding = '8bit';
			}
		}
	}

	/**
	 *        set the mail priority
	 *        $priority : integer taken between 1 (highest) and 5 ( lowest )
	 *        ex: $mail->Priority(1) ; => Highest
	 */
	function Priority($priority) {
		if ((!intval($priority)) || (intval($priority) < 1) || (intval($priority) > 5)) {
			return false;
		}

		$this->Priority = $priority;
		return true;
	}


	/**
	 *    Overload the Send method from PHPMailer to provide defered mails
	 *    @access public
	 */
	function Send() {
		if ($this->defer) {
			return $this->QueueMail();
		} else {
			return PHPMailer::Send();
		}
	}

	function getHostName() {
		// Grab the server address, return a hostname for it.
		if ($host = gethostbyaddr($_SERVER['SERVER_ADDR'])) {
			return $host;
		} else {
			return '[' . $_SERVER['SERVER_ADDR'] . ']';
		}
	}

	/**
	 * Queue mail to allow the queue manager to trigger
	 * the email transfer.
	 *
	 * @access private
	 */
	function QueueMail() {
		global $AppUI;

		require_once $AppUI->getSystemClass('event_queue');
		$ec = new EventQueue;
		$vars = get_object_vars($this);
		return $ec->add(array('Mail', 'SendQueuedMail'), $vars, 'libmail', true);
	}

	/**
	 * Dequeue the email and transfer it.  Called from the queue manager.
	 *
	 * @access private
	 */
	function SendQueuedMail($mod, $type, $originator, $owner, &$args) {
		extract($args);
		if ($this->transport == 'smtp') {
			$this->IsSMTP();
			return $this->Send();
		} else {
			$this->IsMail();
			return $this->Send();
		}
	}

	/**
	 *    Returns the whole e-mail , headers + message
	 *
	 *    can be used for displaying the message in plain text or logging it
	 *
	 *    @return string
	 */
	function Get() {
		$mail = $this->CreateHeader();
		$mail .= $this->CreateBody();
		return $mail;
	}

	/**
	 *    check an email address validity
	 *    @access public
	 *    @param string $address : email address to check
	 *    @return TRUE if email adress is ok
	 */
	function ValidEmail($address) {
		if (preg_match('/^(.*)\<(.+)\>$/D', $address, $regs)) {
			$address = $regs[2];
		}
		return (bool)preg_match('/^[^@ ]+@([-a-zA-Z0-9..]+)$/D', $address);
	}

	/**
	 *    check validity of email addresses
	 *    @param    array $aad -
	 *    @return if unvalid, output an error message and exit, this may -should- be customized
	 */

	function CheckAdresses($aad) {
		foreach ($aad as $ad) {
			if (!$this->ValidEmail($ad)) {
				echo 'Class Mail, method Mail : invalid address ' . $ad;
				exit;
			}
		}
		return true;
	}
	/**
	 * alias for the mispelled CheckAdresses
	 */
	function CheckAddresses($aad) {
		return $this->CheckAdresses($aad);
	}


} // class Mail
?>