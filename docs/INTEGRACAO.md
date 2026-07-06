# Integração com o i-Educar

## Visão geral

Este pacote é uma **biblioteca Laravel** carregada pelo aplicativo i-Educar. Ele **não** substitui:

- exportação e análise Educacenso do core (`EducacensoExportController`, `EducacensoAnaliseController`);
- pacote `portabilis/i-educar-educacenso-package` (se instalado);
- migrations oficiais das tabelas `modules.educacenso_*`.

O pacote adiciona **rotas HTTP**, **views**, **migrations de menu** e **serviços de importação** organizados por ano e formato.

## Provider

`iEducar\Packages\BuritiEducacensoImport\Providers\BuritiEducacensoImportServiceProvider`:

| Método `boot()` | Função |
|-----------------|--------|
| `loadRoutesFrom(routes/web.php)` | Rotas `/educacenso/importacao/...` |
| `loadViewsFrom(..., 'buriti-educacenso-import')` | Views do formulário e resultados |
| `loadMigrationsFrom(database/migrations)` | Menus e permissões |

Não registra comandos Artisan.

## Rotas

Prefixo: `/educacenso/importacao/{year}/{slug}`

| Método | Nome | Controller |
|--------|------|------------|
| GET | `buriti.educacenso-import.index` | `EducacensoImportController@index` |
| POST | `buriti.educacenso-import.import` | `EducacensoImportController@import` |
| GET | `buriti.educacenso-import.result` | `EducacensoImportController@result` |

Middleware (mesmo grupo das rotas web do i-Educar):

- `auth`, `ieducar.navigation`, `ieducar.footer`, `ieducar.xssbypass`, `ieducar.suspended`, `ieducar.checkresetpassword`

Autorização: `$this->authorize('view|modify', $format->process)` via policies de menu do app.

## Estrutura do pacote

```text
src/
├── Formats/           # Catálogo de formatos por ano
├── Http/Controllers/  # EducacensoImportController
├── Parsers/           # EducacensoTxtParser
├── Providers/         # BuritiEducacensoImportServiceProvider
├── Services/          # Importação INEP, situação, resultado
└── Process.php        # IDs de processo (menus)
database/migrations/   # Menus e permissões
resources/views/import/
routes/web.php
```

## Menus

Menu pai existente no i-Educar:

- **Educacenso** (processo `70`)
  - **Importações** (processo `9998848`)

O pacote cria subpastas **2025** e **2026** (type `2`) e links (type `3`) para cada formato.

### Processos (IDs)

| Process | Formato |
|---------|---------|
| 20071 | Migração TXT 2025 |
| 20072 | Identificação 2025 |
| 20073 | Migração TXT 2026 |
| 20074 | Identificação encontrado 2026 |
| 20075 | Identificação prováveis 2026 |
| 20076 | Identificação não encontrados 2026 |
| 20077 | Retorno INEP 2026 |
| 20078 | Situação aluno migração 2026 |
| 20079 | Situação aluno identificação 2026 |

Permissões: copiadas de usuários com acesso ao menu da **Exportação Educacenso** (processo **846**).

## Dependências do aplicativo

Classes e tabelas utilizadas em runtime:

| Área | Dependência |
|------|-------------|
| Modelos | `LegacySchool`, `LegacyStudent`, `Employee`, `LegacySchoolClass`, `LegacyEnrollment`, `LegacyRegistration` |
| INEP | `StudentInep`, `EmployeeInep`, `SchoolClassInep`, `EnrollmentInep` |
| Histórico | `EducacensoImport` (`educacenso_imports`) |
| UI | `App\Http\Controllers\Controller`, `layout.default`, `form.select-institution`, `form.select-school` |
| Autorização | Policies de `process` do i-Educar |

Função opcional do pacote Portabilis:

- `convertSituationEducacensoToIeducar()` — usada na importação de Situação do Aluno se `portabilis/i-educar-educacenso-package` estiver carregado.

## Fluxo de importação

```text
EducacensoImportController@import
    → EducacensoImportService::import()
        → EducacensoTxtParser (por parserType)
        → EducacensoInepImportWriter | EducacensoSituationImportWriter
        → EducacensoImport::create() (histórico)
    → redirect result (session)
EducacensoImportController@result
    → view import.result
```

## Plug-and-play

Instalações que usam `dex/composer-plug-and-play` devem registrar o pacote em `packages/plug-and-play.json`:

```json
{
    "type": "path",
    "url": "./packages/buriti/i-educar-educacenso-import-package",
    "symlink": true
}
```

ou repositório VCS apontando para o GitHub.

## Versionamento

- Tags Git (`v1.1.0`, …) alinham-se ao campo `version` do `composer.json`.
- Campo `extra.ieducar.compatible`: `2.11`, `2.12`.
- Matriz Laravel/PHP: [`docs/COMPATIBILIDADE.md`](COMPATIBILIDADE.md).

## Coexistência com Portabilis Educacenso

O pacote Portabilis pode registrar:

- `/educacenso/importacao/inep` — importação INEP assíncrona (jobs)
- `/educacenso/importacao/situacao` — situação do aluno
- `/educacenso/import-registrations` — importação completa por ano (2019–2025)

Este pacote Buriti **complementa** com menus hierárquicos por ano/formato e interface unificada. Em novas instalações, prefira um único fluxo para evitar confusão dos usuários.

## Fork e contribuição

Alterações em formatos ou novos anos devem manter:

- licença **GPL-2.0-or-later**;
- compatibilidade com layouts oficiais do INEP;
- migrations idempotentes (`updateOrCreate` em menus).

Repositório: [github.com/JaderGabriel/i-educar-educacenso-import-package](https://github.com/JaderGabriel/i-educar-educacenso-import-package)
