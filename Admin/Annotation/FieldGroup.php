<?php
namespace ERD\SonataAdminExtensionsBundle\Admin\Annotation;

/**
 * The annotation used to provide settings for a Field Group.
 * @Annotation
 */
class FieldGroup
{
    /**
     * @var string The (untranslated) name of the group
     */
    public $name;

    /**
     * @var array The settings for the group
     */
    public $settings;

    /**
     * @var array Settings that should be cascaded to every form field in the group.
     * Offering the ability to set these here is just a nice convenience.
     */
    public $fieldSettings;

    public function __construct(array $data)
    {
        $this->name = isset($data['name']) ? $data['name'] : $data['value'];
        $this->fieldSettings = isset($data['fieldSettings']) ? $data['fieldSettings'] : [];

        //remove name, value, etc. and all that's left are just plain settings.
        unset($data['name']);
        unset($data['value']);
        unset($data['fieldSettings']);
        $this->settings = $data;
    }
}