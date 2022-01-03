# HD Tickets

A helpdesk tickets system service API powered by laravel (8.*).


## Features

- 3 main actors: users, agents and admins.
- Ticket workflow configurable.
    - Statuses and transitions.
- Ticket's personal flags.
    - Flags types are configurables, defaults: Favorite, Pinned.
- Users and Tickets are taggables.
    - Tags have standardization.
- Agent/s assignment.
    - Self-assignment.
    - Admins can also assign agents.
- Ticket's support files and priority.
- Metrics
    - User: counts by priorities, status and tags.
    - Agent: counts by priorities, status, tags and avg times.
    - Admin: counts by priorities, status, tags, average times on a global level and average times per agent.
- On creation and transitions callbacks can be set.
    - As default AssignBestMatchedAgent is set on creation, chosen agent based on workload and matching tags.
- Auth: Sign Up, Sign In and Sign Out.


## Installation

1. Clone
```bash
git clone https://github.com/jieizmendi/hd-tickets.git
cd ./hd/hd-service
composer install
```
2. Set ENV
3. Run the migrations


## Configurations

Configurations are found in `config/hd.php`

- User's flag types
```php
    'flags' => ['Favorite', 'Pinned'],
```

- Priority levels
```php
    'priorities' => 10,
```

- Statuses
```php
    'statuses' => ['Open', 'OnHold', 'Pending', 'Solved', 'Closed'],
```

- Transitions
```php
    'transitions' => [
        'Open' => [ // From
            'Pending' => [ // To
                'actor' => 'Agent', // Can be launched by an assigned agent
                'actions' => [ // Actions called on transition
                    \App\Actions\NotifyOwnerThatActionIsNecessary::class,
                ],
            ],
    ...
        'Solved' => [
            'Open' => [
                'actor' => 'Owner', // Can be launched by the ticket's owner
                'actions' => [],
            ],
        ],
    ...
```

- Ticket defaults
```php
    'default' => [
        'status' => 'Open',
        'priority' => 5,

        // The default actions will be invoked at creation time.
        'actions' => [
            \App\Actions\AssignBestMatchedAgentAction::class,
        ],
    ],
```


## Testing

Testing required sqlite.

```bash
php artisan test
```


## To Do

- Expand metrics.
- Front-end solution.
- Move configuration to user.
    - Statuses, transitions, callback assignations and priorities.