<?php

declare(strict_types=1);

use iEducar\Packages\BuritiEducacensoImport\Http\Controllers\EducacensoImportController;
use Illuminate\Support\Facades\Route;

Route::middleware([
  'ieducar.navigation',
  'ieducar.footer',
  'ieducar.xssbypass',
  'ieducar.suspended',
  'auth',
  'ieducar.checkresetpassword',
])->group(function () {
  Route::get('/educacenso/importacao/{year}/{slug}', [EducacensoImportController::class, 'index'])
    ->where('year', '2025|2026')
    ->name('buriti.educacenso-import.index');

  Route::post('/educacenso/importacao/{year}/{slug}', [EducacensoImportController::class, 'import'])
    ->where('year', '2025|2026')
    ->name('buriti.educacenso-import.import');

  Route::get('/educacenso/importacao/{year}/{slug}/resultado', [EducacensoImportController::class, 'result'])
    ->where('year', '2025|2026')
    ->name('buriti.educacenso-import.result');
});
