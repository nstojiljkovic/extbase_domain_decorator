<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence\Generic;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Essential Dots d.o.o. Belgrade
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LazyLoadingProxy
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Persistence\Generic
 */
class LazyLoadingProxy extends \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy {

	/**
	 * The object this property is contained in.
	 *
	 * @var \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface
	 */
	protected $parentObject;

	/**
	 * The name of the property represented by this proxy.
	 *
	 * @var string
	 */
	protected $propertyName;

	/**
	 * The raw field value.
	 *
	 * @var mixed
	 */
	protected $fieldValue;

	/**
	 * Constructs this proxy instance.
	 *
	 * @param object $parentObject The object instance this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @param mixed $fieldValue The raw field value.
	 */
	public function __construct($parentObject, $propertyName, $fieldValue) {
		$this->parentObject = $parentObject;
		$this->propertyName = $propertyName;
		$this->fieldValue = $fieldValue;
	}

	/**
	 * Populate this proxy by asking the $population closure.
	 *
	 * @return object The instance (hopefully) returned
	 */
	// @codingStandardsIgnoreStart
	public function _loadRealInstance() {
		// this check safeguards against a proxy being activated multiple times
		// usually that does not happen, but if the proxy is held from outside
		// it's parent... the result would be weird.
		if ($this->parentObject->_getProperty($this->propertyName) instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy) {
			$columnMap = $this->dataMapper->getDataMap(get_class($this->parentObject))->getColumnMap($this->propertyName);
			$relatesToOne = $columnMap->getTypeOfRelation() == \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap::RELATION_HAS_ONE;

			if ($this->fieldValue) {
				$targetRepository = NULL;
				if ($relatesToOne) {
					$targetType = $this->dataMapper->getType(get_class($this->parentObject), $this->propertyName);
					$targetRepositoryType = $this->getRepositoryClassName($targetType);
					if ($targetRepositoryType) {
						/* @var $targetRepository \TYPO3\CMS\Extbase\Persistence\Repository */
						$targetRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get($targetRepositoryType);
						if ($targetRepository) {
							$propertyValue = $targetRepository->findByUid($this->fieldValue);
						}
					}
				}
				if (!$targetRepository) {
					$objects = $this->dataMapper->fetchRelated($this->parentObject, $this->propertyName, $this->fieldValue, FALSE, FALSE);
					$propertyValue = $this->dataMapper->mapResultToPropertyValue($this->parentObject, $this->propertyName, $objects);
				}
			} else {
				$propertyValue = $relatesToOne ? NULL : array();
			}
			$this->parentObject->_setProperty($this->propertyName, $propertyValue);
			$this->parentObject->_memorizeCleanState($this->propertyName);
			return $propertyValue;
		} else {
			return $this->parentObject->_getProperty($this->propertyName);
		}
	}
	// @codingStandardsIgnoreEnd

	/**
	 * @param string
	 * @return string|NULL
	 */
	protected function getRepositoryClassName($targetType) {
		$found = FALSE;
		while (!$found && $targetType) {
			$delimiter = strpos($targetType, '_') !== FALSE ? '_' : '\\';
			$targetRepositoryType = str_replace($delimiter . 'Domain' . $delimiter . 'Model' . $delimiter, $delimiter . 'Domain' . $delimiter . 'Repository' . $delimiter, $targetType) . 'Repository';
			$targetType = get_parent_class($targetType);
			$found = class_exists($targetRepositoryType);
		}

		return $found ? $targetRepositoryType : NULL;
	}
}
