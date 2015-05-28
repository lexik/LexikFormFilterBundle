<?php

namespace Lexik\Bundle\FormFilterBundle;

use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FormDataExtractorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class LexikFormFilterBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
     public function build(ContainerBuilder $container)
     {
         parent::build($container);

         $container->addCompilerPass(new FormDataExtractorPass());
     }
}
