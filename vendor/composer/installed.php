<?php return array(
    'root' => array(
        'name' => 'clikit/infinite-uploads',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'f3cdd4db6bfd316d35a043a021b5dca74c8cb149',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'clikit/infinite-uploads' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'f3cdd4db6bfd316d35a043a021b5dca74c8cb149',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
