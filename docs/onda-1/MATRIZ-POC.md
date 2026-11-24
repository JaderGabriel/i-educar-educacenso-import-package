# Matriz PoC — 5.10 Censo (Onda 1)

| ID | Status | Evidência |
| --- | --- | --- |
| 5.10.1–5.10.6 | **atende** | Cadastros núcleo (escola, aluno, turma, matrícula, profissional) |
| 5.10.7 / 5.10.7.1–2 | **atende** | Export Educacenso Portabilis + layouts ano |
| 5.10.8 | **parcial** | Restrição exclusão — validar regra na homologação |
| 5.10.9–5.10.10 | **atende** | Identificação INEP + import retorno (`EducacensoImportFormatCatalog`) |
| 5.10.12 | **atende** | Tabelas auxiliares INEP no núcleo/pacote |
| 5.10.13 | **parcial** | Painel acompanhamento — UI import Buriti |
| 5.10.14 | **atende** | Relatórios quantitativos |
| 5.10.15 | **parcial** | Notificação erros — validar |
| 5.10.16–5.10.17 | **atende** | Visualização layout + registros excluídos (docs 2026) |

Layouts: **2025 e 2026** em `EducacensoImportFormatCatalog` + `docs/FORMATOS-CENSO-2026.md`.

```bash
php packages/buriti/i-educar-educacenso-import-package/tests/run.php
```
