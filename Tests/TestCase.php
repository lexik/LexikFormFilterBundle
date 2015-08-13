<?php

namespace Lexik\Bundle\FormFilterBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\Form\FilterExtension;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FormDataExtractorPass;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\LexikFormFilterExtension;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Form\FormFactory
     */
    protected $formFactory;

    public function setUp()
    {
        $this->formFactory = $this->getFormFactory();
    }

    /**
     * Create a form factory instance.
     *
     * @return \Symfony\Component\Form\FormFactory
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
     * EntityManager object together with annotation mapping driver and
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

    /**
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getMongodbDocumentManager($loggerCallback)
    {
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        $reader = new AnnotationReader($cache);
        $xmlDriver = new \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver($reader, array(
            __DIR__.'/Fixtures/Document',
        ));

        $config = new \Doctrine\ODM\MongoDB\Configuration();
        $config->setMetadataCacheImpl($cache);
        $config->setMetadataDriverImpl($xmlDriver);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxy');
        $config->setAutoGenerateProxyClasses(true);
        $config->setClassMetadataFactoryName('Doctrine\ODM\MongoDB\Mapping\ClassMetadataFactory');
        $config->setDefaultDB('lexik_form_filter_bundle_test');
        $config->setHydratorDir(sys_get_temp_dir());
        $config->setHydratorNamespace('Doctrine\ODM\MongoDB\Hydrator');
        $config->setAutoGenerateHydratorClasses(true);
        $config->setDefaultCommitOptions(array());
        $config->setLoggerCallable($loggerCallback);

        $options = array();
        $conn = new \Doctrine\MongoDB\Connection(null, $options, $config);

        $dm = \Doctrine\ODM\MongoDB\DocumentManager::create($conn, $config);

        return $dm;
    }

    protected function initQueryBuilderUpdater()
    {
        $container = $this->getContainer();

        return $container->get('lexik_form_filter.query_builder_updater');
    }

    protected function getContainer()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new LexikFormFilterExtension());

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../vendor/symfony/framework-bundle/Resources/config'));
        $loadXml->load('services.xml');

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loadXml->load('services.xml');
        $loadXml->load('form.xml');
        $loadXml->load('doctrine_dbal.xml');
        $loadXml->load('doctrine_orm.xml');
        $loadXml->load('doctrine_mongodb.xml');

        $container->setParameter('lexik_form_filter.where_method', null);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->addCompilerPass(new FormDataExtractorPass());
        $container->addCompilerPass(new \Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass());

        $container->compile();

        return $container;
    }
}
