<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;

class ActionMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name} {--m=null} {--r=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a new Radiocrud action';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Action';

    /**
     * The name of class being generated.
     *
     * @var string
     */
    private $actionClass;

    /**
     * The model of class being generated.
     *
     * @var string
     */
    private $model;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->setActionClass();

        $path = $this->getPath();

        if (is_file(base_path() . $path . $this->actionClass)) {
            $this->error($this->type.' already exists!');

            return false;
        }

        Storage::disk('base')->put($path . $this->actionClass, $this->buildClass());

        $this->info($this->type.' created successfully.');

        $this->line("<info>Created Action :</info> $this->actionClass");

        return Command::SUCCESS;
    }

    /**
     * Set action class name
     *
     * @return  ActionMakeCommand
     */
    private function setActionClass(): ActionMakeCommand
    {
        $name = $this->argument('name');

        $this->actionClass = $name . '.php';

        return $this;
    }

    private function getPath (): string
    {
        return "\\app\\Actions\\";
    }

    /**
     * Replace the class name for the given stub.
     *
     * @return string
     */
    protected function buildClass()
    {
        $stub = Storage::disk('base')->get('\\stubs\\Radiocrud\\DummyAction.stub');

        $initCommands = $this->makeInitCommands();

        return str_replace(['DummyAction', '{{uses_here}}', '{{init_codes_here}}'], [$this->argument('name'), $initCommands['uses'], $initCommands['initCodes']], $stub);
    }

    protected function addCodeToInitCodes (&$initCodes, $code): string
    {
        return ( $initCodes = empty($initCodes) ? '$this' . $code : $initCodes . $code );
    }

    protected function makeInitCommands ()
    {
        $initCommands = [
            'uses' => '',
            'initCodes' => ''
        ];

        if ($this->hasOption('m') && $this->option('m') != 'null')
        {
            $initCommands['uses'] .= "\nuse App\\Models\\" . $this->option('m') . ';';
            $this->makeModel();
            $initCommands['initCodes'] = $this->addCodeToInitCodes($initCommands['initCodes'], '->setModel(' . $this->option('m') . '::class)');
        }

        if ($this->hasOption('r') && $this->option('r') != 'null')
        {
            $initCommands['uses'] .= "\nuse App\\Http\\Resources\\" . $this->option('r') . ';';
            $this->makeResource();
            $initCommands['initCodes'] = $this->addCodeToInitCodes($initCommands['initCodes'], '->setResource(' . $this->option('r') . '::class)');
        }

        $initCommands['initCodes'] = ! empty($initCommands['initCodes']) ? $initCommands['initCodes'] . ';' : null;

        return $initCommands;
    }

    /**
     * @return void
     */
    public function makeModel ()
    {
        Artisan::call('make:model', ['name' => $this->option('m')]);
        $this->outputTheArtisanOutput(['INFO', 'ALERT']);
    }

    /**
     * @return void
     */
    public function makeResource ()
    {
        Artisan::call('make:resource', ['name' => $this->option('r')]);
        $this->outputTheArtisanOutput(['INFO', 'ALERT']);
    }

    /**
     * @param array $allowedCommands
     * @return void
     */
    public function outputTheArtisanOutput (array $allowedCommands = ['INFO', 'ERROR', 'ALERT'])
    {
        $output = explode(' ', trim(Artisan::output()), 2);
        if (in_array($output[0], $allowedCommands))
        {
            $command = $output[0];
            $this->$command($output[1]);
        }
    }

    /**
     *
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return  base_path('stubs/Radiocrud/DummyAction.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Actions';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model class.'],
        ];
    }
}
