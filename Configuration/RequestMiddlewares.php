<?php
return [
    'frontend' => [
        'Vendor/cleantalk/fe_manager_integration' => [
            'target' => \Cleantalk\Classes\Middleware\handleGeneralPostData::class,
            'after' => [
                'typo3/cms-frontend/site'
            ]
        ],
    ],
];
