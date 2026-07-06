# Revisão técnica e limitações

Documento de auditoria do pacote **buriti/i-educar-educacenso-import-package** (v1.1.3), confrontando a **proposta original** de importação Educacenso com o que está implementado, destacando fragilidades e limitações conhecidas.

Última revisão: julho/2026.

---

## 1. Proposta original

A proposta era disponibilizar no i-Educar um fluxo de **importação Educacenso/INEP** que:

| Requisito | Descrição |
|-----------|-----------|
| Menu hierárquico | **Educacenso → Importações**, com submenus por **ano** e **formato INEP** |
| Formatos por ano | Layouts oficiais (migração, identificação, situação do aluno) para **2025** e **2026** |
| Escopo por escola | Importação filtrada por **ano letivo + escola**, como a análise de exportação |
| Persistência | Gravar códigos INEP e dados de retorno na base local |
| Feedback | Tela final com **resultados** (totais, erros, detalhamento) |
| Empacotamento | Package Buriti, padrão dos demais pacotes do i-Educar |

---

## 2. O que o pacote atende

| Requisito | Status | Observação |
|-----------|--------|------------|
| Menus por ano/formato | **Atendido** | Migrations criam 2025 (2 formatos) e 2026 (7 formatos) |
| Filtro por escola | **Atendido** | Compara `cod_escola_inep` do arquivo com a escola selecionada |
| Códigos INEP (aluno, docente, turma, matrícula) | **Atendido** | Registros 20, 30, 40, 50, 60 e retornos de identificação |
| Situação do aluno (2ª etapa) | **Parcial** | Apenas 2026; atualiza `pmieducar.matricula.aprovado` |
| Tela de resultados | **Atendido** | Totais, tipos e até 100 mensagens de erro |
| Histórico `educacenso_imports` | **Atendido** | Uma linha por execução (sem status de erro) |
| Permissões via menu | **Atendido** | Herda perfis da Exportação Educacenso (846) |
| Package autônomo | **Atendido** | Rotas, views, migrations e services próprios |

### Escopo funcional real

O pacote implementa **importação reversa de retorno INEP** — ou seja, lê arquivos TXT gerados ou processados pelo Educacenso e **atualiza códigos INEP e situações** já existentes no i-Educar.

**Não** implementa a importação completa da Matrícula Inicial (criação/atualização de escola, turmas, pessoas e matrículas a partir do zero), função que no ecossistema Portabilis fica em `ImportServiceFactory` (anos 2019–2025) e na rota `/educacenso/import-registrations`.

---

## 3. Matriz de formatos × capacidade

| Formato | Parser | Grava no banco | Observação |
|---------|--------|----------------|------------|
| `migracao-txt` | `migracao_txt` | INEP turma/pessoa/docente/aluno/matrícula | Ignora reg. 00, 10; não altera dados cadastrais |
| `identificacao-retorno-txt` | `identificacao_retorno_txt` | INEP aluno | **Somente alunos**; não importa INEP de docentes no retorno |
| `identificacao-provaveis-txt` | idem | idem | Mesmo parser; responsabilidade de conferência manual |
| `identificacao-nao-encontrado-txt` | idem | idem | Arquivo em geral sem códigos utilizáveis |
| `inep-retorno-txt` | `inep_retorno_txt` | INEP 20/40/50/60 | Equivalente à migração sem reg. 30 |
| `situacao-aluno-migracao-txt` | `situacao_aluno_txt` | Situação da matrícula | Layout do **retorno INEP**, não do export i-Educar |
| `situacao-aluno-identificacao-txt` | `identificacao_retorno_txt` | INEP aluno | Igual identificação da 1ª etapa |

---

## 4. Fragilidades técnicas

Fragilidades são pontos que **podem causar comportamento incorreto ou inconsistente** em cenários reais.

### 4.1. Escola sem código INEP cadastrado

