<?php

namespace Lexik\Bundle\FormFilterBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FormDataExtractorPass;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\LexikFormFilterExtension;
use Lexik\Bundle\FormFilterBundle\Filter\Form\FilterExtension;
use Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FormFactory
     */
    protected $formFactory;

    public function setUp()
    {
        $this->formFactory = $this->getFormFactory();
    }

    /**
     * Create a form factory instance.
     *
     * @return FormFactory
     */
    public function getFormFactory()
    {
        $resolvedFormTypeFactory = new ResolvedFormTypeFactory();

        $registery = new FormRegistry([new CoreExtension(), new FilterExtension()], $resolvedFormTypeFactory);

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

        if (class_exists('Doctrine\Common\Annotations\DocParser')) {
            $reader = new AnnotationReader(new \Doctrine\Common\Annotations\DocParser());
        } else {
            $reader = new AnnotationReader($cache);
        }

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

        if (class_exists('Doctrine\Common\Annotations\DocParser')) {
            $reader = new AnnotationReader(new \Doctrine\Common\Annotations\DocParser());
        } else {
            $reader = new AnnotationReader($cache);
        }

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
        $container = $this->createContainerBuilder([
            'framework' => ['secret' => 'test'],
            'lexik_form_filter' => [
                'listeners' => [
                    'doctrine_orm' => true, 'doctrine_dbal' => true, 'doctrine_mongodb' => true,
                ]
            ],
        ]);

        return $container->get('lexik_form_filter.query_builder_updater');
    }

    private static function createContainerBuilder(array $configs = [])
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.bundles'          => [
                'FrameworkBundle' => FrameworkBundle::class,
                'DoctrineBundle' => DoctrineBundle::class,
                'LexikJWTAuthenticationBundle' => LexikFormFilterBundle::class
            ],
            'kernel.bundles_metadata' => [],
            'kernel.cache_dir'        => __DIR__,
            'kernel.debug'            => false,
            'kernel.environment'      => 'test',
            'kernel.name'             => 'kernel',
            'kernel.root_dir'         => __DIR__,
            'kernel.project_dir'      => __DIR__,
            'kernel.container_class'  => 'AutowiringTestContainer',
            'kernel.charset'          => 'utf8',
            'env(base64:default::SYMFONY_DECRYPTION_SECRET)' => 'dummy',
        ]));

        $container->registerExtension(new FrameworkExtension());
        $container->registerExtension(new LexikFormFilterExtension());

        $container->setParameter('lexik_form_filter.where_method', null);

        foreach ($configs as $extension => $config) {
            $container->loadFromExtension($extension, $config);
        }

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->addCompilerPass(new FormDataExtractorPass());
        $container->addCompilerPass(new RegisterListenersPass());

        $container->compile();

        return $container;
    }
}
