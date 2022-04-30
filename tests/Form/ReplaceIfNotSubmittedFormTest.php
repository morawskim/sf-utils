<?php

namespace mmo\sf\tests\Form;

use mmo\sf\tests\data\FormToTestReplaceValueDto;
use mmo\sf\tests\data\FormToTestReplaceValueType;
use mmo\sf\tests\data\PersonDto;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\Validator\Validation;

class ReplaceIfNotSubmittedFormTest extends FormIntegrationTestCase
{
    /**
     * @var FormBuilder
     */
    protected $builder;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    private function doSetUp(): void
    {
        parent::setUp();

        $this->dispatcher = new EventDispatcher();
        $this->builder = new FormBuilder('', null, $this->dispatcher, $this->factory);
    }

    public function testCreateNewRecordWithPerson(): void
    {
        $values = [
            'text' => 'foo',
            'person' => [
                'firstName' => 'Bar',
                'lastName' => 'Baz',
            ]
        ];

        $expected = [
            'text' => 'foo',
            'person' => PersonDto::fromValues($values['person']['firstName'], $values['person']['lastName'])
        ];

        $form = $this->factory->create(FormToTestReplaceValueType::class);
        $form->submit($values);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $form->getData());
    }

    public function testSetNullForPersonOnUpdateWhenSendNullSubmitWithoutClearMissing(): void
    {
        $values = [
            'text' => 'foo',
            'person' => PersonDto::fromValues('Bar', 'Baz')
        ];
        $toSend = $values;
        $toSend['person'] = null;
        $expected = $toSend;

        $form = $this->factory->create(FormToTestReplaceValueType::class, $values);
        $form->submit($toSend, false);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $form->getData());
    }

    public function testFormIsInvalidWhenSubmitAndCleanMissingField(): void
    {
        $values = [
            'text' => 'foo',
            'person' => PersonDto::fromValues('Bar', 'Baz')
        ];
        $toSend = ['text' => 'foo', 'person' => null];

        $form = $this->factory->create(FormToTestReplaceValueType::class, $values);
        $form->submit($toSend);

        $this->assertFalse($form->isValid());
        $this->assertTrue($form->isSynchronized());
    }

    public function testUpdatePersonRecord(): void
    {
        $values = [
            'text' => 'foo',
            'person' => PersonDto::fromValues('Bar', 'Baz')
        ];

        $toSend = [
            'text' => $values['text'],
            'person' => [
                'firstName' => $values['person']->firstName,
                'lastName' => 'New'
            ]
        ];
        $expected = [
            'text' => $values['text'],
            'person' => PersonDto::fromValues('Bar', 'New')
        ];

        $form = $this->factory->create(FormToTestReplaceValueType::class, $values);
        $form->submit($toSend);

        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $form->getData());
    }

    public function testUpdatePersonValidator(): void
    {
        $values = [
            'text' => 'foo',
            'person' => PersonDto::fromValues('Bar', 'Baz')
        ];

        $toSend = [
            'text' => $values['text'],
            'person' => [
                'firstName' => $values['person']->firstName,
                'lastName' => ''
            ]
        ];

        $form = $this->factory->create(FormToTestReplaceValueType::class, $values);
        $form->submit($toSend);

        $this->assertFalse($form->isValid());
        $this->assertTrue($form->isSynchronized());
        $this->assertFalse($form->get('person')->get('lastName')->isValid());
        $this->assertSame('This value should not be blank.', $form->get('person')->get('lastName')->getErrors()[0]->getMessage());
    }

    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->getValidator();

        return [
            new ValidatorExtension($validator)
        ];
    }
}
