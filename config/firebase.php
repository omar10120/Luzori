<?php

return [
    'credentials' => [
        // Prefer DB-synced account when present; fall back to project file
        'file' => file_exists(storage_path('firebase/service-account.json'))
            ? storage_path('firebase/service-account.json')
            : storage_path('firebase/my-luzori-project-123654-firebase-adminsdk-fbsvc-38c10fd259.json'),
    ],
];
