<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Services;

use App\Models\EducacensoImport;
use App\Models\LegacySchool;
use iEducar\Packages\BuritiEducacensoImport\Formats\EducacensoImportFormatCatalog;
use iEducar\Packages\BuritiEducacensoImport\Formats\EducacensoImportFormatDefinition;
use iEducar\Packages\BuritiEducacensoImport\Parsers\EducacensoTxtParser;
use Illuminate\Http\UploadedFile;

final class EducacensoImportService
{
  public function __construct(
    private readonly EducacensoTxtParser $parser,
    private readonly EducacensoInepImportWriter $writer,
    private readonly EducacensoSituationImportWriter $situationWriter,
  ) {}

  public function import(
    EducacensoImportFormatDefinition $format,
    int $year,
    int $schoolId,
    UploadedFile $file,
    int $userId,
  ): EducacensoImportResult {
    $content = $file->getContent();
    $linesRead = count(array_filter(preg_split('/\R/u', $content) ?: []));
    $schoolInep = $this->writer->schoolInep($schoolId);
    $school = LegacySchool::query()->findOrFail($schoolId);

    $stats = match ($format->parserType) {
      'migracao_txt' => $this->writer->importMigracao(
        $this->parser->parseMigracao($content),
        $year,
        $schoolId,
        $schoolInep,
      ),
      'inep_retorno_txt' => $this->writer->importInepRetorno(
        $this->parser->parseMigracao($content),
        $year,
        $schoolId,
        $schoolInep,
      ),
      'identificacao_retorno_txt' => $this->writer->importIdentificacaoRetorno(
        $this->parser->parseIdentificacaoRetorno($content),
        $year,
        $schoolId,
        $schoolInep,
      ),
      'situacao_aluno_txt' => $this->situationWriter->import(
        $this->parser->parseSituacaoAluno($content),
        $year,
        $schoolId,
        $schoolInep,
      ),
      default => throw new \InvalidArgumentException("Formato não suportado: {$format->parserType}"),
    };

    EducacensoImport::query()->create([
      'year' => $year,
      'school' => $school->name ?? (string) $schoolId,
      'user_id' => $userId,
      'finished' => true,
      'registration_date' => now(),
    ]);

    return new EducacensoImportResult(
      year: $year,
      schoolId: $schoolId,
      formatSlug: $format->slug,
      formatTitle: $format->title,
      fileName: $file->getClientOriginalName(),
      linesRead: $linesRead,
      imported: $stats['imported'],
      skipped: $stats['skipped'],
      errors: $stats['errors'],
      messages: $stats['messages'],
      countsByType: $stats['countsByType'],
    );
  }

  public function formatOrFail(int $year, string $slug): EducacensoImportFormatDefinition
  {
    $format = EducacensoImportFormatCatalog::find($year, $slug);

    if ($format === null) {
      abort(404, 'Formato de importação não encontrado.');
    }

    return $format;
  }
}
