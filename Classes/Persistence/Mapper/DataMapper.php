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

class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper {

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
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
		$this->decoratorManager = $this->objectManager->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
	}

	/**
	 * Maps a single row on an object of the given class
	 *
	 * @param string $className The name of the target class
	 * @param array $row A single array with field_name => value pairs
	 * @return object An object of the given class
	 */
	protected function mapSingleRow($className, array $row) {
		$className = $this->decoratorManager->getDecoratedClass($className);

		if ($this->persistenceSession->hasIdentifier($row['uid'], $className)) {
			$object = $this->persistenceSession->getObjectByIdentifier($row['uid'], $className);
		} else {
			$object = $this->createEmptyObject($className);
			$object = $this->objectManager->decorateObject($object, $className);
			$this->persistenceSession->registerObject($object, $row['uid']);
			$this->thawProperties($object, $row);
			$object->_memorizeCleanState();
			$this->persistenceSession->registerReconstitutedEntity($object);

		}
		return $object;
	}


	/**
	 * Returns the type of a child object.
	 *
	 * @param string $parentClassName The class name of the object this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @return string The class name of the child object
	 */
	public function getType($parentClassName, $propertyName) {
		return parent::getType($this->decoratorManager->getDecoratedClass($parentClassName), $propertyName);
	}

	/**
	 * Builds and returns the constraint for multi value properties.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject
	 * @param string $propertyName
	 * @param string $fieldValue
	 * @param array $relationTableMatchFields
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint
	 */
	protected function getConstraint(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query, \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject, $propertyName, $fieldValue = '', $relationTableMatchFields = array()) {
		$columnMap = $this->getDataMap(get_class($parentObject))->getColumnMap($propertyName);
		if ($columnMap->getParentKeyFieldName() !== NULL) {
			$constraint = $query->equals($columnMap->getParentKeyFieldName(), $parentObject);
			if ($columnMap->getParentTableFieldName() !== NULL) {
				$constraint = $query->logicalAnd($constraint, $query->equals($columnMap->getParentTableFieldName(), $this->getDataMap(get_class($parentObject))->getTableName()));
			}
		} else {
			$constraint = $query->in('uid', \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $fieldValue));
		}
		if (count($relationTableMatchFields) > 0) {
			foreach ($relationTableMatchFields as $relationTableMatchFieldName => $relationTableMatchFieldValue) {
				$constraint = $query->logicalAnd($constraint, $query->equals($relationTableMatchFieldName, $relationTableMatchFieldValue));
			}
		}
		return $constraint;
	}
}