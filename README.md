# i-Educar Educacenso Import Package (Buriti)

Pacote para importação de arquivos TXT do Educacenso/INEP no i-Educar.

## Funcionalidades

- Menu **Educacenso → Importações** com subpastas por ano (**2025** e **2026**)

### 2025

- **Migração TXT — Exportação INEP** (`migracao-txt`)
- **Identificação INEP — Retorno de alunos** (`identificacao-retorno-txt`)

### 2026 (Matrícula Inicial)

- **Migração TXT — Exportação INEP** (`migracao-txt`)
- **Identificação INEP — Resultado encontrado** (`identificacao-retorno-txt`) — `RESULTADO_ENCONTRADO.txt` / `RESULTADO_CERTO.txt`
- **Identificação INEP — Resultados prováveis** (`identificacao-provaveis-txt`) — `RESULTADO_PROVAVEIS.txt`
- **Identificação INEP — Não encontrados** (`identificacao-nao-encontrado-txt`) — `RESULTADO_SEMID.txt`
- **Retorno INEP — Códigos preenchidos** (`inep-retorno-txt`) — registros 20, 40, 50 e 60 com INEP

### 2026 (Situação do Aluno — 2ª etapa)

- **Situação do Aluno — Migração TXT** (`situacao-aluno-migracao-txt`) — registros 89, 90 e 91
- **Situação do Aluno — Identificação INEP** (`situacao-aluno-identificacao-txt`)

- Importação filtrada por **ano letivo** e **escola** (como a análise de exportação)
- Gravação de códigos INEP em:
  - `modules.educacenso_cod_aluno`
  - `modules.educacenso_cod_docente`
  - `modules.educacenso_cod_turma`
  - `modules.educacenso_matricula`
- Registro em `educacenso_imports` e tela de resultados

## Instalação

1. Adicione o repositório path em `packages/plug-and-play.json`:

```json
{
  "type": "path",
  "url": "./packages/buriti/i-educar-educacenso-import-package",
  "symlink": true
}
```

2. Inclua na seção `require`:

```json
"buriti/i-educar-educacenso-import-package": "@dev"
```

3. Execute:

```bash
composer plug-and-play
php artisan migrate
```

## Uso

Acesse **Educacenso → Importações → [Ano] → [Formato]**, selecione instituição/escola, envie o arquivo TXT e confira o resultado na tela final.

## Compatibilidade

- PHP >= 8.3
- i-Educar 2.10+
