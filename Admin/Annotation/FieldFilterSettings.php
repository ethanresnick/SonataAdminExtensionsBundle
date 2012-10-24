<?php
namespace ERD\SonataAdminExtensionsBundle\Admin\Annotation;

/**
 * @Annotation
 * @property string $filterType Name of the service for the filter_type; guessed if left unset.
 * @property string $filterOptions Options for the filter.
 */
class FieldFilterSettings extends FieldBasics
{
    const CURRENT_VIEW = self::FILTER_VIEW;

    public function __construct(array $data)
    {
        static::$allowedProperties[] = 'filterType';
        static::$allowedProperties[] = 'filterOptions';

        parent::__construct($data);
    }
}