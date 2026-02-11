# 🚀 Guia Rápido de Início

## Passo 1: Iniciar a Aplicação

```bash
./setup.sh
```

Aguarde a mensagem:
```
✅ Setup concluído com sucesso!
📍 API disponível em: http://localhost:8080
```

## Passo 2: Verificar Health da API

```bash
curl http://localhost:8080/health
```

Resposta esperada (todos os serviços operacionais):
```json
{
  "status": "healthy",
  "timestamp": "2026-02-11 01:35:00",
  "version": "1.0.0",
  "services": {
    "database": {"status": "up", "type": "MySQL"},
    "redis": {"status": "up", "type": "Redis"}
  }
}
```

## Passo 3: Testar a API

### Opção A: Usando Postman (Recomendado)

1. Abra o Postman
2. Importe os arquivos da pasta `postman/`:
   - `Propostas-API.postman_collection.json`
   - `Propostas-API.postman_environment.json`
3. Selecione o environment "Propostas API - Local"
4. Execute as requisições na ordem:

**Fluxo Básico:**
1. `Clientes` → `Criar Cliente`
2. `Propostas` → `Criar Proposta`
3. `Ações de Proposta` → `Enviar Proposta para Revisão`
4. `Ações de Proposta` → `Aprovar Proposta`
5. `Auditoria` → `Buscar Auditoria da Proposta`

### Opção B: Usando cURL

```bash
# 1. Criar cliente
curl -X POST http://localhost:8080/api/v1/clientes \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "João Silva",
    "email": "joao.silva@example.com",
    "documento": "12345678901"
  }'

# 2. Criar proposta
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: unique-key-123" \
  -d '{
    "cliente_id": 1,
    "produto": "Plano Premium",
    "valor_mensal": 199.90,
    "origem": "API"
  }'

# 3. Listar propostas
curl http://localhost:8080/api/v1/propostas?page=1&per_page=10

# 4. Enviar para revisão
curl -X POST http://localhost:8080/api/v1/propostas/1/submit \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: unique-key-456" \
  -d '{"versao": 0}'

# 5. Aprovar proposta
curl -X POST http://localhost:8080/api/v1/propostas/1/approve

# 6. Ver auditoria
curl http://localhost:8080/api/v1/propostas/1/auditoria
```

## Passo 4: Explorar Recursos

### Testar Idempotência

Execute a mesma requisição 2x com o mesmo `Idempotency-Key`:

```bash
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: test-123" \
  -d '{
    "cliente_id": 1,
    "produto": "Teste Idempotência",
    "valor_mensal": 99.90,
    "origem": "API"
  }'
```

Resultado: Mesma proposta retornada, sem duplicação! ✅

### Testar Optimistic Locking

```bash
# 1. Buscar proposta (versão 0)
curl http://localhost:8080/api/v1/propostas/1

# 2. Tentar atualizar com versão antiga
curl -X PUT http://localhost:8080/api/v1/propostas/1 \
  -H "Content-Type: application/json" \
  -d '{
    "produto": "Tentando com versão antiga",
    "versao": 0
  }'

# 3. Fazer outra atualização (vai falhar com erro 409)
```

Resultado: Erro 409 Conflict - Proposta foi modificada! ✅

### Testar Fluxo de Status

Status válidos:
- `DRAFT` → `SUBMITTED` ✅
- `SUBMITTED` → `APPROVED` ✅
- `SUBMITTED` → `REJECTED` ✅
- `DRAFT` → `APPROVED` ❌ (inválido)

```bash
# Criar proposta (DRAFT)
curl -X POST http://localhost:8080/api/v1/propostas \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: flow-test" \
  -d '{
    "cliente_id": 1,
    "produto": "Teste Fluxo",
    "valor_mensal": 150.00,
    "origem": "WEB"
  }'

# Tentar aprovar direto (vai falhar)
curl -X POST http://localhost:8080/api/v1/propostas/2/approve

# Enviar para revisão primeiro
curl -X POST http://localhost:8080/api/v1/propostas/2/submit \
  -H "Content-Type: application/json" \
  -H "Idempotency-Key: flow-test-submit" \
  -d '{"versao": 0}'

# Agora aprovar (vai funcionar)
curl -X POST http://localhost:8080/api/v1/propostas/2/approve
```

## Passo 5: Verificar Banco de Dados

```bash
# Acessar PostgreSQL
docker-compose exec postgres psql -U propostas_user -d propostas_db

# Queries úteis
\dt                           # Listar tabelas
SELECT * FROM clientes;        # Ver clientes
SELECT * FROM propostas;       # Ver propostas
SELECT * FROM auditoria_proposta; # Ver auditoria
\q                            # Sair
```

## Passo 6: Ver Logs

```bash
# Logs da aplicação
docker-compose logs -f app

# Logs do PostgreSQL
docker-compose logs -f postgres

# Logs do Redis
docker-compose logs -f redis
```

## Comandos Úteis

```bash
# Parar tudo
docker-compose down

# Reiniciar
docker-compose restart

# Rebuild (após mudanças no código)
docker-compose up -d --build

# Executar nova migration
docker-compose exec app php spark migrate

# Repovoar banco
docker-compose exec app php spark db:seed DatabaseSeeder

# Acessar container
docker-compose exec app bash
```

## Troubleshooting

### Erro: "Port already in use"
```bash
# Parar todos os containers
docker-compose down

# Verificar portas em uso
lsof -i :8080
lsof -i :5432
lsof -i :6379
```

### Erro: "Database connection failed"
```bash
# Reiniciar PostgreSQL
docker-compose restart postgres

# Ver logs
docker-compose logs postgres
```

### Limpar tudo e começar do zero
```bash
docker-compose down -v
./setup.sh
```

## 📚 Próximos Passos

- Leia o [README.md](README.md) completo para entender a arquitetura
- Explore a [Coleção do Postman](postman/) com todos os cenários de teste
- Verifique o código-fonte em `app/` para entender a implementação
- Consulte o [TESTE_TECNICO.md](../TESTE_TECNICO.md) para requisitos completos

## 🎉 Pronto!

Sua API está rodando e pronta para testes. Divirta-se explorando! 🚀
