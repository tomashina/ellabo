<?php
namespace Mail;

class Smtp {
	public $smtp_hostname;
	public $smtp_username;
	public $smtp_password;
	public $smtp_port = 25;
	public $smtp_timeout = 5;
	public $verp = false;

	private function getEhloHostname() {
		return 'ellabo.hr';
	}

	private function readReply($handle) {
		$reply = '';

		while ($line = fgets($handle, 515)) {
			$reply .= $line;

			if (isset($line[3]) && substr($line, 3, 1) == ' ') {
				break;
			}
		}

		return $reply;
	}

	public function send() {
		if (is_array($this->to)) {
			$to = implode(',', $this->to);
		} else {
			$to = $this->to;
		}

		$boundary = '----=_NextPart_' . md5(time());

		$header  = 'MIME-Version: 1.0' . PHP_EOL;
		$header .= 'To: <' . $to . '>' . PHP_EOL;
		$header .= 'Subject: =?UTF-8?B?' . base64_encode($this->subject) . '?=' . PHP_EOL;
		$header .= 'Date: ' . date('D, d M Y H:i:s O') . PHP_EOL;
		$header .= 'From: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . PHP_EOL;

		if (!$this->reply_to) {
			$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->sender) . '?= <' . $this->from . '>' . PHP_EOL;
		} else {
			$header .= 'Reply-To: =?UTF-8?B?' . base64_encode($this->reply_to) . '?= <' . $this->reply_to . '>' . PHP_EOL;
		}

		$header .= 'Return-Path: ' . $this->from . PHP_EOL;
		$header .= 'X-Mailer: PHP/' . phpversion() . PHP_EOL;
		$header .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . PHP_EOL . PHP_EOL;

		if (!$this->html) {
			$message  = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
			$message .= $this->text . PHP_EOL;
		} else {
			$message  = '--' . $boundary . PHP_EOL;
			$message .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '_alt"' . PHP_EOL . PHP_EOL;
			$message .= '--' . $boundary . '_alt' . PHP_EOL;
			$message .= 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;

			if ($this->text) {
				$message .= $this->text . PHP_EOL;
			} else {
				$message .= 'This is a HTML email and your email client software does not support HTML email!' . PHP_EOL;
			}

			$message .= '--' . $boundary . '_alt' . PHP_EOL;
			$message .= 'Content-Type: text/html; charset="utf-8"' . PHP_EOL;
			$message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
			$message .= $this->html . PHP_EOL;
			$message .= '--' . $boundary . '_alt--' . PHP_EOL;
		}

		foreach ($this->attachments as $attachment) {
			if (file_exists($attachment)) {
				$handle = fopen($attachment, 'r');
				$content = fread($handle, filesize($attachment));
				fclose($handle);

				$message .= '--' . $boundary . PHP_EOL;
				$message .= 'Content-Type: application/octet-stream; name="' . basename($attachment) . '"' . PHP_EOL;
				$message .= 'Content-Transfer-Encoding: base64' . PHP_EOL;
				$message .= 'Content-Disposition: attachment; filename="' . basename($attachment) . '"' . PHP_EOL;
				$message .= 'Content-ID: <' . urlencode(basename($attachment)) . '>' . PHP_EOL;
				$message .= 'X-Attachment-Id: ' . urlencode(basename($attachment)) . PHP_EOL . PHP_EOL;
				$message .= chunk_split(base64_encode($content));
			}
		}

		$message .= '--' . $boundary . '--' . PHP_EOL;

		if (substr($this->smtp_hostname, 0, 3) == 'tls') {
			$hostname = substr($this->smtp_hostname, 6);
		} else {
			$hostname = $this->smtp_hostname;
		}

