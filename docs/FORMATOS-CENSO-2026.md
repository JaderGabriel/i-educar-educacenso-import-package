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

| # | Momento no Censo | Menu no i-Educar | Slug | Tratamento pelo pacote | O que muda em relação ao anterior |
|---|------------------|------------------|------|------------------------|-----------------------------------|
| 1 | Antes de enviar ao INEP | Migração TXT — Exportação INEP | `migracao-txt` | **Parcial** | Arquivo **completo** (reg. 00–60); base para envio ao portal |
| 2 | Retorno da identificação (encontrados) | Identificação — Resultado encontrado | `identificacao-retorno-txt` | **Pleno** * | Só **alunos** com match confirmado pelo INEP |
| 3 | Retorno da identificação (prováveis) | Identificação — Resultados prováveis | `identificacao-provaveis-txt` | **Parcial** † | Mesmo layout do #2, mas vínculos **incertos** |
| 4 | Retorno da identificação (não encontrados) | Identificação — Não encontrados | `identificacao-nao-encontrado-txt` | **Limitado** | Lista de quem o INEP **não identificou** |
| 5 | Após migração processada no INEP | Retorno INEP — Códigos preenchidos | `inep-retorno-txt` | **Pleno** * | Códigos INEP de **turma, gestor, docente e matrícula** (sem reg. 30) |
| 6 | 2ª etapa — situação no INEP | Situação do Aluno — Migração TXT | `situacao-aluno-migracao-txt` | **Parcial** ‡ | Grava **situação da matrícula**, não códigos INEP |
| 7 | 2ª etapa — identificação no INEP | Situação do Aluno — Identificação INEP | `situacao-aluno-identificacao-txt` | **Pleno** * | Retorno de identificação na 2ª etapa (códigos de aluno) |

**Legenda de tratamento**

