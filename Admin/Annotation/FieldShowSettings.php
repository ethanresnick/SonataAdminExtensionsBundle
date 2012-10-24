<?php
namespace ERD\SonataAdminExtensionsBundle\Admin\Annotation;

/**
 * @Annotation
 * @property array $options Generic sonata admin fieldDescriptionOptions for this field.
 */
class FieldShowSettings extends FieldBasics
{
    const CURRENT_VIEW = self::SHOW_VIEW;

    public function __construct(array $data)
    {
        static::$allowedProperties[] = 'options';

        parent::__construct($data);
    }
}