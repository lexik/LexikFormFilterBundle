<?php

namespace Lexik\Bundle\FormFilterBundle;

use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FilterTransformerCompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LexikFormFilterBundle extends Bundle
{
     public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new FilterTransformerCompilerPass());
    }
}
