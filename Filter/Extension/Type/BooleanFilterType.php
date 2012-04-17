<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractType implements FilterTypeInterface
{
    const VALUE_YES = 'y';
    const VALUE_NO  = 'n';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'choices' => array(
                self::VALUE_YES  => $this->trans('boolean.yes'),
                self::VALUE_NO   => $this->trans('boolean.no'),
            ),
            'empty_value' => $this->trans('boolean.yes_or_no'),
        );
    }

    /**
     * Set Translator
     *
     * @param Translator $translator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Translate a key for a domain
     *
     * @param string $key
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    private function trans($key, array $parameters = array(), $domain = 'LexikFormFilterBundle')
    {
        return $this->translator->trans($key, $parameters, $domain);
    }

    public function getTransformerId()
    {
        return 'default';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $queryBuilder->getRootAlias(), $field, $paramName))
                ->setParameter($paramName, (int) (self::VALUE_YES == $values['value']), \PDO::PARAM_BOOL);
        }
    }
}