Se `modules.educacenso_cod_escola` não tiver INEP para a escola selecionada, o método `matchesSchool()` **aceita todas as linhas** do arquivo (`null` = sem filtro).

**Risco:** importar dados de outras escolas presentes no mesmo TXT.

**Mitigação recomendada:** bloquear importação quando INEP da escola estiver vazio; hoje apenas documentado.

### 4.2. Contagem de “importados” no registro 60

No registro **60**, a linha é contada como **importada** mesmo quando há erro parcial (ex.: enturmação não encontrada, mas INEP do aluno foi gravado).

**Risco:** tela de resultados superestima sucesso.

### 4.3. Número da linha nos erros

Para registros agrupados (20, 30, 40…), o “número da linha” reportado é o **índice dentro do array** do tipo de registro, não a linha real do arquivo TXT.

**Risco:** dificulta localizar o erro no arquivo original.

### 4.4. Situação do aluno — layout do arquivo

A importação de situação usa índices `[3]` = INEP turma, `[4]` = INEP aluno, `[7]` = situação, alinhado ao **retorno de migração do INEP** (mesmo critério do pacote Portabilis).

O **export** do i-Educar/Portabilis posiciona situação no índice **[8]** e código interno da turma no **[3]**.

**Risco:** enviar arquivo exportado pelo i-Educar no menu “Situação do Aluno — Migração” pode interpretar campos errados.

**Uso correto:** arquivo retornado/processado pelo portal INEP na 2ª etapa.

### 4.5. Turmas em período integral com sufixo `-turno`

Turmas com código `12345-3` têm o sufixo removido antes da gravação (`12345`), mas `SchoolClassInep` no Portabilis pode usar chave composta `(cod_turma, turma_turno_id)`.

**Risco:** INEP gravado na turma errada ou sobrescrito quando há múltiplos turnos parciais.

### 4.6. Enturmação para `matricula_inep`

A busca de `matricula_turma` usa apenas enturmação **ativa** (`ativo = 1`), sem filtro por data de referência do Censo (`data_educacenso` da instituição).

O pacote Portabilis filtra `data_enturmacao <= data_base_educacenso` ao vincular matrícula INEP.

**Risco:** vincular `matricula_inep` à enturmação errada em remanejamentos ou múltiplas enturmações.

### 4.7. Identificação só para alunos

`importIdentificacaoRetorno()` resolve apenas `cod_aluno`. Servidores identificados no retorno INEP (reg. 30 de docente) são ignorados.

### 4.8. Processamento síncrono

Toda importação roda na **requisição HTTP** (sem fila/worker).

**Risco:** timeout em arquivos grandes (limite upload 50 MB); Portabilis usa **jobs** para INEP e situação.

### 4.9. Sem transação de banco

Cada registro é gravado individualmente. Falha no meio do arquivo deixa **dados parcialmente importados** sem rollback.

### 4.10. `educacenso_imports` sempre “finalizada”

O histórico grava `finished = true` mesmo com erros na tela de resultados; não há flag `error` preenchida.

---

## 5. Limitações de produto (não implementado)

| Limitação | Detalhe |
|-----------|---------|
| Importação censitária completa | Não cria escola, turma, aluno, matrícula a partir do TXT completo (reg. 00–60) |
| Anos antes de 2025 | Catálogo fixo em 2025/2026; anos anteriores ficam no pacote Portabilis |
| Situação do aluno em 2025 | Menu não exposto; layout pode ser adicionado futuramente |
| Validação de layout INEP | Não valida quantidade de campos, charset ou regras do manual INEP antes de importar |
| Múltiplos arquivos / escolas por upload | Um arquivo e uma escola por vez; Portabilis aceita vários TXT e divide por reg. 89/00 |
| Listagem de importações | Sem tela de histórico no pacote (existe view órfã no core em `educacenso/import/index`) |
| Notificações ao usuário | Portabilis notifica ao concluir job; este pacote só mostra tela imediata |
| Testes automatizados | Nenhum teste Pest/PHPUnit no pacote |
| Profissionais na identificação | Retorno de identificação de docentes não é importado |
| Registros 00, 10 | Parseados/armazenados em `outros` ou ignorados; não há efeito na base |
| Reimportação / desfazer | Não há rotina de rollback ou log de alterações campo a campo |
| Concorrência | Duas importações simultâneas na mesma escola podem sobrescrever INEP sem controle de versão |

