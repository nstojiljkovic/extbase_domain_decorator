<?php

namespace EssentialDots\ExtbaseDomainDecorator\Object\Container;

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
 * Class Container
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Object\Container
 */
class Container extends \TYPO3\CMS\Extbase\Object\Container\Container implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * registered alternative implementations of a class
	 * e.g. used to know the class for a AbstractClass or a Dependency
	 *
	 * @var array
	 */
	protected $alternativeImplementation = array();

	/**
	 * @var array
	 */
	protected $baseImplementation = array();

	/**
	 * @var \TYPO3\CMS\Extbase\Object\Container\Container
	 */
	protected $defaultObjectContainer;

	/**
	 * constructor
	 */
	public function __construct() {
		$this->defaultObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\Container\\Container');
	}

	/**
	 * register a classname that should be used if a dependency is required.
	 * e.g. used to define default class for a interface
	 *
	 * @param string $className
	 * @param string $alternativeClassName
	 *
	 * @return void
	 */
	public function registerImplementation($className, $alternativeClassName) {
		$this->alternativeImplementation[$className] = $alternativeClassName;
		$this->baseImplementation[$alternativeClassName] = $className;
		$this->defaultObjectContainer->registerImplementation($className, $alternativeClassName);
	}

	/**
	 * Returns the class name for a new instance, taking into account the
	 * class-extension API.
	 *
	 * @param	string		Base class name to evaluate
	 * @return	string		Final class name to instantiate with "new [classname]"
	 */
	public function getImplementationClassName($className) {
		if (isset($this->alternativeImplementation[$className])) {
			$className = $this->alternativeImplementation[$className];
		}

		if (substr($className, -9) === 'Interface') {
			$className = substr($className, 0, -9);
		}

		return $className;
	}

	/**
	 * @param	string		Base class name to evaluate
	 * @return	string		Final class name to instantiate with "new [classname]"
	 */
	public function getBaseClassName($className) {
		return $this->baseImplementation[$className] ? $this->baseImplementation[$className] : $className;
	}
}