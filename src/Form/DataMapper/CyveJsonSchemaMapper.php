<?php

namespace mmo\sf\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * The default implementation of data mapper (PropertyPathMapperTest) also set array key when the value is null
 *
 * This is a problem when fields in JsonSchema are not required.
 * Schema validator doesn't check whether the field value is set. This is something different from the Symfony Validator component.
 * In Symfony if the field value is the null or empty string, the validation is skipped.
 * In JsonSchemaValidator event optional field with value null must match validation rules.
 * Also cyve/json-schema-form-bundle not supported multiple types of field.
 *
 * @link https://github.com/cyve/json-schema-form-bundle/blob/805cc45c0acf380f2d83de24fc8108b2e473aade/src/Form/Helper/FormHelper.php#L28
 */
class CyveJsonSchemaMapper implements DataMapperInterface
{
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        $empty = null === $data || [] === $data;

        if (!$empty && !\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            if (!$empty && null !== $propertyPath && $config->getMapped()) {
                $form->setData($this->getPropertyValue($data, $propertyPath));
            } else {
                $form->setData($config->getData());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        if (null === $data) {
            return;
        }

        if (!\is_array($data) && !\is_object($data)) {
            throw new UnexpectedTypeException($data, 'object, array or empty');
        }

        foreach ($forms as $form) {
            $propertyPath = $form->getPropertyPath();
            $config = $form->getConfig();

            // Write-back is disabled if the form is not synchronized (transformation failed),
            // if the form was not submitted and if the form is disabled (modification not allowed)
            if (null !== $propertyPath && $config->getMapped() && $form->isSubmitted() && $form->isSynchronized() && !$form->isDisabled()) {
                $propertyValue = $form->getData();
                // If the field is of type DateTimeInterface and the data is the same skip the update to
                // keep the original object hash
                if ($propertyValue instanceof \DateTimeInterface && $propertyValue == $this->getPropertyValue($data, $propertyPath)) {
                    continue;
                }

                // If the data is identical to the value in $data, we are
                // dealing with a reference
                if (!$config->getByReference() || $propertyValue !== $this->getPropertyValue($data, $propertyPath)) {
                    $this->propertyAccessor->setValue($data, $propertyPath, $propertyValue);
                }
            }
        }
    }

    private function getPropertyValue($data, $propertyPath)
    {
        try {
            return $this->propertyAccessor->getValue($data, $propertyPath);
        } catch (AccessException $e) {
            if (!$e instanceof UninitializedPropertyException
                // For versions without UninitializedPropertyException check the exception message
                && (class_exists(UninitializedPropertyException::class) || false === strpos($e->getMessage(), 'You should initialize it'))
            ) {
                throw $e;
            }

            return null;
        }
    }
}
