<?php

namespace EssentialDots\ExtbaseDomainDecorator\Decorator;

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
 * Class DecoratorManager
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Decorator
 */
class DecoratorManager implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var array
	 */
	protected $decorators = array();

	/**
	 * @var array
	 */
	protected $registeredDecoratorClasses = array();

	/**
	 * @var array
	 */
	protected $registeredBackendClasses = array();

	/**
	 * @var array
	 */
	protected $registeredDataMapFactoryClasses = array();

	/**
	 * @var array
	 */
	protected $registrations = array();

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Object\Container\Container
	 */
	protected $objectContainer;

	/**
	 * @param $className
	 * @param string $backendClassName
	 * @param string $dataMapFactoryClassName
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function registerBackendAndDataMapFactory($className, $backendClassName = '', $dataMapFactoryClassName = '') {
		$className = ltrim($className, '\\');
		$backendClassName = ltrim($backendClassName, '\\');
		$dataMapFactoryClassName = ltrim($dataMapFactoryClassName, '\\');

		if ($this->registeredBackendClasses[$className] && $this->registeredBackendClasses[$className] != $backendClassName) {
			throw new \InvalidArgumentException(sprintf('The $className "%s" has already a registered backend class.', $className));
		}

		if ($this->registeredDataMapFactoryClasses[$className] && $this->registeredDataMapFactoryClasses[$className] != $dataMapFactoryClassName) {
			throw new \InvalidArgumentException(sprintf('The $className "%s" has already a registered data map factory class.', $className));
		}

		$this->registeredBackendClasses[$className] = $backendClassName;
		$this->registeredDataMapFactoryClasses[$className] = $dataMapFactoryClassName;
	}

	/**
	 * @param $className
	 */
	public function getBackendClassNameForObjectType($className) {
		return $this->registeredBackendClasses[$className];
	}

	/**
	 * @param $className
	 */
	public function getDataMapFactoryClassNameForObjectType($className) {
		return $this->registeredDataMapFactoryClasses[$className];
	}

	/**
	 * @param $className
	 * @param $decoratorClassName
	 * @throws \InvalidArgumentException
	 */
	public function registerDecorator($className, $decoratorClassName) {
		$className = ltrim($className, '\\');
		$decoratorClassName = ltrim($decoratorClassName, '\\');

		if ($this->decorators[$decoratorClassName]) {
			throw new \InvalidArgumentException(sprintf('The $decoratorClassName "%s" has already been registered as a base class.', $decoratorClassName));
		}

		if ($this->registeredDecoratorClasses[$className] && $this->registeredDecoratorClasses[$className] != $this->getBaseClassName($className)) {
			throw new \InvalidArgumentException(sprintf('The $className "%s" has already been registered as a decorator class.', $className));
		}

		if (!$this->registeredDecoratorClasses[$className]) {
			$this->registeredDecoratorClasses[$decoratorClassName] = $this->getBaseClassName($className);

			if (!$this->decorators[$className]) {
				$this->decorators[$className] = array();
			}
			$this->decorators[$className][] = $decoratorClassName;
		}
	}

	/**
	 * @param $decoratorClass
	 * @return string
	 */
	public function getDecoratedClass($decoratorClass) {
		$decoratorClass = ltrim($decoratorClass, '\\');

		return $this->registeredDecoratorClasses[$decoratorClass] ?
				$this->registeredDecoratorClasses[$decoratorClass] :
				$this->getBaseClassName($decoratorClass);
	}

	/**
	 * @param $className
	 * @return array
	 */
	public function getDecoratorsForClassName($className) {
		$className = ltrim($className, '\\');

		$baseClassName = $this->getObjectContainer()->getBaseClassName($className);
		return $this->decorators[$baseClassName] ? $this->decorators[$baseClassName] : array();
	}

	/**
	 * @param $className
	 * @return array
	 */
	public function getBaseClassName($className) {
		$className = ltrim($className, '\\');

		return $this->getObjectContainer()->getBaseClassName($className);
	}

	/**
	 * @return \EssentialDots\ExtbaseDomainDecorator\Object\Container\Container
	 */
	protected function getObjectContainer() {
		if ($this->objectContainer === NULL) {
			$this->objectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Object\\Container\\Container');
		}

		return $this->objectContainer;
	}

	/**
	 * @return void
	 */
	public function destroy() {
		$this->decorators = array();
		$this->registeredDecoratorClasses = array();
		$this->registeredBackendClasses = array();
		$this->registeredDataMapFactoryClasses = array();
		$this->registrations = array();
		$this->objectContainer = NULL;
	}
}