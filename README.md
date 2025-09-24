# Calculadora de Dano Pokémon - Geração 1

Bem-vindo à Calculadora de Dano Pokémon\! Este projeto é uma aplicação web full-stack construída para simular os cálculos de dano das batalhas da Geração 1 de Pokémon. Inspirado em ferramentas como o Pokémon Showdown, este projeto foi desenvolvido com PHP e MySQL no back-end e JavaScript puro para interatividade dinâmica no front-end.

A aplicação evoluiu para incluir um sistema de autenticação de utilizadores completo e seguro, juntamente com um painel de administração para gerir os dados do jogo, tornando-se uma aplicação web robusta e multifacetada.


## ✨ Funcionalidades Implementadas

### Calculadora Principal

  - **Interface de Batalha:** Design inspirado nos jogos clássicos, com sprites e barras de vida interativas.
  - **Seleção Dinâmica:** Os 151 Pokémon são carregados da base de dados.
  - **Carregamento via AJAX:** Stats e listas de golpes são carregados instantaneamente sem recarregar a página.
  - **Cálculo de Dano Preciso (G1):**
      - Implementação da fórmula de dano oficial da Geração 1.
      - **STAB (Same-Type Attack Bonus):** Bónus de 50% no dano.
      - **Efetividade de Tipos:** Cálculo automático de dano Super Efetivo (2x), Não Muito Efetivo (0.5x) e Imunidades (0x).
      - **Acerto Crítico:** Opção para forçar um acerto crítico.
      - **Variação de Dano:** O resultado é exibido como um intervalo (85% - 100%).
  - **Design Responsivo (Mobile First):** A interface adapta-se de forma fluida a qualquer tamanho de ecrã.

### Sistema de Autenticação Completo

  - **Registo Público de Utilizadores:** Página de registo (`registrar.php`) com validação de dados (email, força da senha, confirmação de senha).
  - **Login Seguro:** As senhas são encriptadas com `password_hash()` (nunca guardadas em texto plano). O acesso é gerido por sessões PHP.
  - **Recuperação de Senha:** Fluxo completo e seguro de "Esqueci a minha senha" com tokens temporários e com hash, enviados por email (simulado).
  - **Perfis de Utilizador:** Distinção entre utilizadores `comum` e `admin`, com o painel de administração protegido.

### Painel de Administração

  - **Acesso Protegido:** Apenas utilizadores com o perfil `admin` podem aceder.
  - **Ferramenta de Gestão de Golpes:** Interface visual (CRUD) para associar/desassociar golpes a qualquer Pokémon.

## 🛠️ Tecnologias Utilizadas

  - **Back-end:** PHP 8+, MySQL
  - **Front-end:** HTML5, CSS3 (Flexbox, Variáveis CSS), JavaScript (ES6+ com Fetch API)
  - **Gestor de Dependências:** Composer
  - **Biblioteca de Email:** PHPMailer
  - **Ambiente de Servidor:** XAMPP (Apache)
  - **Controlo de Versão:** Git & GitHub

## 🚀 Como Executar o Projeto Localmente

Siga os passos abaixo para configurar o projeto num novo ambiente do zero.

### Pré-requisitos

  - Ter o [XAMPP](https://www.apachefriends.org/pt_br/index.html) (com Apache e MySQL a correr) instalado.
  - Ter o [Git](https://git-scm.com/) instalado.

### Passos de Instalação

**Passo 1: Clonar o Repositório**
Abra um terminal e clone o projeto para a pasta `htdocs` do seu XAMPP.

```bash
cd C:/xampp/htdocs/
git clone https://github.com/SEU-USUARIO/pokemon-damage-calculator.git
cd pokemon-damage-calculator
```

**Passo 2: Executar o Setup da Base de Dados**
A configuração da base de dados é totalmente automática. Use o método mais conveniente para si.

  * **Método A (Manual / Multi-plataforma): Executando os Scripts em Ordem**
    Abra o terminal na pasta do projeto e execute os scripts PHP na seguinte ordem:

    ```bash
    # 1. Cria a Base de Dados e as Tabelas
    php scripts/criar_estrutura.php

    # 2. Cria os Tipos e os Pokémon
    php scripts/povoar_pokemon_e_tipos.php

    # 3. Cria os Golpes
    php scripts/povoar_golpes.php

    # 4. Liga os Golpes aos Pokémon
    php scripts/ligar_golpes_a_pokemon.php
    ```

**Passo 4: Criar o Primeiro Administrador**
O primeiro administrador é criado de forma segura, sem scripts temporários.

1.  Aceda à página de registo no seu navegador: `http://localhost/pokemon-damage-calculator/registrar.php`.
2.  Crie a sua primeira conta. Por defeito, ela terá o perfil de utilizador "comum".
3.  Abra o **phpMyAdmin**, navegue até à tabela `usuarios` na base de dados `pokemon_calculator`.
4.  Encontre o utilizador que acabou de criar e edite o seu registo: altere o valor na coluna `perfil` de `comum` para `admin`.

**Passo 5: Pronto para Usar\!**

  - **Acede à Calculadora:** `http://localhost/pokemon-damage-calculator/`
  - **Acede ao Painel de Admin:** `http://localhost/pokemon-damage-calculator/login.php` (use as credenciais que acabou de criar e promover a admin).
