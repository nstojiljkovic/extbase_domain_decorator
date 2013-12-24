<?php

namespace EssentialDots\ExtbaseDomainDecorator\Persistence\Generic;

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

class PersistenceManager extends \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager {

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Persistence\Generic\BackendFactory
	 */
	protected $backendFactory;

	/**
	 * @var \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager
	 */
	protected $decoratorManager;

	/**
	 * @var array<\EssentialDots\ExtbaseDomainDecorator\Persistence\Generic\BackendBucket>
	 */
	protected $backendBuckets = array();

	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->backendFactory = $this->objectManager->get('EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\BackendFactory');
		$this->decoratorManager = $this->objectManager->get('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend
	 * @return \EssentialDots\ExtbaseDomainDecorator\Persistence\Generic\BackendBucket
	 */
	protected function getBackendBucket(\TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend) {
		$backendClass = get_class($backend);
		if (!$this->backendBuckets[$backendClass]) {
			$this->backendBuckets[$backendClass] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\BackendBucket', $backend);
		}

		return $this->backendBuckets[$backendClass];
	}

	/**
	 * Returns the number of records matching the query.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return integer
	 * @api
	 */
	public function getObjectCountByQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		return $this->backendFactory->getBackendForObjectType($query->getType())->getObjectCountByQuery($query);
	}

	/**
	 * Returns the object data matching the $query.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return array
	 * @api
	 */
	public function getObjectDataByQuery(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		return $this->backendFactory->getBackendForObjectType($query->getType())->getObjectDataByQuery($query);
	}

	/**
	 * Returns the (internal) identifier for the object, if it is known to the
	 * backend. Otherwise NULL is returned.
	 *
	 * Note: this returns an identifier even if the object has not been
	 * persisted in case of AOP-managed entities. Use isNewObject() if you need
	 * to distinguish those cases.
	 *
	 * @param object $object
	 * @return mixed The identifier for the object if it is known, or NULL
	 * @api
	 */
	public function getIdentifierByObject($object) {
		$lastDecorator = $this->objectManager->getLastDecorator($object);
		$decoratedClassName = $this->decoratorManager->getDecoratedClass(get_class($object));

		return $this->backendFactory->getBackendForObjectType($decoratedClassName)->getIdentifierByObject($lastDecorator);
	}

	/**
	 * Returns the object with the (internal) identifier, if it is known to the
	 * backend. Otherwise NULL is returned.
	 *
	 * @param mixed $identifier
	 * @param string $objectType
	 * @param boolean $useLazyLoading Set to TRUE if you want to use lazy loading for this object
	 * @return object The object for the identifier if it is known, or NULL
	 * @api
	 */
	public function getObjectByIdentifier($identifier, $objectType = NULL, $useLazyLoading = FALSE) {
		$objectType = $this->decoratorManager->getDecoratedClass($objectType);
		$backend = $this->backendFactory->getBackendForObjectType($objectType);
		$backendBucket = $this->getBackendBucket($backend);
		$newObjects = $backendBucket->getNewObjects();

		if (isset($newObjects[$identifier])) {
			return $newObjects[$identifier];
		}
		if ($this->persistenceSession->hasIdentifier($identifier, $objectType)) {
			return $this->persistenceSession->getObjectByIdentifier($identifier, $objectType);
		} else {
			return $this->backendFactory->getBackendForObjectType($objectType)->getObjectByIdentifier($identifier, $objectType);
		}
	}

	/**
	 * Commits new objects and changes to objects in the current persistence
	 * session into the backend
	 *
	 * @return void
	 * @api
	 */
	public function persistAll() {
		// hand in only aggregate roots, leaving handling of subobjects to
		// the underlying storage layer
		// reconstituted entities must be fetched from the session and checked
		// for changes by the underlying backend as well!
		foreach ($this->backendBuckets as $backendBucket) { /* @var $backendBucket \EssentialDots\ExtbaseDomainDecorator\Persistence\Generic\BackendBucket */
			$backendBucket->persistAll();
		}

		// no need to clear the array, persistAll in backend bucket already
		// $this->backendBuckets = array();
	}

	/**
	 * Initializes the persistence manager, called by Extbase.
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->backendFactory->setPersistenceManager($this);
	}

	/**
	 * Tear down the persistence
	 *
	 * This method is called in functional tests to reset the storage between tests.
	 * The implementation is optional and depends on the underlying persistence backend.
	 *
	 * @return void
	 */
	public function tearDown() {
		foreach ($this->backendFactory->getInitializedBackendObjects() as $backend) {  /* @var $backend \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface */
			if (method_exists($backend, 'tearDown')) {
				$backend->tearDown();
			}
		}
	}

