<?php

$EM_CONF['cleantalk'] = [
    'title' => 'CleanTalk anti-spam',
    'description' => 'Protect your site from spam',
    'category' => 'module',
    'author' => 'CleanTalk',
    'author_email' => 'welcome@cleantalk.org',
    'author_company' => 'CleanTalk',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Cleantalk\\Classes\\' => 'Classes',
            'Cleantalk\\Common\\' => 'lib/Cleantalk/Common',
            'Cleantalk\\Custom\\' => 'lib/Cleantalk/Custom'
        ]
    ],
];