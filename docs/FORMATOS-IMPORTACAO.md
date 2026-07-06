# Formatos de importação

Catálogo dos formatos registrados em `EducacensoImportFormatCatalog`. Cada formato corresponde a um item de menu e a uma rota:

```text
/educacenso/importacao/{ano}/{slug}
```

Arquivos aceitos: **TXT** delimitado por pipe (`|`), codificação conforme layout INEP (sem acentos em campos exportados pelo INEP).

## Resumo por ano

| Ano | Qtd. formatos | Etapas |
|-----|---------------|--------|
| 2025 | 2 | Matrícula Inicial |
| 2026 | 7 | Matrícula Inicial + Situação do Aluno |

## 2025

### `migracao-txt` — Migração TXT — Exportação INEP

| Propriedade | Valor |
|-------------|-------|
| Process | `20071` |
| Parser | `migracao_txt` |
| Registros | 00, 10, 20, 30, 40, 50, 60 |

**Origem:** exportação do i-Educar ou arquivo retornado pelo INEP com layout completo da Matrícula Inicial.

**Grava:**

| Registro | Campos utilizados | Tabela |
|----------|-------------------|--------|
| 20 | INEP escola [1], cod turma [2], INEP turma [3] | `educacenso_cod_turma` |
| 30 | cod pessoa [2], INEP [3] | `educacenso_cod_aluno` / `educacenso_cod_docente` |
| 40 | cod servidor [2], INEP gestor [3] | `educacenso_cod_docente` |
| 50 | cod servidor [2], INEP docente [3] | `educacenso_cod_docente` |
| 60 | idpes [2], INEP aluno [3], turma [4], INEP turma [5], matrícula INEP [6] | `educacenso_cod_aluno`, `educacenso_cod_turma`, `educacenso_matricula` |

Índices são **zero-based** no array após `explode('|')`.

---

### `identificacao-retorno-txt` — Identificação INEP — Retorno de alunos

| Propriedade | Valor |
|-------------|-------|
| Process | `20072` |
| Parser | `identificacao_retorno_txt` |

**Origem:** `RESULTADO_ENCONTRADO.txt` ou linhas `30|...` / `cod_pessoa|cod_inep`.

**Grava:** `modules.educacenso_cod_aluno` (aluno com matrícula na escola/ano).

---

## 2026 — Matrícula Inicial

### `migracao-txt`

Igual ao formato 2025, layout Censo Escolar **2026**. Process: `20073`.

### `identificacao-retorno-txt` — Resultado encontrado

| Propriedade | Valor |
|-------------|-------|
| Process | `20074` |
| Arquivos | `RESULTADO_ENCONTRADO.txt`, `RESULTADO_CERTO.txt` |

### `identificacao-provaveis-txt` — Resultados prováveis

| Propriedade | Valor |
|-------------|-------|
| Process | `20075` |
| Arquivo | `RESULTADO_PROVAVEIS.txt` |
| Parser | `identificacao_retorno_txt` |

Mesmo parser do resultado encontrado. Uso sob responsabilidade da escola (conferência manual).

### `identificacao-nao-encontrado-txt` — Não encontrados

| Propriedade | Valor |
|-------------|-------|
| Process | `20076` |
| Arquivo | `RESULTADO_SEMID.txt` |

Importa códigos quando presentes; na maioria dos casos haverá poucos ou nenhum registro importável.

### `inep-retorno-txt` — Retorno INEP — Códigos preenchidos

| Propriedade | Valor |
|-------------|-------|
| Process | `20077` |
| Parser | `inep_retorno_txt` |
| Registros | 20, 40, 50, 60 (ignora 30) |

Arquivo retornado pelo Educacenso após processamento da migração, com campos INEP preenchidos pelo INEP.

---

## 2026 — Situação do Aluno

### `situacao-aluno-migracao-txt` — Migração TXT

| Propriedade | Valor |
|-------------|-------|
| Process | `20078` |
| Parser | `situacao_aluno_txt` |
| Registros | 89, 90, 91 |

**Layout (exportação INEP, índices 0-based):**

| Registro | Campos relevantes |
|----------|-------------------|
| 89 | [1] INEP escola |
| 90 | [2] INEP escola, [3] INEP turma, [4] INEP aluno, [7] situação Educacenso |
| 91 | Idem reg. 90 (alunos admitidos após data de referência) |

**Grava:** atualiza `pmieducar.matricula.aprovado` (e flags de enturmação para transferido/abandono/falecido).

Conversão de situação: usa `convertSituationEducacensoToIeducar()` quando disponível (pacote Portabilis Educacenso); caso contrário, mapeamento padrão embutido.

### `situacao-aluno-identificacao-txt` — Identificação INEP

| Propriedade | Valor |
|-------------|-------|
| Process | `20079` |
| Parser | `identificacao_retorno_txt` |

Retorno de identificação na 2ª etapa; grava códigos INEP de alunos.

---

## Filtro por escola

Para todos os formatos:

1. Obtém `cod_escola_inep` da escola selecionada em `modules.educacenso_cod_escola`.
2. Compara com o INEP da escola em cada linha do arquivo.
3. Linhas de outras escolas são contadas como **ignoradas**.

Turmas são validadas com `pmieducar.turma` (`ref_ref_cod_escola` + `ano`).

## Fonte dos dados importados

Campo `fonte` em tabelas INEP:

| Contexto | Valor |
|----------|-------|
| Migração / retorno INEP | `importacao-inep-{ano}` |
| Identificação | `importacao-identificacao-{ano}` |

## Adicionar novo formato/ano

1. Incluir definição em `src/Formats/EducacensoImportFormatCatalog.php`.
2. Adicionar constante em `src/Process.php`.
3. Implementar ou reutilizar `parserType` em `EducacensoImportService`.
4. Criar migration de menu (ou estender migration existente).
5. Documentar neste arquivo e no `README.md`.
