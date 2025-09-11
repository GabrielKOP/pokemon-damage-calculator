# ー Calculadora de Dano Pokémon - Geração 1

Bem-vindo à Calculadora de Dano Pokémon\! Este projeto é uma aplicação web construída para simular com precisão os cálculos de dano das batalhas da Geração 1 (Red/Blue/Yellow) de Pokémon. Inspirado em ferramentas como o Pokémon Showdown, este projeto foi desenvolvido como um exercício prático, utilizando PHP e MySQL no back-end e JavaScript puro para interatividade dinâmica no front-end.

A aplicação não só calcula o dano final entre dois Pokémon, mas também inclui um painel de administração completo para gerir os dados do jogo, como os golpes que cada Pokémon pode aprender.


## ✨ Funcionalidades Implementadas

Até ao momento, o projeto conta com as seguintes funcionalidades:

### Calculadora Principal

  - **Seleção Dinâmica de Pokémon:** Os 151 Pokémon da Geração 1 são carregados diretamente da base de dados.
  - **Carregamento de Stats via AJAX:** Ao selecionar um Pokémon, os seus stats (HP, Ataque, Defesa, Especial, Velocidade) são exibidos instantaneamente sem recarregar a página.
  - **Carregamento Dinâmico de Golpes:** A lista de golpes do Pokémon atacante é carregada dinamicamente (requer configuração no painel de admin).
  - **Cálculo de Dano Preciso (Geração 1):**
      - Implementação da fórmula de dano oficial da primeira geração.
      - **STAB (Same-Type Attack Bonus):** Bónus de 50% no dano se o tipo do golpe for igual a um dos tipos do Pokémon atacante.
      - **Efetividade de Tipos:** Cálculo automático de dano "Super Efetivo" (2x), "Não Muito Efetivo" (0.5x) e imunidades (0x).
      - **Acerto Crítico:** Opção para forçar um acerto crítico, que dobra o nível do atacante na fórmula de dano, como na Geração 1.
      - **Variação de Dano:** O resultado é exibido como um intervalo (85% - 100%) para simular a variação aleatória dos jogos.
  - **Interface Responsiva:** O layout adapta-se a diferentes tamanhos de ecrã, de desktops a telemóveis.

### Painel de Administração

  - **Sistema de Autenticação Seguro:**
      - Página de login para proteger o acesso à área de administração.
      - As senhas são armazenadas de forma segura no banco de dados usando `password_hash()`.
      - Gestão de sessões para manter o utilizador logado.
  - **Ferramenta de Gestão de Golpes:**
      - Interface visual para associar/desassociar golpes a qualquer Pokémon.
      - Seleção de um Pokémon carrega dinamicamente todos os 165 golpes do jogo.
      - Checkboxes pré-marcadas mostram os golpes que o Pokémon já conhece.
      - As alterações são salvas diretamente na base de dados (funcionalidade CRUD completa).

## 🛠️ Tecnologias Utilizadas

  - **Back-end:** PHP 8+, MySQL
  - **Front-end:** HTML5, CSS3 (Flexbox), JavaScript (ES6+ com Fetch API)
  - **Ambiente de Servidor Local:** XAMPP (Apache)
  - **Controlo de Versão:** Git & GitHub

## 🚀 Como Executar o Projeto Localmente

Siga os passos abaixo para configurar e executar o projeto no seu computador.

### Pré-requisitos

  - Ter o [XAMPP](https://www.apachefriends.org/pt_br/index.html) (ou outro ambiente Apache, MySQL, PHP) instalado.
  - Ter o [Git](https://git-scm.com/) instalado.

### Passos de Instalação

1.  **Clonar o Repositório:**
    Abra um terminal e clone o projeto para a pasta `htdocs` do seu XAMPP.

    ```bash
    cd C:/xampp/htdocs/
    git clone https://github.com/SEU-USUARIO/pokemon-damage-calculator.git
    cd pokemon-damage-calculator
    ```

2.  **Configurar o Banco de Dados:**

      - Inicie o Apache e o MySQL no painel de controlo do XAMPP.
      - Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`) e crie uma nova base de dados chamada `pokemon_calculator`.
      - Selecione a base de dados criada e vá para o separador "SQL".
      - Copie e cole o conteúdo de um ficheiro `database.sql` (que conteria todos os `CREATE TABLE` e `INSERT`) e execute-o.
        *(Nota: Como não criámos um ficheiro .sql, este passo seria executar manualmente os scripts SQL que desenvolvemos ao longo do projeto para criar as tabelas e popular os dados.)*

3.  **Configurar a Conexão:**

      - Verifique se o ficheiro `config/db.php` está com as credenciais corretas para o seu ambiente XAMPP (as credenciais padrão `root` e senha vazia já devem funcionar).

4.  **Criar o Utilizador Administrador:**

      - Crie um ficheiro temporário na raiz do projeto chamado `criar_admin.php`.
      - Coloque o código de criação de utilizador que desenvolvemos, ajuste o seu email e senha.
      - Aceda a `http://localhost/pokemon-damage-calculator/criar_admin.php` no seu navegador **uma única vez**.
      - **Apague o ficheiro `criar_admin.php` imediatamente por segurança.**

5.  **Aceder à Aplicação:**

      - **Calculadora:** `http://localhost/pokemon-damage-calculator/`
      - **Painel de Admin:** `http://localhost/pokemon-damage-calculator/login.php`

## 🔮 Próximos Passos e Melhorias

Este projeto tem uma base sólida, mas ainda há muito que pode ser adicionado:

  - [ ] Adicionar mais modificadores de batalha (ex: Reflect, Light Screen).
  - [ ] Implementar as imagens (sprites) dos Pokémon.
  - [ ] Melhorar a interface e a experiência do utilizador.
  - [ ] Criar mais ferramentas no painel de administração (ex: editar stats de Pokémon, adicionar novos golpes).
  - [ ] Associar todos os golpes a todos os Pokémon usando a ferramenta de gestão criada.

-----