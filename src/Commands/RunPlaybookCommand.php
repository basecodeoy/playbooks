<?php declare(strict_types=1);

/**
 * Copyright (C) BaseCode Oy - All Rights Reserved
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BaseCodeOy\Playbooks\Commands;

use BaseCodeOy\Playbooks\Playbook;
use BaseCodeOy\Playbooks\PlaybookDefinition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Symfony\Component\Console\Question\Question;

final class RunPlaybookCommand extends Command
{
    protected $signature = 'playbook:run {playbook?}';

    protected $description = 'Setup the database against a predefined playbook';

    private array $ranDefinitions = [];

    public function handle(): void
    {
        if (app()->environment() !== 'local') {
            $this->error('This command can only be run in the local environment!');
        }

        $playbookName = $this->argument('playbook');

        if (!$playbookName) {
            $availablePlaybooks = $this->getAvailablePlaybooks();

            $this->comment('Choose a playbook: '.\PHP_EOL);

            foreach ($availablePlaybooks as $availablePlaybook) {
                $this->comment('- '.$availablePlaybook);
            }

            $this->comment('');

            $playbookName = $this->askPlaybookName($availablePlaybooks);
        }

        $playbookDefinition = $this->resolvePlaybookDefinition($playbookName);

        $this->migrate();

        $this->runPlaybook($playbookDefinition);
    }

    private function migrate(): void
    {
        $this->info('Clearing the database');

        $this->call('migrate:fresh');
    }

    private function runPlaybook(PlaybookDefinition $playbookDefinition): void
    {
        foreach ($playbookDefinition->playbook->before() as $before) {
            $this->runPlaybook(
                $this->resolvePlaybookDefinition($before),
            );
        }

        for ($i = 1; $i <= $playbookDefinition->times; ++$i) {
            if ($playbookDefinition->once && $this->definitionHasRun($playbookDefinition)) {
                break;
            }

            $this->infoRunning($playbookDefinition->playbook, $i);

            $playbookDefinition->playbook->run($this->input, $this->output);

            $playbookDefinition->playbook->hasRun();

            $this->ranDefinitions[$playbookDefinition->id] = ($this->ranDefinitions[$playbookDefinition->id] ?? 0) + 1;
        }

        foreach ($playbookDefinition->playbook->after() as $after) {
            $this->runPlaybook(
                $this->resolvePlaybookDefinition($after),
            );
        }
    }

    private function askPlaybookName(array $availablePlaybooks): string
    {
        $helper = $this->getHelper('question');

        $question = new Question('');

        $question->setAutocompleterValues($availablePlaybooks);

        $playbookName = (string) $helper->ask($this->input, $this->output, $question);

        if ($playbookName === '' || $playbookName === '0') {
            $this->error('Please choose a playbook');

            return $this->askPlaybookName($availablePlaybooks);
        }

        return $playbookName;
    }

    private function getAvailablePlaybooks(): array
    {
        $files = \scandir($this->getPlaybooksPath());

        unset($files[0], $files[1]);

        return \array_map(fn (string $file): string|array => \str_replace('.php', '', $file), $files);
    }

    private function resolvePlaybookDefinition($class): PlaybookDefinition
    {
        if ($class instanceof PlaybookDefinition) {
            return $class;
        }

        if ($class instanceof Playbook) {
            return new PlaybookDefinition($class::class);
        }

        $className = $class;
        $namespace = $this->getDefaultNamespace();

        if (!Str::startsWith($class, ['\\'.$namespace, $namespace])) {
            $className = $this->getPlaybooksNamespace().('\\'.$class);
        }

        return new PlaybookDefinition($className);
    }

    private function infoRunning(Playbook $playbook, int $i): void
    {
        $playbookName = $playbook::class;

        $this->info(\sprintf('Running playbook `%s` (#%d)', $playbookName, $i));
    }

    private function definitionHasRun(PlaybookDefinition $playbookDefinition): bool
    {
        return \array_key_exists($playbookDefinition->id, $this->ranDefinitions);
    }

    private function getPlaybooksNamespace(): string
    {
        return App::getNamespace().'Playbooks';
    }

    private function getPlaybooksPath(): string
    {
        return app_path('Playbooks');
    }
}
