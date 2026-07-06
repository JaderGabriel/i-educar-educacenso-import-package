# Uso — importação Educacenso no i-Educar

Este guia descreve o fluxo recomendado para importar arquivos do [Educacenso/INEP](https://educacenso.inep.gov.br) no i-Educar usando o pacote **buriti/i-educar-educacenso-import-package**.

## Visão geral do fluxo

```text
Portal INEP (exportação / retorno TXT)
        ↓
i-Educar: Educacenso → Importações → [Ano] → [Formato]
        ↓
Seleção de escola + upload do arquivo
        ↓
Gravação no banco + tela de resultados
```

A importação é sempre **por ano letivo e por escola**. Registros de outras escolas presentes no mesmo arquivo são **ignorados**.

## Pré-requisitos no i-Educar

Antes de importar:

1. **Código INEP da escola** cadastrado (`modules.educacenso_cod_escola`).
2. **Ano letivo** da escola ativo para o ano do Censo.
3. **Permissão** nos menus de importação (herdada da Exportação Educacenso ou concedida manualmente).
4. Para retorno com códigos ou situação:
   - Alunos, turmas e servidores já existentes no sistema com vínculos corretos.
   - Matrículas/enturmações do ano quando aplicável.

## Matrícula Inicial — sequência recomendada

Ordem sugerida conforme orientações do INEP ([Migração — Matrícula Inicial](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao)):

### Etapa 1 — Exportação do i-Educar (opcional, para conferência)

Use a **Exportação Educacenso** nativa do i-Educar para gerar o TXT de migração e validar dados na **análise de exportação** antes de enviar ao INEP.

### Etapa 2 — Identificação no INEP

No portal: **Matrícula Inicial → Migração → Identificação → Enviar arquivo**.

Baixe o resultado (ZIP com um ou mais TXT):

| Arquivo INEP | Menu no i-Educar (2026) |
|--------------|-------------------------|
| `RESULTADO_ENCONTRADO.txt` / `RESULTADO_CERTO.txt` | Identificação INEP — Resultado encontrado |
| `RESULTADO_PROVAVEIS.txt` | Identificação INEP — Resultados prováveis |
| `RESULTADO_SEMID.txt` | Identificação INEP — Não encontrados |

Em **2025**, use **Identificação INEP — Retorno de alunos** para arquivos de resultado encontrado.

> **Prováveis:** o INEP não garante a correspondência; confira manualmente antes de importar.

### Etapa 3 — Importar códigos INEP no i-Educar

1. Acesse o menu do formato correspondente.
2. Selecione **instituição** e **escola**.
3. Envie o TXT.
4. Na tela de resultados, verifique:
   - **Registros importados** — códigos gravados com sucesso.
   - **Ignorados** — linhas de outra escola ou sem código INEP.
   - **Erros** — pessoa/turma não encontrada no sistema.

### Etapa 4 — Retorno pós-migração no INEP

Após processar a migração no Educacenso, baixe o arquivo com códigos preenchidos e importe em:

**Importações → 2026 → Retorno INEP — Códigos preenchidos**

Este formato processa principalmente registros **20** (turma), **40/50** (gestor/docente) e **60** (aluno/matrícula INEP).

Alternativa: **Migração TXT — Exportação INEP** aceita o arquivo completo (registros 00–60).

## Situação do Aluno (2ª etapa) — 2026

Após a Matrícula Inicial e fechamento da escola no INEP:

1. Exporte ou obtenha o TXT da **Situação do Aluno** (registros 89, 90, 91).
2. Importe em **Importações → 2026 → Situação do Aluno — Migração TXT**.
3. O sistema atualiza a **situação da matrícula** (`pmieducar.matricula.aprovado`) com base no código de situação do Educacenso.

Pré-requisitos:

- Códigos INEP de **turma** e **aluno** já importados.
- Matrícula ativa vinculada à turma no ano selecionado.

Para retorno de identificação na 2ª etapa, use **Situação do Aluno — Identificação INEP**.

## Tela de importação

Campos do formulário:

| Campo | Observação |
|-------|------------|
| Ano | Fixo conforme o menu (2025 ou 2026) |
| Instituição | Filtro das escolas no select |
| Escola | Obrigatório; define o filtro INEP |
| Arquivo TXT | Apenas `.txt`, até 50 MB |

## Tela de resultados

| Métrica | Significado |
|---------|-------------|
| Linhas lidas | Total de linhas não vazias no arquivo |
| Registros importados | Linhas processadas com sucesso |
| Registros ignorados | Outra escola ou dados incompletos |
| Erros | Falha de vínculo (aluno/turma/matrícula não encontrados) |
| Detalhamento por tipo | Contagem por tipo de registro (turma, aluno_inep, situacao_matricula, etc.) |

Até **100 erros** são listados na tela; importações maiores devem ser analisadas pelos totais.

## Histórico

Cada importação concluída registra uma linha em `public.educacenso_imports` (ano, nome da escola, usuário, data).

## Boas práticas

- Importe **uma escola por vez**, mesmo que o arquivo contenha várias.
- Faça **backup do banco** antes de importações em massa em produção.
- Valide uma escola piloto antes de replicar para toda a rede.
- Após atualizar menus ou permissões, execute `php artisan cache:clear`.
- Mantenha o pacote atualizado: `composer update buriti/i-educar-educacenso-import-package`.

## Referências INEP

- [Migração — Matrícula Inicial](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao)
- [Situação do Aluno — orientações de migração (2025/2026)](https://www.gov.br/inep/pt-br/centrais-de-conteudo/acervo-linha-editorial/publicacoes-institucionais/estatisticas-e-indicadores-educacionais/etapas-e-instrucoes-gerais-para-a-migracao-no-sistema-educacenso-2013-2a-etapa-da-coleta-do-censo-escolar-2025-situacao-do-aluno)
