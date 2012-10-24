<?php
namespace ERD\SonataAdminExtensionsBundle\Admin;
use Symfony\Component\Validator\Validator;
use Sonata\AdminBundle\Admin\Admin;

/**
 * Helper functions for the AnnotatedAdmin class.
 *
 * AnnotatedAdmin's main goal is to take annotation information and turn it into an admin class,
 * so we don't want to pollute it with miscellaneous helpers that do things like autocomplete
 * default values. At the same time, I want AnnotatedAdmin to have those helpers' features
 * (they're convenient) and don't want to subclass it (the subclass would likely have to copy
 * too much code), so I decided to create this helpers class and then just inject it to
 * AnnotatedAdmin. That'll make AnnotatedAdmin's implementation of these features lighter and at
 * least a little less coupled (though it's going to be a somewhat tight coupling by necessity).
 */
class AdminHelpers
{
    /**
     * @var Validator
     */
    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function expandFormOptions($property, $type, Admin $admin, $currentOptions)
    {
        if(($type=='sonata_type_translatable_choice' || $type=='choice') && !isset($currentOptions['choices']))
        {
            $currentOptions['choices'] = $this->getAllowedChoices($property, $admin, false);
        }

        return $currentOptions;
    }

    public function expandFilterOptions($property, $type, Admin $admin, $currentOptions)
    {
        return $currentOptions;
    }

    protected function getAllowedChoices($property, Admin $admin, $translateAutomatically = false)
    {
        $classMetadata = $this->validator->getMetadataFactory()->getClassMetadata($admin->getClass());
        $propertyMetadatas = $classMetadata->getMemberMetadatas($property);

        $choices = array();

        foreach($propertyMetadatas as $thisMetadata)
        {
            $constraints = $thisMetadata->getConstraints();

            foreach($constraints as $thisConstraint)
            {
                if($thisConstraint instanceof \Symfony\Component\Validator\Constraints\Choice)
                {
                    $choices = $thisConstraint->choices;
                }
            }
        }

        $values = array();
        foreach($choices as $thisChoice)
        {
            $values[] = 'entity.'.strtolower($admin->getClassnameLabel()).'.choices.'.$thisChoice;
        }

        if($translateAutomatically)
        {
            foreach($values as &$thisValue)
            {
                $thisValue = $admin->trans($thisValue, array('%class%'=>$this->getClassnameLabel()));
            }
        }

        $choices = array_combine($choices, $values);

        return $choices;
    }
}