<?php
namespace ERD\SonataAdminExtensionsBundle\Admin\Annotation;

/**
 * @Annotation
 *
 * @property array $formOptions Options to pass to the symfony form component for this field. Has explicit integration
 * for the 'help' key (auto-translated) and 'choices' key on 'sonata_type_translatable_choice's (auto-guessed).
 * @property array $options Generic sonata admin fieldDescriptionOptions for this field, like edit mode etc.
 */
class FieldFormSettings extends FieldBasics
{
    const CURRENT_VIEW = self::FORM_VIEW;

    public function __construct(array $data)
    {
        static::$allowedProperties[] = 'formOptions';
        static::$allowedProperties[] = 'options';

        parent::__construct($data);
    }
}
