# ROADMAP — Censo / Educacenso — aderência Laguna

> Pacote: `buriti/i-educar-educacenso-import-package` · TR **5.10** · Prioridade **P2** · Nota **A**  
> Alinhado a `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`

## Visão

Manter layout oficial do ano e validar regras 5.10.x na PoC (esforço baixo).

## Fases

### F0 · Checklist

5.10.x × pacotes atuais.

### F1 · Layout ano

Atualizar se necessário.

### F2 · PoC

Ensaio export/import.

## Cobertura 100% dos requisitos (TR)

Fonte: [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) — **18 itens**.

Cada lote deve sair da PoC como **atende** ou entrar no plano de 30 dias.

### Lote R1 — itens 5.10.1 … 5.10.14 (15)

- `5.10.1`, `5.10.2`, `5.10.3`, `5.10.4`, `5.10.5`, `5.10.6`, `5.10.7`, `5.10.7.1`, `5.10.7.2`, `5.10.8`, `5.10.9`, `5.10.10`, `5.10.12`, `5.10.13`, `5.10.14`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Lote R2 — itens 5.10.15 … 5.10.17 (3)

- `5.10.15`, `5.10.16`, `5.10.17`

- [ ] Implementar / validar / evidenciar na matriz PoC

### Gate 100%

- [ ] Todos os lotes R* com status atende ou gap≤30dias
- [ ] Soma atende ≥ 85% na demonstração

## Conformidade Laravel / pacote i-Educar 2.12

Pacote **já existente** e declarado compatível com 2.12. Esta pasta Laguna não redefine o scaffold — apenas o checklist TR. Detalhes de instalação: README do pacote. Padrão geral: `packages/buriti/PADRAO-PACOTE-2.12.md`.

## Dependências

- Cadastro mestre de aluno/escola/turma no i-Educar (SGE).
- Matriz item a item OCR do Anexo I antes de cravar %.
- Alinhamento com apps (5.13/5.14) e exports (4.x) quando o módulo expõe dados ao cidadão/SME.

## Riscos

- Valor global do edital incompatível com greenfield total — reuso e MVP demonstrável são obrigatórios.
- PDF escaneado: numerações OCR podem exigir reconciliação manual.
- PoC só conta o que estiver operacional no dia.

## Definição de pronto da fase PoC

Checklist do `TODO.md` marcado para a fatia MVP + roteiro de demonstração ensaiado.
