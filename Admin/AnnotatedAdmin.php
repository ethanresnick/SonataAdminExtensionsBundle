<?php
namespace ERD\SonataAdminExtensionsBundle\Admin;

use ERD\SonataAdminExtensionsBundle\Admin\Annotation\FieldBasics;
use ERD\SonataAdminExtensionsBundle\Admin\Annotation\FieldGroup;
use ERD\SonataAdminExtensionsBundle\Admin\Annotation\FieldFilterSettings;
use ERD\SonataAdminExtensionsBundle\Admin\AdminHelpers;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Builds an Admin class from a collection of annotations read from the entity.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Sep 16, 2012 Ethan Resnick Design
 */
class AnnotatedAdmin extends Admin
{
    /**
     * @var boolean Have the adminFieldViewObjects been built yet? We can't build them in the constructor because the
     * DI container won't have injected some of the needed services yet, so we do it lazily and track it in this variable.
     */
    protected $adminFieldViewObjectsBuilt = false;

    /**
     * @var array[] The keys are the names of the view constants and the values are each arrays with keys
     * for all the properties that belong in that view. The value of those keys is the annotation object
     * describing that field's (merged) settings in that view.
     */
    protected $adminFieldViewObjects;

    /**
     * @var FieldGroup[] Keys are field group names, each value is a FieldGroup annotation holding the
     * merged settings for that group.
     */
    protected $groupAnnotations;

    /**
     * @var AdminHelpers An instance of a helper class that provides some convenience features for our admin.
     */
    protected $helpers;

