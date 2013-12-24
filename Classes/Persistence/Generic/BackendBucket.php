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

class BackendBucket {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface
	 * @inject
	 */
	protected $backend;

	/**
	 * @var array
	 */
	protected $newObjects;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected $changedObjects;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected $addedObjects;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	protected $removedObjects;

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend
	 */
	public function __construct(\TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface $backend) {
		$this->backend = $backend;
		$this->addedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->changedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->removedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->newObjects = array();
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $addedObjects
	 */
	public function setAddedObjects($addedObjects) {
		$this->addedObjects = $addedObjects;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getAddedObjects() {
		return $this->addedObjects;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $changedObjects
	 */
	public function setChangedObjects($changedObjects) {
		$this->changedObjects = $changedObjects;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getChangedObjects() {
		return $this->changedObjects;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $removedObjects
	 */
	public function setRemovedObjects($removedObjects) {
		$this->removedObjects = $removedObjects;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getRemovedObjects() {
		return $this->removedObjects;
	}

	/**
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\BackendInterface
	 */
	public function getBackend() {
		return $this->backend;
	}

	/**
	 * @param array $newObjects
	 */
	public function setNewObjects($newObjects) {
		$this->newObjects = $newObjects;
	}

	/**
	 * @return array
	 */
	public function getNewObjects() {
		return $this->newObjects;
	}

	/**
	 * persistAll
	 */
	public function persistAll() {
		$this->backend->setAggregateRootObjects($this->addedObjects);
		$this->backend->setChangedEntities($this->changedObjects);
		$this->backend->setDeletedEntities($this->removedObjects);
		$this->backend->commit();

		$this->newObjects = array();
		$this->addedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->removedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->changedObjects = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}
}