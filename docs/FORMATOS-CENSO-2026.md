# Formatos Censo Escolar 2026 — ordem cronológica

Guia dos **sete formatos de importação** disponíveis em **Importações → 2026**, na sequência em que aparecem no fluxo oficial do [Educacenso/INEP](https://educacenso.inep.gov.br).

Referência técnica (slugs, registros, tabelas): [`FORMATOS-IMPORTACAO.md`](FORMATOS-IMPORTACAO.md).

---

## Visão do fluxo

O Censo 2026 divide-se em duas **etapas** no calendário do INEP:

| Etapa | Objetivo no i-Educar | Formatos do pacote |
|-------|----------------------|--------------------|
| **1ª — Matrícula Inicial** | Vincular códigos INEP de turmas, servidores e alunos | 1 a 5 (abaixo) |
| **2ª — Situação do Aluno** | Atualizar situação final da matrícula no sistema | 6 e 7 (abaixo) |

```text
[i-Educar exporta] ──► [INEP: identificação] ──► [i-Educar importa retornos]
        │                        │
        └──── [INEP: migração] ──┴──► [i-Educar importa códigos INEP]
                                              │
                    [INEP: Situação do Aluno] ▼
                              [i-Educar importa situação / identificação]
```

---

## Resumo em ordem cronológica

| # | Momento no Censo | Menu no i-Educar | Slug | O que muda em relação ao anterior |
|---|------------------|------------------|------|-----------------------------------|
| 1 | Antes de enviar ao INEP | Migração TXT — Exportação INEP | `migracao-txt` | Arquivo **completo** (reg. 00–60); base para envio ao portal |
| 2 | Retorno da identificação (encontrados) | Identificação — Resultado encontrado | `identificacao-retorno-txt` | Só **alunos** com match confirmado pelo INEP |
| 3 | Retorno da identificação (prováveis) | Identificação — Resultados prováveis | `identificacao-provaveis-txt` | Mesmo layout do #2, mas vínculos **incertos** |
| 4 | Retorno da identificação (não encontrados) | Identificação — Não encontrados | `identificacao-nao-encontrado-txt` | Lista de quem o INEP **não identificou** |
| 5 | Após migração processada no INEP | Retorno INEP — Códigos preenchidos | `inep-retorno-txt` | Códigos INEP de **turma, gestor, docente e matrícula** (sem reg. 30) |
| 6 | 2ª etapa — situação no INEP | Situação do Aluno — Migração TXT | `situacao-aluno-migracao-txt` | Grava **situação da matrícula**, não códigos INEP |
| 7 | 2ª etapa — identificação no INEP | Situação do Aluno — Identificação INEP | `situacao-aluno-identificacao-txt` | Retorno de identificação na 2ª etapa (códigos de aluno) |

---

## 1ª etapa — Matrícula Inicial

### 1. Migração TXT — Exportação INEP

`migracao-txt` · process `20073` · parser `migracao_txt`

**Quando:** início do processo; antes ou durante o envio ao portal INEP (**Matrícula Inicial → Migração**).

**Origem do arquivo:**

- Exportação do **i-Educar** (ou de outro sistema) com layout Censo 2026; ou
- Arquivo retornado pelo INEP que ainda contenha o layout completo.

**Conteúdo:** registros **00** (cabeçalho), **10** (escola), **20** (turma), **30** (pessoa), **40** (gestor), **50** (docente), **60** (aluno/enturmação).

**O que o pacote grava:**

| Registro | Dado importado |
|----------|----------------|
| 20 | INEP da turma |
| 30 | INEP de aluno ou docente (conforme tipo da pessoa) |
| 40, 50 | INEP do gestor/docente |
| 60 | INEP do aluno, da turma e da matrícula (enturmação) |

**Diferença dos demais:** único formato da 1ª etapa que processa o **arquivo inteiro** e cobre **todos os tipos de vínculo INEP** (turma, pessoa, servidor, matrícula). Registros 00 e 10 são lidos mas não alteram cadastros.

**Uso típico:** conferir códigos já presentes no arquivo de exportação; ou reimportar um TXT completo retornado pelo INEP quando ele mantém todos os registros.

---

### 2. Identificação INEP — Resultado encontrado

`identificacao-retorno-txt` · process `20074` · parser `identificacao_retorno_txt`

**Quando:** após enviar o arquivo ao INEP em **Migração → Identificação** e baixar o ZIP de retorno.

**Arquivos INEP:** `RESULTADO_ENCONTRADO.txt`, `RESULTADO_CERTO.txt`.

**Conteúdo:** linhas de identificação com registro **30** (`30|inep_escola|cod_pessoa|cod_inep`) ou pares `cod_pessoa|cod_inep`.

**O que o pacote grava:** apenas `educacenso_cod_aluno` — o INEP confirmou a correspondência entre o código interno e o código INEP do **aluno**.

**Diferença do #1:**

| Aspecto | Migração (#1) | Identificação encontrado (#2) |
|---------|---------------|-------------------------------|
| Escopo | Turma, servidor, aluno, matrícula | **Somente aluno** |
| Confiança | Depende do arquivo enviado | Match **confirmado** pelo INEP |
| Registros | 00–60 | Apenas linhas de identificação |

**Uso típico:** primeiro retorno a importar após a identificação — maior taxa de sucesso e menor risco.

---

### 3. Identificação INEP — Resultados prováveis

`identificacao-provaveis-txt` · process `20075` · parser `identificacao_retorno_txt` *(igual ao #2)*

**Quando:** mesmo momento da identificação; arquivo separado no ZIP do INEP.

**Arquivo INEP:** `RESULTADO_PROVAVEIS.txt`.

**O que o pacote grava:** mesmo que o #2 (`educacenso_cod_aluno`).

**Diferença do #2:** o INEP **sugere** correspondências, mas **não garante** que o código INEP pertence àquela pessoa. O parser e a gravação são idênticos; a diferença é **operacional e de confiança**.

**Uso típico:** importar **somente após conferência manual** linha a linha. O INEP recomenda validar antes de aceitar os vínculos.

---

### 4. Identificação INEP — Não encontrados

`identificacao-nao-encontrado-txt` · process `20076` · parser `identificacao_retorno_txt` *(igual ao #2)*

**Quando:** mesmo momento da identificação.

**Arquivo INEP:** `RESULTADO_SEMID.txt`.

**O que o pacote grava:** códigos INEP **quando presentes** no arquivo; na prática, a maioria das linhas não traz código utilizável.

**Diferença do #2 e #3:** lista pessoas que o INEP **não conseguiu identificar**. Serve sobretudo para **diagnóstico** (quem falta cadastrar ou corrigir no i-Educar antes de reenviar ao INEP).

**Uso típico:** consulta e correção de cadastros; importação costuma gerar poucos ou nenhum registro gravado.

---

### 5. Retorno INEP — Códigos preenchidos

`inep-retorno-txt` · process `20077` · parser `inep_retorno_txt`

**Quando:** **depois** que o INEP processa a migração da escola e disponibiliza o arquivo com códigos INEP atribuídos pelo sistema federal.

**Origem:** download no portal após conclusão da migração (**Matrícula Inicial → Migração**).

**Conteúdo:** registros **20**, **40**, **50** e **60** com campos INEP preenchidos pelo INEP. O registro **30** (pessoas avulsas) é **ignorado** pelo parser deste formato.

**O que o pacote grava:**

| Registro | Dado |
|----------|------|
| 20 | INEP da turma |
| 40, 50 | INEP do gestor/docente |
| 60 | INEP do aluno, turma e matrícula |

**Diferença do #1:**

| Aspecto | Migração (#1) | Retorno INEP (#5) |
|---------|---------------|-------------------|
| Momento | Antes/durante envio | **Após** processamento no INEP |
| Registro 30 | Processado | **Ignorado** |
| Códigos INEP | Podem estar vazios na exportação | Preenchidos **pelo INEP** |

**Diferença do #2–#4:** cobre **turmas e servidores**, não só alunos; é o retorno **oficial pós-migração**, não o retorno da pré-identificação.

**Uso típico:** etapa principal para sincronizar todos os códigos INEP após o INEP aceitar a migração da escola.

---

## 2ª etapa — Situação do Aluno

A 2ª etapa ocorre **após** o fechamento da Matrícula Inicial no INEP. Pressupõe códigos INEP de turma e aluno já importados (#2–#5).

### 6. Situação do Aluno — Migração TXT

`situacao-aluno-migracao-txt` · process `20078` · parser `situacao_aluno_txt`

**Quando:** na coleta da **Situação do Aluno** no portal INEP (2ª etapa do Censo).

**Origem:** arquivo retornado pelo INEP com registros **89** (escola), **90** e **91** (situação por aluno/turma).

**O que o pacote grava:** `pmieducar.matricula.aprovado` (situação final: aprovado, reprovado, transferido, abandono, etc.).

**Diferença de todos os formatos anteriores:**

| Aspecto | 1ª etapa (#1–#5) | Situação (#6) |
|---------|------------------|---------------|
| Objetivo | Códigos INEP | **Situação da matrícula** |
| Registros | 00–60 | **89, 90, 91** |
| Tabelas | `educacenso_cod_*` | `pmieducar.matricula` |

**Atenção:** o layout esperado é o do **retorno INEP** (situação no índice `[7]`). O export gerado pelo i-Educar usa posições diferentes — ver [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).

**Uso típico:** após o INEP processar a situação dos alunos, importar para alinhar o resultado final no i-Educar.

---

### 7. Situação do Aluno — Identificação INEP

`situacao-aluno-identificacao-txt` · process `20079` · parser `identificacao_retorno_txt` *(igual ao #2)*

**Quando:** se o INEP exigir **nova rodada de identificação** na 2ª etapa (alunos ainda sem código INEP na situação).

**O que o pacote grava:** `educacenso_cod_aluno` — mesmo comportamento do #2.

**Diferença do #6:**

| Aspecto | Situação migração (#6) | Situação identificação (#7) |
|---------|------------------------|-----------------------------|
| Dado gravado | Situação da matrícula | **Código INEP do aluno** |
| Pré-requisito | INEP já vinculado | Alunos ainda sem INEP na 2ª etapa |

**Uso típico:** complementar códigos INEP faltantes antes ou depois de importar a situação (#6), conforme orientação do INEP para a escola.

---

## Qual menu usar? — árvore de decisão

```text
Ainda na Matrícula Inicial?
├─ Sim → Já processou migração no INEP?
│   ├─ Não → Retorno da identificação?
│   │   ├─ ENCONTRADO/CERTO  → #2 identificacao-retorno-txt
│   │   ├─ PROVAVEIS         → #3 identificacao-provaveis-txt
│   │   └─ SEMID             → #4 identificacao-nao-encontrado-txt
│   └─ Sim → Arquivo com códigos do INEP → #5 inep-retorno-txt
│            (ou #1 migracao-txt se o arquivo for completo 00–60)
└─ Não (2ª etapa) → Precisa de código INEP ou de situação?
    ├─ Situação final      → #6 situacao-aluno-migracao-txt
    └─ Identificar aluno   → #7 situacao-aluno-identificacao-txt
```

---

## Comparativo dos parsers

| Parser | Formatos que usam | O que processa |
|--------|-------------------|----------------|
| `migracao_txt` | #1 | Reg. 00–60 (grava 20, 30, 40, 50, 60) |
| `identificacao_retorno_txt` | #2, #3, #4, #7 | Linhas de identificação → INEP aluno |
| `inep_retorno_txt` | #5 | Reg. 20, 40, 50, 60 (ignora 30) |
| `situacao_aluno_txt` | #6 | Reg. 89, 90, 91 → situação matrícula |

---

## Referências INEP

- [Migração — Matrícula Inicial](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao)
- [Situação do Aluno — orientações 2025/2026](https://www.gov.br/inep/pt-br/centrais-de-conteudo/acervo-linha-editorial/publicacoes-institucionais/estatisticas-e-indicadores-educacionais/etapas-e-instrucoes-gerais-para-a-migracao-no-sistema-educacenso-2013-2a-etapa-da-coleta-do-censo-escolar-2025-situacao-do-aluno)

Passo a passo operacional: [`USO.md`](USO.md).
