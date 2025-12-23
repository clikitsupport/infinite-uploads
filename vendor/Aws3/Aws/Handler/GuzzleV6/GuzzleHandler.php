<?php

namespace ClikIT\Infinite_Uploads\Aws\Handler\GuzzleV6;

trigger_error(sprintf(
    'Using the "%s" class is deprecated, use "%s" instead.',
    __NAMESPACE__ . '\GuzzleHandler',
    \ClikIT\Infinite_Uploads\Aws\Handler\Guzzle\GuzzleHandler::class
), E_USER_DEPRECATED);

class_alias(\ClikIT\Infinite_Uploads\Aws\Handler\Guzzle\GuzzleHandler::class, __NAMESPACE__ . '\GuzzleHandler');
