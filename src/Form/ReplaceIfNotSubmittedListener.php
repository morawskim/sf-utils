<?php

namespace mmo\sf\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ReplaceIfNotSubmittedListener implements EventSubscriberInterface
{
    /**
     * @var mixed
     */
    private $replaceValue;

    /**
     * @var bool
     */
    private $shouldBeReplaced;

    public function __construct($replaceValue) {
        $this->replaceValue = $replaceValue;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SUBMIT => 'preSubmit',
            FormEvents::SUBMIT => 'submit',
        ];
    }

    public function preSubmit(FormEvent $event): void {
        if (null === $event->getData()) {
            $this->shouldBeReplaced = true;
        }
    }

    public function submit(FormEvent $event): void {
        if ($this->shouldBeReplaced) {
            $value = $this->replaceValue;
            $event->setData(is_callable($value) ? $value() : $value);
        }
    }
}
