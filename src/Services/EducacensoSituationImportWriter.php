<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Services;

use App\Models\LegacyRegistration;
use App\Models\SchoolClassInep;
use App\Models\StudentInep;

final class EducacensoSituationImportWriter
{
  /**
   * @param array{
   *   escolas: list<array<int, string>>,
   *   alunos_situacao: list<array{linha: int, campos: array<int, string>}>
   * } $parsed
   * @return array{imported: int, skipped: int, errors: int, messages: list<array{linha: int|string, tipo: string, mensagem: string}>, countsByType: array<string, int>}
   */
  public function import(
    array $parsed,
    int $year,
    int $schoolId,
    ?string $schoolInep,
  ): array {
    $imported = 0;
    $skipped = 0;
    $errors = 0;
    $messages = [];
    $countsByType = ['situacao_matricula' => 0];

    foreach ($parsed['alunos_situacao'] as $item) {
      $fields = $item['campos'];
      $tipo = $fields[0] ?? '';
      $inepEscola = $this->normalizeInep($fields[2] ?? null);
      $turmaInep = $this->normalizeInep($fields[3] ?? null);
      $alunoInep = $this->normalizeInep($fields[4] ?? null);
      $situacaoEducacenso = isset($fields[7]) && $fields[7] !== '' ? (int) $fields[7] : null;

      if (!$this->matchesSchool($inepEscola, $schoolInep)) {
        $skipped++;

        continue;
      }

      if ($turmaInep === null || $alunoInep === null || $situacaoEducacenso === null) {
        $skipped++;

        continue;
      }

      $schoolClassId = SchoolClassInep::query()
        ->where('cod_turma_inep', $turmaInep)
        ->value('cod_turma');

      $studentId = StudentInep::query()
        ->where('cod_aluno_inep', $alunoInep)
        ->value('cod_aluno');

      if ($schoolClassId === null || $studentId === null) {
        $errors++;
        $messages[] = [
          'linha' => $item['linha'],
          'tipo' => (string) $tipo,
          'mensagem' => "Turma INEP {$turmaInep} ou aluno INEP {$alunoInep} não encontrados no sistema.",
        ];

        continue;
      }

      $registration = LegacyRegistration::query()
        ->with([
          'lastEnrollment.schoolClass',
          'grade',
        ])
        ->join('pmieducar.matricula_turma', 'matricula_turma.ref_cod_matricula', '=', 'matricula.cod_matricula')
        ->where('matricula.ref_cod_aluno', $studentId)
        ->where('matricula.ano', $year)
        ->where('matricula.ref_ref_cod_escola', $schoolId)
        ->where('matricula_turma.ref_cod_turma', $schoolClassId)
        ->select('matricula.*')
        ->first();

      if ($registration === null) {
        $errors++;
        $messages[] = [
          'linha' => $item['linha'],
          'tipo' => (string) $tipo,
          'mensagem' => "Matrícula não encontrada para aluno {$studentId} na turma {$schoolClassId}.",
        ];

        continue;
      }

      $etapaTurma = (int) ($registration->lastEnrollment?->schoolClass?->etapa_educacenso ?? 0);
      $etapaSerie = $registration->grade?->etapa_educacenso;

      $situacao = $this->convertSituationEducacensoToIeducar(
        $situacaoEducacenso,
        $etapaTurma,
        $etapaSerie !== null ? (int) $etapaSerie : null,
      );

      $data = [
        'aprovado' => $situacao,
        'ativo' => 1,
        'data_cancel' => null,
      ];

      if (in_array($situacao, [
        \App_Model_MatriculaSituacao::TRANSFERIDO,
        \App_Model_MatriculaSituacao::ABANDONO,
        \App_Model_MatriculaSituacao::FALECIDO,
      ], true)) {
        $data['data_cancel'] = $registration->data_matricula;

        $registration->lastEnrollment?->update([
          'ativo' => 0,
          'transferido' => $situacao === \App_Model_MatriculaSituacao::TRANSFERIDO,
          'abandono' => $situacao === \App_Model_MatriculaSituacao::ABANDONO,
          'falecido' => $situacao === \App_Model_MatriculaSituacao::FALECIDO,
          'data_exclusao' => $registration->data_matricula,
        ]);
      }

      $registration->update($data);
      $imported++;
      $countsByType['situacao_matricula']++;
    }

    return compact('imported', 'skipped', 'errors', 'messages', 'countsByType');
  }

  private function matchesSchool(?string $fileSchoolInep, ?string $selectedSchoolInep): bool
  {
    if ($fileSchoolInep === null || $selectedSchoolInep === null) {
      return true;
    }

    return ltrim($fileSchoolInep, '0') === ltrim($selectedSchoolInep, '0');
  }

  private function convertSituationEducacensoToIeducar(int $situation, int $etapaTurma = 0, ?int $etapaSerie = null): int
  {
    if (function_exists('convertSituationEducacensoToIeducar')) {
      return convertSituationEducacensoToIeducar($situation, $etapaTurma, $etapaSerie);
    }

    $etapasMultiEtapas = [56];
    if (in_array($etapaTurma, $etapasMultiEtapas, true) && $etapaSerie !== null) {
      $etapaTurma = $etapaSerie;
    }

    $etapasEducacaoInfantil = [1, 2, 3];
    if (in_array($etapaTurma, $etapasEducacaoInfantil, true) && $situation === 8) {
      return \App_Model_MatriculaSituacao::APROVADO;
    }

    return match ($situation) {
      1 => \App_Model_MatriculaSituacao::TRANSFERIDO,
      2 => \App_Model_MatriculaSituacao::ABANDONO,
      3 => \App_Model_MatriculaSituacao::FALECIDO,
      4 => \App_Model_MatriculaSituacao::REPROVADO,
      5, 6 => \App_Model_MatriculaSituacao::APROVADO,
      default => \App_Model_MatriculaSituacao::EM_ANDAMENTO,
    };
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