    public function __construct($code, $class, $baseControllerName, array $compositeAnnotationData, AdminHelpers $helpers)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->groupAnnotations = $compositeAnnotationData['groups'];
        $this->adminFieldViewObjects = $compositeAnnotationData['fields'];
        $this->helpers = $helpers;
    }

    protected function buildAdminFieldViewObjects()
    {
        if($this->adminFieldViewObjectsBuilt) { return; }

        //the constructor just dumps the raw field data into the
        //property to start, so we read it out and go from there.
        $adminFields = $this->adminFieldViewObjects;

        foreach(FieldBasics::getViews() as $view)
        {
            $this->adminFieldViewObjects[$view] = array();
            $inForm = ($view==FieldBasics::FORM_VIEW);

            foreach($adminFields as $propName=>$fieldObject)
            {
                $viewObject = $fieldObject->getViewObject($view);

                if(!$viewObject) { continue; }

                //Add settings that may have been specified on this field's fieldGroups
                if($inForm && isset($viewObject->group) && isset($this->groupAnnotations[$viewObject->group]))
                {
                    $viewObject->mergeIn(['formOptions'=>$this->groupAnnotations[$viewObject->group]->fieldSettings], false);
                }

                //run all labels and help messages through the translator
                if(isset($viewObject->label)) { $viewObject->label = $this->trans($viewObject->label); }
                foreach(['help','label'] as $key)
                {
                    if(isset($viewObject->formOptions[$key]))
                    {
                        //I can't just set the key directly because formOptions is an overloaded property. ugh.
                        $newOptions = $viewObject->formOptions;
                        $newOptions[$key] = $this->trans($newOptions[$key]);
                        $viewObject->formOptions = $newOptions;
                    }
                }

                $this->adminFieldViewObjects[$view][$propName] = $viewObject;
            }

            //order all the admin fields in this view
            uasort($this->adminFieldViewObjects[$view], array($this, 'sortByPriority'));

            //mark as built
            $this->adminFieldViewObjectsBuilt = true;
        }
    }

    /**
     * A function that can be used as a callback to sort the field annotations by priority.
     *
     * @param $a FieldBasics The first field's merged annotation object (FieldBasics or a subclass)
     * @param $b FieldBasics The second field merged annotation object
     *
     * @return int Whether $a should come earlier (-1), later (1), or at the same place (0) in the sorted array.
     */
    protected function sortByPriority(FieldBasics $a, FieldBasics $b)
    {
        if(isset($a->priority) && isset($b->priority))
        {
            if ($a->priority == $b->priority)
            {
                return 0;
            }

            //higher priorities (lower priority values) come earlier
            return ($a->priority < $b->priority) ? -1 : 1;
        }

        elseif(!isset($a->priority) && !isset($b->priority))
        {
            return 0; //none have priority, so they're equal.
        }

        else //only a or b will have priority
        {
            //return the one with the priority first (so the unprioritized ones go to the end)
            return isset($a->priority) ? -1 : 1;
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->buildAdminFieldViewObjects();

        $this->configureShowOrFormFields($showMapper, FieldBasics::SHOW_VIEW);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->buildAdminFieldViewObjects();

        $this->configureShowOrFormFields($formMapper, FieldBasics::FORM_VIEW);
    }

    protected function configureDatagridFilters(DatagridMapper $dataGridMapper)
    {
        $this->buildAdminFieldViewObjects();

        foreach($this->adminFieldViewObjects[FieldBasics::FILTER_VIEW] as $propName=>$settings)
        {
            $filterType = isset($settings->filterType) ? $settings->filterType : null;
            $filterOptions = $this->getFilterOptions($settings, $propName);
            $fieldOptions = $this->getFormOptions($settings, $propName);

            $dataGridMapper->add($propName, $filterType, $filterOptions, $settings->type, $fieldOptions);
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $this->buildAdminFieldViewObjects();

        foreach($this->adminFieldViewObjects[FieldBasics::LIST_VIEW] as $propName=>$settings)
        {
            if($settings->identifier)
            {
                $listMapper->addIdentifier($propName, $settings->type, $this->getFieldDescriptionOptions($settings, $propName));
            }
            else
            {
                $listMapper->add($propName, $settings->type, $this->getFieldDescriptionOptions($settings, $propName));
            }
        }

        $listMapper->add('_action', 'actions', array(
            'actions' => array(
                'view' => array(),
                'edit' => array(),
                'delete' => array(),
            )
        ));
    }

    protected function configureShowOrFormFields($mapper, $mode)
    {
        foreach($this->adminFieldViewObjects[$mode] as $propName=>$settings)
        {
            $group = $settings->group;
            $fieldDescriptionOptions = $this->getFieldDescriptionOptions($settings, $propName);

            if($group)
            {
                $groupOptions = isset($this->groupAnnotations[$group]) ? $this->groupAnnotations[$group]->settings : [];
                $mapper = $mapper->with($group, $groupOptions);
            }

            if($mode==FieldBasics::FORM_VIEW)
            {
                /** @var $mapper FormMapper */
                $mapper->add($propName, $settings->type, $this->getFormOptions($settings, $propName), $fieldDescriptionOptions);
            }
            else
            {
                /** @var $mapper ShowMapper */
                $mapper->add($propName, $settings->type, $fieldDescriptionOptions);
            }

            if($group)
            {
                $mapper->end();
            }
        }
    }

    protected function getFormOptions(FieldBasics $settings, $propName)
    {
        $options = (isset($settings->formOptions)) ? $settings->formOptions : [];
        if(isset($settings->label) && !isset($options['label'])) { $options['label'] = $settings->label; }

        return $this->helpers->expandFormOptions($propName, $settings->type, $this, $options);
    }

    protected function getFieldDescriptionOptions(FieldBasics $settings, $propName)
    {
        $options = (isset($settings->options)) ? $settings->options : [];
        if(isset($settings->label) && !isset($options['label'])) { $options['label'] = $settings->label; }

        return $options;
    }

    protected function getFilterOptions(FieldFilterSettings $settings, $propName)
    {
        $options = isset($settings->filterOptions) ? $settings->filterOptions : [];
        //the below probably shouldn't work, but because of how sonata ultimately does the merging, it does.
        if(isset($settings->label) && !isset($options['label'])) { $options['label'] = $settings->label; }

        return $this->helpers->expandFilterOptions($propName, $settings->type, $this, $options);
    }
}