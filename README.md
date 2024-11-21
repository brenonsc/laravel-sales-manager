# Laravel Sales Manager 💰📊
![License](https://badgen.net/badge/License/MIT/purple?icon=)
![PHP](https://badgen.net/badge/icon/v8.3/blue?icon=php&label)
![Laravel](https://badgen.net/badge/Laravel/v11/green?icon=)
![Docker](https://badgen.net/badge/icon/Available?icon=docker&label)

O **Laravel Sales Manager** é uma aplicação backend desenvolvida em Laravel com integração via Docker e documentação de endpoints em Swagger. O sistema gerencia usuários, clientes, endereços, produtos e vendas, com autenticação JWT para segurança e rastreamento eficiente.
<br>
<br>

## Tecnologias utilizadas&nbsp; 🔨
<div>
    <img align='center' height='70' width='70' title='PHP' alt='php' src='https://cdn-icons-png.flaticon.com/512/5968/5968332.png' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img align='center' height='50' width='50' title='Laravel' alt='laravel' src='https://cdn.worldvectorlogo.com/logos/laravel-3.svg' />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <img align='center' height='48' width='50' title='JsonWebToken' alt='jsonwebtoken' src='https://images.ctfassets.net/kbkgmx9upatd/6E4gdxqqmafg9Usjz9etTU/bc93ad8e3cea217c3de390239ff34c8c/jwt-hero.png' /> &nbsp;&nbsp;&nbsp;&nbsp;
    <img align='center' height='50' width='50' title='MySQL' alt='mysql' src='https://cdn-icons-png.flaticon.com/512/5968/5968313.png' /> &nbsp;
    <img align='center' height='62' width='72' title='Swagger' alt='swagger' src='https://github.com/bush1D3v/tsbank_api/assets/133554156/6739401f-d03b-47f8-b01f-88da2a9075d1' /> &nbsp;
    <img align='center' height='55' width='55' title='Docker' alt='docker' src='https://cdn4.iconfinder.com/data/icons/logos-and-brands/512/97_Docker_logo_logos-512.png' />
</div>
<br>

## Requisitos&nbsp; ☑️

1. 🐳 **Docker**: Caso não tenha instalado, baixe no [site oficial do Docker](https://www.docker.com/products/docker-desktop).
2. 🐱 **Git**: Para clonar o repositório.
<br>

## Passos para Instalação&nbsp; 🖥️

1. **Clone o Repositório**  
   Abra o terminal, navegue até a pasta desejada e execute:  
   ```bash
   git clone https://github.com/brenonsc/laravel-sales-manager.git
   ```

2. **Entre na Pasta do Projeto**  
   ```bash
   cd laravel-sales-manager
   ```

3. **Inicie o Projeto**  
   Execute o comando para configurar o ambiente e subir os containers Docker:  
   ```bash
   cp .env.example .env && docker compose up --build
   ```

4. **(Opcional)** **_Gere uma nova chave JWT_**  
   > **⚠️ Importante:** Este passo garante segurança no processo de autenticação.  
   Abra uma nova aba no terminal e execute o comando após os containers estarem ativos:  
   ```bash
   docker compose exec app php artisan jwt:secret
   ```
<br>

## Endpoints Documentados&nbsp; 🟢

Os endpoints estão documentados em **Swagger** e podem ser acessados via:  
- [http://localhost:8000](http://localhost:8000)  
- [http://localhost:8000/api/docs](http://localhost:8000/api/docs)
<br>

## Principais Endpoints&nbsp; ▶️

### **Autenticação**&nbsp; 🔐

**Controller:** `AuthController`  
- **POST /login**: Autentica o usuário e retorna um token JWT.  
- **POST /signup**: Registra um novo usuário.  
- **POST /logout**: Encerra a sessão autenticada.  
- **GET /me**: Retorna os dados do usuário autenticado.  

---

### **Clientes**&nbsp; :busts_in_silhouette:

**Controller:** `ClientController`  
- **GET /clients**: Lista todos os clientes.  
- **GET /clients/{id}**: Exibe detalhes de um cliente específico.  
- **POST /clients**: Cria um novo cliente com seus endereços.  
- **PUT /clients/{id}**: Atualiza informações do cliente e seu endereço.  
- **DELETE /clients/{id}**: Deleta logicamente um cliente.  

---

### **Produtos**&nbsp; 📦

**Controller:** `ProductController`  
- **GET /products**: Lista produtos ativos.  
- **GET /products/{id}**: Exibe detalhes de um produto.  
- **POST /products**: Cria um novo produto.  
- **PUT /products/{id}**: Atualiza informações de um produto.  
- **DELETE /products/{id}**: Marca um produto como inativo.  

---

### **Vendas**&nbsp; :chart_with_upwards_trend:

**Controller:** `SaleController`  
- **GET /sales**: Lista todas as vendas.  
- **POST /sales**: Registra uma nova venda.  

<br>

## Testes&nbsp; :man_scientist:

Esta aplicação possui **testes unitários** implementados para todos os **controllers**, garantindo a funcionalidade e a estabilidade da aplicação.

#### Executando os Testes

Para rodar os testes, siga os passos abaixo:

1. Execute as migrações no ambiente de testes:
   ```bash
   docker compose exec app php artisan migrate --env=testing
   ```

2. Execute os testes:
   ```bash
   docker compose exec app php artisan test
   ```
<br>

## Licença&nbsp; :clipboard:

Este software está licenciado sob a [Licença MIT](https://github.com/brenonsc/laravel-sales-manager/blob/main/LICENSE).
<br>
