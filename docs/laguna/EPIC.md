# EPIC — Censo / Educacenso — aderência Laguna

| Campo | Valor |
| --- | --- |
| Pacote | `buriti/i-educar-educacenso-import-package` |
| TR Laguna | **5.10** |
| Prioridade | **P2** |
| Nota atual (executivo) | **A** |
| Fonte | `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md` |
| Regra de ouro | Pregão Eletrônico 77/2026 · Laguna/SC · PoC ≥ 85% por módulo · gaps ≤ 30 dias |
| Base atual | Pacotes Educacenso Portabilis + Buriti import |

## Objetivo

Manter layout oficial do ano e validar regras 5.10.x na PoC (esforço baixo).

## Padrão técnico (i-Educar 2.12)

| Item | Valor |
| --- | --- |
| Alvo | i-Educar **2.12** · PHP **≥ 8.3** · Illuminate **^11|^12|^13** (Laravel 13 no app) |
| Composer | `buriti/i-educar-educacenso-import-package` |
| Namespace | `iEducar\Packages\BuritiEducacensoImport\` → `src/` |
| Compat | `extra.ieducar.compatible: ["2.12"]` · GPL-2.0-or-later · `type: library` |
| Schema / dados | conforme pacote Educacenso já instalado |
| Rotas | prefixo `educacenso (rotas existentes)`; SME com middleware `ieducar.*` + `auth`; API apps via Sanctum quando exposto |
| Tenancy | `PackageTenant::allows('buriti/i-educar-educacenso-import-package')` antes de migrations/menu |
| Plug-and-play | path em `packages/buriti/…`; manter em `ignore` até instalável |
| Referência | `packages/buriti/PADRAO-PACOTE-2.12.md` |

**Nota de arquitetura:** Só validar regras 5.10.x e layout do ano na PoC.

## Cobertura 100% do TR

Este EPIC cobre **todos** os requisitos do módulo(s) 5.10 listados em [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (18 itens OCR).

- PoC: ≥ **85%** demonstráveis e operacionais.
- Até **15%** restante: produção em ≤ **30 dias**, sem ônus.
- Nenhum item do `REQUISITOS-TR.md` pode ficar fora do `ROADMAP.md` / `TODO.md` (direto ou por grupo rastreável).

## Épicos filhos

1. Validação das regras 5.10.x do TR na versão do layout vigente.
2. Roteiro de export/import INEP para a PoC.
3. Restrições de matrícula inicial demonstráveis.

## Critérios de aceite (PoC)

- [ ] Checklist [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) com **100%** dos itens classificados (atende / gap / N/A)
- [ ] Itens **atende** demonstráveis na PoC (≥ 85% do módulo 5.10)
- [ ] Itens **gap** ≤ 15% com plano ≤ 30 dias em produção, sem ônus
- [ ] Nenhum requisito do TR omitido da matriz


## Fora de escopo deste EPIC

- Orçamento comercial da proposta Laguna.
- Substituição do core i-Educar fora do domínio do módulo.
