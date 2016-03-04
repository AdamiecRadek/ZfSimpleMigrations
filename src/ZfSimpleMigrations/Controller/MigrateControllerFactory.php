<?php


namespace ZfSimpleMigrations\Controller;


use Zend\Console\Request;
use Zend\Mvc\Router\Console\RouteMatch;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;

class MigrateControllerFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if ($serviceLocator instanceof AbstractPluginManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        /** @var RouteMatch $routeMatch */
        $routeMatch = $serviceLocator->get('Application')->getMvcEvent()->getRouteMatch();

        $name            = $routeMatch->getParam('name', 'default');
        $migrationConfig = $serviceLocator->get('config')['migrations'][$name];
        $prefix          = isset($migrationConfig['prefix']) ? $migrationConfig['prefix'] : '';

        /** @var Migration $migration */
        $migration = $serviceLocator->get('migrations.migration.' . $name);
        $migration->changeMigrationPrefix($prefix);

        /** @var MigrationSkeletonGenerator $generator */
        $generator = $serviceLocator->get('migrations.skeleton-generator.' . $name);

        $controller = new MigrateController();

        $controller->setMigration($migration);
        $controller->setSkeletonGenerator($generator);

        return $controller;
    }
}
