<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper;

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

class DataMapFactory extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory {

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected $decoratorManager;

	/**
	 * @var array<\EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper\DataMapFactoryInterface>
	 */
	protected $dataMapFactories = array();

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Builds a data map by adding column maps for all the configured columns in the $TCA.
	 * It also resolves the type of values the column is holding and the typo of relation the column
	 * represents.
	 *
	 * @param string $className The class name you want to fetch the Data Map for
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper The data map
	 */
	public function buildDataMap($className) {
		$objectType = $this->getDecoratorManager()->getDecoratedClass($className);
		$dataMapFactoryClassName = $this->getDecoratorManager()->getDataMapFactoryClassNameForObjectType($objectType);
		if ($dataMapFactoryClassName && !$this->dataMapFactories[$dataMapFactoryClassName]) {
			$this->dataMapFactories[$dataMapFactoryClassName] = $this->objectManager->get($dataMapFactoryClassName);
		}
		if ($this->dataMapFactories[$dataMapFactoryClassName]) {
			$realDataMapFactory = $this->dataMapFactories[$dataMapFactoryClassName]; /* @var $realDataMapFactory \EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper\DataMapFactoryInterface */
			$dataMap = $realDataMapFactory->buildDataMap($objectType);
		} else {
			$dataMap = parent::buildDataMap($objectType);
		}
		return $dataMap;
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
}