# Formatos de importação

Catálogo em `EducacensoImportFormatCatalog`. Rota de cada formato:

```text
/educacenso/importacao/{ano}/{slug}
```

Arquivos: **TXT** delimitado por pipe (`|`).

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

## 2026 — Matrícula Inicial

### `migracao-txt` (process `20073`)

Igual ao 2025, layout Censo 2026.

### `identificacao-retorno-txt` (process `20074`)

Arquivos: `RESULTADO_ENCONTRADO.txt`, `RESULTADO_CERTO.txt`.

### `identificacao-provaveis-txt` (process `20075`)

Arquivo: `RESULTADO_PROVAVEIS.txt`. Mesmo parser; conferência manual obrigatória.

### `identificacao-nao-encontrado-txt` (process `20076`)

Arquivo: `RESULTADO_SEMID.txt`. Poucos registros importáveis na prática.

### `inep-retorno-txt` (process `20077`)

| | |
|---|---|
| Parser | `inep_retorno_txt` |
| Registros | 20, 40, 50, 60 |
| Origem | Retorno INEP pós-migração com códigos preenchidos |

---

## 2026 — Situação do Aluno

### `situacao-aluno-migracao-txt` (process `20078`)

| | |
|---|---|
| Parser | `situacao_aluno_txt` |
| Registros | 89, 90, 91 |
| Grava | `pmieducar.matricula.aprovado` |

Campos relevantes (retorno INEP, índices 0-based): reg. 90/91 — `[3]` INEP turma, `[4]` INEP aluno, `[7]` situação.

Usa `convertSituationEducacensoToIeducar()` se o pacote Portabilis estiver instalado.

### `situacao-aluno-identificacao-txt` (process `20079`)

Parser `identificacao_retorno_txt` — retorno de identificação na 2ª etapa.

---

## Adicionar formato/ano

1. `EducacensoImportFormatCatalog.php`
2. `Process.php`
3. Parser em `EducacensoImportService`
4. Migration de menu
5. Atualizar este arquivo
