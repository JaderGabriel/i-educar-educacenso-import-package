# Compatibilidade com o i-Educar

Suporte oficial às versões **2.11** e **2.12**, conforme requisitos publicados pela Portabilis.

Última verificação: julho/2026.

## Matriz de suporte

| i-Educar | Laravel | PHP mínimo | PHP recomendado (UPGRADE) |
|----------|---------|------------|---------------------------|
| **2.11** | 12 (`^12.5`) | ≥ 8.3 | **8.4** |
| **2.12** | 13 (`^13.0`) | ≥ 8.3 | **8.5** |

Versões anteriores a 2.11 não têm suporte oficial.

## Dependências do pacote

| Pacote | Constraint |
|--------|------------|
| `php` | `>=8.3` |
| `illuminate/support`, `http`, `routing` | `^11.0\|^12.0\|^13.0` |

Campo `extra.ieducar.compatible` no `composer.json`: `2.11`, `2.12`.

## Verificação técnica

O pacote usa APIs estáveis do Laravel (provider, controllers, facades) e do core i-Educar (modelos `App\Models\*`, policies de menu, middleware `ieducar.*`, views `layout.default`).

Depende do app ter:

- menu **Importações** (processo `9998848`);
- tabelas `modules.educacenso_*` e `public.educacenso_imports`;
- Exportação Educacenso (processo **846**) como base de permissões.

`portabilis/i-educar-educacenso-package` é **opcional** — quando presente, fornece `convertSituationEducacensoToIeducar()` na importação de situação.

Não há uso de APIs removidas entre Laravel 12 e 13 no código atual.

## Limitações

- Catálogo de anos fixo em **2025** e **2026** (independente da versão do i-Educar).
- Sem testes CI multi-versão no repositório do pacote.

## Referências

- [UPGRADE 2.11](https://github.com/portabilis/i-educar/blob/2.11/UPGRADE.md) · [composer.json 2.11](https://github.com/portabilis/i-educar/blob/2.11/composer.json)
- [UPGRADE 2.12](https://github.com/portabilis/i-educar/blob/2.12/UPGRADE.md) · [composer.json 2.12](https://github.com/portabilis/i-educar/blob/2.12/composer.json)
