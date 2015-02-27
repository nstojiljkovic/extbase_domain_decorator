<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence;

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
 * Class AbstractRepository
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Persistence
 */
abstract class AbstractRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository
	 */
	// @codingStandardsIgnoreStart
	private $_decoratedObject;
	// @codingStandardsIgnoreEnd

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 * @inject
	 */
	protected $decoratorManager;

	/**
	 * Constructs a new Repository
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 */
	public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		parent::__construct($objectManager);

		if (!class_exists($this->objectType)) {
			/* @var $decoratorManager \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager */
			$decoratorManager = $objectManager->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
			$this->objectType = preg_replace(array('/_Repository_(?!.*_Repository_)/', '/Repository$/'), array('_Model_', ''), $decoratorManager->getDecoratedClass($this->getRepositoryClassName()));
		}
	}

	/**
	 * @param string $name
	 * @param string $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		try {
			$result = parent::__call($name, $arguments);
		} catch (\TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException $exception) {
			$result = call_user_func_array(array($this->_decoratedObject, $name), $arguments);
		}
		return $result;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->_decoratedObject->$name = $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return $this->_decoratedObject->$name;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		return isset($this->_decoratedObject->$name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name) {
		unset($this->_decoratedObject->$name);
	}

	/**
	 * This is the magic __wakeup() method. It's invoked by the unserialize statement in the reconstitution process
	 * of the object. If you want to implement your own __wakeup() method in your Domain Object you have to call
	 * parent::__wakeup() first!
	 *
	 * @return void
	 */
	public function __wakeup() {
		if ($this->_decoratedObject) {
			$this->_decoratedObject->__wakeup();
		}
	}

	/**
	 * @param \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository $decoratedObject
	 */
	public function setDecoratedObject($decoratedObject) {
		if ($this->_decoratedObject != $decoratedObject && $this->_decoratedObject) {
			$this->_decoratedObject->setDecoratedObject($decoratedObject);
		} else {
			$this->_decoratedObject = $decoratedObject;
		}
	}

	/**
	 * Adds an object to this repository
	 *
	 * @param object $object The object to add
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 * @return void
	 * @api
	 */
	public function add($object) {
		if ($object === NULL) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException('The object given to add() was not of the type (' . $this->objectType . ') this repository manages.', 1248363335);
		}

		$decoratedClass = $this->decoratorManager->getDecoratedClass(get_class($object));

		if (!is_subclass_of($decoratedClass, $this->objectType) && !is_subclass_of($this->objectType, $decoratedClass) && $decoratedClass != ltrim($this->objectType, '\\')) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException('The object given to add() was not of the type (' . $this->objectType . ') this repository manages.', 1248363335);
		}

		$this->persistenceManager->add($object);
	}

	/**
	 * Removes an object from this repository.
	 *
	 * @param object $object The object to remove
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 * @return void
	 * @api
	 */
	public function remove($object) {
		if ($object === NULL) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException('The object given to remove() was not of the type (' . $this->objectType . ') this repository manages.', 1248363335);
		}

		$decoratedClass = $this->decoratorManager->getDecoratedClass(get_class($object));

		if (!is_subclass_of($decoratedClass, $this->objectType) && !is_subclass_of($this->objectType, $decoratedClass) && $decoratedClass != ltrim($this->objectType, '\\')) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException('The object given to remove() was not of the type (' . $this->objectType . ') this repository manages.', 1248363335);
		}

		$this->persistenceManager->remove($object);
	}

	/**
	 * Replaces an existing object with the same identifier by the given object
	 *
	 * @param object $modifiedObject
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
	 */
	public function update($modifiedObject) {
		if ($modifiedObject === NULL) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException(
				'The modified object given to update() was not of the type (' . $this->objectType . ') this repository manages.',
				1249479625);
		}

		$decoratedClass = $this->decoratorManager->getDecoratedClass(get_class($modifiedObject));

		if (!is_subclass_of($decoratedClass, $this->objectType) && !is_subclass_of($this->objectType, $decoratedClass) && $decoratedClass != ltrim($this->objectType, '\\')) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException(
				'The modified object given to update() was not of the type (' . $this->objectType . ') this repository manages.',
				1249479625);
		}

		$this->persistenceManager->update($modifiedObject);
	}
}