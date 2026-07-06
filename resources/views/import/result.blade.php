@extends('layout.default')

@push('styles')
    <link rel="stylesheet" type="text/css" href="{{ Asset::get('css/ieducar.css') }}"/>
@endpush

@section('content')
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
        <tbody>
        <tr>
            <td class="formdktd" colspan="2" height="24">
                <b>Resultado — {{ $format->title }} ({{ $year }})</b>
            </td>
        </tr>
        <tr>
            <td class="formmdtd" width="30%"><span class="form">Arquivo</span></td>
            <td class="formmdtd">{{ $result->fileName }}</td>
        </tr>
        <tr>
            <td class="formlttd"><span class="form">Escola (código)</span></td>
            <td class="formlttd">{{ $result->schoolId }}</td>
        </tr>
        <tr>
            <td class="formmdtd"><span class="form">Linhas lidas</span></td>
            <td class="formmdtd">{{ $result->linesRead }}</td>
        </tr>
        <tr>
            <td class="formlttd"><span class="form">Registros importados</span></td>
            <td class="formlttd"><strong style="color: #3c763d;">{{ $result->imported }}</strong></td>
        </tr>
        <tr>
            <td class="formmdtd"><span class="form">Registros ignorados</span></td>
            <td class="formmdtd">{{ $result->skipped }}</td>
        </tr>
        <tr>
            <td class="formlttd"><span class="form">Erros</span></td>
            <td class="formlttd"><strong style="color: {{ $result->errors > 0 ? '#a94442' : '#333' }};">{{ $result->errors }}</strong></td>
        </tr>
        </tbody>
    </table>

    @if(!empty($result->countsByType))
        <table class="table-default" style="margin-top: 20px;">
            <tr class="titulo-tabela-listagem">
                <th colspan="2">Detalhamento por tipo</th>
            </tr>
            @foreach($result->countsByType as $tipo => $quantidade)
                @if($quantidade > 0)
                    <tr>
                        <td>{{ str_replace('_', ' ', ucfirst($tipo)) }}</td>
                        <td>{{ $quantidade }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @endif

    @if(!empty($result->messages))
        <table class="table-default" style="margin-top: 20px;">
            <tr class="titulo-tabela-listagem">
                <th>Linha</th>
                <th>Tipo</th>
                <th>Mensagem</th>
            </tr>
            @foreach(array_slice($result->messages, 0, 100) as $message)
                <tr>
                    <td>{{ $message['linha'] }}</td>
                    <td>{{ $message['tipo'] }}</td>
                    <td>{{ $message['mensagem'] }}</td>
                </tr>
            @endforeach
        </table>
        @if(count($result->messages) > 100)
            <p class="text-muted" style="margin-top: 8px;">Exibindo os 100 primeiros erros de {{ count($result->messages) }}.</p>
        @endif
    @endif

    <div style="text-align: center; margin-top: 24px;">
        <a class="btn" href="{{ route('buriti.educacenso-import.index', ['year' => $year, 'slug' => $slug]) }}">
            Nova importação
        </a>
        <a class="btn" href="{{ url('/intranet/educar_educacenso_index.php') }}">
            Voltar ao Educacenso
        </a>
    </div>
@endsection
