# Royal Incorporações – Área Restrita

**Versão:** 2.0  
**Data:** 16/05/2025  
**Responsável Técnico:** Jean Carlos – UIOS Soluções Digitais

---

## Descrição do Projeto

O sistema “Royal Incorporações – Área Restrita” é uma plataforma em WordPress voltada para a gestão de guias de pagamento de IPTU. Ele fornece uma área restrita, onde clientes acessam apenas seus documentos e administradores gerenciam cadastros, uploads e filtros de forma centralizada.

---

## 🧾 1. Funcionalidades Principais

1.1. Tipos de Usuário  
- **Administrador**: acesso total — cadastra clientes, empreendimentos, apartamentos e documentos IPTU.  
- **Cliente**: faz login por CPF + data de nascimento e visualiza somente seus próprios documentos.

1.2. Autenticação  
- Plugin **Royal Login CPF + Redirecionamento**.  
- Campos ACF: `cpf_do_cliente`, `data_de_nascimento_do_cliente`.

1.3. Custom Post Type `iptu`  
- Armazena guias com metadados ACF:  
  - `gp_upload_do_arquivo` (arquivo)  
  - `gp_status` (pendente/pago/vencido)  
  - `gp_vencimento` (data)  
  - `gp_cliente` (relação usuário)

1.4. Taxonomias  
- **Empreendimento**: categorização por bloco/projeto.  
- **Apartamento**: subdivisão vinculada ao empreendimento.

1.5. Filtros e Busca  
- **JetSmartFilters (JetFilter)**: filtros por status, empreendimento, apartamento e campos ACF com AJAX e shortcode.

1.6. Painéis  
- **Cliente (Elementor Pro)**: contadores por status, listagem com download, loop grid customizado.  
- **Administrador**: upload de documentos, cadastro via AJAX, validação com Alpine.js e estilo TailwindCSS.

---

## 🏗️ 2. Estrutura de Páginas

| Rota                        | Descrição                                                                    |
|-----------------------------|------------------------------------------------------------------------------|
| `/login`                    | Autenticação por CPF + data de nascimento                                     |
| `/dashboard`                | Painel único (visão adaptada para cliente ou administrador)                  |
| `/cadastrar-documento`      | Formulário CPT `iptu` para upload de guias                                   |
| `/cadastrar-cliente`        | Registro de novo cliente                                                      |
| `/cadastrar-empreendimento` | Cadastro de termos na taxonomia `empreendimento`                              |
| `/cadastrar-apartamento`    | Cadastro de termos na taxonomia `apartamento`                                 |
| `/politica-de-privacidade`  | Informações de conformidade com LGPD                                          |
| `/ajuda`                    | Central de ajuda com instruções e FAQ                                         |

---

## 🔐 3. Permissões e Fluxo

| Ação                                        | Administrador | Cliente |
|---------------------------------------------|:-------------:|:-------:|
| Acesso ao sistema                           | ✅            | ✅      |
| Cadastro de documentos IPTU                 | ✅            | ❌      |
| Visualização de todos os documentos         | ✅            | ❌      |
| Visualização de documentos próprios         | ✅            | ✅      |
| Cadastro de clientes, empreendimentos, aptos | ✅            | ❌      |
| Aplicação de filtros e busca                | ✅            | ✅      |

---

## 🧩 4. Plugins Ativos Principais

| Plugin                                   | Finalidade                                              |
|------------------------------------------|---------------------------------------------------------|
| Advanced Custom Fields (ACF)             | Gerenciamento de campos personalizados                  |
| Elementor Pro                            | Construção de páginas e exibição dinâmica (Loop Grid)   |
| JetSmartFilters (JetFilter)              | Filtros dinâmicos por ACF                               |
| Royal Elementor Custom Query             | Query condicional para documentos do usuário logado     |
| Royal Gestão de Documentos               | Interface administrativa e lógica de cadastro           |
| Royal Login CPF + Redirecionamento       | Autenticação customizada via CPF + data de nascimento   |

---

## ⚙️ 5. Setup e Configuração

1. Clone o repositório  
   `git clone git@github.com:ojeanclima/royal-incorporacoes-cliente.git`

2. Defina variáveis de ambiente  
   Crie um arquivo `.env` com DB_NAME, DB_USER, DB_PASS, etc.

3. Containers Docker (opcional)  
   ```bash
   docker-compose up -d
   ```

4. Instale dependências e ative plugins no WordPress.

5. Ajuste URLs via WP-CLI  
   ```bash
   wp search-replace 'localhost' 'seu-dominio.com'
   ```

---

## 🔍 6. Recomendações Futuras

- Visualização inline de PDF sem download  
- Notificações automáticas por e-mail ao publicar novo documento  
- Exportação de relatórios (PDF, Excel ou ZIP)  
- Auditoria de acessos e logs detalhados  
- Dashboard com gráficos e estatísticas de uso/documentos

---

## 📜 7. Histórico de Versões

| Versão | Data       | Descrição                                                        |
|--------|------------|------------------------------------------------------------------|
| 2.0    | 16/05/2025 | Documentação aprimorada e ajustes gerais                         |
| 1.1    | 10/05/2025 | Atualização do README com seção de updates                       |
| 1.0    | 01/05/2025 | Inicialização do projeto (WordPress, Docker, variáveis e docs)   |

---