| Status | Significado |
|--------|-------------|
| **Pleno** * | O pacote lê o layout, grava os dados previstos e é o uso recomendado para aquele arquivo — desde que os [pré-requisitos globais](#pré-requisitos-globais) estejam atendidos. |
| **Parcial** | Parte dos registros é gravada; há ressalvas técnicas ou operacionais relevantes (ver seção do formato). |
| **Limitado** | O parser aceita o arquivo, mas o conteúdo típico traz **pouco ou nenhum dado importável**. |
| **Impedido** | O arquivo **não deve** ser usado naquele menu — os dados serão ignorados ou interpretados de forma errada. |

\* Mesmas ressalvas de vínculo (aluno/turma/matrícula deve existir no i-Educar) e [fragilidades conhecidas](#fragilidades-que-afetam-vários-formatos).

† O pacote grava como no #2, mas o INEP **não garante** o vínculo — risco operacional, não técnico.

‡ Funciona com **retorno INEP**; **impedido** se o arquivo for export do i-Educar (layout diferente).

---

## Pré-requisitos globais

Valem para **todos** os formatos. Se algum falhar, a importação falha parcialmente ou grava dados incorretos:

| Pré-requisito | Por quê |
|---------------|---------|
| **INEP da escola** cadastrado em `modules.educacenso_cod_escola` | Sem ele, o filtro por escola **não restringe** o arquivo e linhas de outras escolas podem ser processadas. |
| **Cadastros já existentes** no i-Educar (turma, aluno, servidor, matrícula) | O pacote **atualiza códigos INEP e situação**; não cria escola, turma, pessoa nem matrícula. |
| **Código interno no TXT** correspondente ao sistema (`cod_turma`, `cod_aluno`/`ref_idpes`, `cod_servidor`) | Linha sem vínculo local gera **erro** ou é **ignorada**. |
| **Uma escola por importação** | Arquivo multi-escola é aceito, mas só a escola selecionada é processada; o restante conta como ignorado. |
| **Arquivo `.txt` até 50 MB** | Processamento **síncrono** na requisição HTTP — arquivos grandes podem estourar tempo limite. |

---

## Fragilidades que afetam vários formatos

| Fragilidade | Formatos afetados | Efeito |
|-------------|-------------------|--------|
| Turmas com sufixo `-turno` (ex.: `12345-3`) | #1, #5, #6 | Sufixo removido; INEP pode gravar na turma errada se houver vários turnos. |
| `matricula_inep` sem filtro por data base do Censo | #1, #5 | Pode vincular à enturmação ativa errada em remanejamentos. |
| Registro 60 contado como importado com erro parcial | #1, #5 | Tela de resultados pode superestimar sucesso. |
| Identificação ignora **docentes** | #2, #3, #4, #7 | INEP de servidor no retorno de identificação **não é gravado**. |
| Sem transação de banco | Todos | Falha no meio do arquivo deixa dados **parcialmente** importados. |
| Número da “linha” nos erros não é a linha real do TXT | Todos | Dificulta achar o registro no arquivo original. |

Detalhes: [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).

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

#### Tratamento pelo pacote — **Parcial**

| | |
|---|---|
| **Processa e grava** | Reg. **20** (INEP turma), **30** (INEP aluno/docente), **40/50** (INEP gestor/docente), **60** (INEP aluno, turma e matrícula) — quando o campo INEP no arquivo está preenchido e o vínculo existe no i-Educar. |
| **Lê mas não grava** | Reg. **00** (cabeçalho) e **10** (escola) — não alteram cadastros. |
| **Não faz** | Criar escola, turma, pessoa ou matrícula; validar layout INEP; importar arquivo multi-escola de uma só vez com efeito em todas as escolas. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| Arquivo é **exportação do i-Educar** enviada ao INEP (códigos INEP ainda vazios) | Importação **quase vazia** — não há códigos para gravar. Uso correto: arquivo **retornado** pelo INEP com INEP preenchido, ou preferir o formato **#5**. |
| Aluno/turma/servidor do TXT **não existe** no i-Educar | Linha em **erro** ou ignorada — não cria cadastro. |
| Escola sem INEP cadastrado | Filtro por escola **ineficaz** — risco de processar linhas de outras escolas. |
| Turma com código `12345-3` (integral/turno) | Pode gravar INEP na turma errada. |
| Reg. **60** com enturmação não encontrada | INEP do aluno pode ser gravado, mas matrícula INEP falha; linha ainda conta como “importada”. |

**Veredicto:** adequado para **reimportar retorno** com layout completo. **Não** substitui envio ao portal INEP nem importação censitária que cria cadastros.

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

#### Tratamento pelo pacote — **Pleno** *

| | |
|---|---|
| **Processa e grava** | `educacenso_cod_aluno` — uma linha por aluno identificado, a partir de reg. **30** ou par `cod_pessoa\|cod_inep`. |
| **Condições para gravar** | Código da pessoa no TXT deve resolver para `cod_aluno` (ou `ref_idpes`); aluno com matrícula na escola/ano; código INEP presente na linha. |
| **Não grava** | INEP de **docentes** ou gestores — mesmo que venham no arquivo. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| `cod_pessoa` não bate com aluno no i-Educar | **Erro** — aluno não encontrado. |
| Aluno sem matrícula na escola/ano | **Erro** ou ignorado. |
| INEP da escola no TXT difere da escola selecionada | Linha **ignorada** (filtro por escola). |
| Escola sem INEP cadastrado | Filtro **ineficaz** — ver pré-requisitos globais. |

**Veredicto:** formato **mais indicado** para importar identificação com match confirmado pelo INEP.

---

### 3. Identificação INEP — Resultados prováveis

`identificacao-provaveis-txt` · process `20075` · parser `identificacao_retorno_txt` *(igual ao #2)*

**Quando:** mesmo momento da identificação; arquivo separado no ZIP do INEP.

**Arquivo INEP:** `RESULTADO_PROVAVEIS.txt`.

**O que o pacote grava:** mesmo que o #2 (`educacenso_cod_aluno`).

**Diferença do #2:** o INEP **sugere** correspondências, mas **não garante** que o código INEP pertence àquela pessoa. O parser e a gravação são idênticos; a diferença é **operacional e de confiança**.

**Uso típico:** importar **somente após conferência manual** linha a linha. O INEP recomenda validar antes de aceitar os vínculos.

#### Tratamento pelo pacote — **Parcial** †

| | |
|---|---|
| **Capacidade técnica** | **Idêntica ao #2** — mesmo parser, mesma gravação em `educacenso_cod_aluno`. |
| **Diferença** | Não é limitação do pacote: o INEP **não garante** que o vínculo sugerido está correto. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| Importar sem conferência manual | Pacote **gravará** vínculos possivelmente **errados** — o sistema não distingue “provável” de “encontrado”. |
| Mesmas condições do #2 | Aluno deve existir; docentes não são importados; filtro por escola. |

**Veredicto:** o pacote **consegue tratar** o arquivo, mas o uso seguro exige **validação humana antes** da importação. Não há flag ou modo “somente leitura” no pacote.

---

### 4. Identificação INEP — Não encontrados

`identificacao-nao-encontrado-txt` · process `20076` · parser `identificacao_retorno_txt` *(igual ao #2)*

**Quando:** mesmo momento da identificação.

**Arquivo INEP:** `RESULTADO_SEMID.txt`.

**O que o pacote grava:** códigos INEP **quando presentes** no arquivo; na prática, a maioria das linhas não traz código utilizável.

**Diferença do #2 e #3:** lista pessoas que o INEP **não conseguiu identificar**. Serve sobretudo para **diagnóstico** (quem falta cadastrar ou corrigir no i-Educar antes de reenviar ao INEP).

**Uso típico:** consulta e correção de cadastros; importação costuma gerar poucos ou nenhum registro gravado.

#### Tratamento pelo pacote — **Limitado**

| | |
|---|---|
| **Capacidade técnica** | Mesmo parser do #2 — **aceita** o arquivo sem erro de formato. |
| **Conteúdo típico do INEP** | `RESULTADO_SEMID.txt` lista pessoas **sem** código INEP atribuído — campos INEP vazios ou ausentes. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| Linhas sem `cod_inep` preenchido | **Ignoradas** — nada a gravar. |
| Expectativa de importar códigos em massa | **Frustrada** — arquivo serve para **diagnóstico**, não para sincronização. |

**Veredicto:** não há impedimento técnico de upload, mas na prática o pacote **quase não utiliza** os dados. Use para identificar quem corrigir no i-Educar e reenviar ao INEP.

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

#### Tratamento pelo pacote — **Pleno** *

| | |
|---|---|
| **Processa e grava** | Reg. **20** (turma), **40/50** (gestor/docente), **60** (aluno, turma, matrícula INEP). |
| **Ignora deliberadamente** | Reg. **30** (pessoas) — mesmo que o arquivo contenha INEP de alunos só no reg. 30, **não será importado** neste formato. |
| **Vantagem sobre #1** | Focado no retorno pós-migração; não tenta processar registros irrelevantes. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| INEP de aluno **apenas** no reg. 30 (sem reg. 60) | **Não importado** — use #1 ou importe identificação (#2). |
| Vínculos locais inexistentes (turma, servidor, enturmação) | **Erro** ou ignorado — igual #1. |
| Turmas `-turno`, matrícula sem data base, escola sem INEP | Mesmas fragilidades do #1. |

**Veredicto:** **melhor formato** da 1ª etapa para sincronizar códigos após migração aceita pelo INEP, desde que o retorno traga reg. 20/40/50/60 preenchidos.

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

#### Tratamento pelo pacote — **Parcial** ‡

| | |
|---|---|
| **Processa e grava** | Reg. **90** e **91** → `pmieducar.matricula.aprovado` (e flags de enturmação para transferido/abandono/falecido). Reg. **89** é lido para contexto da escola. |
| **Condições para gravar** | INEP de turma e aluno já em `educacenso_cod_turma` e `educacenso_cod_aluno`; matrícula ativa no ano para o par turma+aluno. |
| **Conversão de situação** | Usa `convertSituationEducacensoToIeducar()` se o pacote Portabilis estiver instalado; senão, mapeamento simplificado embutido. |

**Impedimentos**

| Situação | Resultado |
|----------|-----------|
| Arquivo é **export do i-Educar** (situação no índice `[8]`, código interno da turma em `[3]`) | **Impedido** — campos lidos nas posições erradas; situações gravadas incorretamente ou ignoradas. |
| Aluno/turma **sem INEP** importado na 1ª etapa | **Erro** — vínculo não encontrado. |
| Layout de retorno INEP correto, mas matrícula inexistente | Linha em **erro**. |

**Veredicto:** funciona de forma confiável **somente** com TXT de **retorno do portal INEP** na 2ª etapa. Não use o export gerado pelo i-Educar neste menu.

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

#### Tratamento pelo pacote — **Pleno** *

| | |
|---|---|
| **Processa e grava** | Igual ao **#2** — `educacenso_cod_aluno` a partir do retorno de identificação. |
| **Não grava** | Situação da matrícula (use o **#6**) nem INEP de docentes. |

**Impedimentos e ressalvas**

| Situação | Resultado |
|----------|-----------|
| Confundir com o menu de situação (#6) | Códigos INEP gravados, mas **situação não atualizada** — menus têm finalidades distintas. |
| Mesmas condições do #2 | Aluno deve existir no i-Educar; filtro por escola. |

**Veredicto:** adequado quando a 2ª etapa exige nova identificação de alunos; não substitui o #6 para situação final.

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

| Parser | Formatos | O que processa | Limitação principal |
|--------|----------|----------------|---------------------|
| `migracao_txt` | #1 | Reg. 00–60 (grava 20, 30, 40, 50, 60) | Não cria cadastros; export sem INEP gera importação vazia |
| `identificacao_retorno_txt` | #2, #3, #4, #7 | Identificação → INEP aluno | Só alunos; #3 exige conferência manual; #4 pouco importável |
| `inep_retorno_txt` | #5 | Reg. 20, 40, 50, 60 | Ignora reg. 30 |
| `situacao_aluno_txt` | #6 | Reg. 89–91 → situação | Layout do retorno INEP; export i-Educar **impedido** |

## O que o pacote não faz em nenhum formato

- Criar ou atualizar cadastros censitários completos (escola, turma, pessoa, matrícula a partir do zero).
- Importar várias escolas de um único upload com efeito em todas.
- Validar manual INEP (quantidade de campos, regras de negócio do Censo).
- Desfazer ou auditar alterações campo a campo.
- Processar em fila assíncrona (arquivos muito grandes podem falhar por timeout).

Para importação censitária completa ou jobs em background, use o pacote **Portabilis** (`/educacenso/import-registrations`, `/educacenso/importacao/inep`).

---

## Referências INEP

- [Migração — Matrícula Inicial](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao)
- [Situação do Aluno — orientações 2025/2026](https://www.gov.br/inep/pt-br/centrais-de-conteudo/acervo-linha-editorial/publicacoes-institucionais/estatisticas-e-indicadores-educacionais/etapas-e-instrucoes-gerais-para-a-migracao-no-sistema-educacenso-2013-2a-etapa-da-coleta-do-censo-escolar-2025-situacao-do-aluno)

Passo a passo operacional: [`USO.md`](USO.md).
