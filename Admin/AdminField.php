<?php
namespace ERD\SonataAdminExtensionsBundle\Admin;
use  \ERD\SonataAdminExtensionsBundle\Admin\Annotation\FieldBasics;
use ERD\SonataAdminExtensionsBundle\Admin\Annotation as Annotation;

/**
 * Our annotation classes make good containers for the data read directly out of the annotations,
 * but this class offers a bit more of a structure for working with the data after that.
 *
 * It stores a single instance of each kind of annotation (i.e. Basics, FormSettings, etc.), into
 * which all the instances of that kind of annotation (i.e. from up the inerhitance hierarchy) are merged.
 * Then it provides a simple facade for accessing and working with those merged collections.
 */
class AdminField
{
    /**
     * @var FieldBasics An object into which we'll merge all the basic settings annotations for this field.
     */
    protected $basicProperties;

    /**
     * @var array Keys are our view constants, values are the canonical (i.e. merged) annotation instance for that view.
     */
    protected $viewSpecificProperties = [];

    public function __construct()
    {
        $this->basicProperties  = new FieldBasics([]);

        $this->viewSpecificProperties = [
            FieldBasics::FORM_VIEW   => new Annotation\FieldFormSettings([]),
            FieldBasics::SHOW_VIEW   => new Annotation\FieldShowSettings([]),
            FieldBasics::LIST_VIEW   => new Annotation\FieldListSettings([]),
            FieldBasics::FILTER_VIEW => new Annotation\FieldFilterSettings([])
        ];
    }

    public function hasView($view)
    {
        $legalView = (in_array($view, FieldBasics::getViews()));
        $fieldIsShown = (!in_array('all', (array) $this->basicProperties->excludeFrom));
        $nonExcludedView = (!in_array($view, (array) $this->basicProperties->excludeFrom));

        return $legalView && $fieldIsShown && $nonExcludedView;
    }

    public function getViewObject($view)
    {
        if(!$this->hasView($view)) { return false; }

        $viewObject = clone $this->viewSpecificProperties[$view];
        $viewObject->mergeIn($this->basicProperties, false);

        //unset properties that outside here.
        unset($viewObject->for);
        unset($viewObject->excludeFrom);

        return $viewObject;
    }

    /**
     *
     */
    public function mergeIn(FieldBasics $annotation, $overwrite = true)
    {
        $view = get_class($annotation);
        $view = $view::CURRENT_VIEW;

        //find what we're merging into
        if($view == FieldBasics::CURRENT_VIEW)
        {
            $this->basicProperties->mergeIn($annotation, $overwrite);
        }
        else
        {
            $this->viewSpecificProperties[$view]->mergeIn($annotation, $overwrite);
        }
    }
}
