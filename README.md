# ZfSimpleMigrations

Simple Migrations for Zend Framework 2. Project originally based on [ZendDbMigrations](https://github.com/vadim-knyzev/ZendDbMigrations) but module author did not response for issues and pull-requests so fork became independent project.

## Installation

### Using composer

```bash
php composer.phar require vgarvardt/zf-simple-migrations:dev-master
php composer.phar update
```
add `ZfSimpleMigrations` to the `modules` array in application.config.php

## Usage

### Available commands

* `migration version [<name>]` - show last applied migration (`name` specifies a configured migration)
* `migration list [<name>] [--all]` - list available migrations (`all` includes applied migrations)
* `migration apply [<name>] [<version>] [--force] [--down] [--fake]` - apply or rollback migration
* `migration generate [<name>]` - generate migration skeleton class

Migration classes are stored in `/path/to/project/migrations/` dir by default.

Generic migration class has name `Version<YmdHis>` and implement `ZfSimpleMigrations\Library\MigrationInterface`.

### Migration class example

``` php
<?php

namespace ZfSimpleMigrations\Migrations;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;

class Version20130403165433 extends AbstractMigration
{
    public static $description = "Migration description";

    public function up(MetadataInterface $schema)
    {
        //$this->addSql(/*Sql instruction*/);
    }

    public function down(MetadataInterface $schema)
    {
        //$this->addSql(/*Sql instruction*/);
    }
}
```

### Accessing ServiceLocator In Migration Class

By implementing the `Zend\ServiceManager\ServiceLocatorAwareInterface` in your migration class you get access to the
ServiceLocator used in the application.

``` php
<?php

namespace ZfSimpleMigrations\Migrations;

use ZfSimpleMigrations\Library\AbstractMigration;
use Zend\Db\Metadata\MetadataInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class Version20130403165433 extends AbstractMigration
                            implements ServiceLocatorAwareInterface
{
    public static $description = "Migration description";

    /** @var ServiceLocatorInterface */
    protected $serviceLocator;

    public function up(MetadataInterface $schema)
    {
         //$this->getServiceLocator()->get(/*Get service by alias*/);
         //$this->addSql(/*Sql instruction*/);

    }

    public function down(MetadataInterface $schema)
    {
        //$this->getServiceLocator()->get(/*Get service by alias*/);
        //$this->addSql(/*Sql instruction*/);
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
```

## Configuration
  
### User Configuration

The top-level key used to configure this module is `migrations`. 

#### Migration Configurations: Migrations Name

Each key under `migrations` is a migrations configuration, and the value is an array with one or more of
the following keys.

##### Sub-key: `dir`

The path to the directory where migration files are stored. Defaults to `./migrations` in the project root dir.

##### Sub-key: `namespace` 

The class namespace that migration classes will be generated with. Defaults to `ZfSimpleMigrations\Migrations`.

##### Sub-key: `show_log` (optional)

Flag to log output of the migration. Defaults to `true`.

##### Sub-key: `adapter` (optional)

The service alias that will be used to fetch a `Zend\Db\Adapter\Adapter` from the service manager.

#### User configuration example:

```php
'migrations' => array(
    'default' => array(
            'dir' => dirname(__FILE__) . '/../../../../migrations-app',
            'namespace' => 'App\Migrations',    
    ),
    'albums' => array(
            'dir' => dirname(__FILE__) . '/../../../../migrations-albums',
            'namespace' => 'Albums\Migrations',
            'adapter' => 'AlbumDb'    
    ),
),
```