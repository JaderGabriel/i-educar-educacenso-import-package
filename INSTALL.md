# Instalação

Pressupõe i-Educar clonado, PostgreSQL configurado e acesso ao menu **Educacenso**.

Requisitos (i-Educar 2.11/2.12, PHP, Laravel): [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md).

## 1. Adicionar com Composer

### GitHub (deploy)

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

```bash
composer update buriti/i-educar-educacenso-import-package
```

### Path local (plug-and-play ou desenvolvimento)

Em `packages/plug-and-play.json` ou `composer.json`:

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
composer plug-and-play          # ou: composer update buriti/i-educar-educacenso-import-package
```

O provider `BuritiEducacensoImportServiceProvider` é descoberto via `extra.laravel.providers`. Se necessário: `php artisan package:discover` e `composer dump-autoload -o`.

## 2. Migrations e cache

```bash
php artisan migrate
php artisan cache:clear
```

Migrations do pacote registram menus **2025** (2 formatos) e **2026** (7 formatos) e copiam permissões da Exportação Educacenso (processo **846**).

Confirme que o menu pai **Importações** (processo `9998848`) existe sob **Educacenso**.

## 3. Verificação

```bash
php artisan route:list --name=buriti.educacenso-import
```

Rotas esperadas: `index`, `import` (POST), `result`.

No i-Educar, com permissão de exportação Educacenso, os menus aparecem em **Educacenso → Importações**. Lista completa de formatos: [`docs/FORMATOS-IMPORTACAO.md`](docs/FORMATOS-IMPORTACAO.md).

## 4. Primeira importação

Cadastre o **código INEP da escola**, escolha o formato correto e envie o TXT. Guia operacional: [`docs/USO.md`](docs/USO.md).

## 5. Docker

```bash
docker compose exec php composer plug-and-play
docker compose exec php artisan migrate
docker compose exec php artisan cache:clear
```

## 6. Problemas comuns

| Sintoma | Ação |
|---------|------|
| Menus não aparecem | `migrate` + `cache:clear`; confira permissões (processos 20071–20079 ou 846) |
| 403 | Conceda permissão ao menu do formato ou à Exportação Educacenso |
| Rotas ausentes | `composer dump-autoload` + `php artisan package:discover` |
| Registros ignorados | INEP da escola no arquivo difere do cadastro (`modules.educacenso_cod_escola`) |
| Aluno não encontrado | Código no TXT deve bater com `cod_aluno` ou `ref_idpes` |
| Situação não atualizada | Turma e aluno precisam de INEP; use arquivo de **retorno INEP**, não export i-Educar |
| Conflito Portabilis | Pacotes coexistem; desative menus duplicados se necessário |

## 7. Atualização

```bash
composer update buriti/i-educar-educacenso-import-package
php artisan migrate
php artisan cache:clear
```
