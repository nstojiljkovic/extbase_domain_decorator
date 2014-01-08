<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence\Generic;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Nikola Stojiljkovic, Essential Dots d.o.o. Belgrade
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class BackendFactory implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface
	 */
	protected $backend;

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var array<\TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface>
	 */
	protected $backends = array();

	/**
	 * @var \\EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 * @inject
	 */
	protected $decoratorManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend
	 */
	public function injectBackend(\TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend) {
		$this->backend = $backend;
		$this->backends[get_class($backend)] = $backend;
	}

	/**
	 * @param string $objectType
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface
	 */
	public function getBackendForObjectType($objectType = '') {
		$backend = $this->backend;
		$backendClassName = $this->decoratorManager->getBackendClassNameForObjectType($objectType);

		if ($backendClassName) {
			if (!$this->backends[$backendClassName]) {
				$this->backends[$backendClassName] = $this->objectManager->get($backendClassName);
				$this->backends[$backendClassName]->setPersistenceManager($this->persistenceManager);
			}

			$backend = $this->backends[$backendClassName] ? $this->backends[$backendClassName] : $backend;
		}

		return $backend;
	}

	/**
	 * @return array
	 */
	public function getInitializedBackendObjects() {
		$defaultBackendClassName = get_class($this->backend);

		if (!$this->backends[$defaultBackendClassName]) {
			$this->backends[$defaultBackendClassName] = $this->backend;
		}

		return $this->backends;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
	 */
	public function setPersistenceManager(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;

		foreach ($this->backends as $backend) { /* @var $backend \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface */
			$backend->setPersistenceManager($this->persistenceManager);
		}
	}
}