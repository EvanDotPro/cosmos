<?php
return array(
    'languages' => true,
    'modules' => array(
        'ext_backend' => array(
            'views' => true
        ),
        'ext_frontend' => array(
            'views' => array(
                'placeholders'  => true,
                'layouts'       => false
            )
        ),
        'provided' => array(
            'sample_samplemod' => array(

            )
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