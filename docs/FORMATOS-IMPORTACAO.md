# Formatos de importação

Catálogo em `EducacensoImportFormatCatalog`. Rota de cada formato:

```text
/educacenso/importacao/{ano}/{slug}
```

Arquivos: **TXT** delimitado por pipe (`|`).

**Censo 2026 em ordem cronológica** (diferenças entre os sete formatos, quando usar cada um): [`FORMATOS-CENSO-2026.md`](FORMATOS-CENSO-2026.md).

## Visão geral dos menus

| Ano | Slug | Menu |
|-----|------|------|
| 2025 | `migracao-txt` | Migração TXT — Exportação INEP |
| 2025 | `identificacao-retorno-txt` | Identificação INEP — Retorno de alunos |
| 2026 | `migracao-txt` | Migração TXT — Exportação INEP |
| 2026 | `identificacao-retorno-txt` | Identificação INEP — Resultado encontrado |
| 2026 | `identificacao-provaveis-txt` | Identificação INEP — Resultados prováveis |
| 2026 | `identificacao-nao-encontrado-txt` | Identificação INEP — Não encontrados |
| 2026 | `inep-retorno-txt` | Retorno INEP — Códigos preenchidos |
| 2026 | `situacao-aluno-migracao-txt` | Situação do Aluno — Migração TXT |
| 2026 | `situacao-aluno-identificacao-txt` | Situação do Aluno — Identificação INEP |

## Tabelas gravadas

| Tabela | Conteúdo |
|--------|----------|
| `modules.educacenso_cod_aluno` | INEP do aluno |
| `modules.educacenso_cod_docente` | INEP do servidor/docente |
| `modules.educacenso_cod_turma` | INEP da turma |
| `modules.educacenso_matricula` | INEP da matrícula (enturmação) |
| `public.educacenso_imports` | Histórico (ano, escola, usuário) |
| `pmieducar.matricula` | Situação (`aprovado`) na importação de Situação do Aluno |

Campo `fonte` nas tabelas INEP: `importacao-inep-{ano}` ou `importacao-identificacao-{ano}`.

## Filtro por escola

Compara `cod_escola_inep` do arquivo com `modules.educacenso_cod_escola` da escola selecionada. Linhas de outras escolas são **ignoradas**.

> **Atenção:** se a escola não tiver INEP cadastrado, o filtro fica permissivo — ver [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).

---

## 2025

### `migracao-txt` (process `20071`)

| | |
|---|---|
| Parser | `migracao_txt` |
| Registros | 00, 10, 20, 30, 40, 50, 60 |
| Origem | Exportação i-Educar ou retorno INEP (layout Matrícula Inicial) |

| Registro | Grava em |
|----------|----------|
| 20 | `educacenso_cod_turma` |
| 30 | `educacenso_cod_aluno` / `educacenso_cod_docente` |
| 40, 50 | `educacenso_cod_docente` |
| 60 | `educacenso_cod_aluno`, `educacenso_cod_turma`, `educacenso_matricula` |

Índices zero-based após `explode('|')`.

### `identificacao-retorno-txt` (process `20072`)

| | |
|---|---|
| Parser | `identificacao_retorno_txt` |
| Origem | `RESULTADO_ENCONTRADO.txt` |
| Grava | `educacenso_cod_aluno` (somente alunos) |

---

## 2026

Detalhamento cronológico, diferenças entre formatos e árvore de decisão: [`FORMATOS-CENSO-2026.md`](FORMATOS-CENSO-2026.md).

Referência rápida por slug:

| Slug | Parser | Grava principalmente |
|------|--------|----------------------|
| `migracao-txt` | `migracao_txt` | INEP turma, pessoa, servidor, matrícula (reg. 20–60) |
| `identificacao-retorno-txt` | `identificacao_retorno_txt` | INEP aluno (encontrados) |
| `identificacao-provaveis-txt` | `identificacao_retorno_txt` | INEP aluno (prováveis) |
| `identificacao-nao-encontrado-txt` | `identificacao_retorno_txt` | INEP aluno (quando houver) |
| `inep-retorno-txt` | `inep_retorno_txt` | INEP turma, servidor, matrícula (reg. 20, 40, 50, 60) |
| `situacao-aluno-migracao-txt` | `situacao_aluno_txt` | Situação da matrícula (reg. 89–91) |
| `situacao-aluno-identificacao-txt` | `identificacao_retorno_txt` | INEP aluno (2ª etapa) |

---

## Adicionar formato/ano

1. `EducacensoImportFormatCatalog.php`
2. `Process.php`
3. Parser em `EducacensoImportService`
4. Migration de menu
5. Atualizar este arquivo
