<?php

namespace SkyHackeR\MultiAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * Forked and upgraded for Laravel 10 by SkyHackeR
 * Original author: Al Amin Firdows
 */
class InstallMultiAuthCommand extends Command
{
    protected $signature = 'laravel-multi-auth:install {guards*} {--f|force} {--model} {--views} {--routes}';
    protected $description = 'Install Multi Auth scaffolding for given guard(s). Example: php artisan laravel-multi-auth:install admin';

    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }

    public function handle()
    {
        $guards = $this->argument('guards');
        $force = $this->option('force');

        if (! $force) {
            $this->error('This command must be run with -f to confirm. Use --help for options.');
            return 1;
        }

        foreach ($guards as $guardRaw) {
            $guard = Str::of($guardRaw)->trim()->lower()->__toString();
            $this->info("Scaffolding guard: {$guard}");

            $this->createModel($guard);
            $this->createMigration($guard);
            $this->createControllers($guard);
            $this->createViews($guard);
            $this->createRoutesFile($guard);
            $this->appendAuthConfig($guard);

            $this->info("Finished scaffolding '{$guard}'.");
        }

        $this->info('All done. Please review generated files and run: php artisan migrate');
        return 0;
    }

    protected function createModel(string $guard)
    {
        $modelClass = Str::studly($guard);
        $path = app_path("Models/{$modelClass}.php");

        if ($this->files->exists($path)) {
            $this->warn("Model already exists: {$path}");
            return;
        }

        $stub = $this->getStub('model.stub');
        $stub = str_replace('{{model}}', $modelClass, $stub);

        $this->ensureDirectory(app_path('Models'));
        $this->files->put($path, $stub);
        $this->info("Model created: {$path}");
    }

    protected function createMigration(string $guard)
    {
        $table = $guard . 's';
        $timestamp = date('Y_m_d_His');
        $file = database_path("migrations/{$timestamp}_create_{$table}_table.php");

        $stub = $this->getStub('migration.stub');
        $stub = str_replace('{{table}}', $table, $stub);

        $this->files->put($file, $stub);
        $this->info("Migration created: {$file}");
    }

    protected function createControllers(string $guard)
    {
        $studly = Str::studly($guard);
        $baseDir = app_path("Http/Controllers/{$studly}/Auth");
        $this->ensureDirectory($baseDir);

        $controllerStubs = [
            'LoginController.stub',
            'RegisterController.stub',
            'ForgotPasswordController.stub',
            'ResetPasswordController.stub'
        ];

        foreach ($controllerStubs as $stubFile) {
            $content = $this->getStub("controllers/{$stubFile}");
            $content = str_replace(['{{guard}}', '{{Guard}}'], [$guard, $studly], $content);
            $target = "{$baseDir}/" . str_replace('.stub', '.php', $stubFile);
            $this->files->put($target, $content);
            $this->info("Controller created: {$target}");
        }
    }

    protected function createViews(string $guard)
    {
        $viewDir = resource_path("views/{$guard}/auth");
        $this->ensureDirectory($viewDir);

        $views = [
            'login.blade.stub' => 'login.blade.php',
            'register.blade.stub' => 'register.blade.php',
            'passwords/email.blade.stub' => 'passwords/email.blade.php',
            'passwords/reset.blade.stub' => 'passwords/reset.blade.php'
        ];

        foreach ($views as $stub => $targetName) {
            $content = $this->getStub("views/{$stub}");
            $content = str_replace('{{guard}}', $guard, $content);
            $filePath = $viewDir . '/' . $targetName;
            $this->ensureDirectory(dirname($filePath));
            $this->files->put($filePath, $content);
            $this->info("View created: {$filePath}");
        }

        // Optionally create a simple home view so middleware redirect has something
        $homePath = resource_path("views/{$guard}/home.blade.php");
        if (!$this->files->exists($homePath)) {
            $this->files->put($homePath, "<h1>" . Str::studly($guard) . " Home</h1>");
            $this->info("Home view created: {$homePath}");
        }
    }

    protected function createRoutesFile(string $guard)
    {
        $routesDir = base_path('routes');
        $file = "{$routesDir}/{$guard}.php";
        $stub = $this->getStub('routes.stub');
        $stub = str_replace(['{{guard}}', '{{Guard}}'], [$guard, Str::studly($guard)], $stub);

        if (! $this->files->exists($file)) {
            $this->files->put($file, $stub);
            $this->info("Route file created: {$file}");
        } else {
            $this->warn("Route file already exists: {$file}");
        }

        // append include to routes/web.php if not present
        $web = base_path('routes/web.php');
        if ($this->files->exists($web)) {
            $includeLine = "require base_path('routes/{$guard}.php');";
            $webContent = $this->files->get($web);
            if (strpos($webContent, "routes/{$guard}.php") === false) {
                $this->files->append($web, PHP_EOL . $includeLine . PHP_EOL);
                $this->info("Appended include to routes/web.php");
            }
        } else {
            $this->warn("routes/web.php not found. Please include routes/{$guard}.php manually.");
        }
    }

    protected function appendAuthConfig(string $guard)
    {
        $authPath = config_path('auth.php');
        if (! $this->files->exists($authPath)) {
            $this->error('config/auth.php not found. Are you running this inside a Laravel application?');
            return;
        }

        $content = $this->files->get($authPath);

        // add guard
        if (strpos($content, "'{$guard}' => [") === false) {
            $guardSnippet = "\n        '{$guard}' => [\n            'driver' => 'session',\n            'provider' => '{$guard}s',\n        ],\n";
            $content = preg_replace("/('guards'\\s*=>\\s*\\[)/", "$1" . $guardSnippet, $content, 1);
        }

        // add provider
        if (strpos($content, "'{$guard}s' => [") === false) {
            $providerSnippet = "\n        '{$guard}s' => [\n            'driver' => 'eloquent',\n            'model' => App\\\\Models\\\\" . Str::studly($guard) . "::class,\n        ],\n";
            $content = preg_replace("/('providers'\\s*=>\\s*\\[)/", "$1" . $providerSnippet, $content, 1);
        }

        // add password broker into 'passwords' array if not exists
        if (strpos($content, "'passwords' => [") !== false && strpos($content, "'{$guard}s' => [") === false) {
            $passwordSnippet = "\n        '{$guard}s' => [\n            'provider' => '{$guard}s',\n            'table' => 'password_resets',\n            'expire' => 60,\n            'throttle' => 60,\n        ],\n";
            $content = preg_replace("/('passwords'\\s*=>\\s*\\[)/", "$1" . $passwordSnippet, $content, 1);
        }

        $this->files->put($authPath, $content);
        $this->info('Updated config/auth.php (guards/providers/passwords).');
    }

    protected function getStub($name)
    {
        $path = __DIR__ . '/../stubs/' . ltrim($name, '/');
        if (! $this->files->exists($path)) {
            $this->error("Stub not found: {$path}");
            return '';
        }
        return $this->files->get($path);
    }

    protected function ensureDirectory($path)
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }
}
