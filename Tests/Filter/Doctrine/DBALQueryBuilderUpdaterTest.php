<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

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
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilderUpdater;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\EmbedFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\RangeFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;

/**
 * Filter query builder tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DBALQueryBuilderUpdaterTest extends DoctrineQueryBuilderUpdater
{
    public function testBuildQuery()
    {
        parent::createBuildQueryTest('getSQL', array(
            'SELECT i FROM item i',
            'SELECT i FROM item i WHERE i.name LIKE \'blabla\'',
            'SELECT i FROM item i WHERE (i.name LIKE \'blabla\') AND (i.position > 2)',
            'SELECT i FROM item i WHERE (i.name LIKE \'blabla\') AND (i.position > 2) AND (i.enabled = 1)',
            'SELECT i FROM item i WHERE (i.name LIKE \'blabla\') AND (i.position > 2) AND (i.enabled = 1)',
            'SELECT i FROM item i WHERE (i.name LIKE \'%blabla\') AND (i.position <= 2) AND (i.createdAt = \'2013-09-27\')',
        ));
    }

    public function testApplyFilterOption()
    {
        parent::createApplyFilterOptionTest('getSQL', array(
            'SELECT i FROM item i WHERE (i.name <> \'blabla\') AND (i.position <> 2)',
        ));
    }

    public function testNumberRange()
    {
        parent::createNumberRangeTest('getSQL', array(
            'SELECT i FROM item i WHERE (i.position > 1) AND (i.position < 3)',
        ));
    }

    public function testNumberRangeDefaultValues()
    {
        parent::createNumberRangeDefaultValuesTest('getSQL', array(
            'SELECT i FROM item i WHERE (i.default_position >= 1) AND (i.default_position <= 3)',
        ));
    }

    public function testDateRange()
    {
        parent::createDateRangeTest('getSQL', array(
            'SELECT i FROM item i WHERE (i.createdAt <= \'2012-05-22\') AND (i.createdAt >= \'2012-05-12\')',
        ));
    }

    protected function createDoctrineQueryBuilder()
    {
        return $this->conn
                    ->createQueryBuilder()
                    ->select('i')
                    ->from('item', 'i');
    }
}
