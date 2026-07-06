<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Services;

use App\Models\EmployeeInep;
use App\Models\EnrollmentInep;
use App\Models\LegacyEnrollment;
use App\Models\LegacySchoolClass;
use App\Models\SchoolClassInep;
use App\Models\StudentInep;
use Illuminate\Support\Facades\DB;

final class EducacensoInepImportWriter
{
  public function __construct(
    private readonly EducacensoPersonResolver $personResolver,
  ) {}

  /**
   * @param array{
   *   header: array<int, string>|null,
   *   escolas: list<array<int, string>>,
   *   turmas: list<array<int, string>>,
   *   pessoas: list<array<int, string>>,
   *   gestores: list<array<int, string>>,
   *   docentes: list<array<int, string>>,
   *   alunos: list<array<int, string>>,
   *   outros: list<array{tipo: string, linha: int, campos: array<int, string>}>
   * } $parsed
   * @return array{imported: int, skipped: int, errors: int, messages: list<array{linha: int|string, tipo: string, mensagem: string}>, countsByType: array<string, int>}
   */
  public function importMigracao(
    array $parsed,
    int $year,
    int $schoolId,
    ?string $schoolInep,
  ): array {
    $imported = 0;
    $skipped = 0;
    $errors = 0;
    $messages = [];
    $countsByType = [
      'turma' => 0,
      'aluno_inep' => 0,
      'docente_inep' => 0,
      'gestor_inep' => 0,
      'pessoa_inep' => 0,
      'matricula_inep' => 0,
    ];

    foreach ($parsed['turmas'] as $index => $fields) {
      $line = $index + 1;
      $inepEscola = $this->normalizeInep($fields[1] ?? null);

      if (!$this->matchesSchool($inepEscola, $schoolInep)) {
        $skipped++;

        continue;
      }

      $codTurma = $this->normalizeTurmaCod((string) ($fields[2] ?? ''));
      $inepTurma = $this->normalizeInep($fields[3] ?? null);

      if ($codTurma === '' || $inepTurma === null) {
        $skipped++;

        continue;
      }

      if (!$this->turmaBelongsToSchool($codTurma, $schoolId, $year)) {
        $errors++;
        $messages[] = [
          'linha' => $line,
          'tipo' => '20',
          'mensagem' => "Turma {$codTurma} não pertence à escola/ano selecionados.",
        ];

        continue;
      }

      SchoolClassInep::query()->updateOrCreate(
        ['cod_turma' => (int) $codTurma],
        [
          'cod_turma_inep' => (int) $inepTurma,
          'fonte' => "importacao-inep-{$year}",
        ]
      );

      $imported++;
      $countsByType['turma']++;
    }

    foreach ($parsed['pessoas'] as $index => $fields) {
      $line = $index + 1;
      $inepEscola = $this->normalizeInep($fields[1] ?? null);

      if (!$this->matchesSchool($inepEscola, $schoolInep)) {
        $skipped++;

        continue;
      }

      $codigoPessoa = (string) ($fields[2] ?? '');
      $inep = $this->normalizeInep($fields[3] ?? null);

      if ($inep === null) {
        $skipped++;

        continue;
      }

      if ($this->importStudentInep($codigoPessoa, $inep, $year)) {
        $imported++;
        $countsByType['pessoa_inep']++;

        continue;
      }

      if ($this->importEmployeeInep($codigoPessoa, $inep)) {
        $imported++;
        $countsByType['pessoa_inep']++;

        continue;
      }

      $errors++;
      $messages[] = [
        'linha' => $line,
        'tipo' => '30',
        'mensagem' => "Pessoa {$codigoPessoa} não encontrada para vincular INEP {$inep}.",
      ];
    }

    foreach ($parsed['gestores'] as $index => $fields) {
      $line = $index + 1;

      if (!$this->matchesSchool($this->normalizeInep($fields[1] ?? null), $schoolInep)) {
        $skipped++;

        continue;
      }

      $codigoPessoa = (string) ($fields[2] ?? '');
      $inep = $this->normalizeInep($fields[3] ?? null);

      if ($inep === null || !$this->importEmployeeInep($codigoPessoa, $inep)) {
        $errors++;
        $messages[] = [
          'linha' => $line,
          'tipo' => '40',
          'mensagem' => "Gestor {$codigoPessoa} não encontrado para vincular INEP {$inep}.",
        ];

        continue;
      }

      $imported++;
      $countsByType['gestor_inep']++;
    }

    foreach ($parsed['docentes'] as $index => $fields) {
      $line = $index + 1;

      if (!$this->matchesSchool($this->normalizeInep($fields[1] ?? null), $schoolInep)) {
        $skipped++;

        continue;
      }

      $codigoPessoa = (string) ($fields[2] ?? '');
      $inep = $this->normalizeInep($fields[3] ?? null);

      if ($inep === null || !$this->importEmployeeInep($codigoPessoa, $inep)) {
        $errors++;
        $messages[] = [
          'linha' => $line,
          'tipo' => '50',
          'mensagem' => "Docente {$codigoPessoa} não encontrado para vincular INEP {$inep}.",
        ];

        continue;
      }

      $imported++;
      $countsByType['docente_inep']++;
    }

    foreach ($parsed['alunos'] as $index => $fields) {
      $line = $index + 1;

      if (!$this->matchesSchool($this->normalizeInep($fields[1] ?? null), $schoolInep)) {
        $skipped++;

        continue;
      }

      $codigoPessoa = (string) ($fields[2] ?? '');
      $inepAluno = $this->normalizeInep($fields[3] ?? null);
      $codTurma = $this->normalizeTurmaCod((string) ($fields[4] ?? ''));
      $inepTurma = $this->normalizeInep($fields[5] ?? null);
      $matriculaInep = $this->normalizeInep($fields[6] ?? null);

      $codAluno = $this->personResolver->resolveStudentId($codigoPessoa);

      if ($codAluno === null) {
        $errors++;
        $messages[] = [
          'linha' => $line,
          'tipo' => '60',
          'mensagem' => "Aluno {$codigoPessoa} não encontrado.",
        ];

        continue;
      }

      if ($inepAluno !== null) {
        StudentInep::query()->updateOrCreate(
          ['cod_aluno' => $codAluno],
          [
            'cod_aluno_inep' => (int) $inepAluno,
            'fonte' => "importacao-inep-{$year}",
          ]
        );
        $countsByType['aluno_inep']++;
      }

      if ($codTurma !== '' && $inepTurma !== null && $this->turmaBelongsToSchool($codTurma, $schoolId, $year)) {
        SchoolClassInep::query()->updateOrCreate(
          ['cod_turma' => (int) $codTurma],
          [
            'cod_turma_inep' => (int) $inepTurma,
            'fonte' => "importacao-inep-{$year}",
          ]
        );
        $countsByType['turma']++;
      }

      if ($matriculaInep !== null && $codTurma !== '') {
        $enrollment = LegacyEnrollment::query()
          ->where('ref_cod_turma', (int) $codTurma)
          ->where('ativo', 1)
          ->whereHas('registration', function ($query) use ($codAluno, $year) {
            $query->where('ref_cod_aluno', $codAluno)
              ->where('ano', $year);
          })
          ->first();

        if ($enrollment) {
          EnrollmentInep::query()->updateOrCreate(
            ['matricula_turma_id' => $enrollment->getKey()],
            ['matricula_inep' => (int) $matriculaInep]
          );
          $countsByType['matricula_inep']++;
        } else {
          $errors++;
          $messages[] = [
            'linha' => $line,
            'tipo' => '60',
            'mensagem' => "Enturmação não encontrada para aluno {$codAluno} na turma {$codTurma}.",
          ];
        }
      }

      $imported++;
    }

    return compact('imported', 'skipped', 'errors', 'messages', 'countsByType');
  }

