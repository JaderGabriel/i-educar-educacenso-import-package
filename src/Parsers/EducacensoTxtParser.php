<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Parsers;

final class EducacensoTxtParser
{
  /**
   * @return array{
   *   header: array<int, string>|null,
   *   escolas: list<array<int, string>>,
   *   turmas: list<array<int, string>>,
   *   pessoas: list<array<int, string>>,
   *   gestores: list<array<int, string>>,
   *   docentes: list<array<int, string>>,
   *   alunos: list<array<int, string>>,
   *   outros: list<array{tipo: string, linha: int, campos: array<int, string>}>
   * }
   */
  public function parseMigracao(string $content): array
  {
    $result = [
      'header' => null,
      'escolas' => [],
      'turmas' => [],
      'pessoas' => [],
      'gestores' => [],
      'docentes' => [],
      'alunos' => [],
      'outros' => [],
    ];

    $lines = preg_split('/\R/u', $content) ?: [];

    foreach ($lines as $index => $line) {
      $line = trim($line);

      if ($line === '') {
        continue;
      }

      $fields = explode('|', $line);
      $tipo = $fields[0] ?? '';

      match ($tipo) {
        '00' => $result['header'] = $fields,
        '10' => $result['escolas'][] = $fields,
        '20' => $result['turmas'][] = $fields,
        '30' => $result['pessoas'][] = $fields,
        '40' => $result['gestores'][] = $fields,
        '50' => $result['docentes'][] = $fields,
        '60' => $result['alunos'][] = $fields,
        default => $result['outros'][] = [
          'tipo' => $tipo,
          'linha' => $index + 1,
          'campos' => $fields,
        ],
      };
    }

    return $result;
  }

  /**
   * Retorno de identificação: linhas com registro 30 (pessoa) ou pares cod_pessoa|cod_inep.
   *
   * @return list<array{inepEscola: ?string, codigoPessoa: string, codigoInep: string, linha: int}>
   */
  public function parseIdentificacaoRetorno(string $content): array
  {
    $items = [];
    $lines = preg_split('/\R/u', $content) ?: [];

    foreach ($lines as $index => $line) {
      $line = trim($line);

      if ($line === '') {
        continue;
      }

      $fields = str_contains($line, '|')
        ? explode('|', $line)
        : (preg_split('/[;\t,]/', $line) ?: []);

      $tipo = $fields[0] ?? '';

      if ($tipo === '30' && count($fields) >= 4) {
        $items[] = [
          'inepEscola' => $this->normalizeInep($fields[1] ?? null),
          'codigoPessoa' => trim((string) ($fields[2] ?? '')),
          'codigoInep' => $this->normalizeInep($fields[3] ?? null) ?? '',
          'linha' => $index + 1,
        ];

        continue;
      }

      if (count($fields) >= 2 && ctype_digit(trim((string) $fields[0]))) {
        $items[] = [
          'inepEscola' => null,
          'codigoPessoa' => trim((string) $fields[0]),
          'codigoInep' => $this->normalizeInep($fields[1] ?? null) ?? '',
          'linha' => $index + 1,
        ];
      }
    }

    return $items;
  }

  /**
   * @return array{
   *   escolas: list<array<int, string>>,
   *   alunos_situacao: list<array{linha: int, campos: array<int, string>}>
   * }
   */
  public function parseSituacaoAluno(string $content): array
  {
    $result = [
      'escolas' => [],
      'alunos_situacao' => [],
    ];

    $lines = preg_split('/\R/u', $content) ?: [];

    foreach ($lines as $index => $line) {
      $line = trim($line);

      if ($line === '') {
        continue;
      }

      $fields = explode('|', $line);
      $tipo = $fields[0] ?? '';

      if ($tipo === '89') {
        $result['escolas'][] = $fields;

        continue;
      }

      if (in_array($tipo, ['90', '91'], true)) {
        $result['alunos_situacao'][] = [
          'linha' => $index + 1,
          'campos' => $fields,
        ];
      }
    }

    return $result;
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
