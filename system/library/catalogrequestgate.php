<?php
class CatalogRequestGateException extends RuntimeException {}

class CatalogRequestGate {
	private $handle;

	private function __construct($handle) {
		$this->handle = $handle;
	}

	/**
	 * Admit catalog requests before they connect to MySQL. Each slot is held
	 * until session writes and both database clients have closed at shutdown.
	 * Lock files may remain on disk; only an active flock occupies a slot.
	 */
	public static function acquire($storage, $identity, $limit = 12) {
		if (!is_int($limit) || $limit < 1) {
			throw new CatalogRequestGateException('Invalid catalog request limit.');
		}

		// Do not let filesystem warnings corrupt the structured busy response.
		set_error_handler(function() { return true; });
		try {
			$directory = rtrim($storage, '/') . '/catalog-db-' . hash('sha256', $identity);
			if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
				throw new CatalogRequestGateException('Catalog request lock storage unavailable.');
			}

			for ($slot = 0; $slot < $limit; $slot++) {
				$handle = fopen($directory . '/' . $slot . '.lock', 'c');
				if (!$handle) {
					throw new CatalogRequestGateException('Catalog request lock storage unavailable.');
				}

				if (flock($handle, LOCK_EX | LOCK_NB)) {
					return new self($handle);
				}
				fclose($handle);
			}

			throw new CatalogRequestGateException('Catalog request limit reached.');
		} finally {
			restore_error_handler();
		}
	}

	public function release() {
		if (is_resource($this->handle)) {
			flock($this->handle, LOCK_UN);
			fclose($this->handle);
			$this->handle = null;
		}
	}

	public function __destruct() {
		$this->release();
	}
}
