<?php

namespace Lexik\Bundle\FormFilterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\Configuration;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;


/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class LexikFormFilterExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('form.xml');

        foreach ($config['listeners'] as $name => $enable) {
            if ($enable) {
                $loader->load(sprintf('%s.xml', $name));
            }
        }

        if (isset($config['force_case_insensitivity'])) {
            $filterPrepareDef = $container->getDefinition('lexik_form_filter.filter_prepare');
            $filterPrepareDef->addMethodCall(
                'setForceCaseInsensitivity',
                array($config['force_case_insensitivity'])
            );
        }

        if (isset($config['encoding'])) {
            $filterPrepareDef = $container->getDefinition('lexik_form_filter.filter_prepare');
            $filterPrepareDef->addMethodCall(
                'setEncoding',
                array($config['encoding'])
            );
        }

        $container->setParameter('lexik_form_filter.where_method', $config['where_method']);
        $container->setParameter('lexik_form_filter.condition_pattern', FilterOperands::getStringOperandByString($config['condition_pattern']));
    }
}
