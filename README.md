# Importação Educacenso / INEP

Importa arquivos TXT do Educacenso pela interface web para sincronizar códigos INEP e situação de matrícula com cadastros já existentes.

Composer: `buriti/i-educar-educacenso-import-package`

## Para que serve

Acelera a conciliação anual com o Censo Escolar: menus por ano/formato, filtro por escola e tela de resultados — sem substituir a importação censitária completa do pacote Portabilis.

## Desenho de funções

| Função | Entrega |
| --- | --- |
| Importação por ano/formato | Fluxo web Matrícula Inicial e Situação do Aluno |
| Filtro por escola | Processamento direcionado |
| Sincronização INEP | Códigos e situação alinhados ao cadastro local |
| Tela de resultados | Conferência do que foi atualizado / rejeitado |

## Quem usa

- Equipe de censo / secretaria escolar
- SME (acompanhamento)

## Integrações

- Cadastros do núcleo i-Educar
- Portal Educacenso (origem dos TXT)

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** |
| **i-Diário** | Portabilis i-Diário (API / SSO do núcleo) |
| Pacote | `1.2.1` |

Declarado em `composer.json` → `extra.ieducar` / `extra.idiario`.

## Documentação

| Documento | Uso |
| --- | --- |
| [`INSTALL.md`](./INSTALL.md) | Instalação e troubleshooting |
| [`docs/USO.md`](./docs/USO.md) | Passo a passo operacional |
| [`docs/FORMATOS-IMPORTACAO.md`](./docs/FORMATOS-IMPORTACAO.md) | Slugs, registros e tabelas |
| [`docs/COMPATIBILIDADE.md`](./docs/COMPATIBILIDADE.md) | Matriz de versões |

---

Licença: **GPL-2.0-or-later** · Organização [i-Educar](https://github.com/i-Educar)
