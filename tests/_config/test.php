<?php

use craft\helpers\ArrayHelper;
use craft\services\Config;

$_SERVER['REMOTE_ADDR'] = '1.1.1.1';
$_SERVER['REMOTE_PORT'] = 654321;

$basePath = dirname(__DIR__, 2);

$srcPath = $basePath.'/src';
$vendorPath = $basePath.'/vendor';

$craftSrcPath = $vendorPath . '/craftcms/cms/src';

$appType = 'web';
$environment = 'TEST';

// Load the general config
$configService = new Config();
$configService->env = $environment;
$configService->configDir = $craftSrcPath.'/config';
$configService->appDefaultsDir = $craftSrcPath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'defaults';
$generalConfig = $configService->getConfigFromFile('general');

// Load the config
$components = [
    'config' => $configService,
];

$config = ArrayHelper::merge(
    [
        'vendorPath' => $vendorPath,
        'env' => $environment,
        'components' => $components,
    ],
    require $craftSrcPath."/config/app.php",
    require $craftSrcPath."/config/app.{$appType}.php",
    $configService->getConfigFromFile('app'),
    $configService->getConfigFromFile("app.{$appType}")
);

if (defined('CRAFT_SITE') || defined('CRAFT_LOCALE')) {
    $config['components']['sites']['currentSite'] = defined('CRAFT_SITE') ? CRAFT_SITE : CRAFT_LOCALE;
}

return $config;