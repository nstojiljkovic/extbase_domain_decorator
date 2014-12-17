<?php

namespace EssentialDots\ExtbaseDomainDecorator\Transaction;

use TYPO3\CMS\Core\SingletonInterface;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Essential Dots d.o.o. Belgrade
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class Service
 * @package EssentialDots\ExtbaseDomainDecorator\Reflection
 *
 * Limited support for transactions:
 * * Currently works only on TYPO3_DB
 * * Make sure you use InnoDB engine
 * * Distributed transactions are not implemented obviously
 */
class TransactionManager implements SingletonInterface {

	/**
	 * @var int
	 */
	protected $transactionsInProcessCount = 0;

	/**
	 * @return void
	 */
	public function startTransaction() {
		if ($this->transactionsInProcessCount++ === 0) {
			if (!$this->getDatabase()->sql_query('START TRANSACTION;')) {
				throw new \RuntimeException('Could not start transaction, error: ' . $this->getDatabase()->sql_errno() . ', ' . $this->getDatabase()->sql_error());
			}
		}
	}

	/**
	 * @return void
	 */
	public function commitTransaction() {
		if (--$this->transactionsInProcessCount === 0) {
			if (!$this->getDatabase()->sql_query('COMMIT;')) {
				throw new \RuntimeException('Could not commit, error: ' . $this->getDatabase()->sql_errno() . ', ' . $this->getDatabase()->sql_error());
			}
		}
	}

	/**
	 * @return void
	 */
	public function rollbackTransaction() {
		if (--$this->transactionsInProcessCount === 0) {
			if (!$this->getDatabase()->sql_query('ROLLBACK;')) {
				throw new \RuntimeException('Could not rollback transaction, error: ' . $this->getDatabase()->sql_errno() . ', ' . $this->getDatabase()->sql_error());
			}
		}
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Destructor:
	 * Releases lock automatically when instance is destroyed.
	 *
	 * @return	void
	 */
	public function __destruct() {
		if ($this->transactionsInProcessCount > 0) {
			error_log('Something strange happened, there are still transactions in process which have not been committed or rolled back. Rolling back now...');
			if (!$this->getDatabase()->sql_query('ROLLBACK;')) {
				throw new \RuntimeException('Could not rollback transaction, error: ' . $this->getDatabase()->sql_errno() . ', ' . $this->getDatabase()->sql_error());
			}
		}
	}
}