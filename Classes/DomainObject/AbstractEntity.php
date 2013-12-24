<?php

namespace EssentialDots\ExtbaseDomainDecorator\DomainObject;

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

abstract class AbstractEntity extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * @var \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
	 */
	private $_decoratedObject;

	/**
	 * @var An array holding the clean property values. Set right after reconstitution of the object
	 */
	private $_cleanProperties;

	/**
	 * @param $decoratedObject
	 */
	public function __construct($decoratedObject = NULL) {
		$this->_decoratedObject = $decoratedObject;
	}

	/**
	 * @return null|\TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject
	 */
	public function getDecoratedObject() {
		return $this->_decoratedObject;
	}

	/**
	 * Setter for the pid.
	 *
	 * @param int|NULL $pid
	 * @return void
	 */
	public function setPid($pid) {
		if ($this->_decoratedObject) {
			$this->_decoratedObject->setPid($pid);
		} else {
			parent::setPid($pid);
		}
	}

	/**
	 * Getter for the pid.
	 *
	 * @return int The pid or NULL if none set yet.
	 */
	public function getPid() {
		if ($this->_decoratedObject) {
			return $this->_decoratedObject->getPid();
		} else {
			return parent::getPid();
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return mixed|null
	 */
	public function __call($name, $arguments) {
		if ($this->_decoratedObject) {
			return call_user_func_array(array($this->_decoratedObject, $name), $arguments);
		} else {
			error_log("Method doesn't exists ".get_class($this)."::".$name);
			return null;
		}
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
			$this->_decoratedObject->initializeObject();
		}
		$this->initializeObject();
	}

	/**
	 * Initialize object
 	 */
	public function initializeObject() {
	}

	/**
	 * Reconstitutes a property. Only for internal use.
	 *
	 * @param string $propertyName
	 * @param mixed $propertyValue
	 * @return bool
	 */
	public function _setProperty($propertyName, $propertyValue) {
		if ($propertyName=='uid' || $propertyName=='pid') {
			$this->$propertyName = $propertyValue;
			if ($this->_decoratedObject) {
				$this->_decoratedObject->_setProperty($propertyName, $propertyValue);
			}
			return TRUE;
		} elseif (property_exists($this, $propertyName)) {
			$this->$propertyName = $propertyValue;
			return TRUE;
		} else {
			return $this->_decoratedObject ? $this->_decoratedObject->_setProperty($propertyName, $propertyValue) : '';
		}
	}

	/**
	 * Returns the property value of the given property name. Only for internal use.
	 *
	 * @param string $propertyName
	 * @return mixed|null
	 */
	public function _getProperty($propertyName) {
		if (property_exists($this, $propertyName)) {
			return $this->$propertyName;
		} else {
			return $this->_decoratedObject ? $this->_decoratedObject->$propertyName : null;
		}
	}

	/**
	 * Returns a hash map of property names and property values. Only for internal use.
	 *
	 * @return array The properties
	 */
	public function _getProperties() {
		$properties = get_object_vars($this);
		foreach ($properties as $propertyName => $propertyValue) {
			if (substr($propertyName, 0, 1) === '_') {
				unset($properties[$propertyName]);
			}
		}
		if ($this->_decoratedObject) {
			foreach ($this->_decoratedObject->_getProperties() as $propertyName => $propertyValue) {
				if (!$properties[$propertyName]) {
					$properties[$propertyName] = $propertyValue;
				}
			}
		}
		return $properties;
	}

	/**
	 * Returns the property value of the given property name. Only for internal use.
	 *
	 * @param string $propertyName
	 * @return bool
	 */
	public function _hasProperty($propertyName) {
		return property_exists($this, $propertyName) || (isset($this->_decoratedObject) && $this->_decoratedObject->_hasProperty($propertyName));
	}

	/**
	 * Register an object's clean state, e.g. after it has been reconstituted
	 * from the database.
	 *
	 * @param string $propertyName The name of the property to be memorized. If omitted all persistable properties are memorized.
	 * @return void
	 */
	public function _memorizeCleanState($propertyName = NULL) {
		if ($propertyName !== NULL) {
			if (property_exists($this, $propertyName)) {
				$this->_memorizePropertyCleanState($propertyName);
			} elseif ($this->_decoratedObject) {
				$this->_decoratedObject->_memorizePropertyCleanState($propertyName);
			}
		} else {
			parent::_memorizeCleanState();
			if ($this->_decoratedObject) {
				$this->_decoratedObject->_memorizeCleanState();
			}
		}
	}

	/**
	 * Register an properties's clean state, e.g. after it has been reconstituted
	 * from the database.
	 *
	 * @param string $propertyName The name of the property to be memorized. If omittet all persistable properties are memorized.
	 * @return void
	 */
	public function _memorizePropertyCleanState($propertyName) {
		if (!is_array($this->_cleanProperties)) {
			$this->_cleanProperties = array();
		}

		if (property_exists($this, $propertyName)) {
			$propertyValue = $this->$propertyName;
			if (is_object($propertyValue)) {
				$this->_cleanProperties[$propertyName] = clone($propertyValue);

				// We need to make sure the clone and the original object
				// are identical when compared with == (see _isDirty()).
				// After the cloning, the Domain Object will have the property
				// "isClone" set to TRUE, so we manually have to set it to FALSE
				// again. Possible fix: Somehow get rid of the "isClone" property,
				// which is currently needed in Fluid.
				if ($propertyValue instanceof \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject) {
					$this->_cleanProperties[$propertyName]->_setClone(FALSE);
				}
			} else {
				$this->_cleanProperties[$propertyName] = $propertyValue;
			}
		} elseif ($this->_decoratedObject) {
			$this->_decoratedObject->_memorizePropertyCleanState($propertyName);
		}
	}

	/**
	 * Returns a hash map of clean properties and $values.
	 *
	 * @return array
	 */
	public function _getCleanProperties() {
		if ($this->_decoratedObject) {
			if (is_array($this->_cleanProperties)) {
				$decoratedCleanProperties = $this->_decoratedObject->_getCleanProperties();
				if (is_array($decoratedCleanProperties)) {
					return array_merge($decoratedCleanProperties, $this->_cleanProperties);
				} else {
					return $this->_cleanProperties;
				}
			} else {
				return $this->_decoratedObject->_getCleanProperties();
			}
		} else {
			return $this->_cleanProperties;
		}
	}

	/**
	 * Returns the clean value of the given property. The returned value will be NULL if the clean state was not memorized before, or
	 * if the clean value is NULL.
	 *
	 * @param string $propertyName The name of the property to be memorized. If omittet all persistable properties are memorized.
	 * @return mixed The clean property value or NULL
	 */
	public function _getCleanProperty($propertyName) {
		$cleanProperties = $this->_getCleanProperties();
		if (is_array($cleanProperties)) {
			return isset($cleanProperties[$propertyName]) ? $cleanProperties[$propertyName] : NULL;
		} else {
			return NULL;
		}
	}

	/**
	 * Returns TRUE if the properties were modified after reconstitution
	 *
	 * @param string $propertyName An optional name of a property to be checked if its value is dirty
	 * @return boolean
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException
	 */
	public function _isDirty($propertyName = NULL) {
		if ($this->uid !== NULL && is_array($this->_cleanProperties) && $this->uid != $this->_getCleanProperty('uid')) throw new \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException('The uid "' . $this->uid . '" has been modified, that is simply too much.', 1222871239);
		if ($propertyName === NULL) {
			foreach ($this->_getCleanProperties() as $propertyName => $cleanPropertyValue) {
				if ($this->isPropertyDirty($cleanPropertyValue, $this->$propertyName) === TRUE) return TRUE;
			}
		} else {
			if ($this->isPropertyDirty($this->_getCleanProperty($propertyName), $this->$propertyName) === TRUE) return TRUE;
		}
		return FALSE;
	}
}