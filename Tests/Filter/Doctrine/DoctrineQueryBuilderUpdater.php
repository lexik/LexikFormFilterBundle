<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FormDataExtractorPass;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\LexikFormFilterExtension;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilderUpdater;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\RangeFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\FormType;

/**
 * Filter query builder tests.
 */
abstract class DoctrineQueryBuilderUpdater extends TestCase
{
    /**
     * Get query parameters from the query builder.
     *
     * @param $qb
     * @return array
     */
    protected function getQueryBuilderParameters($qb)
    {
        if ($qb instanceof \Doctrine\DBAL\Query\QueryBuilder) {
            return $qb->getParameters();
        }

        if ($qb instanceof \Doctrine\ORM\QueryBuilder) {
            $params = array();

            foreach ($qb->getParameters() as $parameter) {
                $params[$parameter->getName()] = $parameter->getValue();
            }

            return $params;
        }

        return array();
    }

    protected function createBuildQueryTest($method, array $dqls)
    {
        $form = $this->formFactory->create(new ItemFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        // without binding the form
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());


        // bind a request to the form - 1 params
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => ''));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[1], $doctrineQueryBuilder->{$method}());


        // bind a request to the form - 2 params
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[2], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2), $this->getQueryBuilderParameters($doctrineQueryBuilder));


        // bind a request to the form - 3 params
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[3], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_enabled' => true), $this->getQueryBuilderParameters($doctrineQueryBuilder));


        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(new ItemFilterType(false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[4], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_enabled' => 1), $this->getQueryBuilderParameters($doctrineQueryBuilder));


        // bind a request to the form - date + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name' => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position' => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array('year' => 2013, 'month' => 9, 'day' => 27),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[5], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_createdAt' => '2013-09-27'), $this->getQueryBuilderParameters($doctrineQueryBuilder));


        // bind a request to the form - datetime + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(true, false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name' => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position' => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array(
                'date' => array('year' => 2013, 'month' => 9, 'day' => 27),
                'time' => array('hour' => 13, 'minute' => 21),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[6], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_createdAt' => '2013-09-27 13:21:00'), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createApplyFilterOptionTest($method, array $dqls)
    {
        $form = $this->formFactory->create(new ItemCallbackFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    protected function createNumberRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('position' => array('left_number' => 1, 'right_number' => 3)));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_position_left' => 1,
            'p_i_position_right' => 3,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createNumberRangeCompoundTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('position_selector' => array(
            'left_number' => array('text' => 4, 'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN),
            'right_number' => array('text' => 8, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
        )));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_position_selector_left' => 4,
            'p_i_position_selector_right' => 8,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createNumberRangeDefaultValuesTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('default_position' => array('left_number' => 1, 'right_number' => 3)));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_default_position_left' => 1,
            'p_i_default_position_right' => 3,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createDateRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'createdAt' => array(
                'left_date' => '2012-05-12',
                'right_date' => array('year' => '2012', 'month' => '5', 'day' => '22'),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    public function createDateTimeRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'updatedAt' => array(
                'left_datetime' => array(
                    'date' => '2012-05-12',
                    'time' => '14:55',
                 ),
                'right_datetime' => array(
                    'date' => array('year' => '2012', 'month' => '6', 'day' => '10'),
                    'time' => array('hour' => 22, 'minute' => 12)
                 ),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    public function createFilterStandardTypeTest($method, array $dqls)
    {
        $form = $this->formFactory->create(new FormType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'     => 'hey dude',
            'position' => 99,
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    protected function initQueryBuilder()
    {
        $container = $this->getContainer();

        return $container->get('lexik_form_filter.query_builder_updater');
    }

    protected function getContainer()
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new LexikFormFilterExtension());

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../vendor/symfony/framework-bundle/Symfony/Bundle/FrameworkBundle/Resources/config'));
        $loadXml->load('services.xml');

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config'));
        $loadXml->load('services.xml');
        $loadXml->load('form.xml');
        $loadXml->load('listeners.xml');

        $container->setParameter('lexik_form_filter.where_method', null);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->addCompilerPass(new FormDataExtractorPass());

        // dirty fix - force subcriber class to don't get an error in RegisterListenersPass.
        $container->getDefinition('lexik_form_filter.get_filter.doctrine_orm')->setClass($container->getParameter('lexik_form_filter.get_filter.doctrine_orm.class'));
        $container->getDefinition('lexik_form_filter.get_filter.doctrine_dbal')->setClass($container->getParameter('lexik_form_filter.get_filter.doctrine_dbal.class'));

        if (class_exists('Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\RegisterKernelListenersPass')) {
            $container->addCompilerPass(new \Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\RegisterKernelListenersPass()); // SF < 2.3
        } else {
            $container->addCompilerPass(new \Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass()); // SF 2.3
        }

        $container->compile();

        return $container;
    }
}
