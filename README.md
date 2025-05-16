Aqui está o conteúdo do README.md com uma formatação aprimorada, tornando-o mais organizado, visualmente agradável e fácil de ler:

---

# 🏢 Royal Incorporações – Área Restrita

**Versão:** 2.0  
**Data:** 16/05/2025  
**Responsável Técnico:** Jean Carlos – UIOS Soluções Digitais

---

## 🧾 Documentação Geral – Sistema de Área Restrita

### 📌 1. Visão Geral

Sistema em **WordPress** para gestão de documentos do tipo **IPTU (Custom Post Type)**, com área restrita para clientes e administradores.  
A estrutura é baseada em **ACF**, **Elementor Pro**, **JetSmartFilters** (apenas JetFilter) e plugins personalizados.

---

### ⚙️ 2. Funcionalidades Principais

#### 2.1. Tipos de Usuário

- **Administrador:** acesso total ao sistema, criação de documentos, cadastro de clientes, empreendimentos e apartamentos.
- **Cliente:** visualiza apenas seus próprios documentos, filtrados automaticamente por CPF e Data de Nascimento.

#### 2.2. Login por CPF + Data de Nascimento

- Implementado via plugin `Royal Login CPF + Redirecionamento`.
- Campos personalizados ACF: `cpf_do_cliente`, `data_de_nascimento_do_cliente`.

#### 2.3. Custom Post Type: `iptu`

- Armazena documentos por cliente.
- Campos personalizados ACF:  
  - `arquivo`  
  - `status`  
  - `data_de_emissao`  
  - `gp_cliente` (usuário)  
  - `empreendimento`  
  - `apartamento`

#### 2.4. Taxonomias

- **Empreendimento:** categorização por bloco/projeto.
- **Apartamento:** categorização adicional associada ao empreendimento.

#### 2.5. Filtros com JetSmartFilters

- Plugin ativo: `JetFilter - Filtro por Cliente ACF`.
- Filtragem baseada em campos ACF e taxonomias.
- Suporte a AJAX e Shortcode.
- Filtros por status, apartamento e empreendimento.

#### 2.6. Painel do Cliente (Elementor Pro)

- Contadores por status (Pendente, Pago, Vencido).
- Listagem de documentos com botão de download.
- Renderização via Loop Grid com query condicional (`Royal Elementor Custom Query`).

#### 2.7. Painel do Administrador

- Upload de documentos.
- Cadastro de clientes.
- Cadastro/seleção dinâmica de empreendimentos e apartamentos via AJAX.
- Validação visual e condicional dos campos com **Alpine.js** e **TailwindCSS**.

---

### 🏗️ 3. Estrutura de Páginas

| Página                      | Descrição                                                          |
|-----------------------------|--------------------------------------------------------------------|
| `/login`                    | Login com CPF e Data de Nascimento                                 |
| `/dashboard`                | Painel único (visualização condicional: cliente ou administrador)  |
| `/cadastrar-documento`      | Upload de documentos no CPT `iptu`, vinculando usuário/taxonomia   |
| `/cadastrar-cliente`        | Cadastro de cliente com CPF e Data de Nascimento                   |
| `/cadastrar-empreendimento` | Cadastro de termos da taxonomia `empreendimento`                   |
| `/cadastrar-apartamento`    | Cadastro de termos da taxonomia `apartamento`                      |
| `/politica-de-privacidade`  | Informações da LGPD                                                |
| `/ajuda`                    | Central de Ajuda com instruções e dúvidas frequentes               |

---

### 🔐 4. Permissões e Fluxo

| Ação                                            | Administrador | Cliente |
|-------------------------------------------------|:-------------:|:-------:|
| Login e acesso ao sistema                       | ✅            | ✅      |
| Upload e cadastro de documentos                 | ✅            | ❌      |
| Visualização de todos documentos                | ✅            | ❌      |
| Visualização de documentos próprios             | ✅            | ✅      |
| Cadastro de clientes                            | ✅            | ❌      |
| Cadastro de empreendimentos/apartamentos        | ✅            | ❌      |
| Filtros por status, apartamento, empreendimento | ✅            | ✅      |

---

### 🧩 5. Plugins Ativos Principais

| Plugin                              | Função                                                                |
|-------------------------------------|-----------------------------------------------------------------------|
| **ACF (Advanced Custom Fields)**    | Gerenciamento de campos personalizados                                |
| **Elementor Pro**                   | Construção de páginas e exibição dinâmica (Loop Grid)                 |
| **JetSmartFilters (JetFilter)**     | Filtros baseados em campos ACF                                        |
| **Royal Elementor Custom Query**    | Query condicional para documentos do usuário logado                   |
| **Royal Gestão de Documentos**      | Interface administrativa e lógica de cadastro                         |
| **Royal Login CPF + Redirecionamento**| Login por CPF + Data de Nascimento                                  |

---

### 📦 6. Recomendações Futuras

- Visualização direta de PDF sem download.
- Notificações automáticas por e-mail ao publicar novo documento.
- Exportação de relatórios (PDF, Excel ou ZIP).
- Auditoria de acessos e logs detalhados.
- Dashboard com gráficos e estatísticas de uso/documentos.

---

### ✅ 7. Homologação e Validação

| Item                                   | Critério de Aceitação                      |
|----------------------------------------|--------------------------------------------|
| Login por CPF/Data                     | Validação funcional e segura               |
| Filtro dinâmico por cliente/status     | Testado com JetSmartFilters                |
| Upload de documentos com metadados     | OK via formulário ACF                      |
| Visualização condicional de documentos | Apenas documentos vinculados ao usuário    |
| Cadastro AJAX de taxonomias            | Campos carregados sem reload               |

---
