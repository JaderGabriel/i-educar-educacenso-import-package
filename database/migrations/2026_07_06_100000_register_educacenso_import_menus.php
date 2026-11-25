<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use iEducar\Packages\BuritiEducacensoImport\Formats\EducacensoImportFormatCatalog;
use iEducar\Packages\BuritiEducacensoImport\Process;

return new class extends Migration
{
  public function up(): void
  {
    $educacenso = Menu::query()->where('process', Process::MENU_EDUCACENSO)->first()
      ?? Menu::query()->where('title', 'Educacenso')->whereNull('parent_id')->first();

    $importacoes = Menu::query()->where('process', Process::EDUCACENSO_IMPORTACOES)->first();

    if ($importacoes === null && $educacenso) {
      // Reaproveita o nó "Importações" legado (sem process) ou cria novo.
      $legado = Menu::query()
        ->where('parent_id', $educacenso->getKey())
        ->where('title', 'Importações')
        ->first();

      if ($legado) {
        $legado->update(['process' => Process::EDUCACENSO_IMPORTACOES, 'active' => true]);
        $importacoes = $legado->fresh();
      } else {
        $importacoes = Menu::query()->create([
          'parent_id' => $educacenso->getKey(),
          'title' => 'Importações',
          'process' => Process::EDUCACENSO_IMPORTACOES,
          'order' => 2,
          'type' => 2,
          'parent_old' => Process::MENU_EDUCACENSO,
          'old' => Process::EDUCACENSO_IMPORTACOES,
          'active' => true,
        ]);
      }
    }

    if ($importacoes === null) {
      return;
    }

    // Garante ACL no nó Importações (legado podia existir sem permissão tipada).
    DB::statement(
      'INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, cadastra, visualiza, exclui, menu_id)
         SELECT ref_cod_tipo_usuario, 1, 1, 1, ?
           FROM pmieducar.menu_tipo_usuario
          WHERE menu_id = (SELECT id FROM public.menus WHERE process = 846 LIMIT 1)
            AND NOT EXISTS (
              SELECT 1 FROM pmieducar.menu_tipo_usuario mtu
               WHERE mtu.ref_cod_tipo_usuario = menu_tipo_usuario.ref_cod_tipo_usuario
                 AND mtu.menu_id = ?
            )',
      [$importacoes->getKey(), $importacoes->getKey()]
    );

    foreach (EducacensoImportFormatCatalog::supportedYears() as $order => $year) {
      $yearMenu = Menu::query()->updateOrCreate([
        'parent_id' => $importacoes->getKey(),
        'title' => (string) $year,
      ], [
        'order' => $order + 1,
        'type' => 2,
        'parent_old' => Process::EDUCACENSO_IMPORTACOES,
        'active' => true,
      ]);

      foreach (EducacensoImportFormatCatalog::formatsForYear($year) as $formatOrder => $format) {
        Menu::query()->updateOrCreate([
          'process' => $format->process,
        ], [
          'parent_id' => $yearMenu->getKey(),
          'title' => $format->title,
          'description' => $format->description,
          'link' => "/educacenso/importacao/{$year}/{$format->slug}",
          'order' => $formatOrder + 1,
          'type' => 3,
          'parent_old' => Process::EDUCACENSO_IMPORTACOES,
          'old' => $format->process,
          'active' => true,
        ]);

        DB::statement(
          'INSERT INTO pmieducar.menu_tipo_usuario (ref_cod_tipo_usuario, cadastra, visualiza, exclui, menu_id)
             SELECT ref_cod_tipo_usuario, 1, 1, 1, ?
               FROM pmieducar.menu_tipo_usuario
              WHERE menu_id = (SELECT id FROM public.menus WHERE process = 846 LIMIT 1)
                AND NOT EXISTS (
                  SELECT 1 FROM pmieducar.menu_tipo_usuario mtu
                   WHERE mtu.ref_cod_tipo_usuario = menu_tipo_usuario.ref_cod_tipo_usuario
                     AND mtu.menu_id = ?
                )',
          [
            Menu::query()->where('process', $format->process)->value('id'),
            Menu::query()->where('process', $format->process)->value('id'),
          ]
        );
      }
    }
  }

  public function down(): void
  {
    $processes = [
      Process::MIGRACAO_TXT_2025,
      Process::IDENTIFICACAO_RETORNO_2025,
      Process::MIGRACAO_TXT_2026,
      Process::IDENTIFICACAO_RETORNO_2026,
      Process::IDENTIFICACAO_PROVAVEIS_2026,
      Process::IDENTIFICACAO_NAO_ENCONTRADO_2026,
      Process::INEP_RETORNO_2026,
      Process::SITUACAO_ALUNO_MIGRACAO_2026,
      Process::SITUACAO_ALUNO_IDENTIFICACAO_2026,
    ];

    foreach ($processes as $process) {
      $menuId = Menu::query()->where('process', $process)->value('id');

      if ($menuId) {
        DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $menuId)->delete();
      }

      Menu::query()->where('process', $process)->delete();
    }

    $importacoes = Menu::query()->where('process', Process::EDUCACENSO_IMPORTACOES)->first();

    if ($importacoes) {
      Menu::query()
        ->where('parent_id', $importacoes->getKey())
        ->whereIn('title', ['2025', '2026'])
        ->delete();
    }
  }
};
