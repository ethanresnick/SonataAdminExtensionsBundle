<?php

namespace ERD\SonataAdminExtensionsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ERDSonataAdminExtensionsBundle extends Bundle
{
    /**
     * Sets SonataAdminBundle as this bundle's parent, which allows us to override
     * templates we wouldn't otherwise couldn't (because Sonata doesn't expose 
     * them with a config option). The templates we're overriding are all those 
     * in Resources/views that aren't prefixed with the name erd_
     */
    public function getParent()
    {
        return 'SonataAdminBundle';
    }
}