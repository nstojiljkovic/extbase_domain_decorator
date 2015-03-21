<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * register default implementations
 */

/* @var $extbaseObjectContainer \TYPO3\CMS\Extbase\Object\Container\Container */
$extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Object\\Container\\Container');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Reflection\\ReflectionService', 'EssentialDots\\ExtbaseDomainDecorator\\Reflection\\Service');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMap', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMap');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMapper', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMapper');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMapFactory', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMapFactory');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', 'EssentialDots\\ExtbaseDomainDecorator\\Object\\ObjectManager');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface', 'EssentialDots\\ExtbaseDomainDecorator\\Object\\ObjectManager');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\LazyLoadingProxy', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\LazyLoadingProxy');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Session', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\Session');
$extbaseObjectContainer->registerImplementation('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager', 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\PersistenceManager');
$extbaseObjectContainer->registerImplementation('EssentialDots\\ExtbaseDomainDecorator\\Domain\\Model\\AbstractFrontendGroup', 'EssentialDots\\ExtbaseDomainDecorator\\Domain\\Model\\FrontendGroup');
$extbaseObjectContainer->registerImplementation('EssentialDots\\ExtbaseDomainDecorator\\Domain\\Model\\AbstractFrontendUser', 'EssentialDots\\ExtbaseDomainDecorator\\Domain\\Model\\FrontendUser');
unset($extbaseObjectContainer);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Reflection\\ReflectionService']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Reflection\\Service';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMap']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMap';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMapper']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMapper';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Mapper\\DataMapFactory']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Mapper\\DataMapFactory';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Object\\ObjectManager']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Object\\ObjectManager';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Object\\ObjectManagerInterface']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Object\\ObjectManager';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\LazyLoadingProxy']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\LazyLoadingProxy';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Session']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\Session';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager']['className'] = 'EssentialDots\\ExtbaseDomainDecorator\\Persistence\\Generic\\PersistenceManager';

/**
 * register type converter
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('EssentialDots\\ExtbaseDomainDecorator\\Property\\TypeConverter\\PersistentObjectConverter');

