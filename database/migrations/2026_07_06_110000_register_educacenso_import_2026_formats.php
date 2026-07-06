<?php

use App\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use iEducar\Packages\BuritiEducacensoImport\Formats\EducacensoImportFormatCatalog;
use iEducar\Packages\BuritiEducacensoImport\Process;

return new class extends Migration
{
  private const BASELINE_PROCESSES = [
    Process::MIGRACAO_TXT_2025,
    Process::IDENTIFICACAO_RETORNO_2025,
    Process::MIGRACAO_TXT_2026,
    Process::IDENTIFICACAO_RETORNO_2026,
  ];

  private const ADDITIONAL_2026_PROCESSES = [
    Process::IDENTIFICACAO_PROVAVEIS_2026,
    Process::IDENTIFICACAO_NAO_ENCONTRADO_2026,
    Process::INEP_RETORNO_2026,
    Process::SITUACAO_ALUNO_MIGRACAO_2026,
    Process::SITUACAO_ALUNO_IDENTIFICACAO_2026,
  ];

  public function up(): void
  {
    $importacoes = Menu::query()
      ->where('process', Process::EDUCACENSO_IMPORTACOES)
      ->firstOrFail();

    $yearMenu = Menu::query()->firstOrCreate([
      'parent_id' => $importacoes->getKey(),
      'title' => '2026',
    ], [
      'order' => 2,
      'type' => 2,
      'parent_old' => Process::EDUCACENSO_IMPORTACOES,
      'active' => true,
    ]);

    foreach (EducacensoImportFormatCatalog::formatsForYear(2026) as $formatOrder => $format) {
      if (in_array($format->process, self::BASELINE_PROCESSES, true)) {
        Menu::query()->updateOrCreate([
          'process' => $format->process,
        ], [
          'parent_id' => $yearMenu->getKey(),
          'title' => $format->title,
          'description' => $format->description,
          'link' => "/educacenso/importacao/2026/{$format->slug}",
          'order' => $formatOrder + 1,
          'type' => 3,
          'parent_old' => Process::EDUCACENSO_IMPORTACOES,
          'old' => $format->process,
          'active' => true,
        ]);
      } else {
        Menu::query()->updateOrCreate([
          'process' => $format->process,
        ], [
          'parent_id' => $yearMenu->getKey(),
          'title' => $format->title,
          'description' => $format->description,
          'link' => "/educacenso/importacao/2026/{$format->slug}",
          'order' => $formatOrder + 1,
          'type' => 3,
          'parent_old' => Process::EDUCACENSO_IMPORTACOES,
          'old' => $format->process,
          'active' => true,
        ]);

        $this->grantPermissions($format->process);
      }
    }

    Menu::query()
      ->where('process', Process::IDENTIFICACAO_RETORNO_2026)
      ->update([
        'title' => 'Identificação INEP — Resultado encontrado',
        'description' => 'Arquivo TXT de retorno da pré-validação/identificação INEP (ex.: RESULTADO_ENCONTRADO.txt ou RESULTADO_CERTO.txt), com códigos INEP vinculados ao sistema.',
      ]);
  }

  public function down(): void
  {
    foreach (self::ADDITIONAL_2026_PROCESSES as $process) {
      $menuId = Menu::query()->where('process', $process)->value('id');

      if ($menuId) {
        DB::table('pmieducar.menu_tipo_usuario')->where('menu_id', $menuId)->delete();
      }

      Menu::query()->where('process', $process)->delete();
    }

    Menu::query()
      ->where('process', Process::IDENTIFICACAO_RETORNO_2026)
      ->update([
        'title' => 'Identificação INEP — Retorno de alunos',
        'description' => 'Arquivo TXT de retorno da pré-validação/identificação INEP (ex.: RESULTADO_ENCONTRADO.txt), com códigos INEP de alunos vinculados ao sistema.',
      ]);
  }

  private function grantPermissions(int $process): void
  {
    $menuId = Menu::query()->where('process', $process)->value('id');

    if ($menuId === null) {
      return;
    }

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
      [$menuId, $menuId]
    );
  }
};
