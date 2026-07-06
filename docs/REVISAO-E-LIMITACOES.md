# Revisão técnica e limitações

Auditoria do pacote (v1.1.4): aderência à proposta, fragilidades e o que não está implementado.

Formatos e tabelas: [`FORMATOS-IMPORTACAO.md`](FORMATOS-IMPORTACAO.md). Fluxo operacional: [`USO.md`](USO.md).

Última revisão: julho/2026.

## Proposta vs. entregue

| Requisito | Status |
|-----------|--------|
| Menu Educacenso → Importações por ano/formato | Atendido |
| Importação por escola | Atendido |
| Gravação de códigos INEP | Atendido |
| Situação do aluno (2ª etapa) | Parcial — só 2026 |
| Tela de resultados | Atendido |
| Histórico `educacenso_imports` | Atendido |
| Package autônomo | Atendido |

**Escopo real:** sincronização de **retorno INEP** com cadastros existentes. **Não** cria escola, turma, pessoa ou matrícula a partir do TXT completo (isso fica no pacote Portabilis, rota `/educacenso/import-registrations`).

## Fragilidades técnicas

| # | Problema | Risco |
|---|----------|-------|
| 1 | Escola sem INEP → `matchesSchool()` aceita todas as linhas | Importar dados de outras escolas |
| 2 | Registro 60 contado como importado com erro parcial | Resultados superestimam sucesso |
| 3 | “Linha” nos erros é índice do array, não linha do TXT | Dificulta localizar erro no arquivo |
| 4 | Situação usa layout do **retorno INEP** (`[7]`), não export i-Educar (`[8]`) | Campos interpretados errado |
| 5 | Turmas `cod-turno`: sufixo truncado, sem `turma_turno_id` | INEP na turma errada |
| 6 | `matricula_inep` sem filtro por data base do Censo | Enturmação errada em remanejamentos |
| 7 | Identificação só importa alunos, não docentes | INEP de servidor ignorado |
| 8 | Processamento síncrono (sem fila) | Timeout em arquivos grandes |
| 9 | Sem transação de banco | Importação parcial sem rollback |
| 10 | `educacenso_imports` sempre `finished=true` | Histórico não reflete erros |

## Limitações de produto

- Importação censitária completa (reg. 00–60 criando cadastros).
- Anos antes de 2025; situação do aluno em 2025.
- Validação de layout INEP antes de importar.
- Um arquivo e uma escola por upload.
- Sem tela de histórico, notificações ou testes automatizados.
- Sem rollback de reimportação.

## Comparação com Portabilis

| Aspecto | Buriti | Portabilis |
|---------|--------|------------|
| Menu | Por ano/formato | Por tipo de importação |
| Filtro escola na UI | Sim | Via divisão do arquivo |
| Importação completa | Não | Sim (2019–2025) |
| Assíncrono (jobs) | Não | Sim |
| Data base Censo na matrícula INEP | Não | Sim |

Pacotes podem coexistir; desative menus duplicados.

## Checklist de produção

- [ ] Backup do banco
- [ ] INEP da escola em `modules.educacenso_cod_escola`
- [ ] Arquivo corresponde ao menu escolhido (export vs retorno INEP)
- [ ] Escola piloto validada
- [ ] Conferência manual de `RESULTADO_PROVAVEIS`
- [ ] `cache:clear` após migrations de menu

## Roadmap sugerido

1. Bloquear importação sem INEP da escola
2. Corrigir contagem no registro 60
3. Reportar linha real do arquivo nos erros
4. Suporte a `turma_turno_id`
5. Filtro por data base do Censo
6. INEP de docentes na identificação
7. Modo dry-run / transação
8. Testes com fixtures TXT
9. Situação 2025 e detecção de layout
10. Fila assíncrona

## Conclusão

O pacote atende a proposta de interface, importação por escola, códigos INEP, situação (2026) e tela de resultados — como **sincronização de retorno INEP**, não substituto do fluxo censitário completo nem assíncrono do Portabilis.
