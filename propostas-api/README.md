# API REST - Gestão de Propostas Comerciais

Sistema de gestão de propostas comerciais com suporte a idempotência, versionamento otimista e auditoria completa.

## 📋 Requisitos

### Com Docker (Recomendado)
- **Docker**: 20.10 ou superior
- **Docker Compose**: 2.0 ou superior

### Sem Docker
- **PHP**: 8.2 ou superior
- **PostgreSQL**: 15 ou superior
- **Redis**: 7 ou superior
- **Composer**: Para gerenciamento de dependências
- **Extensões PHP**: pdo_pgsql, redis

## 🚀 Instalação

### Opção 1: Com Docker (Recomendado)

```bash
# 1. Executar script de setup
./setup.sh
```

Pronto! A API estará disponível em `http://localhost:8080` com banco de dados já populado.

**Comandos úteis:**
```bash
# Ver logs
docker-compose logs -f app

# Acessar container
docker-compose exec app bash

# Parar containers
docker-compose down

# Reiniciar tudo
docker-compose restart
```

### Opção 2: Instalação Manual (Sem Docker)

#### 1. Clonar/Acessar o projeto

```bash
cd propostas-api
```

#### 2. Instalar dependências

```bash
composer install
```

#### 3. Configurar banco PostgreSQL

Crie o banco de dados:
```sql
CREATE DATABASE propostas_db;
CREATE USER propostas_user WITH PASSWORD 'propostas_pass';
GRANT ALL PRIVILEGES ON DATABASE propostas_db TO propostas_user;
```

#### 4. Configurar ambiente

Copie o arquivo `.env.example` para `.env` e ajuste as configurações:

```bash
cp .env.example .env
```

Edite `.env` e configure:
```env
database.default.hostname = localhost
database.default.database = propostas_db
database.default.username = propostas_user
database.default.password = propostas_pass
```

#### 5. Executar migrations

```bash
php spark migrate --all
```

#### 6. Executar seeders

```bash
php spark db:seed DatabaseSeeder
```

#### 7. Iniciar servidor

```bash
php spark serve
```

A API estará disponível em `http://localhost:8080`

## 📬 Testando com Postman

Importe os arquivos da pasta `postman/`:
- `Propostas-API.postman_collection.json` - Coleção completa com todos os endpoints
- `Propostas-API.postman_environment.json` - Variáveis de ambiente

A coleção inclui:
- ✅ Todos os 11 endpoints da API
- ✅ Exemplos de requisições
- ✅ Testes de idempotência
- ✅ Testes de optimistic locking
- ✅ Testes de fluxo de status

## 📚 Endpoints Disponíveis

### Clientes

#### Criar Cliente
```http
POST /api/v1/clientes
Content-Type: application/json

{
  "nome": "João Silva",
  "email": "joao@example.com",
  "documento": "12345678901"
}
```

#### Buscar Cliente
```http
GET /api/v1/clientes/{id}
```

### Propostas

#### Criar Proposta
```http
POST /api/v1/propostas
Content-Type: application/json
Idempotency-Key: unique-key-123

{
  "cliente_id": 1,
  "produto": "Plano Premium",
  "valor_mensal": 199.90,
  "origem": "API"
}
```

#### Atualizar Proposta (com Optimistic Locking)
```http
PATCH /api/v1/propostas/{id}
Content-Type: application/json

{
  "produto": "Plano Enterprise",
  "valor_mensal": 299.90,
  "versao": 0
}
```

#### Buscar Proposta
```http
GET /api/v1/propostas/{id}
```

#### Listar Propostas (com filtros e paginação)
```http
GET /api/v1/propostas?status=SUBMITTED&valor_min=100&valor_max=500&page=1&per_page=10&sort=created_at:desc
```

