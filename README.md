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

**✨ Zero configuração necessária!** Tudo já está pronto para uso.

```bash
cd propostas-api
docker compose up -d
```

⏱️ **Tempo de inicialização**: ~30 segundos
🔑 **`.env` incluído**: Sem necessidade de configuração manual
📦 **Banco populado**: 30 propostas de exemplo já criadas automaticamente

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
- ✅ **Documentação Swagger/OpenAPI 3.0** com interface interativa

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

## 📖 Documentação Interativa (Swagger UI)

**🎯 Acesse a documentação completa da API:**

👉 **http://localhost:8080/api/docs**

### O que você encontra na documentação:

- 📋 **Todos os 11+ endpoints** documentados com exemplos
- 🎯 **Try it out**: Teste os endpoints diretamente no navegador
- 📝 **Schemas completos** de request/response
- 🔑 **Headers especiais**: Idempotency-Key, versioning
- ⚡ **Códigos HTTP** com descrições detalhadas
- 🔄 **Fluxo de status** das propostas documentado
- 💡 **Exemplos práticos** para cada endpoint

### Recursos da Documentação:

```
✅ OpenAPI 3.0 Specification
✅ Interface Swagger UI interativa
✅ Testes direto no navegador
✅ Download da especificação (.yaml)
✅ Filtros e busca de endpoints
✅ Exemplos de request/response
✅ Descrição de parâmetros e schemas
```

**Arquivo da especificação**: [`propostas-api/public/openapi.yaml`](propostas-api/public/openapi.yaml)

---

## 🧪 Testando a API

### Opção 1: Swagger UI (Mais Fácil) ⭐

Acesse **http://localhost:8080/api/docs** e teste direto no navegador com a interface interativa!

### Opção 2: Postman

1. Importe os arquivos em `propostas-api/postman/`
2. Selecione o environment "Propostas API - Local"
3. Execute os requests na ordem sugerida

### Opção 3: cURL

```bash
# Health check
curl http://localhost:8080/health

# Acessar documentação Swagger
open http://localhost:8080/api/docs  # macOS
# ou
start http://localhost:8080/api/docs  # Windows
# ou visite diretamente no navegador

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
- ✅ Documentação OpenAPI 3.0 completa
- ✅ Swagger UI interativo para testes

### DevOps
- ✅ Containerização completa
- ✅ Health checks configurados
- ✅ Migrations automáticas na inicialização
- ✅ Seeds para dados de teste
- ✅ Logs centralizados

## 💡 Como Usar a Documentação Swagger

1. **Acesse**: http://localhost:8080/api/docs
2. **Explore**: Navegue pelos endpoints organizados por tags
3. **Teste**: Clique em "Try it out" em qualquer endpoint
4. **Execute**: Preencha os parâmetros e clique em "Execute"
5. **Veja a resposta**: Response body, headers e código HTTP

**Dicas:**
- 🔍 Use o campo de busca para encontrar endpoints rapidamente
- 📋 Clique em "Schema" para ver a estrutura completa dos objetos
- 💾 Use "Download" para baixar a especificação OpenAPI
- 🎯 Teste fluxos completos: criar cliente → criar proposta → enviar → aprovar

## 📝 Próximos Passos

Para entender melhor o projeto, consulte:
1. **[Swagger UI](http://localhost:8080/api/docs)** ⭐ - Documentação interativa (RECOMENDADO)
2. **[propostas-api/postman/](propostas-api/postman/)** - Collection Postman com cenários de teste
3. **[propostas-api/public/openapi.yaml](propostas-api/public/openapi.yaml)** - Especificação OpenAPI 3.0

## 📌 Nota sobre `.env` Commitado

⚠️ **Importante**: O arquivo `.env` foi intencionalmente incluído no repositório **apenas para facilitar a avaliação técnica**.

**Justificativa**:
- ✅ Contém apenas credenciais de desenvolvimento local (não produção)
- ✅ Simplifica setup para avaliadores (zero configuração)
- ✅ Demonstra conhecimento sobre quando essa prática é aceitável
- ❌ **Nunca faça isso em produção** ou com credenciais reais

Em produção, o `.env` deve estar no `.gitignore` e as credenciais devem ser gerenciadas via secrets management (Vault, AWS Secrets Manager, etc.).

## 👨‍💻 Autor

Desenvolvido como projeto de avaliação técnica para DataSystem.

---

**🎯 Status**: Projeto completo e pronto para avaliação
