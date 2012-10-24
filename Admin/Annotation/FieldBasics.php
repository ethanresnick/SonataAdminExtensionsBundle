<?php
namespace ERD\SonataAdminExtensionsBundle\Admin\Annotation;

/**
 * The annotation that stores the basic settings of how an entity's property will be handled in the admin ui.
 *
 * This class doesn't store each field in a property, because, if we did that, then we couldn't easily
 * distinguish between a property that was explicitly set to null in the annotation and one that was
 * simply not set in the annotation (they'd both have the value null in the object). So we use an array
 * instead, and a property that wasn't set simply won't exist as a key on the array, while one set null
 * explicitly would exist as a key but with the value null. (We're faking a bit of JS-like dynamism.)
 *
 * @Annotation
 *
 * @property array $excludeFrom View(s) to exclude the field from. Allowed values are any of the view constants or "all".
 * @property string $for Name of the property this annotation is for. (Only applicable when used as a class annotation
 *                       to override a parent annotation w/o having to redefine the property in the subclass).
 * @property int $priority How important the field is. Lower numbers will be shown higher/earlier in forms/lists.
 * @property string $type (Optional) The name of this field's form type. Will be guessed if left unset.
 * @property string $group (Optional) The (untranslated) name of the group to put this field in.
 * @property string $publicName (Optional) Name of the property in accessors, if different from its name in the class.
 * @property string $label (Optional) What to label this field. Will be run through the translator.
 * @property boolean $identifier Whether this field uniquely (perhaps not literally but at least from the user's
 *                               perspective) identifies the entity. Like the title of an article, for instance.
 */
class FieldBasics
{
    const CURRENT_VIEW = 'basic';
    const FORM_VIEW   = 'form';
    const FILTER_VIEW = 'filter';
    const SHOW_VIEW   = 'show';
    const LIST_VIEW   = 'list';

    public static function getViews()
    {
        return [static::FORM_VIEW, static::FILTER_VIEW, static::SHOW_VIEW, static::LIST_VIEW];
    }

    /**
     * @var array All the data on this annotation, in the form propName => value.
     */
    protected $data = [];

    /**
     * @var array All the allowed keys in this annotation.
     */
    protected static $allowedProperties = ['excludeFrom','for','priority','type','group','publicName','identifier','label'];

    /**
     * Puts the options set on the annotation into the annotation directly with a few simple transformations:
     *
     * 1). The default "value" key becomes the priority if no priority is otherwise set, allowing for concision.
     *
     * @param array $data The options set on the annotation
     */
    public function __construct(array $data)
    {
        foreach($data as $key=>$value)
        {
            if(in_array($key, static::$allowedProperties))
            {
                $this->data[$key] = $value;
            }
        }

        if(isset($data['value']) && !isset($data['priority']))
        {
            $this->data['priority'] = $data['value'];
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if(array_key_exists($name, $this->data))
        {
            return $this->data[$name];
        }
    }

    public function __set($name, $value)
    {
        if(in_array($name, static::$allowedProperties))
        {
            $this->data[$name] = $value;
        }
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function getCurrentProperties()
    {
        return array_keys($this->data);
    }

    /**
     * @param FieldBasics|array $from
     * @param bool $overwrite Overwrite existing values in this annotation when there's a conflict?
     */
    public function mergeIn($from, $overwrite=true)
    {
        $isArray = is_array($from);
        $props = (!$isArray) ? $from->getCurrentProperties() : array_keys($from);

        foreach($props as $prop)
        {
            $fromVal = ($isArray) ? $from[$prop] : $from->{$prop};

            //if the property doesn't exist in $to, we need to add it, regardless of overwrite
            //if it does exist and its not an array (so no opportunity to merge $to and $from)
            //we decide whether to overwrite its value depending solely on $overwrite.
            if(!isset($this->{$prop}) || (!is_array($this->{$prop}) && $overwrite))
            {
                $this->{$prop} = $fromVal;
            }

            //if the property exists in $to but it's an array, we merge the values, but we use
            //$overwrite to decide which value wins if there's a key conflict.
            elseif(is_array($this->{$prop}))
            {
                if($overwrite)
                {
                    $this->{$prop} = array_merge($this->{$prop}, $fromVal);
                }
                else
                {
                    $this->{$prop} = array_merge($fromVal, $this->{$prop});
                }
            }
        }
    }
}
