<?php

return [
    'frontend' => [
        'typo3/cms-backend/locked-backend' => [
            'target' => \Cleantalk\Classes\Middleware\CatchPostMiddleware::class,
            'after' => [
                'typo3/cms-frontend/content-length-headers',
            ],
        ],
    ],
];
