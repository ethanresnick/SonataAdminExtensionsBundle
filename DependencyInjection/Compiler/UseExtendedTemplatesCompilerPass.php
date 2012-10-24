<?php
namespace ERD\SonataAdminExtensionsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Loads SonataAdminExtension's bundles templates in place of the SonataAdminBundle templates 
 * they're meant to override in a way that let's them still extend those templates.
 * 
 * Here's the deal. Say SonataAdminBundle has a template I want to extend. If I make a template
 * of the same name in the ERDSonataAdminExtensionsBundle then I've overriden the Sonata one 
 * with the ERD one, because the ERD bundle is a child of the Sonata bundle. The problem is that
 * my new template can't {% extend %} the original SonataAdmin template because now all 
 * references to that template point to the ERD one, creating a loop. So I have two options:
 * 
 *   1. Duplicate all the code of the Sonata template in my ERD version, so I can get the 
 *      original stuff and my changes. Problem with this is that it's not very flexible--
 *      it requires me to update the template every time Sonata makes a change (rather than only
 *      when Sonata makes a change affecting the part I'm extending) and it requires me to
 *      document what's my stuff to make sure I preserve it every time I update the rest of the
 *      template (error-prone) and to make it easier (but still not easy) to read.
 * 
 *   2. Do the below compiler pass which checks if SonataAdminBundle is being configured with
 *      the default version of a template that I want to extend and, if so, injects mine instead.
 *      Mine can now extend the original SonataAdmin template because it has a different name.
 *
 * @author Ethan Resnick Design <hi@ethanresnick.com>
 * @copyright Jul 15, 2012 Ethan Resnick Design
 */
class UseExtendedTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {/* UNECCESSARY BC I DECIDED USERS SHOULD HAVE TO EXPLICITLY CONFIG THE TEMPLATES. ALSO, WHY WOULD THIS GO IN A
        COMPILER PASS OVER A CONFIG FILE??
        $templatesParam = 'sonata.admin.configuration.templates';
        //$defaults = array('layout'=>'SonataAdminBundle::standard_layout.html.twig'); //defaults for the templates I'm extending.
        //$replacements = array('layout'=>'ERDSonataAdminExtensionsBundle::standard_layout_extended.html.twig');
        
        if($container->hasParameter($templatesParam))
        {
            $templates = $container->getParameter($templatesParam);
            
            foreach($defaults as $templateType=>$defaultTemplate)
            {
                if($templates[$templateType]==$defaultTemplate)
                {
                    $templates[$templateType] = $replacements[$templateType];
                }
            }
            
            $container->setParameter($templatesParam, $templates);
        }*/
    }
}