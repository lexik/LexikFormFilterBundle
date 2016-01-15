<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Register listeners to compute conditions to be applied on a Doctrine ORM query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineORMSubscriber extends AbstractDoctrineSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // Lexik form filter types
            'lexik_form_filter.apply.orm.filter_boolean'        => array('filterBoolean'),
            'lexik_form_filter.apply.orm.filter_checkbox'       => array('filterCheckbox'),
            'lexik_form_filter.apply.orm.filter_choice'         => array('filterValue'),
            'lexik_form_filter.apply.orm.filter_date'           => array('filterDate'),
            'lexik_form_filter.apply.orm.filter_date_range'     => array('filterDateRange'),
            'lexik_form_filter.apply.orm.filter_datetime'       => array('filterDateTime'),
            'lexik_form_filter.apply.orm.filter_datetime_range' => array('filterDateTimeRange'),
            'lexik_form_filter.apply.orm.filter_entity'         => array('filterEntity'),
            'lexik_form_filter.apply.orm.filter_number'         => array('filterNumber'),
            'lexik_form_filter.apply.orm.filter_number_range'   => array('filterNumberRange'),
            'lexik_form_filter.apply.orm.filter_text'           => array('filterText'),

            // Symfony2 types
            'lexik_form_filter.apply.orm.text'     => array('filterText'),
            'lexik_form_filter.apply.orm.email'    => array('filterValue'),
            'lexik_form_filter.apply.orm.integer'  => array('filterValue'),
            'lexik_form_filter.apply.orm.money'    => array('filterValue'),
            'lexik_form_filter.apply.orm.number'   => array('filterValue'),
            'lexik_form_filter.apply.orm.percent'  => array('filterValue'),
            'lexik_form_filter.apply.orm.search'   => array('filterValue'),
            'lexik_form_filter.apply.orm.url'      => array('filterValue'),
            'lexik_form_filter.apply.orm.choice'   => array('filterValue'),
            'lexik_form_filter.apply.orm.entity'   => array('filterEntity'),
            'lexik_form_filter.apply.orm.country'  => array('filterValue'),
            'lexik_form_filter.apply.orm.language' => array('filterValue'),
            'lexik_form_filter.apply.orm.locale'   => array('filterValue'),
            'lexik_form_filter.apply.orm.timezone' => array('filterValue'),
            'lexik_form_filter.apply.orm.date'     => array('filterDate'),
            'lexik_form_filter.apply.orm.datetime' => array('filterDate'),
            'lexik_form_filter.apply.orm.birthday' => array('filterDate'),
            'lexik_form_filter.apply.orm.checkbox' => array('filterValue'),
            'lexik_form_filter.apply.orm.radio'    => array('filterValue'),
        );
    }

    /**
     * @param GetFilterConditionEvent $event
     * @throws \Exception
     */
    public function filterEntity(GetFilterConditionEvent $event)
    {
        $expr   = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (is_object($values['value'])) {
            $paramName = $this->generateParameterName($event->getField());
            $filterField = $event->getField();

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $event->getQueryBuilder();

            if ($dqlFrom = $event->getQueryBuilder()->getDQLPart('from')) {
                $rootPart = reset($dqlFrom);
                $fieldName = ltrim($event->getField(), $rootPart->getAlias() . '.');
                $metadata = $queryBuilder->getEntityManager()->getClassMetadata($rootPart->getFrom());

                if (isset($metadata->associationMappings[$fieldName]) && (!$metadata->associationMappings[$fieldName]['isOwningSide'] || $metadata->associationMappings[$fieldName]['type'] === ClassMetadataInfo::MANY_TO_MANY)) {
                    if (!$event->getFilterQuery()->hasJoinAlias($fieldName)) {
                        $queryBuilder->leftJoin($event->getField(), $fieldName);
                    }

                    $filterField = $fieldName;
                }
            }

            if ($values['value'] instanceof Collection) {
                $ids = array();

                foreach ($values['value'] as $value) {
                    $ids[] = $this->getValueIdentifier($value);
                }

                if (count($ids) > 0) {
                    $event->setCondition(
                        $expr->in($filterField, ':'.$paramName),
                        array($paramName => array($ids, Connection::PARAM_INT_ARRAY))
                    );
                }
            } else {
                $event->setCondition(
                    $expr->eq($filterField, ':'.$paramName),
                    array($paramName => array($this->getValueIdentifier($values['value']), Type::INTEGER))
                );
            }
        }
    }

    /**
     * Get identifier of an object, with getter or `id` attribute.
     *
     * @param $value
     * @return integer
     * @throws \RuntimeException
     */
    private function getValueIdentifier($value)
    {
        if (is_callable(array($value, 'getId'))) {
            return $value->getId();
        } elseif (isset($value->id)) {
            return $value->id;
        }

        throw new \RuntimeException(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($value)));
    }
}
