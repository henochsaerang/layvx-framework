<?php

namespace App\Core;

class StructureDefinitions
{
    /**
     * Get the directory structure for a given preset.
     *
     * @param string $preset The name of the structure preset (e.g., 'mvc', 'adr').
     * @return array The list of directories to create.
     */
    public static function getDirectories(string $preset): array
    {
        $common = [
            'routes',
            'public',
            'database/tabel',
            'app/Providers',
            'app/Middleware',
        ];

        switch ($preset) {
            case 'mvc':
                return array_merge($common, [
                    'app/Controllers',
                    'app/Models',
                    'views',
                    'storage/framework/views'
                ]);

            case 'adr':
                return array_merge($common, [
                    'app/Actions',
                    'app/Domain',
                    'app/Responders',
                    'views'
                ]);
            
            case 'ddd':
                return array_merge($common, [
                    'app/Domain',
                    'app/Application',
                    'app/Infrastructure'
                ]);

            case 'hmvc':
                return array_merge($common, [
                    'app/Modules',
                    'views'
                ]);

            case 'minimal':
                return $common;

            default:
                return [];
        }
    }
}
