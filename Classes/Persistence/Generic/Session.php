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

class Session extends \TYPO3\CMS\Extbase\Persistence\Generic\Session {

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected $decoratorManager;

	/**
	 * Injects the decorator manager
	 *
	 * @param \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager $decoratorManager
	 * @return void
	 */
	public function injectDecoratorManager(\EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager $decoratorManager) {
		$this->decoratorManager = $decoratorManager;
	}

	/**
	 * Injects a Reflection Service instance
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
		$this->decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
	}

	/**
	 * Checks whether the given identifier is known to the identity map
	 *
	 * @param string $identifier
	 * @param string $className
	 * @return boolean
	 */
	public function hasIdentifier($identifier, $className) {
		$className = $this->decoratorManager->getDecoratedClass($className);

		return isset($this->identifierMap[strtolower($className)][$identifier]);
	}

	/**
	 * Returns the object for the given identifier
	 *
	 * @param string $identifier
	 * @param string $className
	 * @return object
	 * @api
	 */
	public function getObjectByIdentifier($identifier, $className) {
		$className = $this->decoratorManager->getDecoratedClass($className);

		return $this->identifierMap[strtolower($className)][$identifier];
	}

	/**
	 * Register an identifier for an object
	 *
	 * @param object $object
	 * @param string $identifier
	 * @api
	 */
	public function registerObject($object, $identifier) {
		$this->objectMap[$object] = $identifier;
		$className = $this->decoratorManager->getDecoratedClass(get_class($object));
		$this->identifierMap[strtolower($className)][$identifier] = $object;
	}

	/**
	 * Unregister an object
	 *
	 * @param object $object
	 * @return void
	 */
	public function unregisterObject($object) {
		$className = $this->decoratorManager->getDecoratedClass(get_class($object));
		unset($this->identifierMap[strtolower($className)][$this->objectMap[$object]]);
		$this->objectMap->detach($object);
	}
}
?>