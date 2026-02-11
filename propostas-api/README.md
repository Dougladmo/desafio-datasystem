# API REST - Gestão de Propostas

Sistema de gerenciamento de propostas comerciais desenvolvido com CodeIgniter 4, MySQL e Redis.

## 🚀 Quick Start - Apenas 1 Comando

### Pré-requisitos
- **Docker Desktop** instalado e rodando
- **Docker Compose** (já incluído no Docker Desktop)

### Passo 1: Subir a aplicação

```bash
docker compose up -d
```

**O que acontece automaticamente:**
1. ✅ Baixa as imagens (MySQL 8.0, Redis 7)
2. ✅ Constrói a imagem da aplicação PHP
3. ✅ Cria a rede Docker interna
4. ✅ Inicia MySQL e Redis
5. ✅ Aguarda o MySQL ficar pronto (healthcheck)
6. ✅ Container de inicialização roda migrations e seeders
7. ✅ Inicia o servidor PHP na porta 8080

⏱️ **Tempo total**: 15-30 segundos (primeira vez pode demorar mais para baixar imagens)

### Passo 2: Verificar se está funcionando

```bash
# Listar propostas (deve retornar 10 de 30 propostas)
curl http://localhost:8080/api/v1/propostas

# Buscar proposta específica
curl http://localhost:8080/api/v1/propostas/1

# Ver logs da aplicação (para debug)
docker compose logs -f app
```

### Passo 3: Parar o sistema

```bash
# Parar containers (mantém dados do banco)
docker compose down

# Parar e remover TODOS os dados (recomeçar do zero)
docker compose down -v
```

### 🔄 Reconstruir após mudanças

Se você modificar código ou Dockerfile:

```bash
# Reconstruir e reiniciar
docker compose up -d --build

# Ou rebuild completo sem cache
docker compose build --no-cache
docker compose up -d
```

## 📋 Endpoints Disponíveis

### Health Check
- `GET /health` - Verificar status da API e serviços (MySQL, Redis)

### Clientes
- `POST /api/v1/clientes` - Criar cliente
- `GET /api/v1/clientes/{id}` - Buscar cliente por ID

### Propostas
- `GET /api/v1/propostas` - Listar propostas (com filtros e paginação)
- `POST /api/v1/propostas` - Criar proposta (com idempotência)
- `GET /api/v1/propostas/{id}` - Buscar proposta por ID
- `PUT /api/v1/propostas/{id}` - Atualizar proposta (com optimistic locking)

### Ações de Proposta
- `POST /api/v1/propostas/{id}/submit` - Enviar para análise
- `POST /api/v1/propostas/{id}/approve` - Aprovar proposta
- `POST /api/v1/propostas/{id}/reject` - Rejeitar proposta
- `POST /api/v1/propostas/{id}/cancel` - Cancelar proposta

### Auditoria
- `GET /api/v1/propostas/{id}/auditoria` - Histórico de alterações

## 🧪 Testando com Postman (Recomendado)

### Passo 1: Importar Collection

1. Abra o **Postman**
2. Clique em **Import** (botão no canto superior esquerdo)
3. Arraste ou selecione os arquivos da pasta `postman/`:
   - 📄 `Propostas-API.postman_collection.json` - Collection com 11 endpoints
   - 🌍 `Propostas-API.postman_environment.json` - Variáveis de ambiente

### Passo 2: Configurar Environment

1. No canto superior direito, selecione o environment: **"Propostas API - Local"**
2. Verifique se a variável `base_url` está configurada como `http://localhost:8080`

### Passo 3: Testar os Endpoints

A collection está organizada em pastas. **Ordem sugerida de testes:**

#### 📁 1. Clientes
- ✅ `POST Criar Cliente` - Cria um novo cliente
- ✅ `GET Buscar Cliente por ID` - Busca cliente criado

#### 📁 2. Propostas - CRUD
- ✅ `POST Criar Proposta` - Cria proposta (observe o `Idempotency-Key`)
- ✅ `POST Criar Proposta (Idempotência)` - Mesma key retorna mesma proposta
- ✅ `GET Listar Propostas` - Lista com paginação
- ✅ `GET Buscar Proposta por ID` - Busca específica
- ✅ `PUT Atualizar Proposta` - Atualiza com optimistic locking

#### 📁 3. Propostas - Ações
- ✅ `POST Enviar para Análise (Submit)` - DRAFT → SUBMITTED
- ✅ `POST Aprovar Proposta` - SUBMITTED → APPROVED
- ✅ `POST Rejeitar Proposta` - SUBMITTED → REJECTED
- ✅ `POST Cancelar Proposta` - Qualquer status → CANCELLED

