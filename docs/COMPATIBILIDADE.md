# Compatibilidade com o i-Educar

Este documento descreve a compatibilidade oficial do pacote **buriti/i-educar-educacenso-import-package** com as versões **2.11** e **2.12** do i-Educar, conforme os requisitos publicados pela Portabilis.

Última verificação: julho/2026.

---

## Matriz de suporte

| i-Educar | Branch | Laravel | PHP mínimo (composer) | PHP testado (UPGRADE) | Suporte do pacote |
|----------|--------|---------|------------------------|------------------------|-------------------|
| **2.11** | `2.11` | **12** (`^12.5`) | ≥ 8.3 | **8.4** | **Suportado** |
| **2.12** | `2.12` | **13** (`^13.0`) | ≥ 8.3 | **8.5** | **Suportado** |

Versões anteriores (2.10 e abaixo) **não** fazem parte do suporte oficial deste pacote, embora instalações em Laravel 11 possam funcionar com dependências alinhadas — não há garantia nem testes automatizados para esses cenários.

---

## Dependências do pacote

Declaradas em `composer.json`:

| Pacote | Constraint | Motivo |
|--------|------------|--------|
| `php` | `>=8.3` | Alinhado ao mínimo do i-Educar 2.11/2.12 |
| `illuminate/support` | `^11.0\|^12.0\|^13.0` | Cobre Laravel 11–13 (2.10 legado até 2.12) |
| `illuminate/http` | idem | Controllers e validação |
| `illuminate/routing` | idem | Rotas web |

Campo `extra.ieducar.compatible`: `2.11`, `2.12`.

---

## O que foi verificado

### Código do pacote

O pacote usa apenas APIs estáveis do Laravel e do core i-Educar:

- Service provider (`loadRoutesFrom`, `loadViewsFrom`, `loadMigrationsFrom`)
- Controllers com `authorize()`, `menu()`, `breadcrumb()` do `App\Http\Controllers\Controller`
- Facades `DB`, `Validator`
- Modelos Eloquent do aplicativo (`App\Models\*`, `EducacensoImport`)
- Views Blade com componentes existentes (`layout.default`, `form.select-institution`, `form.select-school`)
- Middleware padrão do i-Educar (`auth`, `ieducar.navigation`, etc.)

Não há uso de APIs removidas entre Laravel 12 e 13 no código atual.

### Integração com o core

| Dependência do app | Presente em 2.11 | Presente em 2.12 | Uso no pacote |
|--------------------|------------------|------------------|---------------|
| Menu **Importações** (processo `9998848`) | Sim | Sim | Pai dos submenus do pacote |
| Tabelas `modules.educacenso_*` | Sim | Sim | Gravação de códigos INEP |
| `public.educacenso_imports` | Sim | Sim | Histórico de importação |
| Exportação Educacenso (processo `846`) | Sim | Sim | Base de permissões |
| Policies de `process` | Sim | Sim | Autorização nos menus |

### Pacote opcional Portabilis

`portabilis/i-educar-educacenso-package` é **opcional**. Quando instalado, a função `convertSituationEducacensoToIeducar()` é usada na importação de situação; caso contrário, o pacote aplica mapeamento simplificado embutido. Compatível com ambas as versões do i-Educar desde que o pacote Portabilis também esteja na versão adequada ao Laravel do host.

---

## Requisitos recomendados por ambiente

### i-Educar 2.11

```bash
php --version          # ≥ 8.4 recomendado
php artisan --version  # Laravel 12.x
```

### i-Educar 2.12

```bash
php --version          # ≥ 8.5 recomendado
php artisan --version  # Laravel 13.x
```

PostgreSQL, Redis e demais requisitos seguem o [UPGRADE.md](https://github.com/portabilis/i-educar/blob/2.11/UPGRADE.md) ou [UPGRADE.md 2.12](https://github.com/portabilis/i-educar/blob/2.12/UPGRADE.md) do i-Educar.

---

## Instalação por versão

O procedimento é o mesmo em 2.11 e 2.12 (ver [`INSTALL.md`](../INSTALL.md)):

```bash
composer update buriti/i-educar-educacenso-import-package
php artisan migrate
php artisan cache:clear
```

Em instalações com plug-and-play:

```bash
composer plug-and-play:update   # i-Educar 2.12+
# ou
composer plug-and-play          # i-Educar 2.11
```

---

## Limitações de compatibilidade

| Item | Detalhe |
|------|---------|
| Anos do Censo no menu | Catálogo fixo em **2025** e **2026**; independente da versão do i-Educar |
| Rotas `{year}` | Restrição `2025\|2026` no código; novo ano exige atualização do pacote |
| i-Educar &lt; 2.11 | Sem suporte oficial |
| Testes CI multi-versão | Não configurados no repositório do pacote |

---

## Referências

- [i-Educar 2.11 — UPGRADE.md](https://github.com/portabilis/i-educar/blob/2.11/UPGRADE.md)
- [i-Educar 2.12 — UPGRADE.md](https://github.com/portabilis/i-educar/blob/2.12/UPGRADE.md)
- [i-Educar 2.11 — composer.json](https://github.com/portabilis/i-educar/blob/2.11/composer.json)
- [i-Educar 2.12 — composer.json](https://github.com/portabilis/i-educar/blob/2.12/composer.json)
