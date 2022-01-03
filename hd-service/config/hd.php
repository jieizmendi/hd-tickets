<?php

return [
    'roles' => ['Admin', 'Agent', 'User'],
    'flags' => ['Favorite', 'Pinned'],

    'priorities' => 10,
    'default' => [
        'status' => 'Open',
        'priority' => 5,

        // The default actions will be invoked at creation time.
        'actions' => [
            \App\Actions\AssignBestMatchedAgentAction::class,
        ],
    ],

    'statuses' => ['Open', 'OnHold', 'Pending', 'Solved', 'Closed'],

    // These ones are use in metrics, ex. avg. time to end status.
    'end_statuses' => ['Solved', 'Closed'],

    'transitions' => [
        'Open' => [
            'OnHold' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Pending' => [
                'actor' => 'Agent',
                'actions' => [
                    \App\Actions\NotifyOwnerThatActionIsNecessary::class,
                ],
            ],
            'Solved' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'OnHold' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Pending' => [
            'Closed' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Solved' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Closed' => [
            'Open' => [
                'actor' => 'Agent',
                'actions' => [],
            ],
        ],
        'Pending' => [
            'Open' => [
                'actor' => 'Owner',
                'actions' => [],
            ],
        ],
        'Solved' => [
            'Open' => [
                'actor' => 'Owner',
                'actions' => [],
            ],
        ],
    ],
];