  /**
   * Retorno do INEP com códigos preenchidos (registros 20, 40, 50 e 60).
   *
   * @param array{
   *   header: array<int, string>|null,
   *   escolas: list<array<int, string>>,
   *   turmas: list<array<int, string>>,
   *   pessoas: list<array<int, string>>,
   *   gestores: list<array<int, string>>,
   *   docentes: list<array<int, string>>,
   *   alunos: list<array<int, string>>,
   *   outros: list<array{tipo: string, linha: int, campos: array<int, string>}>
   * } $parsed
   * @return array{imported: int, skipped: int, errors: int, messages: list<array{linha: int|string, tipo: string, mensagem: string}>, countsByType: array<string, int>}
   */
  public function importInepRetorno(
    array $parsed,
    int $year,
    int $schoolId,
    ?string $schoolInep,
  ): array {
    $parsed['pessoas'] = [];

    return $this->importMigracao($parsed, $year, $schoolId, $schoolInep);
  }

  /**
   * @param list<array{inepEscola: ?string, codigoPessoa: string, codigoInep: string, linha: int}> $items
   * @return array{imported: int, skipped: int, errors: int, messages: list<array{linha: int|string, tipo: string, mensagem: string}>, countsByType: array<string, int>}
   */
  public function importIdentificacaoRetorno(
    array $items,
    int $year,
    int $schoolId,
    ?string $schoolInep,
  ): array {
    $imported = 0;
    $skipped = 0;
    $errors = 0;
    $messages = [];
    $countsByType = ['aluno_inep' => 0];

    foreach ($items as $item) {
      $inepEscola = $item['inepEscola'];

      if ($inepEscola !== null && !$this->matchesSchool($inepEscola, $schoolInep)) {
        $skipped++;

        continue;
      }

      $codigoPessoa = $item['codigoPessoa'];
      $inep = $this->normalizeInep($item['codigoInep']);

      if ($inep === null) {
        $skipped++;

        continue;
      }

      $codAluno = $this->personResolver->resolveStudentId($codigoPessoa);

      if ($codAluno === null) {
        $errors++;
        $messages[] = [
          'linha' => $item['linha'],
          'tipo' => 'identificacao',
          'mensagem' => "Aluno {$codigoPessoa} não encontrado para vincular INEP {$inep}.",
        ];

        continue;
      }

      if (!$this->studentBelongsToSchool($codAluno, $schoolId, $year)) {
        $errors++;
        $messages[] = [
          'linha' => $item['linha'],
          'tipo' => 'identificacao',
          'mensagem' => "Aluno {$codAluno} sem matrícula na escola/ano selecionados.",
        ];

        continue;
      }

      StudentInep::query()->updateOrCreate(
        ['cod_aluno' => $codAluno],
        [
          'cod_aluno_inep' => (int) $inep,
          'fonte' => "importacao-identificacao-{$year}",
        ]
      );

      $imported++;
      $countsByType['aluno_inep']++;
    }

    return compact('imported', 'skipped', 'errors', 'messages', 'countsByType');
  }

