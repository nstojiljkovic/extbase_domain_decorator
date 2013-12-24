TYPO3 Extbase Domain Decorator
===========

Extension key: extbase_domain_decorator
Author: Nikola Stojiljković, Essential Dots d.o.o. Belgrade

This is a TYPO3 extension which adds support to Extbase framework for decorating of domain objects and domain repositories. Furthermore, it allows registering arbitrary data mapper and storage backend for any Extbase domain model.

## 1. Problems solved

Before reading further, make sure you understand what and how related patterns work:

* domain decorator - http://sourcemaking.com/design_patterns/decorator,
* data mapper - http://martinfowler.com/eaaCatalog/dataMapper.html

## 2. Domain decorator

Extbase out of the box supports concept of types of records - it can instantiate a subclass of an original domain model class depending on the type field in the database. This approach has limitations as multiple extensions extending one domain model have to be dependent of each other.

This extension allows for multiple extensions to decorate a base domain object model. This is similar to how TYPO3 BE works - multiple extensions can add fields to an existing record type without knowing of each other.

### 2.1. Registering decorators

In ext_localconf.php of an extension which decorates a base domain model, you need to put something like this:

```php
  /* @var $decoratorManager \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager */
  $decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager');
  $decoratorManager->registerDecorator('CompanyName\\BaseExtension\\Domain\\Model\\AbstractObject', 'CompanyName\\ExtendingExtension\\Domain\\Model\\ObjectWithAdditionalProperties');
  $decoratorManager->registerDecorator('CompanyName\\BaseExtension\\Domain\\Repository\\ObjectRepository', 'CompanyName\\ExtendingExtension\\Domain\\Repository\\ObjectWithAdditionalPropertiesRepository');
  unset($decoratorManager);
```

### 2.2. Decorating domain objects

In the previous example, _\CompanyName\BaseExtension\Domain\Model\AbstractObject_ is the base domain model, while _\CompanyName\ExtendingExtension\Domain\Model\ObjectWithAdditionalProperties_ is its decorator. There are few rules:
* Decorator class needs to extend the base class
* The base class needs to extend _\EssentialDots\ExtbaseDomainDecorator\DomainObject\AbstractEntity_

You can easily manipulate with the decorated objects when a decorator is registered - all base objects will be "wrapped" with a decorator:

* Extbase property mapper will forward the decorated object to controllers expecting base domain object,
* Repository of base domain objects will always return decorated objects,
* Manually instantiating domain objects via _ObjectManager_ will always return decorated object.

It is possible for multiple extensions to decorate a single base domain object. All of the new getters/setter functions (or any other methods) defined in the decorators will be accessible anywhere.

#### 2.2.1. Limitations

* Currently all decorators are applied to an object when instantiating a new domain model.

### 2.3. Decorating repositories

In the previous sample, _\CompanyName\ExtendingExtension\Domain\Repository\ObjectWithAdditionalPropertiesRepository_ is the decorator for base _\CompanyName\BaseExtension\Domain\Repository\ObjectRepository_. The rules are:

* Decorator class needs to extend the base class
* The base class needs to extend _\EssentialDots\ExtbaseDomainDecorator\Persistence\AbstractRepository_

#### 2.3.1. Limitations

There are huge limitations when decorating repositories due to the way how repositories are instantiated:

* The decorators are injected to the base class, they are not true decorators,
* Using standard Extbase inject methods (in controllers, viewhelpers etc) will usually give you just the base domain repository (you should read this as **always**). The workaround is to use _ObjectManager_ (and get base repository) in order to get the decorated one (in case you are expecting a decorated repository, otherwise you can safely just use the standard inject methods):
```php
  $objectRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('CompanyName\\BaseExtension\\Domain\\Repository\\ObjectRepository');
```

### 2.3. Tips

IDEs do not play nice with our decorated objects, so autocomplete is not working out of the box. Solving this is easy, just use | for separating multiple classes which object can extend. For example:

```php
  /* @var $objectRepository \CompanyName\BaseExtension\Domain\Repository\ObjectRepository|\CompanyName\ExtendingExtension\Domain\Repository\ObjectWithAdditionalPropertiesRepository */
  $objectRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')->get('CompanyName\\BaseExtension\\Domain\\Repository\\ObjectRepository');
```

### 2.4. Samples

The list of publicly available sample extensions is pending.

## 3. Data mapper and storage backends

Extbase domain models are out of the box always served from the TYPO3 database. With extbase_domain_decorator it is possible to define custom data map factory for creating data maps for a particular domain model. Furthermore, it is possible to define a backend per domain model. That way you can combine domain models persisted in TYPO3 database and any other source in your extensions.

This allows horizontal scaling of your architecture.

### 3.1. Registering data map factories and backends

In ext_localconf.php of an extension which decorates a base domain model, you need to put something like this:

```php
	/* @var $decoratorManager \EssentialDots\ExtbaseDomainDecorator\Decorator\DecoratorManager */
	$decoratorManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("EssentialDots\\ExtbaseDomainDecorator\\Decorator\\DecoratorManager");
	$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\Account', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
	$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\Email', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
	$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\EmailAddress', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
	$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\SupportCase', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
	$decoratorManager->registerBackendAndDataMapFactory('EssentialDots\\EdSugarcrm\\Domain\\Model\\User', 'EssentialDots\\EdSugarcrm\\Persistence\\Generic\\Backend', 'EssentialDots\\EdSugarcrm\\Persistence\\Mapper\\DataMapFactory');
	unset($decoratorManager);
```

### 3.2. Samples

* [TYPO3 SugarCRM domain model - ed_sugarcrm](https://github.com/nstojiljkovic/ed_sugarcrm/)

### 3.3. TODO

* Implement phpunit tests in order to easily test the extension with new version of Extbase

## 4. License

Extbase Domain Decorator is licensed under the terms of the GPL License.

## 5. Support

Please contact company [Essential Dots](http://www.essentialdots.com/) in order to get commercial support for this extension.
