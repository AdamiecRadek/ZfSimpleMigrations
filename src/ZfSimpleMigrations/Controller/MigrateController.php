<?php
namespace ZfSimpleMigrations\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use ZfSimpleMigrations\Library\Migration;
use ZfSimpleMigrations\Library\MigrationException;
use ZfSimpleMigrations\Library\MigrationSkeletonGenerator;
use ZfSimpleMigrations\Library\OutputWriter;

/**
 * Migration commands controller
 */
class MigrateController extends AbstractActionController
{
    /**
     * @var \ZfSimpleMigrations\Library\Migration
     */
    protected $migration;

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->getRequest() instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        return parent::onDispatch($e);
    }

    /**
     * Overridden only for PHPDoc return value for IDE code helpers
     *
     * @return ConsoleRequest
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * Get current migration version
     *
     * @return int
     */
    public function versionAction()
    {
        return sprintf("Current version %s\n", $this->getMigration()->getCurrentVersion());
    }

    /**
     * List migrations - not applied by default, all with 'all' flag.
     *
     * @return string
     */
    public function listAction()
    {
        $migrations = $this->getMigration()->getMigrationClasses($this->getRequest()->getParam('all'));
        $list = array();
        foreach ($migrations as $m) {
            $list[] = sprintf("%s %s - %s", $m['applied'] ? '-' : '+', $m['version'], $m['description']);
        }
        return (empty($list) ? 'No migrations available.' : implode("\n", $list)) . "\n";
    }

    /**
     * Apply migration
     */
    public function applyAction()
    {
        $version = $this->getRequest()->getParam('version');

        $migrations = $this->getMigration()->getMigrationClasses();
        $currentMigrationVersion = $this->getMigration()->getCurrentVersion();
        $force = $this->getRequest()->getParam('force');

        if (is_null($version) && $force) {
            return "Can't force migration apply without migration version explicitly set.";
        }
        if (!$force && is_null($version) && $currentMigrationVersion >= $this->getMigration()->getMaxMigrationVersion($migrations)) {
            return "No migrations to apply.\n";
        }

        try {
            $this->getMigration()->migrate($version, $force, $this->getRequest()->getParam('down'));
            return "Migrations applied!\n";
        } catch (MigrationException $e) {
            return "ZfSimpleMigrations\\Library\\MigrationException\n" . $e->getMessage() . "\n";
        }
    }

    /**
     * Generate new migration skeleton class
     */
    public function generateSkeletonAction()
    {
        $config = $this->getServiceLocator()->get('Config');

        $generator = new MigrationSkeletonGenerator($config['migrations']['dir'], $config['migrations']['namespace']);
        $classPath = $generator->generate();

        return sprintf("Generated skeleton class @ %s\n", realpath($classPath));
    }

    /**
     * @return Migration
     */
    protected function getMigration()
    {
        if (!$this->migration) {
            /** @var $adapter \Zend\Db\Adapter\Adapter */
            $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $config = $this->getServiceLocator()->get('Configuration');

            $output = null;
            if ($config['migrations']['show_log']) {
                $console = $this->getServiceLocator()->get('console');
                $output = new OutputWriter(function ($message) use ($console) {
                    $console->write($message . "\n");
                });
            }

            /** @var $migrationVersionTable \ZfSimpleMigrations\Model\MigrationVersionTable */
            $migrationVersionTable = $this->getServiceLocator()->get('ZfSimpleMigrations\Model\MigrationVersionTable');

            $this->migration = new Migration($adapter, $config['migrations'], $migrationVersionTable, $output);
        }
        return $this->migration;
    }
}
