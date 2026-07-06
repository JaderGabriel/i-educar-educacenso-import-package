# buriti/i-educar-educacenso-import-package

Pacote Laravel para o [i-Educar](https://github.com/portabilis/i-educar) que disponibiliza **importação de arquivos TXT do Educacenso/INEP** pela interface web, com menus por ano letivo, filtro por escola e tela de resultados.

Fluxos cobertos:

- **Matrícula Inicial** — migração, retorno de códigos INEP e arquivos de identificação (`RESULTADO_ENCONTRADO`, `RESULTADO_PROVAVEIS`, `RESULTADO_SEMID`).
- **Situação do Aluno (2ª etapa)** — a partir de 2026, migração com registros 89/90/91 e identificação INEP.

Compatível com i-Educar **2.11** (Laravel 12) e **2.12** (Laravel 13), PHP **≥ 8.3** (recomendado **8.4+** em 2.11 e **8.5+** em 2.12). Detalhes em [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md).

Repositório: [github.com/JaderGabriel/i-educar-educacenso-import-package](https://github.com/JaderGabriel/i-educar-educacenso-import-package)

## Licença

Este pacote é distribuído sob os termos da **GNU General Public License v2.0 ou posterior**. Veja o arquivo [`LICENSE`](LICENSE).

## Instalação

Instruções detalhadas em **[`INSTALL.md`](INSTALL.md)**.

Resumo com Composer (repositório VCS):

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

Com **plug-and-play** (instalações Serventec/Buriti):

```json
"require": {
    "buriti/i-educar-educacenso-import-package": "@dev"
}
```

```bash
composer plug-and-play
php artisan migrate
php artisan cache:clear
```

Desenvolvimento local (path):

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/buriti/i-educar-educacenso-import-package",
        "symlink": true
    }
]
```

Após `composer update`, o provider `BuritiEducacensoImportServiceProvider` é descoberto automaticamente (`extra.laravel.providers`).

## Uso rápido

1. Acesse **Educacenso → Importações → [Ano] → [Formato]**.
2. Selecione **instituição** e **escola**.
3. Envie o arquivo **TXT** exportado ou retornado pelo [portal Educacenso](https://educacenso.inep.gov.br).
4. Confira a **tela de resultados** (importados, ignorados, erros e detalhamento por tipo).

Rotas (exemplos):

| Formato | URL |
|---------|-----|
| Migração 2026 | `/educacenso/importacao/2026/migracao-txt` |
| Identificação 2026 | `/educacenso/importacao/2026/identificacao-retorno-txt` |
| Situação do aluno 2026 | `/educacenso/importacao/2026/situacao-aluno-migracao-txt` |

## Menus e formatos por ano

### 2025

| Menu | Slug | Origem INEP |
|------|------|-------------|
| Migração TXT — Exportação INEP | `migracao-txt` | Matrícula Inicial → Migração → Exportação |
| Identificação INEP — Retorno de alunos | `identificacao-retorno-txt` | Matrícula Inicial → Migração → Identificação → Download |

### 2026 — Matrícula Inicial

| Menu | Slug | Arquivo típico |
|------|------|----------------|
| Migração TXT — Exportação INEP | `migracao-txt` | Exportação do sistema / layout 00–60 |
| Identificação INEP — Resultado encontrado | `identificacao-retorno-txt` | `RESULTADO_ENCONTRADO.txt`, `RESULTADO_CERTO.txt` |
| Identificação INEP — Resultados prováveis | `identificacao-provaveis-txt` | `RESULTADO_PROVAVEIS.txt` |
| Identificação INEP — Não encontrados | `identificacao-nao-encontrado-txt` | `RESULTADO_SEMID.txt` |
| Retorno INEP — Códigos preenchidos | `inep-retorno-txt` | Retorno pós-processamento (reg. 20, 40, 50, 60) |

### 2026 — Situação do Aluno (2ª etapa)

| Menu | Slug | Registros |
|------|------|-----------|
| Situação do Aluno — Migração TXT | `situacao-aluno-migracao-txt` | 89, 90, 91 |
| Situação do Aluno — Identificação INEP | `situacao-aluno-identificacao-txt` | Retorno de identificação |

Detalhes de cada formato, registros e tabelas afetadas: [`docs/FORMATOS-IMPORTACAO.md`](docs/FORMATOS-IMPORTACAO.md).

## O que é gravado no banco

| Tabela | Conteúdo |
|--------|----------|
| `modules.educacenso_cod_aluno` | Código INEP do aluno |
| `modules.educacenso_cod_docente` | Código INEP do servidor/docente |
| `modules.educacenso_cod_turma` | Código INEP da turma |
| `modules.educacenso_matricula` | Código INEP da matrícula (enturmação) |
| `public.educacenso_imports` | Histórico da importação (ano, escola, usuário) |
| `pmieducar.matricula` | Situação final (`aprovado`) na importação de Situação do Aluno |

A importação é **filtrada pela escola selecionada**, comparando o código INEP da escola no arquivo com `modules.educacenso_cod_escola`.

## Permissões

Os menus são criados pelas migrations do pacote. As permissões de **cadastro/visualização/exclusão** são concedidas aos mesmos **tipos de usuário** que já acessam a **Exportação Educacenso** (processo **846**).

Cada formato possui um `process` próprio (20071–20079). Usuários sem permissão na exportação precisam receber acesso aos novos menus em **Configurações → Permissões**.

## Relação com outros pacotes

- **`portabilis/i-educar-educacenso-package`** — exportação, análise e importações legadas (`/educacenso/importacao/inep`, `/educacenso/importacao/situacao`). Este pacote Buriti **organiza** o fluxo por ano/formato no menu **Importações** e unifica a experiência com filtro por escola e tela de resultados.
- Pacotes podem coexistir; revise menus duplicados se ambos estiverem instalados.

## Documentação

| Documento | Conteúdo |
|-----------|----------|
| [`INSTALL.md`](INSTALL.md) | Requisitos, instalação, migrations e verificação |
| [`docs/USO.md`](docs/USO.md) | Passo a passo no portal INEP e no i-Educar |
| [`docs/FORMATOS-IMPORTACAO.md`](docs/FORMATOS-IMPORTACAO.md) | Layouts, registros e comportamento de cada formato |
| [`docs/INTEGRACAO.md`](docs/INTEGRACAO.md) | Provider, rotas, migrations e dependências do app |
| [`docs/COMPATIBILIDADE.md`](docs/COMPATIBILIDADE.md) | Matriz i-Educar 2.11/2.12, Laravel e PHP |
| [`docs/REVISAO-E-LIMITACOES.md`](docs/REVISAO-E-LIMITACOES.md) | Auditoria técnica, fragilidades e limitações conhecidas |

## Revisão e limitações

Antes de usar em produção, leia **[`docs/REVISAO-E-LIMITACOES.md`](docs/REVISAO-E-LIMITACOES.md)**. Resumo:

- O pacote **sincroniza códigos INEP e situações** com dados já existentes no i-Educar; **não** substitui a importação censitária completa (pacote Portabilis).
- Exige **INEP da escola** cadastrado; sem ele, o filtro por escola fica permissivo.
- Importação de **situação** espera arquivo de **retorno INEP**, não o export gerado pelo i-Educar.
- Processamento **síncrono**, sem testes automatizados no pacote.

## Requisitos do projeto hospedeiro

- i-Educar **2.11** ou **2.12** com migrations aplicadas e menu **Educacenso → Importações** (processo `9998848`).
- Escolas com **código INEP** em `modules.educacenso_cod_escola`.
- Para Situação do Aluno: turmas e alunos já com códigos INEP vinculados.
- Modelos `App\Models\*` (aluno, servidor, turma, matrícula) do core.

## Autores

Buriti — ver [`composer.json`](composer.json).
