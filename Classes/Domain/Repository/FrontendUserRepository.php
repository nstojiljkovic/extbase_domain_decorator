<?php

namespace EssentialDots\ExtbaseDomainDecorator\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Nikola Stojiljkovic, Essential Dots d.o.o. Belgrade
 *
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

class FrontendUserRepository extends \EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository {

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
	 * @return null|\EssentialDots\ExtbaseDomainDecorator\Domain\Model\AbstractFrontendUser
	 */
	public function getCurrentFrontendUser() {
		if ($GLOBALS['TSFE']->fe_user->user['uid']) {
			return $this->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		} else {
			return null;
		}
	}
}