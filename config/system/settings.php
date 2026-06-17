<?php
return [
    'BE' => [
        'debug' => false,
        'installToolPassword' => '$argon2i$v=19$m=65536,t=16,p=1$c1dnR0JBeUpOZkdiZ2ZPdQ$Y/ej8SxvC2LRTaC70NiNymnA9v3sQKxWU9e7m1xjQdg',
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'DB' => [
        'Connections' => [
            'Default' => [
                'charset' => 'utf8mb4',
                'dbname' => 'db_team09',
                'defaultTableOptions' => [
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ],
                'driver' => 'mysqli',
                'host' => 'localhost',
                'password' => 'C+2w_team09',
                'port' => 3306,
                'user' => 'team09',
            ],
        ],
    ],
    'EXTENSIONS' => [
        'backend' => [
            'backendFavicon' => '',
            'backendLogo' => '',
            'loginBackgroundImage' => '',
            'loginFootnote' => '',
            'loginHighlightColor' => '',
            'loginLogo' => '',
            'loginLogoAlt' => '',
        ],
        'bootstrap_package' => [
            'disableCssProcessing' => '0',
            'disableGoogleFontCaching' => '0',
        ],
        'extensionmanager' => [
            'automaticInstallation' => '1',
            'offlineMode' => '0',
        ],
        'ke_search' => [
            'additionalWordCharacters' => '',
            'allowEmptySearch' => '1',
            'enableExplicitAnd' => '0',
            'enablePartSearch' => '1',
            'finishNotification' => '0',
            'indexTagTitlesAsHiddenContent' => '1',
            'loglevel' => 'ERROR',
            'multiplyValueToTitle' => '1',
            'notificationRecipient' => '',
            'notificationSender' => 'no_reply@domain.com',
            'notificationSubject' => '[KE_SEARCH INDEXER NOTIFICATION]',
            'pathCatdoc' => '/usr/bin/',
            'pathPdfinfo' => '/usr/bin/',
            'pathPdftotext' => '/usr/bin/',
            'searchWordLength' => '4',
        ],
        'powermail' => [
            'disableIpLog' => '1',
            'disableMarketingInformation' => '0',
            'disablePluginInformation' => '0',
            'disablePluginInformationMailPreview' => '0',
            'enableCaching' => '0',
            'replaceIrreWithElementBrowser' => '0',
        ],
    ],
    'FE' => [
        'cacheHash' => [
            'enforceValidation' => true,
        ],
        'debug' => false,
        'disableNoCacheParameter' => true,
        'passwordHashing' => [
            'className' => 'TYPO3\\CMS\\Core\\Crypto\\PasswordHashing\\Argon2iPasswordHash',
            'options' => [],
        ],
    ],
    'LANG' => [
        'availableLocales' => [
            'de',
        ],
    ],
    'LOG' => [
        'TYPO3' => [
            'CMS' => [
                'deprecations' => [
                    'writerConfiguration' => [
                        'notice' => [
                            'TYPO3\CMS\Core\Log\Writer\FileWriter' => [
                                'disabled' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'MAIL' => [
        'transport' => 'sendmail',
        'transport_sendmail_command' => '/usr/sbin/sendmail -t -i -f kalenbor@uni-trier.de',
        'transport_smtp_encrypt' => '',
        'transport_smtp_password' => '',
        'transport_smtp_server' => '',
        'transport_smtp_username' => '',
    ],
    'SYS' => [
        'UTF8filesystem' => true,
        'caching' => [
            'cacheConfigurations' => [
                'hash' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                ],
                'pages' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
                'rootline' => [
                    'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
                    'options' => [
                        'compression' => true,
                    ],
                ],
            ],
        ],
        'devIPmask' => '',
        'displayErrors' => 0,
        'encryptionKey' => '69de8bd68b15a172a2ef230ad3b703bdd9522d39f4704d067f4b82ff5f2ebe60148397ef4cc5297b19bb120bd64d5cab',
        'exceptionalErrors' => 4096,
        'features' => [
            'frontend.cache.autoTagging' => true,
            'security.system.enforceAllowedFileExtensions' => true,
        ],
        'sitename' => 'Content Management',
        'systemMaintainers' => [
            2,
        ],
    ],
];
