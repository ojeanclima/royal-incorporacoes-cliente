Claro, Jean! A seguir, apresento uma documentação técnica detalhada para configurar uma listagem dinâmica no JetEngine que exibe documentos personalizados associados a usuários específicos, com controle de acesso baseado na função do usuário.

---

# 📄 Documentação Técnica: Listagem de Documentos por Usuário com JetEngine

## 🎯 Objetivo
Configurar uma listagem dinâmica ond:

 **Administradores** visualizam todos os documentos do tipo personalizado `documentos.
 **Clientes** visualizam apenas os documentos associados a eles por meio de uma relação many-to-man.

## 🧩 Estrutura de Dados

- **Custom Post Type (CPT):* `documents`
- **Meta Fields do CPT:**
 - `titulo_do_documeno`
 - `arquio`
 - `stats`
 - `data_de_emisso`
- **Relação JetEngine:**
  - **ID da Relação:* `2`
  - **Objeto Pai:* `documents`
  - **Objeto Filho:* `uses`
  - **Tipo de Relação:* Many-to-Mny

## ⚙️ Configuração no JetEngine Query Builder

### 1. Criar uma Nova Consulta SQL
- Acesse o painel do WordPress e vá para **JetEngine > Query Builder > Add New*.
- **Nome da Consulta:* `Documentos por Usuáro`
- **Tipo de Consulta:* `SQL Quey`

### 2. Consulta SQL Principa

No editor avançado da consulta, insira o seguinte códio:


```sql
SELECT DISTINCT p.ID, 
       p.post_title, 
       p.post_date,
       titulo.meta_value AS titulo_do_documento,
       arquivo.meta_value AS arquivo,
       status.meta_value AS status,
       emissao.meta_value AS data_de_emissao
FROM wp_posts AS p
LEFT JOIN wp_jet_rel_12 AS r ON p.ID = r.parent_object_id
LEFT JOIN wp_usermeta AS um ON r.child_object_id = um.user_id AND um.meta_key = 'wp_capabilities'
LEFT JOIN wp_postmeta AS titulo ON titulo.post_id = p.ID AND titulo.meta_key = 'titulo_do_documento'
LEFT JOIN wp_postmeta AS arquivo ON arquivo.post_id = p.ID AND arquivo.meta_key = 'arquivo'
LEFT JOIN wp_postmeta AS status ON status.post_id = p.ID AND status.meta_key = 'status'
LEFT JOIN wp_postmeta AS emissao ON emissao.post_id = p.ID AND emissao.meta_key = 'data_de_emissao'
WHERE p.post_type = 'documentos'
  AND p.post_status = 'publish'
  AND (
    um.meta_value LIKE '%administrator%'
    OR r.child_object_id = %current_user_id%
  )
``


**Observações:**
- A macro `%current_user_id%` é utilizada para obter dinamicamente o ID do usuário atul.- Certifique-se de selecionar a macro através do botão “Dynamic Tags” para garantir a substituição correa.

### 3. Consulta de Contagem para Paginaçã

Para habilitar a paginação com o JetSmartFilters, insira a seguinte consulta na seção **Count SQL Query*:


```sql
SELECT COUNT(DISTINCT p.ID) AS total
FROM wp_posts AS p
LEFT JOIN wp_jet_rel_12 AS r ON p.ID = r.parent_object_id
LEFT JOIN wp_usermeta AS um ON r.child_object_id = um.user_id AND um.meta_key = 'wp_capabilities'
WHERE p.post_type = 'documentos'
  AND p.post_status = 'publish'
  AND (
    um.meta_value LIKE '%administrator%'
    OR r.child_object_id = %current_user_id%
  )
``


## 🎨 Criação do Listing Template
1. Vá para **JetEngine > Listings > Add Ne**.
2. **Fonte do Listing** `Query Builer`
3. **Selecione a Consulta** `Documentos por Usuáio`4. Utilize widgets como **Dynamic Field** para exibir os camos:
  - `titulo_do_documeto`
  - `arquivo` (pode ser exibido como link ou botão de downlad)
  - `staus`
  - `data_de_emisao`

## 🧪 Testes e Validaçã

- **Administrador:** Deve visualizar todos os documetos
- **Cliente:** Deve visualizar apenas os documentos associados aele.

## 📌 Considerações Finas

- Evite digitar macros manualmente; utilize o botão “Dynamic Tags” para inseri-las corretaent.
- Certifique-se de que os nomes dos meta fields utilizados na consulta correspondem exatamente aos definidos n CP.
- Para mais informações sobre o uso de macros no JetEngine, consulte a [documentação oficial](https://crocoblock.com/knowledge-base/jetengine/macros-gude/).

Se desejar, posso fornecer um arquivo `.json` para importação direta dessa configuração no JetEngine. Gostaria de prosseguir com isso? 