		$handle = fsockopen($hostname, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

		if (!$handle) {
			throw new \Exception('Error: ' . $errstr . ' (' . $errno . ')');
		}

		if (substr(PHP_OS, 0, 3) != 'WIN') {
			socket_set_timeout($handle, $this->smtp_timeout, 0);
		}

		// Read SMTP greeting
		$this->readReply($handle);

		// Initial EHLO
		fputs($handle, 'EHLO ' . $this->getEhloHostname() . "\r\n");

		$reply = '';

		while ($line = fgets($handle, 515)) {
			$reply .= $line;

			// Some SMTP servers respond with 220 before 250. Ignore that 220 response.
			if (substr($reply, 0, 3) == 220 && isset($line[3]) && substr($line, 3, 1) == ' ') {
				$reply = '';
				continue;
			} else if (isset($line[3]) && substr($line, 3, 1) == ' ') {
				break;
			}
		}

		if (substr($reply, 0, 3) != 250) {
			throw new \Exception('Error: EHLO not accepted from server! Reply: ' . $reply);
		}

		// STARTTLS for tls:// hostname
		if (substr($this->smtp_hostname, 0, 3) == 'tls') {
			fputs($handle, 'STARTTLS' . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 220) {
				throw new \Exception('Error: STARTTLS not accepted from server! Reply: ' . $reply);
			}

		$crypto_method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
    $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
}

if (!stream_socket_enable_crypto($handle, true, $crypto_method)) {
    throw new \Exception('Error: TLS encryption failed!');
}
		}

		if (!empty($this->smtp_username) && !empty($this->smtp_password)) {
			// EHLO again after STARTTLS
			fputs($handle, 'EHLO ' . $this->getEhloHostname() . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 250) {
				throw new \Exception('Error: EHLO not accepted from server! Reply: ' . $reply);
			}

			fputs($handle, 'AUTH LOGIN' . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 334) {
				throw new \Exception('Error: AUTH LOGIN not accepted from server! Reply: ' . $reply);
			}

			fputs($handle, base64_encode($this->smtp_username) . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 334) {
				throw new \Exception('Error: Username not accepted from server! Reply: ' . $reply);
			}

			fputs($handle, base64_encode($this->smtp_password) . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 235) {
				throw new \Exception('Error: Password not accepted from server! Reply: ' . $reply);
			}
		} else {
			fputs($handle, 'HELO ' . $this->getEhloHostname() . "\r\n");

			$reply = $this->readReply($handle);

			if (substr($reply, 0, 3) != 250) {
				throw new \Exception('Error: HELO not accepted from server! Reply: ' . $reply);
			}
		}

		if ($this->verp) {
			fputs($handle, 'MAIL FROM: <' . $this->from . '>XVERP' . "\r\n");
		} else {
			fputs($handle, 'MAIL FROM: <' . $this->from . '>' . "\r\n");
		}

		$reply = $this->readReply($handle);

		if (substr($reply, 0, 3) != 250) {
			throw new \Exception('Error: MAIL FROM not accepted from server! Reply: ' . $reply);
		}

		if (!is_array($this->to)) {
			fputs($handle, 'RCPT TO: <' . $this->to . '>' . "\r\n");

			$reply = $this->readReply($handle);

			if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
				throw new \Exception('Error: RCPT TO not accepted from server! Reply: ' . $reply);
			}
		} else {
			foreach ($this->to as $recipient) {
				fputs($handle, 'RCPT TO: <' . $recipient . '>' . "\r\n");

				$reply = $this->readReply($handle);

				if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
					throw new \Exception('Error: RCPT TO not accepted from server! Reply: ' . $reply);
				}
			}
		}

		fputs($handle, 'DATA' . "\r\n");

		$reply = $this->readReply($handle);

		if (substr($reply, 0, 3) != 354) {
			throw new \Exception('Error: DATA not accepted from server! Reply: ' . $reply);
		}

		// According to RFC 821 we should not send more than 1000 characters including CRLF
		$message = str_replace("\r\n", "\n", $header . $message);
		$message = str_replace("\r", "\n", $message);

		$lines = explode("\n", $message);

		foreach ($lines as $line) {
			$results = str_split($line, 998);

			foreach ($results as $result) {
				if (substr(PHP_OS, 0, 3) != 'WIN') {
					fputs($handle, $result . "\r\n");
				} else {
					fputs($handle, str_replace("\n", "\r\n", $result) . "\r\n");
				}
			}
		}

		fputs($handle, '.' . "\r\n");

		$reply = $this->readReply($handle);

		if (substr($reply, 0, 3) != 250) {
			throw new \Exception('Error: DATA not accepted from server! Reply: ' . $reply);
		}

		fputs($handle, 'QUIT' . "\r\n");

		$reply = $this->readReply($handle);

		if (substr($reply, 0, 3) != 221) {
			throw new \Exception('Error: QUIT not accepted from server! Reply: ' . $reply);
		}

		fclose($handle);
	}
}