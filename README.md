# Desafio DataSystem - Sistema de Gestão de Propostas

> **📋 Projeto desenvolvido para avaliação técnica**

## 🎯 Sobre o Projeto

API REST completa para gerenciamento de propostas comerciais, demonstrando habilidades em:
- ✅ Desenvolvimento backend com PHP 8.2 e CodeIgniter 4
- ✅ Arquitetura limpa e padrões de projeto
- ✅ Containerização com Docker e Docker Compose
- ✅ Banco de dados MySQL com migrations e seeders
- ✅ Cache distribuído com Redis
- ✅ Testes automatizados
- ✅ Documentação técnica completa

## 🚀 Quick Start

```bash
cd propostas-api
docker compose up -d
```

⏱️ **Tempo de inicialização**: ~30 segundos

A API estará disponível em: **http://localhost:8080**

## 📚 Documentação Completa

Toda a documentação técnica está em **[propostas-api/](propostas-api/)**:

- **[README.md](propostas-api/README.md)** - Documentação completa da API
- **[QUICKSTART.md](propostas-api/QUICKSTART.md)** - Guia rápido de inicialização
- **[postman/](propostas-api/postman/)** - Collection Postman com todos os endpoints

## 🏗️ Estrutura do Projeto

```
desafio-datasystem/
├── propostas-api/           # API principal (CodeIgniter 4)
│   ├── app/                 # Código-fonte da aplicação
│   ├── postman/             # Collections Postman para testes
│   ├── tests/               # Testes automatizados
│   ├── docker-compose.yml   # Orquestração dos serviços
│   ├── Dockerfile           # Imagem da aplicação
│   ├── README.md            # Documentação completa
│   └── QUICKSTART.md        # Guia rápido
└── README.md                # Este arquivo
```

## 🎯 Funcionalidades Implementadas

### Core Features
- ✅ **CRUD completo** de clientes e propostas
- ✅ **Idempotência** com Redis (Idempotency-Key header)
- ✅ **Optimistic Locking** para controle de concorrência
- ✅ **Fluxo de status** com validação de transições
- ✅ **Auditoria automática** de todas as operações
- ✅ **Soft delete** para propostas
- ✅ **Validação de CPF/CNPJ**

### Tecnologias
- **Backend**: PHP 8.2 + CodeIgniter 4.7
- **Database**: MySQL 8.0
- **Cache**: Redis 7
- **Containerization**: Docker + Docker Compose
- **Testing**: PHPUnit

### Endpoints Principais
```
GET  /health                          # Health check
POST /api/v1/clientes                 # Criar cliente
GET  /api/v1/clientes/{id}            # Buscar cliente
POST /api/v1/propostas                # Criar proposta
GET  /api/v1/propostas                # Listar propostas
GET  /api/v1/propostas/{id}           # Buscar proposta
PUT  /api/v1/propostas/{id}           # Atualizar proposta
POST /api/v1/propostas/{id}/submit    # Enviar para análise
POST /api/v1/propostas/{id}/approve   # Aprovar proposta
POST /api/v1/propostas/{id}/reject    # Rejeitar proposta
GET  /api/v1/propostas/{id}/auditoria # Histórico de alterações
```

## 🧪 Testando a API

### Opção 1: Postman (Recomendado)

1. Importe os arquivos em `propostas-api/postman/`
2. Selecione o environment "Propostas API - Local"
3. Execute os requests na ordem sugerida

### Opção 2: cURL

```bash
# Health check
curl http://localhost:8080/health

# Criar cliente
curl -X POST http://localhost:8080/api/v1/clientes \
  -H "Content-Type: application/json" \
  -d '{"nome":"João Silva","email":"joao@example.com","documento":"12345678901"}'

# Criar proposta
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: unique-key-123" \
  -d '{"cliente_id":1,"produto":"Plano Premium","valor_mensal":199.90,"origem":"API"}'
```

## 🔧 Comandos Úteis

```bash
# Ver logs
docker compose logs -f app

# Rodar migrations
docker compose exec app php spark migrate

# Executar testes
docker compose exec app composer test

# Acessar container
docker compose exec app sh

# Parar todos os serviços
docker compose down

# Limpar tudo e recomeçar
docker compose down -v && docker compose up -d
```

## 📊 Database Schema

### Tabelas
- **clientes**: Dados dos clientes (nome, email, documento)
- **propostas**: Propostas comerciais (produto, valor, status, versão)
- **auditoria_proposta**: Histórico de todas as alterações

### Status Flow
```
DRAFT → SUBMITTED → APPROVED
               ↓
           REJECTED

Qualquer status → CANCELLED
```

## 🛡️ Aspectos Técnicos Destacados

### Segurança
- ✅ Validação de entrada em todos os endpoints
- ✅ Proteção contra SQL injection (prepared statements)
- ✅ Validação algorítmica de CPF/CNPJ
- ✅ Permissões seguras no Docker (775 em vez de 777)

### Performance
- ✅ Cache Redis para idempotência
- ✅ Optimistic locking para alta concorrência
- ✅ Índices otimizados no banco de dados
- ✅ Docker multi-stage build otimizado

### Qualidade de Código
- ✅ PSR-4 autoloading
- ✅ Model events para auditoria automática
- ✅ Separação de responsabilidades (MVC)
- ✅ Tratamento consistente de erros
- ✅ Testes automatizados

### DevOps
- ✅ Containerização completa
- ✅ Health checks configurados
- ✅ Migrations automáticas na inicialização
- ✅ Seeds para dados de teste
- ✅ Logs centralizados

## 📝 Próximos Passos

Para entender melhor o projeto, consulte:
1. **[propostas-api/README.md](propostas-api/README.md)** - Documentação técnica completa
2. **[propostas-api/QUICKSTART.md](propostas-api/QUICKSTART.md)** - Tutorial passo a passo
3. **[propostas-api/postman/](propostas-api/postman/)** - Collection com cenários de teste

## 👨‍💻 Autor

Desenvolvido como projeto de avaliação técnica para DataSystem.

---

**🎯 Status**: Projeto completo e pronto para avaliação
