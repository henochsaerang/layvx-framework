<?php

namespace App\Core;

use App\Commands\CacheClearCommand;
use App\Commands\HelpCommand;
use App\Commands\MakeControllerCommand;
use App\Commands\MakeMigrationCommand;
use App\Commands\MakeModelCommand;
use App\Commands\MigrateCommand;
use App\Commands\ServeCommand;

class Kernel {
    protected $commands = [
        'serve' => ServeCommand::class,
        'cache:clear' => CacheClearCommand::class,
        'buat:controller' => MakeControllerCommand::class,
        'buat:model' => MakeModelCommand::class,
        'buat:tabel' => MakeMigrationCommand::class,
        'buat:hapus_tabel' => MakeMigrationCommand::class,
        'migrasi' => MigrateCommand::class,
        'help' => HelpCommand::class,
    ];

    public function handle($argv) {
        $commandName = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        if (!isset($this->commands[$commandName])) {
            echo "Error: Command '{$commandName}' not found.\n";
            (new HelpCommand())->handle();
            exit(1);
        }

        $commandClass = $this->commands[$commandName];
        $commandInstance = new $commandClass();

        // A bit of a hack to pass the command name to the migration creator
        if ($commandName === 'buat:hapus_tabel') {
            $args['command'] = 'buat:hapus_tabel';
        }

        $commandInstance->handle($args);
    }

    public function getCommands() {
        return $this->commands;
    }
}