	/**
	 * Return a query object for the given type.
	 *
	 * @param string $type
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	// TODO: override this :)
	//	public function createQueryForType($type) {
	//		return $this->queryFactory->create($type);
	//	}

	/**
	 * Adds an object to the persistence.
	 *
	 * @param object $object The object to add
	 * @return void
	 * @api
	 */
	public function add($object) {
		$lastDecorator = $this->objectManager->getLastDecorator($object);
		$objectType = $this->decoratorManager->getDecoratedClass(get_class($lastDecorator));
		$backend = $this->backendFactory->getBackendForObjectType($objectType);
		$backendBucket = $this->getBackendBucket($backend);

		$backendBucket->getAddedObjects()->attach($lastDecorator);
		$backendBucket->getRemovedObjects()->detach($lastDecorator);
	}

	/**
	 * Removes an object to the persistence.
	 *
	 * @param object $object The object to remove
	 * @return void
	 * @api
	 */
	public function remove($object) {
		$lastDecorator = $this->objectManager->getLastDecorator($object);
		$objectType = $this->decoratorManager->getDecoratedClass(get_class($lastDecorator));
		$backend = $this->backendFactory->getBackendForObjectType($objectType);
		$backendBucket = $this->getBackendBucket($backend);

		if ($backendBucket->getAddedObjects()->contains($lastDecorator)) {
			$backendBucket->getAddedObjects()->detach($lastDecorator);
		} else {
			$backendBucket->getRemovedObjects()->attach($lastDecorator);
		}
	}

	/**
	 * Update an object in the persistence.
	 *
	 * @param object $object The modified object
	 * @return void
	 * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
	 * @api
	 */
	public function update($object) {
		$lastDecorator = $this->objectManager->getLastDecorator($object);
		$objectType = $this->decoratorManager->getDecoratedClass(get_class($lastDecorator));
		$backend = $this->backendFactory->getBackendForObjectType($objectType);
		$backendBucket = $this->getBackendBucket($backend);


		if ($this->isNewObject($lastDecorator)) {
			throw new \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException('The object of type "' . get_class($object) . '" given to update must be persisted already, but is new.', 1249479819);
		}
		$backendBucket->getChangedObjects()->attach($object);
	}

	/**
	 * Checks if the given object has ever been persisted.
	 *
	 * @param object $object The object to check
	 * @return boolean TRUE if the object is new, FALSE if the object exists in the persistence session
	 * @api
	 */
	public function isNewObject($object) {
		return parent::isNewObject($this->objectManager->getLastDecorator($object));
	}

	/**
	 * Registers an object which has been created or cloned during this request.
	 *
	 * A "new" object does not necessarily
	 * have to be known by any repository or be persisted in the end.
	 *
	 * Objects registered with this method must be known to the getObjectByIdentifier()
	 * method.
	 *
	 * @param object $object The new object to register
	 * @return void
	 */
	public function registerNewObject($object) {
		$lastDecorator = $this->objectManager->getLastDecorator($object);
		$objectType = $this->decoratorManager->getDecoratedClass(get_class($lastDecorator));
		$backend = $this->backendFactory->getBackendForObjectType($objectType);
		$backendBucket = $this->getBackendBucket($backend);

		$identifier = $this->getIdentifierByObject($lastDecorator);
		$newObjects = $backendBucket->getNewObjects();
		$newObjects[$identifier] = $lastDecorator;
		$backendBucket->setNewObjects($newObjects);
	}

	/**
	 * Clears the in-memory state of the persistence.
	 *
	 * Managed instances become detached, any fetches will
	 * return data directly from the persistence "backend".
	 *
	 * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
	 * @return void
	 */
	public function clearState() {
		foreach ($this->backendBuckets as $backendBucket) { /* @var $backendBucket \EssentialDots\ExtbaseDomainDecorator\Persistence\Generic\BackendBucket */
			$backendBucket->setNewObjects(array());
			$backendBucket->setAddedObjects(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
			$backendBucket->setRemovedObjects(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
			$backendBucket->setChangedObjects(new \TYPO3\CMS\Extbase\Persistence\ObjectStorage());
		}

		$this->persistenceSession->destroy();
	}
}
?>