#### 📁 4. Auditoria
- ✅ `GET Histórico de Auditoria` - Ver todas alterações da proposta

#### 📁 5. Filtros e Buscas
- ✅ `GET Filtrar por Status` - Filtra propostas por status
- ✅ `GET Filtrar por Valor` - Busca por faixa de preço
- ✅ `GET Ordenação e Paginação` - Ordena e pagina resultados

### 🎯 Cenários de Teste Importantes

#### Teste 1: Idempotência
1. Execute `POST Criar Proposta` - Anote o ID retornado
2. Execute novamente `POST Criar Proposta (Idempotência)` com a **mesma** `Idempotency-Key`
3. ✅ **Resultado esperado**: Retorna a mesma proposta, não cria duplicata

#### Teste 2: Optimistic Locking
1. Execute `GET Buscar Proposta por ID` - Anote o campo `versao` (ex: 0)
2. Execute `PUT Atualizar Proposta` com `versao: 0`
3. ✅ **Resultado esperado**: Atualização bem-sucedida, versão incrementa para 1
4. Tente atualizar novamente com `versao: 0` (versão antiga)
5. ❌ **Resultado esperado**: Erro 409 Conflict

#### Teste 3: Fluxo de Status
1. Crie uma proposta (status inicial: DRAFT)
2. Execute `POST Enviar para Análise` (DRAFT → SUBMITTED)
3. Execute `POST Aprovar Proposta` (SUBMITTED → APPROVED)
4. Tente executar `POST Rejeitar Proposta`
5. ❌ **Resultado esperado**: Erro - status APPROVED é final

#### Teste 4: Auditoria
1. Execute várias ações em uma proposta (criar, atualizar, submit, aprovar)
2. Execute `GET Histórico de Auditoria`
3. ✅ **Resultado esperado**: Lista todas as ações com timestamps e payloads

### 💡 Dicas do Postman

- **Variáveis**: A collection usa `{{base_url}}` e `{{proposta_id}}` automaticamente
- **Scripts**: Alguns requests salvam IDs automaticamente para uso nos próximos
- **Idempotency-Key**: É gerado automaticamente com `{{$guid}}`
- **Status**: Observe o código HTTP de resposta (200, 201, 400, 409, etc.)

## 🎯 Funcionalidades Implementadas

### Regras de Negócio
- ✅ **Idempotência**: Suporte a `Idempotency-Key` header com cache Redis (24h)
- ✅ **Optimistic Locking**: Controle de concorrência com campo `versao`
- ✅ **Fluxo de Status**: Transições validadas (DRAFT → SUBMITTED → APPROVED/REJECTED)
- ✅ **Auditoria Automática**: Todas as alterações registradas via Model Events
- ✅ **Soft Delete**: Deleção lógica de propostas
- ✅ **Validação de CPF/CNPJ**: Validação algorítmica de documentos

### Tecnologias
- **Backend**: PHP 8.2 + CodeIgniter 4.7
- **Banco de Dados**: MySQL 8.0
- **Cache**: Redis 7
- **Containerização**: Docker + Docker Compose

## 📊 Estrutura do Banco de Dados

### Tabela: clientes
- `id` - Identificador único
- `nome` - Nome do cliente
- `email` - Email único
- `documento` - CPF ou CNPJ (validado)
- `created_at`, `updated_at` - Timestamps

### Tabela: propostas
- `id` - Identificador único
- `cliente_id` - FK para clientes
- `produto` - Nome do produto/serviço
- `valor_mensal` - Valor da proposta
- `status` - DRAFT | SUBMITTED | APPROVED | REJECTED | CANCELLED
- `origem` - WEB | MOBILE | API
- `versao` - Versão para optimistic locking
- `created_at`, `updated_at`, `deleted_at` - Timestamps

### Tabela: auditoria_proposta
- `id` - Identificador único
- `proposta_id` - FK para propostas
- `actor` - Quem fez a alteração
- `evento` - Tipo de evento (CREATED, UPDATED, DELETED, etc.)
- `payload` - Dados da alteração (JSON)
- `created_at` - Timestamp

## 🔧 Comandos Úteis

### Ver logs da aplicação
```bash
docker compose logs -f app
```

### Ver logs do MySQL
```bash
docker compose logs -f mysql
```

### Acessar o container da aplicação
```bash
docker compose exec app sh
```

### Executar comandos CodeIgniter
```bash
# Ver status das migrations
docker compose exec app php spark migrate:status

# Criar nova migration
docker compose exec app php spark make:migration NomeDaMigration

# Rollback de migrations
docker compose exec app php spark migrate:rollback
```

