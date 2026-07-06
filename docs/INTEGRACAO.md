# Integração com o i-Educar

Biblioteca Laravel carregada pelo aplicativo. **Não substitui** exportação/análise do core nem migrations das tabelas `modules.educacenso_*`.

Adiciona rotas, views, migrations de menu e serviços de importação por ano/formato.

## Provider

`BuritiEducacensoImportServiceProvider` registra:

| Recurso | Origem |
|---------|--------|
| Rotas | `routes/web.php` |
| Views | `resources/views/import/` (namespace `buriti-educacenso-import`) |
| Migrations | `database/migrations/` |

## Rotas

Prefixo: `/educacenso/importacao/{year}/{slug}` (`year`: 2025 ou 2026)

| Método | Nome | Ação |
|--------|------|------|
| GET | `buriti.educacenso-import.index` | Formulário |
| POST | `buriti.educacenso-import.import` | Processamento |
| GET | `buriti.educacenso-import.result` | Resultados |

Middleware: `auth`, `ieducar.navigation`, `ieducar.footer`, `ieducar.xssbypass`, `ieducar.suspended`, `ieducar.checkresetpassword`.

Autorização: `$this->authorize('view|modify', $format->process)`.

## Estrutura

```text
src/Formats/          Catálogo por ano
src/Parsers/          EducacensoTxtParser
src/Services/         Writers INEP e situação
src/Http/Controllers/ EducacensoImportController
src/Process.php       IDs de menu (20071–20079)
```

## Menus e processos

Pai: **Educacenso** (`70`) → **Importações** (`9998848`).

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

Permissões copiadas da Exportação Educacenso (**846**).

## Dependências em runtime

Modelos: `LegacySchool`, `LegacyStudent`, `Employee`, `LegacySchoolClass`, `LegacyEnrollment`, `LegacyRegistration`, `StudentInep`, `EmployeeInep`, `SchoolClassInep`, `EnrollmentInep`, `EducacensoImport`.

## Fluxo

```text
Controller@import → EducacensoImportService
  → EducacensoTxtParser → InepImportWriter | SituationImportWriter
  → EducacensoImport::create()
  → redirect result (session)
```

## Coexistência com Portabilis

O pacote Portabilis oferece importações legadas (`/educacenso/importacao/inep`, `/situacao`, `/import-registrations`) com jobs assíncronos. Este pacote organiza o fluxo por ano/formato com filtro explícito por escola. Detalhes e comparação: [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).
