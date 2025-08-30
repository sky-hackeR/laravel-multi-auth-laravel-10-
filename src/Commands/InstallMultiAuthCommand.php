<?php

namespace SkyHackeR\MultiAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class InstallMultiAuthCommand extends Command
{
    protected $signature = 'laravel-multi-auth:install {guards*} {--f|force}';
    protected $description = 'Install Multi Auth scaffolding for given guard(s). Example: php artisan laravel-multi-auth:install admin';

    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }

    public function handle()
    {
        if (!$this->option('force')) {
            $this->error('You must use -f to confirm. Use --help for details.');
            return 1;
        }

        foreach ($this->argument('guards') as $guardRaw) {
            $guard = Str::lower(trim($guardRaw));
            $studly = Str::studly($guard);
            $this->info("Setting up guard: {$guard}");

            $this->createModel($guard);
            $this->createMigration($guard);
            $this->createControllers($guard);
            $this->createViews($guard);
            $this->createRouteFile($guard); 
            $this->appendRoutesToWeb($guard);
            $this->appendAuthConfig($guard);

            $this->info("Finished scaffolding '{$guard}'");
        }

        $this->info('All done! Review files and run: php artisan migrate');
        return 0;
    }

    protected function createModel(string $guard)
    {
        $modelClass = Str::studly($guard);
        $path = app_path("Models/{$modelClass}.php");

        if ($this->files->exists($path) && !$this->option('force')) {
            $this->warn("Model exists: {$path}");
            return;
        }

        $stub = $this->getStub('models/model.stub', true);
        $stub = str_replace('{{model}}', $modelClass, $stub);

        $this->ensureDirectory(app_path('Models'));
        $this->files->put($path, $stub);
        $this->info("Model created: {$path}");
    }

    protected function createMigration(string $guard)
    {
        $table = Str::plural($guard);
        $timestamp = date('Y_m_d_His');
        $file = database_path("migrations/{$timestamp}_create_{$table}_table.php");

        if ($this->migrationExists($table) && !$this->option('force')) {
            $this->warn("Migration for '{$table}' already exists. Skipping.");
            return;
        }

        $stub = $this->getStub('migrations/migration.stub', true);
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
            $target = "{$baseDir}/" . str_replace('.stub', '.php', $stubFile);
            if ($this->files->exists($target) && !$this->option('force')) {
                $this->warn("Controller exists: {$target}");
                continue;
            }

            $content = $this->getStub("controllers/{$stubFile}", true);
            $content = str_replace(['{{guard}}', '{{Guard}}'], [$guard, $studly], $content);

            $this->files->put($target, $content);
            $this->info("Controller created: {$target}");
        }
    }

    protected function createViews(string $guard){
        $studly = Str::studly($guard);

        // Base directories
        $baseViewDir   = resource_path("views/{$guard}");
        $authDir       = "{$baseViewDir}/auth";
        $passwordsDir  = "{$authDir}/passwords";
        $layoutDir     = "{$baseViewDir}/layout";

        $this->ensureDirectory($authDir);
        $this->ensureDirectory($passwordsDir);
        $this->ensureDirectory($layoutDir);

        // Map stubs to blade view paths
        $views = [
            'auth/login.blade.stub'             => "{$authDir}/login.blade.php",
            'auth/register.blade.stub'          => "{$authDir}/register.blade.php",
            'auth/passwords/email.blade.stub'   => "{$passwordsDir}/email.blade.php",
            'auth/passwords/reset.blade.stub'   => "{$passwordsDir}/reset.blade.php",
            'layout/auth.blade.stub'            => "{$layoutDir}/auth.blade.php",
            'home.blade.stub'                   => "{$baseViewDir}/home.blade.php",
        ];

        foreach ($views as $stub => $targetPath) {
            if ($this->files->exists($targetPath) && !$this->option('force')) {
                $this->warn("View exists: {$targetPath}");
                continue;
            }

            $content = $this->getStub("views/{$stub}", true);
            $content = str_replace(
                ['{{guard}}', '{{Guard}}'],
                [$guard, $studly],
                $content
            );

            $this->files->put($targetPath, $content);
            $this->info("View created: {$targetPath}");
        }
    }



    protected function createRouteFile(string $guard)
    {
        $routePath = base_path("routes/{$guard}.php");
        if ($this->files->exists($routePath) && !$this->option('force')) {
            $this->warn("Route file exists: {$routePath}");
            return;
        }

        $stub = $this->getStub('routes/routefile.stub', true);
        $stub = str_replace('{{guard}}', $guard, $stub);

        $this->files->put($routePath, $stub);
        $this->info("Created route file: {$routePath}");
    }

    protected function appendRoutesToWeb(string $guard)
    {
        $studly = Str::studly($guard);
        $webPath = base_path('routes/web.php');

        $webContent = $this->files->get($webPath);
        $requireLine = "require base_path('routes/{$guard}.php');";

        if (!str_contains($webContent, $requireLine)) {
            $this->files->append($webPath, "\n{$requireLine}\n");
            $this->info("Added require for {$guard}.php in web.php");
        }

        $stub = $this->getStub('routes/web.stub', true);
        $stub = str_replace(['{{guard}}', '{{Guard}}'], [$guard, $studly], $stub);

        if (!str_contains($webContent, "{$studly} Auth Routes")) {
            $this->files->append($webPath, "\n{$stub}\n");
            $this->info("Added {$studly} auth routes to web.php");
        } else {
            $this->warn("Auth routes for {$studly} already exist in web.php");
        }
    }

    protected function appendAuthConfig(string $guard)
    {
        $authPath = config_path('auth.php');
        if (!$this->files->exists($authPath)) {
            $this->error('config/auth.php not found.');
            return;
        }

        $content = $this->files->get($authPath);
        $model = "App\\\\Models\\\\" . Str::studly($guard);

        if (!str_contains($content, "'{$guard}' => [")) {
            $guardSnippet = "\n        '{$guard}' => [\n            'driver' => 'session',\n            'provider' => '{$guard}s',\n        ],\n";
            $content = preg_replace("/('guards'\\s*=>\\s*\\[)/", "$1" . $guardSnippet, $content, 1);
        }

        if (!str_contains($content, "'{$guard}s' => [")) {
            $providerSnippet = "\n        '{$guard}s' => [\n            'driver' => 'eloquent',\n            'model' => {$model}::class,\n        ],\n";
            $content = preg_replace("/('providers'\\s*=>\\s*\\[)/", "$1" . $providerSnippet, $content, 1);
        }

        if (!str_contains($content, "'{$guard}s' => [")) {
            $passwordSnippet = "\n        '{$guard}s' => [\n            'provider' => '{$guard}s',\n            'table' => 'password_resets',\n            'expire' => 60,\n            'throttle' => 60,\n        ],\n";
            $content = preg_replace("/('passwords'\\s*=>\\s*\\[)/", "$1" . $passwordSnippet, $content, 1);
        }

        $this->files->put($authPath, $content);
        $this->info('Updated config/auth.php (guards, providers, passwords)');
    }

    protected function getStub($name, $required = false)
    {
        $path = __DIR__ . '/../stubs/' . ltrim($name, '/');
        if (!$this->files->exists($path)) {
            if ($required) {
                $this->error("Required stub not found: {$path}");
                exit(1);
            }
            return '';
        }
        return $this->files->get($path);
    }

    protected function ensureDirectory($path)
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function migrationExists($table): bool
    {
        return !empty(glob(database_path("migrations/*_create_{$table}_table.php")));
    }
}