### Limpar tudo e recomeçar
```bash
docker compose down -v  # Remove volumes (apaga dados do banco)
docker compose up -d    # Recria tudo do zero
```

## 📝 Exemplos de Uso

### Verificar Health Check
```bash
curl http://localhost:8080/health
```

Resposta esperada:
```json
{
  "status": "healthy",
  "timestamp": "2026-02-11 01:35:00",
  "version": "1.0.0",
  "services": {
    "database": {
      "status": "up",
      "type": "MySQL"
    },
    "redis": {
      "status": "up",
      "type": "Redis"
    }
  }
}
```

### Criar Cliente
```bash
curl -X POST http://localhost:8080/api/v1/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao@example.com",
    "documento": "12345678901"
  }'
```

### Criar Proposta com Idempotência
```bash
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: minha-chave-unica-123" \
  -d '{
    "cliente_id": 1,
    "produto": "Plano Premium",
    "valor_mensal": 499.90,
    "origem": "API"
  }'
```

### Atualizar Proposta com Optimistic Locking
```bash
curl -X PUT http://localhost:8080/api/v1/propostas/1 \
  -H "Content-Type: application/json" \
  -d '{
    "produto": "Plano Premium Plus",
    "valor_mensal": 599.90,
    "versao": 0
  }'
```

### Enviar Proposta para Análise
```bash
curl -X POST http://localhost:8080/api/v1/propostas/1/submit \
  -H "Content-Type: application/json" \
  -d '{"versao": 0}'
```

### Listar Propostas com Filtros
```bash
# Filtrar por status
curl "http://localhost:8080/api/v1/propostas?status=SUBMITTED"

# Filtrar por faixa de valor
curl "http://localhost:8080/api/v1/propostas?valor_min=1000&valor_max=5000"

# Ordenar e paginar
curl "http://localhost:8080/api/v1/propostas?sort=valor_mensal&order=desc&page=1&per_page=20"
```

### Buscar Histórico de Auditoria
```bash
curl http://localhost:8080/api/v1/propostas/1/auditoria
```

## 🐛 Troubleshooting

### Porta 8080 já está em uso
```bash
# Verificar processos usando a porta
lsof -i :8080

# Matar processo (substitua PID pelo número do processo)
kill <PID>

# Ou altere a porta no docker-compose.yml:
ports:
  - "8081:8080"  # Usar porta 8081 no host
```

### MySQL não está pronto
Se você vir erros de conexão com o banco, aguarde alguns segundos adicionais. O entrypoint já tem verificação de healthcheck, mas em máquinas mais lentas pode demorar um pouco mais.

### Limpar cache do Redis
```bash
docker compose exec redis redis-cli FLUSHALL
```

## 🏗️ Arquitetura

```
┌─────────────────────────────────────────────┐
│           Docker Compose Network            │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │  MySQL   │  │  Redis   │  │   API    │ │
│  │  :3306   │  │  :6379   │  │  :8080   │ │
│  └──────────┘  └──────────┘  └──────────┘ │
└─────────────────────────────────────────────┘
```

### Fluxo de Request
1. Request HTTP → PHP Server
2. Idempotency Filter → Verifica Redis
3. Routes → Controller
4. Controller → Model (Business Logic)
5. Model Events → Auditoria
6. Response → JSON

## 📄 Estrutura de Diretórios

```
propostas-api/
├── app/
│   ├── Config/
│   │   ├── Database.php          # Configuração do banco
│   │   ├── Routes.php            # Definição de rotas
│   │   └── Validation.php        # Regras de validação
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── ClienteController.php
│   │       └── PropostaController.php
│   ├── Database/
│   │   ├── Migrations/           # Migrations do banco
│   │   └── Seeds/                # Seeders de dados
│   ├── Filters/
│   │   └── IdempotencyFilter.php # Filtro de idempotência
│   ├── Models/
│   │   ├── AuditoriaPropostaModel.php
│   │   ├── ClienteModel.php
│   │   └── PropostaModel.php
│   └── Validation/
│       └── DocumentoRules.php    # Validação CPF/CNPJ
├── postman/                      # Collections Postman
├── docker-compose.yml            # Orquestração Docker
├── Dockerfile                    # Imagem da aplicação
├── docker-entrypoint.sh          # Script de inicialização
└── README.md                     # Este arquivo
```

## 👨‍💻 Autor

Desenvolvido como parte do teste técnico de Gestão de Propostas.

---

**📌 Nota**: Este projeto foi desenvolvido para fins de avaliação técnica e demonstração de habilidades em desenvolvimento backend.
