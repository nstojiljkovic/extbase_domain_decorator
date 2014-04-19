<?php

namespace EssentialDots\ExtbaseDomainDecorator\Reflection;

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

class Service extends \TYPO3\CMS\Extbase\Reflection\ReflectionService {

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected $signalSlotDispatcher;

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected $decoratorManager;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Builds class schemata from classes annotated as entities or value objects
	 *
	 * @param string $className
	 * @return \TYPO3\CMS\Extbase\Reflection\ClassSchema
	 */
	protected function buildClassSchema($className) {
		/* @var $objectContainer \EssentialDots\ExtbaseDomainDecorator\Object\Container\Container */
		$objectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Object\\Container\\Container');
			// find implementation class name for the required class name
		$implementationClassName = $objectContainer->getImplementationClassName($className);
			// find decorated class name for the implementation class
		$decoratedClassName = $this->getDecoratorManager()->getDecoratedClass($implementationClassName);
			// find implementation class name for the decorated class name
		$implementationClassName = $objectContainer->getImplementationClassName($decoratedClassName);

		// create decorated class schemata
		if (isset($this->classSchemata[$implementationClassName])) {
			$classSchema = $this->classSchemata[$implementationClassName];
		} else {
			$classSchema = parent::buildClassSchema($implementationClassName);

			// add properties of all decorators to the decorated class schemata
			foreach ($this->getDecoratorManager()->getDecoratorsForClassName($decoratedClassName) as $decoratorClassName) {
				foreach ($this->getClassPropertyNames($decoratorClassName) as $propertyName) {
					if (!$classSchema->hasProperty($propertyName)) {
						if (!$this->isPropertyTaggedWith($decoratorClassName, $propertyName, 'transient') && $this->isPropertyTaggedWith($decoratorClassName, $propertyName, 'var')) {
							$cascadeTagValues = $this->getPropertyTagValues($decoratorClassName, $propertyName, 'cascade');
							$classSchema->addProperty($propertyName, implode(' ', $this->getPropertyTagValues($decoratorClassName, $propertyName, 'var')), $this->isPropertyTaggedWith($decoratorClassName, $propertyName, 'lazy'), $cascadeTagValues[0]);
						}
						if ($this->isPropertyTaggedWith($decoratorClassName, $propertyName, 'uuid')) {
							$classSchema->setUuidPropertyName($propertyName);
						}
						if ($this->isPropertyTaggedWith($decoratorClassName, $propertyName, 'identity')) {
							$classSchema->markAsIdentityProperty($propertyName);
						}
					}
				}
			}

			// cache schemata for decorated and all decorator classes
			$this->classSchemata[$implementationClassName] = $classSchema;
			$this->classSchemata[$decoratedClassName] = $classSchema;
			$this->classSchemata[$this->getDecoratorManager()->getBaseClassName($decoratedClassName)] = $classSchema;
			foreach ($this->getDecoratorManager()->getDecoratorsForClassName($decoratedClassName) as $decoratorClassName) {
				$this->classSchemata[$decoratorClassName] = $classSchema;
			}

			$this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'afterBuildClassSchema', array($classSchema));
		}

		return $classSchema;
	}

	/**
	 * @return \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected function getDecoratorManager() {
		if (!$this->decoratorManager) {
			$this->decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
		}

		return $this->decoratorManager;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		if (!$this->signalSlotDispatcher) {
			$this->signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
		}
		return $this->signalSlotDispatcher;
	}
}