<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
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
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $conn;

    public function setUp()
    {
        parent::setUp();

        $this->em = $this->getSqliteEntityManager();
        $this->conn = $this->em->getConnection();
    }

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
        $form = $this->formFactory->create(ItemFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

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
        $form = $this->formFactory->create(ItemFilterType::class);

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[2], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2), $this->getQueryBuilderParameters($doctrineQueryBuilder));

        // bind a request to the form - 3 params
        $form = $this->formFactory->create(ItemFilterType::class);

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[3], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_enabled' => true), $this->getQueryBuilderParameters($doctrineQueryBuilder));

        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'checkbox' => true,
        ));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[4], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_enabled' => 1), $this->getQueryBuilderParameters($doctrineQueryBuilder));

        // bind a request to the form - date + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'with_selector' => true,
        ));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'      => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position'  => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array('year' => 2013, 'month' => 9, 'day' => 27),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[5], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_createdAt' => new \DateTime('2013-09-27')), $this->getQueryBuilderParameters($doctrineQueryBuilder));

        // bind a request to the form - datetime + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'with_selector' => true,
            'datetime'      => true,
        ));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'      => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position'  => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array(
                'date' => array('year' => 2013, 'month' => 9, 'day' => 27),
                'time' => array('hour' => 13, 'minute' => 21),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[6], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array('p_i_position' => 2, 'p_i_createdAt' => new \DateTime('2013-09-27 13:21:00')), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createDisabledFieldTest($method, array $dqls)
    {
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'with_selector' => false,
            'disabled_name' => true,
        ));
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    protected function createApplyFilterOptionTest($method, array $dqls)
    {
        $form = $this->formFactory->create(ItemCallbackFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    protected function createNumberRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('position' => array('left_number' => 1, 'right_number' => 3)));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_position_left'  => 1,
            'p_i_position_right' => 3,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createNumberRangeCompoundTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('position_selector' => array(
            'left_number'  => array('text' => 4, 'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN),
            'right_number' => array('text' => 8, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
        )));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_position_selector_left'  => 4,
            'p_i_position_selector_right' => 8,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createNumberRangeDefaultValuesTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array('default_position' => array('left_number' => 1, 'right_number' => 3)));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
        $this->assertEquals(array(
            'p_i_default_position_left'  => 1,
            'p_i_default_position_right' => 3,
        ), $this->getQueryBuilderParameters($doctrineQueryBuilder));
    }

    protected function createDateRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'createdAt' => array(
                'left_date'  => '2012-05-12',
                'right_date' => array('year' => '2012', 'month' => '5', 'day' => '22'),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    protected function createDateRangeWithTimezoneTest($method, array $dqls)
    {
        // same dates
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array(
            'startAt' => array(
                'left_date'  => '2015-10-20',
                'right_date' => '2015-10-20',
            ),
        ));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());

        // different dates
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array(
            'startAt' => array(
                'left_date'  => '2015-10-01',
                'right_date' => '2015-10-16',
            ),
        ));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[1], $doctrineQueryBuilder->{$method}());
    }

    public function createDateTimeRangeTest($method, array $dqls)
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'updatedAt' => array(
                'left_datetime' => array(
                    'date' => '2012-05-12',
                    'time' => '14:55',
                 ),
                'right_datetime' => array(
                    'date' => array('year' => '2012', 'month' => '6', 'day' => '10'),
                    'time' => array('hour' => 22, 'minute' => 12),
                 ),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }

    public function createFilterStandardTypeTest($method, array $dqls)
    {
        $form = $this->formFactory->create(FormType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'     => 'hey dude',
            'position' => 99,
        ));

        $filterQueryBuilder->addFilterConditions($form, $doctrineQueryBuilder);
        $this->assertEquals($dqls[0], $doctrineQueryBuilder->{$method}());
    }
}
