<?php
return array_merge_recursive(array(
    'phpSettings' => array(
        'date' => array(
            'timezone' => 'America/Phoenix'
        )
    ),
    'autoloadernamespaces' => array(
        'Cosmos_',
        'ZendC_'
    ),
    'resources' => array(
        'multidb' => array(
            'master' => array(
                'adapter'   => 'pdo_mysql',
                'host'      => 'localdev',
                'username'  => 'cosmos',
                'password'  => 'cosmos',
                'dbname'    => 'cosmos',
                'default'   => true
            ),
            'slave1' => array(
                'adapter'   => 'pdo_mysql',
                'host'      => 'localdev',
                'username'  => 'cosmos',
                'password'  => 'cosmos',
                'dbname'    => 'cosmos'
            )
        )
    )
), include dirname(__FILE__) . '/' . APPLICATION_ENV . '.config.php');

?>
[production]
phpsettings.display_startup_errors = 0
phpsettings.display_errors = 0
phpsettings.date.timezone = "America/Phoenix"

autoloadernamespaces[] = "Cosmos_"
autoloadernamespaces[] = "ZendC_"

resources.session.name = "cosmos"

config[] = APPLICATION_PATH "/configs/" APPLICATION_MODE ".ini";
config[] = APPLICATION_PATH "/configs/sites/" APPLICATION_HOST ".ini";

[staging : production]

[testing : production]
phpSettings.display_startup_errors = 1
phpSettings.display_errors = 1

[development : production]
phpsettings.display_startup_errors = 1
phpsettings.display_errors = 1
phpsettings.error_reporting = E_ALL | E_STRICT