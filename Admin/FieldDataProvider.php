<?php
namespace ERD\SonataAdminExtensionsBundle\Admin;
use Doctrine\Common\Annotations\Reader;
use ERD\AnnotationHelpers\PowerReaderInterface;
use ERD\SonataAdminExtensionsBundle\Admin\AdminField;

/**
 * Responsible for reading all the field and fieldgroup annotations up the entity hierarchy and merging
 * them into an AdminField object for each field and a merged FieldGroup annotation object for each group.
 *
 * Those merged objects are then available from getCompositeData().
 */
class FieldDataProvider
{
    protected $reader;
    protected $fieldAnnotationClass;
    protected $fieldGroupAnnotationClass;

    public function __construct(PowerReaderInterface $annotationReader, $fieldAnnotationClass, $fieldGroupAnnotationClass)
    {
        $this->reader = $annotationReader;
        $this->fieldAnnotationClass = $fieldAnnotationClass;
        $this->fieldGroupAnnotationClass = $fieldGroupAnnotationClass;
    }

    public function getCompositeData($entityClass)
    {
        return ['fields'=>$this->getCompositeAdminFields($entityClass),
                'groups'=>$this->getCompositeGroupAnnotations($entityClass)];
    }

    protected function getCompositeGroupAnnotations($entityClass)
    {
        $reflClass = new \ReflectionClass($entityClass);
        $annotations = array(); //All class annotations from the whole hierarchy

        //get the class annotations recursively
        do
        {
            $classAnnotations = $this->reader->getClassAnnotations($reflClass, $this->fieldGroupAnnotationClass);

            foreach($classAnnotations as $annotation)
            {
                if(!isset($annotations[$annotation->name]))
                {
                    $annotations[$annotation->name] = [];
                }

                $annotations[$annotation->name][] = $annotation;
            }
        }
        while(($reflClass = $reflClass->getParentClass()));

        //merge them for each group.
        foreach($annotations as $groupName=>$groupAnnotations)
        {
            $annotations[$groupName] = $this->mergeGroupAnnotations($groupAnnotations);
        }

        return $annotations;
    }

    protected function getCompositeAdminFields($entityClass)
    {
        $reflClass = new \ReflectionClass($entityClass);
        $adminFields = array();

        //gather all the field annotations for all the properties by looping up the inheritance hierarchy
        do
        {
            foreach($reflClass->getProperties() as $reflProp)
            {
                $propName = $reflProp->getName();
                $annotations = $this->reader->getPropertyAnnotationsFromClass($reflProp, $reflClass, $this->fieldAnnotationClass);

                foreach($annotations as $annotation)
                {
                    if(!isset($adminFields[$propName]))
                    {
                        $adminFields[$propName] = new AdminField();
                    }

                    $adminFields[$propName]->mergeIn($annotation, false);
                }
            }
        }
        while($reflClass = $reflClass->getParentClass());


        return $adminFields;
    }

    protected function mergeGroupAnnotations($annotations)
    {
        if(count($annotations) < 2) { return $annotations[0]; }

        for($i=1, $len=count($annotations); $i<$len; $i++)
        {
            $annotations[0]->settings = array_merge($annotations[$i]->settings, $annotations[0]->settings);
            $annotations[0]->fieldSettings = array_merge($annotations[$i]->fieldSettings, $annotations[0]->fieldSettings);
        }

        return $annotations[0];
    }
}