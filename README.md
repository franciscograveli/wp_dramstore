# Dram Store — Loja Virtual (Trabalho de E-Commerce / WooCommerce)

Loja virtual fictícia de whiskies, gins e destilados premium, desenvolvida como
trabalho da disciplina de E-Commerce (Tecnologia em Gestão da Tecnologia da
Informação — Instituto Federal Sudeste de Minas Gerais). Construída com
**WordPress + WooCommerce**, rodando 100% em **Docker**.

## Estrutura do projeto

```
.
├── docker-compose.yml          # orquestra os containers do projeto
└── wordpress/                  # arquivos do WordPress (montados no container)
    └── database-export/
        └── dram_store_db.sql   # dump completo do banco de dados
```

## Serviços (containers)

| Serviço | Container | Porta | Descrição |
|---|---|---|---|
| WordPress | `wp_app` | 8080 | Loja e painel administrativo |
| MariaDB | `wp_db` | (interna) | Banco de dados |
| Mailpit | `wp_mailpit` | 8025 (web) / 1025 (SMTP) | Captura e-mails em ambiente de dev |

## Como rodar o projeto

**Pré-requisito:** Docker Desktop instalado.

1. Suba os containers:
   ```bash
   docker compose up -d
   ```

2. **Se for uma instalação nova** (banco vazio), importe o dump SQL incluso:
   ```bash
   docker exec -i wp_db mariadb -u wordpress -pwordpress wordpress < wordpress/database-export/dram_store_db.sql
   ```
   Se o banco já foi criado pelo próprio `docker-compose up` com dados (volume `db_data` já populado), esse passo não é necessário.

3. Acesse a loja: **http://localhost:8080**

4. Acesse o painel administrativo pela URL customizada (veja seção de segurança abaixo):
   **http://localhost:8080/acesso-equipe-dram/**

5. E-mails do site (confirmação de pedido, recuperação de carrinho, redefinição de senha etc.) não são enviados de verdade neste ambiente — eles ficam disponíveis no Mailpit:
   **http://localhost:8025**

## Segurança: proteção contra brute-force no login

Por padrão, o WordPress expõe as URLs `/wp-admin/` e `/wp-login.php` publicamente.
Essas são as portas de entrada nº1 de ataques automatizados de força bruta
(bots tentando milhares de combinações de usuário/senha). Foram aplicadas duas
camadas de proteção:

1. **WPS Hide Login** — troca a URL de login padrão por uma customizada e
   imprevisível. Acessar `/wp-admin/` ou `/wp-login.php` agora retorna
   **404** (a página nem revela que existe um WordPress ali). A URL real de
   login passou a ser `/acesso-equipe-dram/`.

2. **Limit Login Attempts Reloaded** — mesmo que alguém descubra a URL certa,
   o plugin bloqueia temporariamente o IP após algumas tentativas de login
   incorretas seguidas, tornando um ataque de força bruta inviável na prática.

⚠️ **Importante:** guarde a URL de login (`/acesso-equipe-dram/`) em lugar
seguro. Se ela for esquecida, é possível recuperá-la editando diretamente a
tabela `wp_options` no banco (campo `whl_page`), ou removendo a pasta do
plugin `wp-content/plugins/wps-hide-login` via arquivos para voltar ao
`/wp-admin/` padrão temporariamente.

## Credenciais de acesso

- **URL de login:** http://localhost:8080/acesso-equipe-dram/
- **Usuário administrador:** `root`
- **Senha:** `root`

## Principais plugins instalados

| Plugin | Função |
|---|---|
| WooCommerce | Motor da loja (produtos, pedidos, carrinho, frete, cupons) |
| Elementor + Header Footer Elementor | Construtor visual de páginas, cabeçalho e rodapé |
| Rank Math SEO | Otimização de SEO dos produtos e páginas |
| FiboSearch (Ajax Search for WooCommerce) | Busca inteligente com autocomplete |
| YITH WooCommerce Wishlist | Lista de desejos |
| YITH WooCommerce Product Slider Carousel | Carrossel de produtos na home |
| Strong Testimonials | Depoimentos de clientes, com formulário público de envio |
| Click to Chat | Botão flutuante de WhatsApp |
| PagBank for WooCommerce | Gateway de pagamento (cartão de crédito + Pix) |
| Correios Automático (Infixs) | Cálculo de frete via Correios (PAC/SEDEX) |
| Cart Abandonment Recovery for WooCommerce | E-mails automáticos de recuperação de carrinho abandonado |
| WP Mail SMTP | Roteia e-mails do site para o Mailpit em desenvolvimento |
| WPS Hide Login + Limit Login Attempts Reloaded | Segurança do login administrativo |

## Observações importantes

- **PagBank** está configurado em modo *sandbox* (parcelamento e Pix
  configurados). Para processar pagamentos reais, é necessário conectar uma
  conta PagBank válida com chaves de API reais.
- O cálculo de frete via **Correios Automático** exige credenciais de
  contrato reais dos Correios para funcionar em produção.
- **Mailpit** existe apenas para visualizar e-mails durante o
  desenvolvimento local — nenhum e-mail é realmente entregue. Em produção,
  configure um SMTP real (Gmail, SendGrid etc.) no plugin WP Mail SMTP.
- As imagens de produto e banners usam fotos obtidas de bancos de imagem
  livres/CC (Wikimedia Commons); para uso comercial real, considere
  substituir por fotografia própria ou licenciada.
