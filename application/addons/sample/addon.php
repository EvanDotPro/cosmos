<?php
return array(
    'languages' => true,
    'modules' => array(
        'extended' => array(
            'ext_backend' => array(
                'views'         => true,
                'controllers'   => true
            ),
            'ext_storefront' => array(
                'views'         => true,
                'controllers'   => true
            )
        ),
        'provided' => array(
            'sample_samplemod' => true
        )
    ),
    'routes' => array(
        'test_route' => array(
            'type'      => 'Zend_Controller_Router_Route',
            'route'     => 's/:controller/:action/*',
            'defaults'  => array(
                'module'        => 'sample_samplemod',
                'controller'    => 'index',
                'action'        => 'index'
            )
        )
    ),
    'services'  => true
);