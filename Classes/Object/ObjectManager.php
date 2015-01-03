<?php

namespace EssentialDots\ExtbaseDomainDecorator\Object;

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
 * Class ObjectManager
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Object
 */
class ObjectManager extends \TYPO3\CMS\Extbase\Object\ObjectManager {

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected $decoratorManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected $lastDecoratorMapping;

	/**
	 * Constructs a new Object Manager
	 */
	public function __construct() {
		parent::__construct();
		$this->decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
		$this->lastDecoratorMapping = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Returns a fresh or existing instance of the object specified by $objectName.
	 *
	 * Important:
	 *
	 * If possible, instances of Prototype objects should always be created with the
	 * Object Manager's create() method and Singleton objects should rather be
	 * injected by some type of Dependency Injection.
	 *
	 * @param string $objectName The name of the object to return an instance of
	 * @return object The object instance
	 * @api
	 */
	public function get($objectName) {
		$arguments = func_get_args();

		$instance = call_user_func_array(array($this, 'parent::get'), $arguments);

		$instance = $this->decorateObject($instance, $objectName);

		return $instance;
	}

	/**
	 * Creates a fresh instance of the object specified by $objectName.
	 *
	 * This factory method can only create objects of the scope prototype.
	 * Singleton objects must be either injected by some type of Dependency Injection or
	 * if that is not possible, be retrieved by the get() method of the
	 * Object Manager
	 *
	 * @param string $objectName The name of the object to create
	 * @return object The new object instance
	 * @api
	 */
	public function create($objectName) {
		$arguments = func_get_args();

		$instance = call_user_func_array(array($this, 'parent::create'), $arguments);

		$instance = $this->decorateObject($instance, $objectName);

		return $instance;
	}

	/**
	 * @param $instance
	 * @param $objectName
	 * @return \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository|object
	 * @throws \Exception
	 */
	public function decorateObject($instance, $objectName) {
		$decorators = $this->decoratorManager->getDecoratorsForClassName($objectName);

		if (count($decorators)) {
			foreach ($decorators as $decorator) {
				if ($instance instanceof \EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity) {
					$delimiter = strpos(get_class($instance), '_') !== FALSE ? '_' : '\\';
					$decoratedRepository = str_replace($delimiter . 'Model' . $delimiter, $delimiter . 'Repository' . $delimiter, get_class($instance)) . 'Repository';
					$delimiter = strpos($decorator, '_') !== FALSE ? '_' : '\\';
					$decoraterRepository = str_replace($delimiter . 'Model' . $delimiter, $delimiter . 'Repository' . $delimiter, $decorator) . 'Repository';

					if (class_exists($decoratedRepository) && !class_exists($decoraterRepository)) {
						class_alias($decoratedRepository, $decoraterRepository);
					}

					$instance = parent::get($decorator, $instance);
				} elseif ($instance instanceof \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository) {
					$newInstance = parent::get($decorator);
					/* @var $instance \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository */
					$instance->setDecoratedObject($newInstance);
				} else {
					throw new \Exception(
						'Decorator ' . $decorator . ' defined on a class ' . $objectName .
						' which is not a subclass of EssentialDots\\ExtbaseDomainDecorator\\DomainObject\\AbstractEntity nor EssentialDots\\ExtbaseDomainDecorator\\Persistence\\AbstractRepository.');
				}
			}
		}

		if ($instance instanceof \EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity) {
			$this->registerLastDecorator($instance);
		}

		return $instance;
	}

	/**
	 * @param \EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity $instance
	 */
	protected function registerLastDecorator(\EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity $instance) {
		$decoratedObject = $instance->getDecoratedObject();
		while (!is_null($decoratedObject)) {
			$this->lastDecoratorMapping[$decoratedObject] = $instance;
			$decoratedObject = $decoratedObject->getDecoratedObject();
		}
	}

	/**
	 * @param mixed $instance
	 * @return mixed
	 */
	public function getLastDecorator($instance) {
		return $this->lastDecoratorMapping[$instance] ? $this->lastDecoratorMapping[$instance] : $instance;
	}
}