# buriti/i-educar-educacenso-import-package

Pacote Laravel para o [i-Educar](https://github.com/portabilis/i-educar) que importa arquivos **TXT do Educacenso/INEP** pela interface web: menus por ano e formato, filtro por escola e tela de resultados.

**Escopo:** sincroniza **códigos INEP** e **situação de matrícula** com cadastros já existentes no i-Educar. Não substitui a importação censitária completa do pacote Portabilis.

**Compatibilidade:** i-Educar **2.11** e **2.12** — ver [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md).

Repositório: [github.com/JaderGabriel/i-educar-educacenso-import-package](https://github.com/JaderGabriel/i-educar-educacenso-import-package)

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** (min. 2.12.0) |
| **i-Diário** | Portabilis i-Diário (espelho Laguna) via API/SSO do core |
| Pacote | `1.2.0` |

Declarado em `composer.json` → `extra.ieducar` e `extra.idiario`.

## Uso rápido

1. Instale o pacote — [`INSTALL.md`](INSTALL.md).
2. Acesse **Educacenso → Importações → [Ano] → [Formato]**.
3. Selecione escola e envie o TXT do [portal Educacenso](https://educacenso.inep.gov.br).
4. Confira a tela de resultados.

Fluxo detalhado no portal INEP e no i-Educar: [`docs/USO.md`](docs/USO.md).

## Documentação

| Documento | Conteúdo |
|-----------|----------|
| [`INSTALL.md`](INSTALL.md) | Instalação, migrations e solução de problemas |
| [`docs/USO.md`](docs/USO.md) | Passo a passo operacional (Matrícula Inicial e Situação do Aluno) |
| [`docs/FORMATOS-IMPORTACAO.md`](docs/FORMATOS-IMPORTACAO.md) | Slugs, registros TXT e tabelas gravadas |
| [`docs/FORMATOS-CENSO-2026.md`](docs/FORMATOS-CENSO-2026.md) | Censo 2026: formatos em ordem cronológica e diferenças |
| [`docs/INTEGRACAO.md`](docs/INTEGRACAO.md) | Provider, rotas, processos e dependências do app |
| [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md) | i-Educar 2.11/2.12, Laravel e PHP |
| [`docs/REVISAO-E-LIMITACOES.md`](docs/REVISAO-E-LIMITACOES.md) | Auditoria técnica, fragilidades e checklist de produção |

Antes de usar em produção, leia a [revisão e limitações](docs/REVISAO-E-LIMITACOES.md).

## Licença

GNU GPL v2.0 ou posterior — [`LICENSE`](LICENSE).