  public function schoolInep(int $schoolId): ?string
  {
    $value = DB::table('modules.educacenso_cod_escola')
      ->where('cod_escola', $schoolId)
      ->value('cod_escola_inep');

    return $this->normalizeInep($value);
  }

  private function importStudentInep(string $codigoPessoa, string $inep, int $year): bool
  {
    $codAluno = $this->personResolver->resolveStudentId($codigoPessoa);

    if ($codAluno === null) {
      return false;
    }

    StudentInep::query()->updateOrCreate(
      ['cod_aluno' => $codAluno],
      [
        'cod_aluno_inep' => (int) $inep,
        'fonte' => "importacao-inep-{$year}",
      ]
    );

    return true;
  }

  private function importEmployeeInep(string $codigoPessoa, string $inep): bool
  {
    $codServidor = $this->personResolver->resolveServidorId($codigoPessoa);

    if ($codServidor === null) {
      return false;
    }

    EmployeeInep::query()->updateOrCreate(
      ['cod_servidor' => $codServidor],
      ['cod_docente_inep' => (int) $inep]
    );

    return true;
  }

  private function matchesSchool(?string $fileSchoolInep, ?string $selectedSchoolInep): bool
  {
    if ($fileSchoolInep === null || $selectedSchoolInep === null) {
      return true;
    }

    return ltrim($fileSchoolInep, '0') === ltrim($selectedSchoolInep, '0');
  }

  private function turmaBelongsToSchool(string $codTurma, int $schoolId, int $year): bool
  {
    if ($codTurma === '' || !ctype_digit($codTurma)) {
      return false;
    }

    return LegacySchoolClass::query()
      ->where('cod_turma', (int) $codTurma)
      ->where('ref_ref_cod_escola', $schoolId)
      ->where('ano', $year)
      ->exists();
  }

  private function studentBelongsToSchool(int $codAluno, int $schoolId, int $year): bool
  {
    return DB::table('pmieducar.matricula')
      ->where('ref_cod_aluno', $codAluno)
      ->where('ano', $year)
      ->where('ref_ref_cod_escola', $schoolId)
      ->where('ativo', 1)
      ->exists();
  }

  private function normalizeTurmaCod(string $cod): string
  {
    if (str_contains($cod, '-')) {
      return explode('-', $cod)[0];
    }

    return trim($cod);
  }

  private function normalizeInep(mixed $value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }

    $digits = preg_replace('/\D/', '', (string) $value);

    return $digits !== '' ? $digits : null;
  }
}
