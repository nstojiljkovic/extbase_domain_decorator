<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper;
use EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * Class DataMapper
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Persistence\Mapper
 */
class DataMapper extends \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper {

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
	 * Maps a single row on an object of the given class
	 *
	 * @param string $className The name of the target class
	 * @param array $row A single array with field_name => value pairs
	 * @return object An object of the given class
	 */
	protected function mapSingleRow($className, array $row) {
		$className = $this->getDecoratorManager()->getDecoratedClass($className);

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
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object
	 */
	// @codingStandardsIgnoreStart
	public function reloadObjectFromDB(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object) {
		$this->reloadObjectFromDataBase($object);
	}
	// @codingStandardsIgnoreEnd

	/**
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object
	 * @throws Exception\ObjectNotFoundInDBException
	 */
	public function reloadObjectFromDataBase(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object) {
		$tableName = $this->getDataMap(get_class($object))->getTableName();
		$enableFields = $this->getEnableFields($tableName);
		$uid = $this->getDatabase()->fullQuoteStr($object->getUid(), $tableName);
		$res = $this->getDatabase()->sql_query('
			# @tables_used = ' . $tableName . ';

			SELECT * FROM ' . $tableName . ' WHERE uid = ' . $uid . $enableFields);
		if ($res && ($row = $this->getDatabase()->sql_fetch_assoc($res))) {
			$className = get_class($object);
			$dataMap = $this->getDataMap($className);
			$properties = $object->_getProperties();
			foreach ($properties as $propertyName => $_) {
				if (!$dataMap->isPersistableProperty($propertyName)) {
					continue;
				}
				$object->_setProperty($propertyName, NULL);
			}
			$this->thawProperties($object, $row);
			$object->_memorizeCleanState();
		} else {
			throw new Exception\ObjectNotFoundInDBException('Object not found in DB', 9886423);
		}
	}

	/**
	 * Returns the type of a child object.
	 *
	 * @param string $parentClassName The class name of the object this proxy is part of
	 * @param string $propertyName The name of the proxied property in it's parent
	 * @return string The class name of the child object
	 */
	public function getType($parentClassName, $propertyName) {
		return parent::getType($this->getDecoratorManager()->getDecoratedClass($parentClassName), $propertyName);
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
	protected function getConstraint(
			\TYPO3\CMS\Extbase\Persistence\QueryInterface $query,
			\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $parentObject,
			$propertyName,
			$fieldValue = '',
			$relationTableMatchFields = array()) {
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

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @param string $table
	 * @param string $alias
	 *
	 * @return string
	 */
	protected function getEnableFields($table, $alias = '') {
		$enableFields = \TYPO3\CMS\Backend\Utility\BackendUtility::BEenableFields ( $table );
		if (trim($enableFields) == 'AND') {
			$enableFields = '';
		}
		$enableFields .= \TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($table);

		if ($alias) {
			$enableFields = str_replace($table . '.', $alias . '.', $enableFields);
		}

		return $enableFields;
	}

	/**
	 * Sets the given properties on the object.
	 *
	 * @param \TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object The object to set properties on
	 * @param array $row
	 * @return void
	 */
	protected function thawProperties(\TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface $object, array $row) {
		$params = array(
			'pObj' => &$this,
			'object' => &$object,
			'row' => &$row,
		);
		$this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'beforeThawProperties', $params);
		parent::thawProperties($object, $row);
		$className = get_class($object);
		$dataMap = $this->getDataMap($className);
		$object->_setProperty('uid', $row['uid']);
		$object->_setProperty('pid', $row['pid']);
		$object->_setProperty('_localizedUid', $row['uid']);
		if ($dataMap->getLanguageIdColumnName() !== NULL) {
			$object->_setProperty('_languageUid', $row[$dataMap->getLanguageIdColumnName()]);
			if (isset($row['_LOCALIZED_UID'])) {
				$object->_setProperty('_localizedUid', $row['_LOCALIZED_UID']);
			}
		}
		$this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'afterThawProperties', $params);
	}

	/**
	 * @return \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected function getDecoratorManager() {
		if (!$this->decoratorManager) {
			$this->decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
				->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
		}

		return $this->decoratorManager;
	}

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	// @codingStandardsIgnoreStart
	protected $_signalSlotDispatcher = NULL;
	// @codingStandardsIgnoreEnd

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		if (!isset($this->_signalSlotDispatcher)) {
			$this->_signalSlotDispatcher = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
		}
		return $this->_signalSlotDispatcher;
	}
}