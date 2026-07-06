<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Formats;

use iEducar\Packages\BuritiEducacensoImport\Process;

final class EducacensoImportFormatCatalog
{
  /** @return list<int> */
  public static function supportedYears(): array
  {
    return [2025, 2026];
  }

  /** @return list<EducacensoImportFormatDefinition> */
  public static function formatsForYear(int $year): array
  {
    return array_values(array_filter(
      self::all(),
      fn (EducacensoImportFormatDefinition $f) => $f->year === $year
    ));
  }

  public static function find(int $year, string $slug): ?EducacensoImportFormatDefinition
  {
    foreach (self::all() as $format) {
      if ($format->year === $year && $format->slug === $slug) {
        return $format;
      }
    }

    return null;
  }

  /** @return list<EducacensoImportFormatDefinition> */
  private static function all(): array
  {
    return [
      new EducacensoImportFormatDefinition(
        year: 2025,
        slug: 'migracao-txt',
        title: 'Migração TXT — Exportação INEP',
        description: 'Arquivo TXT delimitado por pipe (|) com registros 00–60 exportado pelo portal Educacenso (Matrícula Inicial → Migração → Exportação). Layout Censo Escolar 2025.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::MIGRACAO_TXT_2025,
        parserType: 'migracao_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2025,
        slug: 'identificacao-retorno-txt',
        title: 'Identificação INEP — Retorno de alunos',
        description: 'Arquivo TXT de retorno da pré-validação/identificação INEP (ex.: RESULTADO_ENCONTRADO.txt), com códigos INEP de alunos vinculados ao sistema.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::IDENTIFICACAO_RETORNO_2025,
        parserType: 'identificacao_retorno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'migracao-txt',
        title: 'Migração TXT — Exportação INEP',
        description: 'Arquivo TXT delimitado por pipe (|) com registros 00–60 exportado pelo portal Educacenso (Matrícula Inicial → Migração → Exportação). Layout Censo Escolar 2026.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::MIGRACAO_TXT_2026,
        parserType: 'migracao_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'identificacao-retorno-txt',
        title: 'Identificação INEP — Resultado encontrado',
        description: 'Arquivo TXT de retorno da pré-validação/identificação INEP (ex.: RESULTADO_ENCONTRADO.txt ou RESULTADO_CERTO.txt), com códigos INEP vinculados ao sistema.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::IDENTIFICACAO_RETORNO_2026,
        parserType: 'identificacao_retorno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'identificacao-provaveis-txt',
        title: 'Identificação INEP — Resultados prováveis',
        description: 'Arquivo RESULTADO_PROVAVEIS.txt retornado pelo INEP na identificação (vínculos sugeridos; conferência manual obrigatória).',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::IDENTIFICACAO_PROVAVEIS_2026,
        parserType: 'identificacao_retorno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'identificacao-nao-encontrado-txt',
        title: 'Identificação INEP — Não encontrados',
        description: 'Arquivo RESULTADO_SEMID.txt com alunos ou profissionais não identificados pelo INEP (importa códigos quando presentes no arquivo).',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::IDENTIFICACAO_NAO_ENCONTRADO_2026,
        parserType: 'identificacao_retorno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'inep-retorno-txt',
        title: 'Retorno INEP — Códigos preenchidos',
        description: 'Arquivo TXT retornado pelo Educacenso após o processamento da migração, contendo códigos INEP de turmas, gestores/docentes e alunos (registros 20, 40, 50 e 60).',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::INEP_RETORNO_2026,
        parserType: 'inep_retorno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'situacao-aluno-migracao-txt',
        title: 'Situação do Aluno — Migração TXT',
        description: 'Arquivo TXT da 2ª etapa do Censo (Situação do Aluno) com registros 89, 90 e 91, exportado ou retornado pelo portal INEP.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::SITUACAO_ALUNO_MIGRACAO_2026,
        parserType: 'situacao_aluno_txt',
      ),
      new EducacensoImportFormatDefinition(
        year: 2026,
        slug: 'situacao-aluno-identificacao-txt',
        title: 'Situação do Aluno — Identificação INEP',
        description: 'Arquivo de retorno da identificação INEP na 2ª etapa (situação do aluno), para atualização de códigos INEP no sistema local.',
        mimeHint: 'text/plain',
        extensions: ['txt'],
        process: Process::SITUACAO_ALUNO_IDENTIFICACAO_2026,
        parserType: 'identificacao_retorno_txt',
      ),
    ];
  }
}
