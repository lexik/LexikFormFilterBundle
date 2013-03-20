<?php

namespace Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add extraction methods to the data extraction service.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FormDataExtractorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('lexik_form_filter.form_data_extractor')) {
            $definition = $container->getDefinition('lexik_form_filter.form_data_extractor');

            foreach ($container->findTaggedServiceIds('lexik_form_filter.data_extraction_method') as $id => $attributes) {
                $definition->addMethodCall('addMethod', array(new Reference($id)));
            }
        }
    }
}
