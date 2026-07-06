<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Services;

use App\Models\Employee;
use App\Models\LegacyStudent;

final class EducacensoPersonResolver
{
  public function resolveStudentId(string $codigoPessoa): ?int
  {
    $codigoPessoa = trim($codigoPessoa);

    if ($codigoPessoa === '') {
      return null;
    }

    if (ctype_digit($codigoPessoa)) {
      $byCodAluno = LegacyStudent::query()
        ->where('cod_aluno', (int) $codigoPessoa)
        ->value('cod_aluno');

      if ($byCodAluno) {
        return (int) $byCodAluno;
      }

      $byIdpes = LegacyStudent::query()
        ->where('ref_idpes', (int) $codigoPessoa)
        ->value('cod_aluno');

      if ($byIdpes) {
        return (int) $byIdpes;
      }
    }

    return null;
  }

  public function resolveServidorId(string $codigoPessoa): ?int
  {
    $codigoPessoa = trim($codigoPessoa);

    if ($codigoPessoa === '' || !ctype_digit($codigoPessoa)) {
      return null;
    }

    $id = (int) $codigoPessoa;

    $byCodServidor = Employee::query()
      ->where('cod_servidor', $id)
      ->value('cod_servidor');

    if ($byCodServidor) {
      return (int) $byCodServidor;
    }

    $byIdpes = Employee::query()
      ->whereHas('person', fn ($query) => $query->where('idpes', $id))
      ->value('cod_servidor');

    return $byIdpes ? (int) $byIdpes : null;
  }
}