---

## 6. Comparação com pacote Portabilis Educacenso

| Aspecto | Buriti (este pacote) | Portabilis `i-educar-educacenso-package` |
|---------|----------------------|------------------------------------------|
| Organização do menu | Por **ano** e **formato INEP** | Por **tipo** (INEP, situação, matrícula completa) |
| Filtro explícito por escola na UI | Sim | Situação divide arquivo por reg. 89; INEP por reg. 00 |
| Importação completa por ano | Não | Sim (2019–2025) |
| INEP assíncrono | Não | Sim (jobs) |
| Situação assíncrona | Não | Sim; apenas **admin** |
| Data base Censo na matrícula INEP | Não | Sim (`config educacenso.data_base`) |
| Turma turno integral | Parcial | Suporte a `cod_turma-turno` |

Os pacotes **podem coexistir**; avalie desativar menus duplicados para não confundir usuários.

---

## 7. Dependências do ambiente

Para importação bem-sucedida, o ambiente deve ter:

1. **Código INEP da escola** cadastrado antes da importação.
2. **Alunos, servidores e turmas** já existentes no i-Educar (importação não cria cadastros).
3. **Matrículas e enturmações** do ano letivo corretos para vínculo de `matricula_inep` e situação.
4. **Permissão** nos menus do pacote (processos 20071–20079).
5. Opcional: pacote Portabilis para função `convertSituationEducacensoToIeducar()` completa (senão usa mapeamento simplificado embutido).

---

## 8. Checklist antes de importar em produção

- [ ] Backup do banco
- [ ] INEP da escola conferido em `modules.educacenso_cod_escola`
- [ ] Arquivo TXT corresponde ao **formato do menu** escolhido (export vs retorno INEP)
- [ ] Escola piloto validada antes de replicar na rede
- [ ] Conferência manual dos arquivos **RESULTADO_PROVAVEIS**
- [ ] `php artisan cache:clear` após migrations de menu
- [ ] Verificar se menus Portabilis duplicados estão desativados

---

## 9. Melhorias sugeridas (roadmap)

Prioridade sugerida para evolução do pacote:

1. **Bloquear importação** se escola não tiver INEP cadastrado.
2. **Corrigir contagem** de importados no registro 60 (só contar sucesso real).
3. **Reportar linha real** do arquivo nos erros.
4. **Suporte a `turma_turno_id`** na gravação de `educacenso_cod_turma`.
5. **Filtro por data base** do Censo na enturmação (matrícula INEP).
6. **Importar INEP de docentes** no retorno de identificação.
7. **Transação** opcional ou modo “simular” (dry-run).
8. **Testes** com arquivos TXT de fixture por formato.
9. **Situação 2025** e detecção automática de layout (export vs retorno).
10. **Fila assíncrona** para arquivos grandes.

---

## 10. Conclusão

O pacote **satisfaz a proposta** de interface organizada (menu por ano/formato), importação **por escola**, gravação de **códigos INEP** e **situação do aluno** (2026), com **tela de resultados** — desde que o entendimento seja: trata-se de **sincronização de retorno INEP** com dados já cadastrados no i-Educar, e **não** de substituto da importação censitária completa nem do fluxo assíncrono do pacote Portabilis.

Para uso em produção, as fragilidades das seções **4** e **5** devem ser consideradas no processo operacional da secretaria até que as melhorias do roadmap sejam implementadas.
