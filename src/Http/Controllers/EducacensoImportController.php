<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\BuritiEducacensoImport\Services\EducacensoImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class EducacensoImportController extends Controller
{
  public function __construct(
    private readonly EducacensoImportService $service,
  ) {}

  public function index(Request $request, int $year, string $slug)
  {
    $format = $this->service->formatOrFail($year, $slug);
    $this->authorize('view', $format->process);
    $this->menu($format->process);
    $this->breadcrumb($format->title, [
      url('/intranet/educar_educacenso_index.php') => 'Educacenso',
    ]);

    return view('buriti-educacenso-import::import.index', [
      'format' => $format,
      'year' => $year,
      'slug' => $slug,
    ]);
  }

  public function import(Request $request, int $year, string $slug)
  {
    $format = $this->service->formatOrFail($year, $slug);
    $this->authorize('modify', $format->process);

    $validator = Validator::make($request->all(), [
      'ano' => 'required|integer|in:' . $year,
      'ref_cod_escola' => 'required|integer|min:1',
      'arquivo' => 'required|file|mimes:txt|max:51200',
    ], attributes: [
      'ano' => 'Ano',
      'ref_cod_escola' => 'Escola',
      'arquivo' => 'Arquivo',
    ]);

    if ($validator->fails()) {
      return redirect()
        ->route('buriti.educacenso-import.index', ['year' => $year, 'slug' => $slug])
        ->withErrors($validator)
        ->withInput();
    }

    try {
      $result = $this->service->import(
        $format,
        (int) $request->input('ano'),
        (int) $request->input('ref_cod_escola'),
        $request->file('arquivo'),
        (int) $request->user()->getKey(),
      );

      session(['buriti_educacenso_import_result' => $result]);

      return redirect()->route('buriti.educacenso-import.result', [
        'year' => $year,
        'slug' => $slug,
      ]);
    } catch (\Throwable $exception) {
      return redirect()
        ->route('buriti.educacenso-import.index', ['year' => $year, 'slug' => $slug])
        ->withErrors(['arquivo' => 'Erro ao processar o arquivo: ' . $exception->getMessage()])
        ->withInput();
    }
  }

  public function result(Request $request, int $year, string $slug)
  {
    $format = $this->service->formatOrFail($year, $slug);
    $this->authorize('view', $format->process);

    $result = session('buriti_educacenso_import_result');

    if ($result === null) {
      return redirect()
        ->route('buriti.educacenso-import.index', ['year' => $year, 'slug' => $slug])
        ->withErrors(['error' => 'Nenhuma importação foi processada.']);
    }

    $this->menu($format->process);
    $this->breadcrumb('Resultado da importação', [
      url('/intranet/educar_educacenso_index.php') => 'Educacenso',
      route('buriti.educacenso-import.index', ['year' => $year, 'slug' => $slug]) => $format->title,
    ]);

    session()->forget('buriti_educacenso_import_result');

    return view('buriti-educacenso-import::import.result', [
      'format' => $format,
      'result' => $result,
      'year' => $year,
      'slug' => $slug,
    ]);
  }
}
