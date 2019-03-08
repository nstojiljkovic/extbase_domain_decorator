<?php

namespace EssentialDots\ExtbaseDomainDecorator\Property\TypeConverter;

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
 * This converter transforms arrays or strings to persistent objects. It does the following:
 *
 * - If the input is string, it is assumed to be a UID. Then, the object is fetched from persistence.
 * - If the input is array, we check if it has an identity property.
 *
 * - If the input has an identity property and NO additional properties, we fetch the object from persistence.
 * - If the input has an identity property AND additional properties, we fetch the object from persistence,
 *   create a clone on it, and set the sub-properties. We only do this if the configuration option "CONFIGURATION_MODIFICATION_ALLOWED" is TRUE.
 * - If the input has NO identity property, but additional properties, we create a new object and return it.
 *   However, we only do this if the configuration option "CONFIGURATION_CREATION_ALLOWED" is TRUE.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class PersistentObjectConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter {

	/**
	 * @var integer
	 */
	protected $priority = 25;

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Object\Container\Container
	 */
	protected $objectContainer;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\Container\Container $objectContainer
	 * @return void
	 */
	public function injectObjectContainer(\TYPO3\CMS\Extbase\Object\Container\Container $objectContainer) {
		if ($objectContainer instanceof \EssentialDots\ExtbaseDomainDecorator\Object\Container\Container) {
			$this->objectContainer = $objectContainer;
		} else {
			$this->objectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Object\\Container\\Container');
		}
	}

	/**
	 * We can only convert if the $targetType is either tagged with entity or value object.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @return boolean
	 */
	public function canConvertFrom($source, $targetType) {
		$targetType = $this->objectContainer->getImplementationClassName($targetType);
		$targetRepositoryType = $this->getRepositoryClassName($targetType);
		$isValueObject = is_subclass_of($targetType, 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject');
		$isEntity = is_subclass_of($targetType, 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity');
		return $targetRepositoryType && ($isEntity || $isValueObject);
	}

	/**
	 * Fetch an object from persistence layer.
	 *
	 * @param mixed $identity
	 * @param string $targetType
	 * @return object
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
	 */
	protected function fetchObjectFromPersistence($identity, $targetType) {
		if ($identity) {
			$targetType = $this->objectContainer->getImplementationClassName($targetType);
			$targetRepositoryType = $this->getRepositoryClassName($targetType);
			/* @var $targetRepository \TYPO3\CMS\Extbase\Persistence\Repository */
			$targetRepository = $this->objectManager->get($targetRepositoryType);
			$object = $targetRepository->findByUid($identity);
		} else {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException('The identity property "' . $identity . '" is no UID.', 1297931020);
		}

		if ($identity && $object === NULL) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException('Object with identity "' . var_export($identity, TRUE) . '" not found.', 1297933823);
		}

		return $object;
	}

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
