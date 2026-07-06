<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Providers;

use Illuminate\Support\ServiceProvider;

final class BuritiEducacensoImportServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    //
  }

  public function boot(): void
  {
    $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
    $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'buriti-educacenso-import');
    $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
  }
}