Filtros disponíveis:
- `status`: DRAFT, SUBMITTED, APPROVED, REJECTED, CANCELLED
- `valor_min`: Valor mínimo
- `valor_max`: Valor máximo
- `cliente_id`: ID do cliente
- `origem`: WEB, MOBILE, API
- `page`: Página atual (padrão: 1)
- `per_page`: Itens por página (padrão: 10, máx: 100)
- `sort`: Campo:direção (ex: created_at:desc)

#### Enviar Proposta para Revisão
```http
POST /api/v1/propostas/{id}/submit
Content-Type: application/json
Idempotency-Key: unique-key-456

{
  "versao": 0
}
```

#### Aprovar Proposta
```http
POST /api/v1/propostas/{id}/approve
```

#### Rejeitar Proposta
```http
POST /api/v1/propostas/{id}/reject
```

#### Cancelar Proposta
```http
POST /api/v1/propostas/{id}/cancel
```

#### Buscar Auditoria da Proposta
```http
GET /api/v1/propostas/{id}/auditoria
```

## 🔐 Recursos Implementados

### 1. Idempotência
Endpoints de criação (`POST`) suportam o header `Idempotency-Key`. Requisições com a mesma chave retornam o mesmo resultado sem duplicação.

**Cache**: Redis (configurável) com TTL de 24 horas

### 2. Optimistic Locking
Atualizações de propostas requerem o campo `versao` para prevenir conflitos de concorrência.

**Fluxo**:
1. Cliente lê proposta (versao: 0)
2. Cliente envia atualização com versao: 0
3. Se outra requisição modificou antes, retorna erro 409 Conflict

### 3. Fluxo de Status
Transições válidas:
- `DRAFT` → `SUBMITTED`, `CANCELLED`
- `SUBMITTED` → `APPROVED`, `REJECTED`, `CANCELLED`
- `APPROVED` → (final)
- `REJECTED` → (final)
- `CANCELLED` → (final)

### 4. Auditoria Automática
Todas as operações são registradas automaticamente via Model Events:
- `CREATED`: Proposta criada
- `UPDATED`: Proposta atualizada
- `SUBMITTED`: Enviada para revisão
- `APPROVED`: Aprovada
- `REJECTED`: Rejeitada
- `CANCELLED`: Cancelada
- `DELETED_LOGICAL`: Soft delete

### 5. Validações
- **CPF/CNPJ**: Validação algorítmica completa
- **Email**: Validação de formato e unicidade
- **Valor Mensal**: Deve ser maior que zero
- **Status**: Apenas valores permitidos
- **Origem**: WEB, MOBILE ou API

## 🗄️ Estrutura do Banco de Dados

### Tabela: clientes
- `id`: INT (PK)
- `nome`: VARCHAR(255)
- `email`: VARCHAR(255) UNIQUE
- `documento`: VARCHAR(14) (CPF/CNPJ)
- `created_at`, `updated_at`: DATETIME

### Tabela: propostas
- `id`: INT (PK)
- `cliente_id`: INT (FK → clientes.id)
- `produto`: VARCHAR(255)
- `valor_mensal`: DECIMAL(10,2)
- `status`: ENUM
- `origem`: ENUM
- `versao`: INT (optimistic locking)
- `created_at`, `updated_at`, `deleted_at`: DATETIME

### Tabela: auditoria_proposta
- `id`: INT (PK)
- `proposta_id`: INT (FK → propostas.id)
- `actor`: VARCHAR(255)
- `evento`: ENUM
- `payload`: JSON
- `created_at`: DATETIME

## 🧪 Testes

### Executar todos os testes
```bash
./vendor/bin/phpunit
```

### Testes implementados
- ✅ **StatusFlowTest**: Validação de transições de status
- ✅ **IdempotencyTest**: Verificação de idempotência
- ✅ **OptimisticLockTest**: Controle de concorrência
- ✅ **PropostaSearchTest**: Filtros e paginação

## 🏗️ Arquitetura

