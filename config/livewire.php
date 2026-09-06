<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payload Guards
    |--------------------------------------------------------------------------
    |
    | Filament's RichEditor sends a nested TipTap document through Livewire.
    | Keep the payload guards enabled while allowing that document structure.
    |
    */
    'payload' => [
        'max_size' => 1024 * 1024,
        'max_nesting_depth' => 20,
        'max_calls' => 50,
        'max_components' => 200,
    ],
];
