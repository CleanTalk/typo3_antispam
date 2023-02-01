<?php

$EM_CONF['cleantalk'] = [
    'title' => 'CleanTalk antispam',
    'description' => 'Protect your site from spam',
    'category' => 'module',
    'author' => 'Cleantalk',
    'author_email' => 'welcome@cleantalk.org',
    'author_company' => 'Cleantalk',
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
];