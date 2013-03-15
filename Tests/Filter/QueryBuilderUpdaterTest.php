<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\RegisterKernelListenersPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Lexik\Bundle\FormFilterBundle\DependencyInjection\LexikFormFilterExtension;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FilterTransformerCompilerPass;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdater;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemEmbeddedOptionsFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\RangeFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;

/**
 * Filter query builder tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilderUpdaterTest extends TestCase
{
    public function testBuildQuery()
    {
        $form = $this->formFactory->create(new ItemFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        // without any params
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // 1 params
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'blabla', 'position' => ''));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'blabla\'';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // 2 params
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'blabla', 'position' => 2));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'blabla\' AND i.position > 2';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // 3 params
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'blabla\' AND i.position > 2 AND i.enabled = 1';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(new ItemFilterType(false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'blabla\' AND i.position > 2 AND i.enabled = 1';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // date + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array(
            'name' => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position' => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array('year' => 2013, 'month' => 9, 'day' => 27),
        ));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'%blabla\' AND i.position <= 2 AND i.createdAt = \'2013-09-27\'';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());


        // datetime + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(true, false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array(
            'name' => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position' => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array(
                'date' => array('year' => 2013, 'month' => 9, 'day' => 27),
                'time' => array('hour' => 13, 'minute' => 21),
            ),
        ));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE \'%blabla\' AND i.position <= 2 AND i.createdAt = \'2013-09-27 13:21:00\'';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testApplyFilterOption()
    {
        $form = $this->formFactory->create(new ItemCallbackFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'blabla', 'position' => 2));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name <> \'blabla\' AND i.position <> 2';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testNumberRange()
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('position' => array('left_number' => 1, 'right_number' => 3)));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.position > 1 AND i.position < 3';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testNumberRangeDefaultValues()
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('default_position' => array('left_number' => 1, 'right_number' => 3)));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.default_position >= 1 AND i.default_position <= 3';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);

        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testDateRange()
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array(
            'createdAt' => array(
                'left_date' => array('year' => '2012', 'month' => '5', 'day' => '12'),
                'right_date' => array('year' => '2012', 'month' => '5', 'day' => '22'),
            ),
        ));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.createdAt <= \'2012-05-22\' AND i.createdAt >= \'2012-05-12\'';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testDateTimeRange()
    {
        // use filter type options
        $form = $this->formFactory->create(new RangeFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array(
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

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.updatedAt <= \'2012-06-10 22:12:00\' AND i.updatedAt >= \'2012-05-12 14:55:00\'';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testEmbedFormFilter()
    {
        // doctrine query builder without any joins
        $form = $this->formFactory->create(new ItemEmbeddedOptionsFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->bind(array('name' => 'dude', 'options' => array('label' => 'color', 'rank' => 3)));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i';
        $expectedDql .= ' LEFT JOIN i.options opt WHERE i.name LIKE \'dude\' AND opt.label LIKE \'color\' AND opt.rank = 3';
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);

        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());

        // doctrine query builder with joins
        $form = $this->formFactory->create(new ItemEmbeddedOptionsFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $doctrineQueryBuilder->leftJoin('i.options', 'o');
        $form->bind(array('name' => 'dude', 'options' => array('label' => 'size', 'rank' => 5)));

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i';
        $expectedDql .= ' LEFT JOIN i.options o WHERE i.name LIKE \'dude\' AND o.label LIKE \'size\' AND o.rank = 5';

        $filterQueryBuilder->setParts(array('i.options' => 'o'));
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);

        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    /**
     * Initialize a doctrine query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createDoctrineQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from('Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity', 'i');
    }

    /**
     * Returns query builder updater.
     *
     * @return FilterBuilderUpdater
     */
    protected function initQueryBuilder()
    {
        $container = $this->getContainer();

        return $container->get('lexik_form_filter.query_builder_updater');
    }

    /**
     * Create a container instance with required element for run tests.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder();
        $filter = new LexikFormFilterExtension();
        $container->registerExtension($filter);

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../vendor/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/config'));
        $loadXml->load('services.xml');

        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loadXml->load('services.xml');
        $loadXml->load('form_types.xml');
        $loadXml->load('doctrine/orm/filters.xml');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->addCompilerPass(new RegisterKernelListenersPass());
        $container->addCompilerPass(new FilterTransformerCompilerPass());
        $container->compile();

        return $container;
    }
}
