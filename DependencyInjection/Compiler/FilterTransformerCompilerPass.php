<?php

namespace Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FilterTransformerCompilerPass implements CompilerPassInterface
{
    /**
     * (non-PHPdoc)
     * @see Symfony\Component\DependencyInjection\Compiler.CompilerPassInterface::process()
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('lexik_form_filter.transformer_aggregator')) {
            return;
        }

        $definition = $container->getDefinition('lexik_form_filter.transformer_aggregator');

        foreach ($container->findTaggedServiceIds('lexik_form_filter.transformer') as $id => $attributes) {
            $definition->addMethodCall('addFilterTransformer', array($id, new Reference($id)));
        }
    }
}
