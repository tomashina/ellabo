<?php
namespace DB;

class ConnectionException extends \RuntimeException {
	public function __construct($code = 0) {
		parent::__construct('Database connection unavailable.', (int)$code);
	}
}
