# Instalação — buriti/i-educar-educacenso-import-package

Este guia pressupõe um projeto **i-Educar** já clonado, com PHP **≥ 8.3**, Composer, banco PostgreSQL configurado (`.env`) e acesso ao menu **Educacenso**.

## 1. Requisitos

| Item | Versão / nota |
|------|----------------|
| i-Educar | **2.11** ou **2.12** (suporte oficial) |
| PHP | ≥ 8.3 (recomendado **8.4+** em 2.11, **8.5+** em 2.12) |
| Laravel / Illuminate | **12** (i-Educar 2.11) ou **13** (i-Educar 2.12) |
| PostgreSQL | tabelas `modules.educacenso_*` e `public.educacenso_imports` |
| Permissão | Exportação Educacenso (processo **846**) ou equivalente nos novos menus |

Matriz completa: [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md).

O pacote declara `illuminate/support`, `illuminate/http` e `illuminate/routing`. Modelos Eloquent, policies de menu e layout web vêm do **aplicativo**.

## 2. Adicionar o pacote com Composer

### 2.1. A partir do GitHub (recomendado para deploy)

No `composer.json` do i-Educar (ou em `packages/plug-and-play.json`):

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/JaderGabriel/i-educar-educacenso-import-package.git"
    }
],
"require": {
    "buriti/i-educar-educacenso-import-package": "^1.1"
}
```

Execute:

```bash
composer update buriti/i-educar-educacenso-import-package
```

### 2.2. Plug-and-play (path local)

Em `packages/plug-and-play.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "./packages/buriti/i-educar-educacenso-import-package",
        "symlink": true
    }
],
"require": {
    "buriti/i-educar-educacenso-import-package": "@dev"
}
```

```bash
composer plug-and-play
```

### 2.3. Desenvolvimento com cópia local (sem plug-and-play)

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/buriti/i-educar-educacenso-import-package",
        "symlink": true
    }
],
"require": {
    "buriti/i-educar-educacenso-import-package": "@dev"
}
```

```bash
composer update buriti/i-educar-educacenso-import-package
```

## 3. Descoberta do Laravel

O `composer.json` do pacote registra `BuritiEducacensoImportServiceProvider` em `extra.laravel.providers`.

Se necessário:

```bash
php artisan package:discover
composer dump-autoload -o
```

## 4. Migrations

O provider carrega as migrations do pacote automaticamente:

```bash
php artisan migrate
```

Migrations incluídas:

| Arquivo | Função |
|---------|--------|
| `2026_07_06_100000_register_educacenso_import_menus.php` | Menus 2025/2026 e permissões iniciais |
| `2026_07_06_110000_register_educacenso_import_2026_formats.php` | Formatos adicionais 2026 e atualização de títulos |

Após migrar, limpe o cache de menus:

```bash
php artisan cache:clear
```

> Em ambientes com `LEGACY_SEED_DATA=true`, confirme que o menu pai **Importações** (processo `9998848`) já existe sob **Educacenso**.

## 5. Verificar rotas

```bash
php artisan route:list --name=buriti.educacenso-import
```

Saída esperada:

- `buriti.educacenso-import.index` — formulário
- `buriti.educacenso-import.import` — processamento (POST)
- `buriti.educacenso-import.result` — resultados

## 6. Verificar menus

No i-Educar, usuário com permissão de exportação Educacenso:

**Educacenso → Importações**

```
Importações
├── 2025
│   ├── Migração TXT — Exportação INEP
│   └── Identificação INEP — Retorno de alunos
└── 2026
    ├── Migração TXT — Exportação INEP
    ├── Identificação INEP — Resultado encontrado
    ├── Identificação INEP — Resultados prováveis
    ├── Identificação INEP — Não encontrados
    ├── Retorno INEP — Códigos preenchidos
    ├── Situação do Aluno — Migração TXT
    └── Situação do Aluno — Identificação INEP
```

## 7. Primeira importação

1. Cadastre o **código INEP da escola** (Escola → Dados gerais → Código INEP).
2. Exporte ou baixe o TXT no [Educacenso/INEP](https://www.gov.br/inep/pt-br/areas-de-atuacao/pesquisas-estatisticas-e-indicadores/censo-escolar/orientacoes/matricula-inicial/migracao).
3. Acesse o formato correspondente em **Importações → [Ano]**.
4. Selecione instituição e escola, anexe o `.txt` (máx. 50 MB) e clique em **Importar arquivo**.
5. Revise a tela de resultados.

Guia detalhado: [`docs/USO.md`](docs/USO.md).

## 8. Docker

Na raiz do i-Educar:

```bash
docker compose exec php composer plug-and-play
docker compose exec php artisan migrate
docker compose exec php artisan cache:clear
```

## 9. Problemas comuns

| Sintoma | Ação |
|---------|------|
| Menus não aparecem | `php artisan migrate` e `php artisan cache:clear`; confira permissões do tipo de usuário |
| 403 ao acessar importação | Conceda permissão ao menu do formato (processos 20071–20079) ou à Exportação Educacenso (846) |
| Rotas não encontradas | `composer dump-autoload` e `php artisan package:discover` |
| Registros ignorados | Código INEP da escola no arquivo difere do cadastrado; confira `modules.educacenso_cod_escola` |
| Aluno não encontrado na identificação | Verifique se o código da pessoa no TXT corresponde a `cod_aluno` ou `ref_idpes` |
| Situação não atualizada | Turma e aluno precisam ter INEP em `educacenso_cod_turma` e `educacenso_cod_aluno` |
| Conflito com pacote Portabilis | Dois importadores podem coexistir; desative menus duplicados se necessário |

## 10. Atualização do pacote

```bash
composer update buriti/i-educar-educacenso-import-package
php artisan migrate
php artisan cache:clear
```

## 11. Licença

Uso sob **GPL-2.0-or-later** — leia [`LICENSE`](LICENSE).

## 12. Documentação complementar

- [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md) — matriz i-Educar 2.11/2.12, Laravel e PHP
- [`docs/REVISAO-E-LIMITACOES.md`](docs/REVISAO-E-LIMITACOES.md) — auditoria técnica, fragilidades e checklist de produção
