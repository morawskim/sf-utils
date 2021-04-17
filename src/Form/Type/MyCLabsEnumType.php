<?php

namespace mmo\sf\Form\Type;

use mmo\sf\Form\DataTransformer\ValueToMyCLabsEnumTransformer;
use MyCLabs\Enum\Enum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyCLabsEnumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ValueToMyCLabsEnumTransformer($options['enum_class']);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                /** @var Enum|string $enumClass */
                $enumClass = $options['enum_class'];

                return $enumClass::toArray();
            }
        ]);

        $resolver
            ->setRequired('enum_class')
            ->setAllowedValues('enum_class', static function ($value) {
                return is_a($value, Enum::class, true);
            })
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
