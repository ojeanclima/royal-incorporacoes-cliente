Com base em toda a documentação já criada para o projeto **Royal Incorporações**, segue a **nova versão do escopo do projeto**, consolidando todas as funcionalidades, tecnologias utilizadas e instruções atualizadas (excluindo JetForm e incluindo JetFilter e ACF):

---

## 🧾 **DOCUMENTAÇÃO GERAL – SISTEMA DE ÁREA RESTRITA (ROYAL INCORPORAÇÕES)**

**Versão:** 2.0
**Data:** 16/05/2025
**Responsável Técnico:** Jean Carlos – UIOS Soluções Digitais

---

### 📌 **1. VISÃO GERAL**

Sistema em WordPress para gestão de documentos do tipo IPTU (Custom Post Type), com área restrita para clientes e administradores. A estrutura é baseada em ACF, Elementor Pro, JetSmartFilters (apenas filtro) e um conjunto de plugins customizados desenvolvidos especialmente para o projeto.

---

### ⚙️ **2. FUNCIONALIDADES PRINCIPAIS**

#### 2.1. Tipos de Usuário

* **Administrador**: acesso total ao sistema, criação de documentos, cadastro de clientes, empreendimentos e apartamentos.
* **Cliente**: visualiza apenas seus documentos filtrados automaticamente por CPF e Data de Nascimento.

#### 2.2. Login por CPF + Data de Nascimento

* Implementado via plugin `Royal Login CPF + Redirecionamento`.
* Campos personalizados ACF: `cpf_do_cliente`, `data_de_nascimento_do_cliente`.

#### 2.3. Custom Post Type: `iptu`

* Armazena os documentos por cliente.
* Campos ACF personalizados: `arquivo`, `status`, `data_de_emissao`, `gp_cliente` (usuário), `empreendimento`, `apartamento`.

#### 2.4. Taxonomias

* **Empreendimento**: usada para categorizar documentos por bloco ou projeto.
* **Apartamento**: categorização adicional associada ao empreendimento.

#### 2.5. Filtros com JetSmartFilters

* Plugin ativo: `JetFilter - Filtro por Cliente ACF`.
* Filtragem baseada em campos personalizados ACF e taxonomias.
* Suporte a AJAX e Shortcode.
* Filtro por status, apartamento e empreendimento.

#### 2.6. Painel do Cliente (via Elementor Pro)

* Contadores por status (Pendente, Pago, Vencido).
* Listagem de documentos com botão de download.
* Renderização via Loop Grid com query condicional do plugin `Royal Elementor Custom Query`.

#### 2.7. Painel do Administrador

* Upload de novos documentos.
* Cadastro de novos clientes.
* Cadastro ou seleção dinâmica de empreendimentos e apartamentos com AJAX.
* Validação visual e condicional dos campos com Alpine.js e TailwindCSS.

---

### 🏗️ **3. ESTRUTURA DE PÁGINAS**

| Página                      | Descrição                                                          |
| --------------------------- | ------------------------------------------------------------------ |
| `/login`                    | Login com CPF e data de nascimento                                 |
| `/dashboard`                | Painel único com exibição condicional (cliente ou administrador)   |
| `/cadastrar-documento`      | Upload de documentos no CPT `iptu` com relação a usuário/taxonomia |
| `/cadastrar-cliente`        | Cadastro de cliente com CPF e data de nascimento                   |
| `/cadastrar-empreendimento` | Cadastro de termos da taxonomia `empreendimento`                   |
| `/cadastrar-apartamento`    | Cadastro de termos da taxonomia `apartamento`                      |
| `/politica-de-privacidade`  | Página com informações da LGPD                                     |
| `/ajuda`                    | Central de Ajuda com instruções e dúvidas frequentes               |

---

### 🔐 **4. PERMISSÕES E FLUXO**

| Ação                                            | Administrador | Cliente |
| ----------------------------------------------- | ------------- | ------- |
| Login e acesso ao sistema                       | ✅             | ✅       |
| Upload e cadastro de documentos                 | ✅             | ❌       |
| Visualização de todos documentos                | ✅             | ❌       |
| Visualização de documentos próprios             | ✅             | ✅       |
| Cadastro de clientes                            | ✅             | ❌       |
| Cadastro de empreendimentos/apartamentos        | ✅             | ❌       |
| Filtros por status, apartamento, empreendimento | ✅             | ✅       |

---

### 🧩 **5. PLUGINS ATIVOS PRINCIPAIS**

| Plugin                              | Função                                                                |
| ----------------------------------- | --------------------------------------------------------------------- |
| ACF (Advanced Custom Fields)        | Gerenciamento de campos personalizados                                |
| Elementor Pro                       | Construção de páginas e exibição dinâmica (Loop Grid)                 |
| JetSmartFilters (somente JetFilter) | Filtros com base nos campos ACF                                       |
| Royal Elementor Custom Query        | Query condicional para exibir somente os documentos do usuário logado |
| Royal Gestão de Documentos          | Interface administrativa e lógica de cadastro                         |
| Royal Login CPF + Redirecionamento  | Login por CPF + Data de Nascimento                                    |

---

### 📦 **6. RECOMENDAÇÕES FUTURAS**

* Visualização direta de PDF sem download.
* Notificações automáticas por e-mail quando um novo documento for publicado.
* Exportação de relatórios (PDF, Excel ou ZIP).
* Auditoria de acessos e logs detalhados.
* Dashboard com gráficos e estatísticas de uso/documentos.

---

### ✅ **7. HOMOLOGAÇÃO E VALIDAÇÃO**

| Item                                   | Critério de Aceitação                   |
| -------------------------------------- | --------------------------------------- |
| Login por CPF/Data                     | Validação funcional e segura            |
| Filtro dinâmico por cliente/status     | Testado com JetSmartFilters             |
| Upload de documentos com metadados     | Funcionalidade OK via formulário ACF    |
| Visualização condicional de documentos | Apenas documentos vinculados ao usuário |
| Cadastro AJAX de taxonomias            | Campos carregados sem reload            |

---
