<?php

namespace mmo\sf\tests\data;

use mmo\sf\Form\ReplaceIfNotSubmittedListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FormToTestReplaceValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('text', TextType::class);
        $builder->add('person', PersonType::class, [
            'required' => false,
        ]);
        $builder->get('person')->addEventSubscriber(new ReplaceIfNotSubmittedListener(null));
    }
}
