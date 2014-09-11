<?php return array(
    // Bootstrap the configuration file with AWS specific features
    'includes' => array('_aws'),
    'services' => array(
        // All AWS clients extend from 'default_settings'. Here we are
        // overriding 'default_settings' with our default credentials and
        // providing a default region setting.
        'default_settings' => array(
            'params' => array(
                'key'    => 'your api key here',
                'secret' => 'your api secret goes here',
                // 'region' => 'us-west-1' //dont specify region if you are running this from another region than your buckets
            )
        )
    )
);
