# Uso — importação Educacenso

Fluxo operacional no [portal INEP](https://educacenso.inep.gov.br) e no i-Educar.

```text
Portal INEP (TXT) → Educacenso → Importações → [Ano] → [Formato]
  → escola + upload → resultados
```

Importação **por escola**: registros de outras escolas no mesmo arquivo são ignorados.

Pré-requisitos técnicos (INEP cadastrado, permissões, versão do i-Educar): [`INSTALL.md`](../INSTALL.md) e [`COMPATIBILIDADE.md`](COMPATIBILIDADE.md).

Catálogo de formatos e slugs: [`FORMATOS-IMPORTACAO.md`](FORMATOS-IMPORTACAO.md).

## Matrícula Inicial

Sequência conforme [orientações INEP](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao):

1. **(Opcional)** Exporte pelo i-Educar e valide na análise de exportação antes de enviar ao INEP.
2. **Identificação no INEP** — envie o arquivo e baixe o ZIP de retorno.
3. **Importe no i-Educar** — escolha o menu conforme o arquivo:

| Arquivo INEP | Menu (2026) |
|--------------|-------------|
| `RESULTADO_ENCONTRADO.txt` / `RESULTADO_CERTO.txt` | Identificação — Resultado encontrado |
| `RESULTADO_PROVAVEIS.txt` | Identificação — Resultados prováveis |
| `RESULTADO_SEMID.txt` | Identificação — Não encontrados |

Em **2025**, use **Identificação INEP — Retorno de alunos** para resultados encontrados.

> Arquivos **PROVAVEIS** exigem conferência manual antes da importação.

4. **Retorno pós-migração** — após processamento no Educacenso, importe em **Retorno INEP — Códigos preenchidos** (ou **Migração TXT** para arquivo completo 00–60).

## Situação do Aluno (2026)

1. Obtenha o TXT da 2ª etapa (registros 89/90/91) no INEP.
2. Importe em **Situação do Aluno — Migração TXT**.
3. O sistema atualiza `pmieducar.matricula.aprovado`.

Pré-requisitos: INEP de turma e aluno já importados; matrícula ativa no ano.

Para retorno de identificação na 2ª etapa: **Situação do Aluno — Identificação INEP**.

> Use arquivo de **retorno INEP**, não o export gerado pelo i-Educar — ver [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).

## Telas

**Formulário:** ano (fixo pelo menu), instituição, escola, arquivo `.txt` (máx. 50 MB).

**Resultados:**

| Métrica | Significado |
|---------|-------------|
| Linhas lidas | Total de linhas não vazias |
| Importados | Processados com sucesso |
| Ignorados | Outra escola ou dados incompletos |
| Erros | Vínculo não encontrado (aluno/turma/matrícula) |

Até **100 erros** listados; importações grandes exigem análise pelos totais.

## Boas práticas

- Uma escola por importação.
- Backup do banco antes de uso em produção.
- Escola piloto antes de replicar na rede.
- `php artisan cache:clear` após alterar menus ou permissões.

Checklist completo: [`REVISAO-E-LIMITACOES.md`](REVISAO-E-LIMITACOES.md).
