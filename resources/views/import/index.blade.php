@extends('layout.default')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.css') }}"/>
@endpush

@section('content')
    <form id="formcadastro"
          action="{{ route('buriti.educacenso-import.import', ['year' => $year, 'slug' => $slug]) }}"
          method="post"
          enctype="multipart/form-data">
        @csrf
        <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
            <tbody>
            <tr>
                <td class="formdktd" colspan="2" height="24">
                    <b>{{ $format->title }} ({{ $year }})</b>
                </td>
            </tr>
            <tr>
                <td class="formmdtd" colspan="2">
                    <p style="margin: 10px 0;">{{ $format->description }}</p>
                    <div style="background-color: #d9edf7; border: 1px solid #bce8f1; color: #31708f; padding: 10px; margin: 10px 0; border-radius: 4px;">
                        <p style="margin: 0; font-weight: bold;">Importação por escola</p>
                        <p style="margin: 8px 0 0;">Selecione o ano letivo e a escola, depois envie o arquivo TXT exportado/retornado pelo INEP. Apenas registros da escola selecionada serão importados.</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="formmdtd" valign="top">
                    <span class="form">Ano</span>
                    <span class="campo_obrigatorio">*</span>
                </td>
                <td class="formmdtd" valign="top">
                    <input type="hidden" name="ano" value="{{ $year }}">
                    <span class="form">{{ $year }}</span>
                </td>
            </tr>
            <tr>
                <td class="formlttd" valign="top">
                    <span class="form">Instituição</span>
                    <span class="campo_obrigatorio">*</span>
                </td>
                <td class="formlttd" valign="top">
                    @include('form.select-institution')
                </td>
            </tr>
            <tr>
                <td class="formmdtd" valign="top">
                    <span class="form">Escola</span>
                    <span class="campo_obrigatorio">*</span>
                </td>
                <td class="formmdtd" valign="top">
                    @include('form.select-school')
                </td>
            </tr>
            <tr>
                <td class="formlttd" valign="top">
                    <span class="form">Arquivo TXT</span>
                    <span class="campo_obrigatorio">*</span>
                    <br>
                    <sub style="vertical-align:top;">Máximo 50MB</sub>
                </td>
                <td class="formlttd" valign="top">
                    <input type="file" class="geral" name="arquivo" id="arquivo" accept=".txt,text/plain" required>
                    @if($errors->has('arquivo'))
                        <br><span style="color: red;">{{ $errors->first('arquivo') }}</span>
                    @endif
                    @if($errors->has('ref_cod_escola'))
                        <br><span style="color: red;">{{ $errors->first('ref_cod_escola') }}</span>
                    @endif
                </td>
            </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 16px;">
            <button class="btn-green" type="submit">
                <i class="fa fa-upload"></i> Importar arquivo
            </button>
        </div>
    </form>
@endsection

@prepend('scripts')
    <script type="text/javascript" src="{{ Asset::get('/vendor/legacy/Portabilis/Assets/Javascripts/ClientApi.js') }}"></script>
    <script type="text/javascript" src="{{ Asset::get('/vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/DynamicInput.js') }}"></script>
    <script type="text/javascript" src="{{ Asset::get('/vendor/legacy/DynamicInput/Assets/Javascripts/HandlesSelect.js') }}"></script>
    <script type="text/javascript" src="{{ Asset::get('/intranet/scripts/select.js') }}"></script>
@endprepend