### Princípios Aplicados
- **KISS**: Uso de recursos nativos do CodeIgniter 4
- **DRY**: Reutilização via Model Events
- **YAGNI**: Apenas o solicitado, sem features extras
- **Separation of Concerns**: Controllers slim, Models com lógica

### Componentes
```
Request → Routes → Filter (Idempotency) → Controller → Model → Database
                                             ↓
                                        Validation
                                             ↓
                                       Model Events → Auditoria
```

### Decisões Técnicas

**Por que SQLite?**
- Simplicidade de setup (sem servidor de banco)
- Ideal para desenvolvimento e testes
- Suporte completo a Foreign Keys e transações
- Fácil migração para MySQL/PostgreSQL se necessário

**Por que Model Events?**
- Auditoria automática e consistente
- Reduz código duplicado nos controllers
- Centraliza regras de negócio

**Por que Optimistic Locking?**
- Melhor performance que Pessimistic Lock
- Adequado para APIs REST stateless
- Simples de implementar e testar

## 🔧 Troubleshooting

### Erro de permissão no SQLite
```bash
chmod 666 writable/database/propostas.db
chmod 777 writable/database
```

### Cache não funciona
Verifique se Redis está rodando:
```bash
redis-cli ping
```

Ou configure cache para arquivo em `.env`:
```env
cache.handler = file
```

### Migrations falhando
Limpe o banco e execute novamente:
```bash
rm writable/database/propostas.db
php spark migrate --all
```

## 📝 Exemplos de Uso com cURL

### Criar cliente e proposta
```bash
# 1. Criar cliente
curl -X POST http://localhost:8080/api/v1/clientes \
  -H "Content-Type: application/json" \
  -d '{"nome":"João Silva","email":"joao@test.com","documento":"12345678901"}'

# 2. Criar proposta
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: abc123" \
  -d '{"cliente_id":1,"produto":"Plano Premium","valor_mensal":199.90,"origem":"API"}'

# 3. Enviar para revisão
curl -X POST http://localhost:8080/api/v1/propostas/1/submit \
  -H "Content-Type: application/json" \
  -d '{"versao":0}'

# 4. Aprovar
curl -X POST http://localhost:8080/api/v1/propostas/1/approve

# 5. Ver auditoria
curl http://localhost:8080/api/v1/propostas/1/auditoria
```

## 📊 Status do Projeto

- ✅ Setup inicial
- ✅ Migrations (3 tabelas)
- ✅ Models (Cliente, Proposta, Auditoria)
- ✅ Validação CPF/CNPJ
- ✅ Controllers (Base, Cliente, Proposta)
- ✅ Idempotency Filter
- ✅ Routes configuradas
- ✅ Seeders criados
- ✅ Docker Compose configurado
- ✅ PostgreSQL + Redis
- ✅ Coleção Postman completa
- ⏳ Testes (a implementar)
- ✅ Documentação

## 👨‍💻 Desenvolvimento

Estrutura de diretórios:
```
app/
├── Config/
│   ├── Database.php
│   ├── Filters.php
│   ├── Routes.php
│   └── Validation.php
├── Controllers/
│   └── Api/
│       ├── BaseController.php
│       └── V1/
│           ├── ClienteController.php
│           └── PropostaController.php
├── Database/
│   ├── Migrations/
│   │   ├── CreateClientesTable.php
│   │   ├── CreatePropostasTable.php
│   │   └── CreateAuditoriaPropostaTable.php
│   └── Seeds/
│       ├── DatabaseSeeder.php
│       ├── ClienteSeeder.php
│       └── PropostaSeeder.php
├── Filters/
│   └── IdempotencyFilter.php
├── Models/
│   ├── ClienteModel.php
│   ├── PropostaModel.php
│   └── AuditoriaPropostaModel.php
└── Validation/
    └── DocumentoRules.php
```

## 📄 Licença

Este projeto foi desenvolvido como parte de um teste técnico.
