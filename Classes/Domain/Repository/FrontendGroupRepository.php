<?php

namespace EssentialDots\ExtbaseDomainDecorator\Domain\Repository;

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
 * Class FrontendGroupRepository
 *
 * @package EssentialDots\ExtbaseDomainDecorator\Domain\Repository
 */
class FrontendGroupRepository extends \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository {

	/**
	 * @var array<\EssentialDots\ExtbaseDomainDecorator\Domain\Model\AbstractFrontendGroup>
	 */
	// @codingStandardsIgnoreStart
	protected $_currentFrontendGroups = array();
	// @codingStandardsIgnoreEnd

	/**
	 * Injects query settings object.
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings The Query Settings
	 * @return void
	 */
	public function injectQuerySettings(\TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings) {
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * @param bool $useCache
	 * @return array<\EssentialDots\ExtbaseDomainDecorator\Domain\Model\AbstractFrontendGroup>
	 */
	public function getCurrentFrontendGroups($useCache = TRUE) {
		if (!$this->_currentFrontendGroups || !$useCache) {
			if ($GLOBALS['TSFE']->fe_user->groupData && $GLOBALS['TSFE']->fe_user->groupData['uid']) {
				foreach ($GLOBALS['TSFE']->fe_user->groupData['uid'] as $uid) {
					$userGroup = $this->findByUid($uid);
					if ($userGroup) {
						$this->_currentFrontendGroups[] = $userGroup;
					}
				}
			} else {
				$this->_currentFrontendGroups = array();
			}
		}

		return $this->_currentFrontendGroups;
	}

	/**
	 * @return array
	 */
	public function getCurrentFrontendGroupUids() {
		return $GLOBALS['TSFE']->fe_user->groupData['uid'] ? $GLOBALS['TSFE']->fe_user->groupData['uid'] : array();
	}
}