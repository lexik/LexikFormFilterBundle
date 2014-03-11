<?php

namespace Lexik\Bundle\FormFilterBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\FilterExtension;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    /**
     * @var Symfony\Component\Form\FormFactory
     */
    protected $formFactory;


    public function setUp()
    {
        $this->em          = $this->getSqliteEntityManager();
        $this->conn        = $this->em->getConnection();
        $this->formFactory = $this->getFormFactory();
    }

    /**
     * Create a form factory instance.
     *
     * @return Symfony\Component\Form\FormFactory
     */
    public function getFormFactory()
    {
        $resolvedFormTypeFactory = new ResolvedFormTypeFactory();

        $registery = new FormRegistry(array(
            new CoreExtension(),
            new FilterExtension(),
        ), $resolvedFormTypeFactory);

        $formFactory = new FormFactory($registery, $resolvedFormTypeFactory);

        return $formFactory;
    }

    /**
     * EntityManager mock object together with annotation mapping driver and
     * pdo_sqlite database in memory
     *
     * @return EntityManager
     */
    public function getSqliteEntityManager()
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        $reader = new AnnotationReader($cache);
        $mappingDriver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, array(
            __DIR__.'/Fixtures/Entity',
        ));

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array());

        $config->setMetadataDriverImpl($mappingDriver);
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxy');
        $config->setAutoGenerateProxyClasses(true);
        $config->setClassMetadataFactoryName('Doctrine\ORM\Mapping\ClassMetadataFactory');
        $config->setDefaultRepositoryClassName('Doctrine\ORM\EntityRepository');

        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $em = EntityManager::create($conn, $config);

        return $em;
    }
}
