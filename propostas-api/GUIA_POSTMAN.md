# 🧪 Guia Completo de Testes - Postman

## 📌 Pré-requisitos

1. Postman instalado
2. Docker containers rodando:
```bash
cd /Users/dougladmo/Documents/desafio-datasystem/propostas-api
docker-compose up -d
```

3. Base URL configurada: `http://localhost:8080`

---

## 🔄 ORDEM DOS TESTES

### ✅ PASSO 1: Health Check

**Verificar se a API está funcionando**

```
Método: GET
URL: http://localhost:8080/health
```

**Resposta Esperada:**
```json
{
    "status": "healthy",
    "timestamp": "2026-02-11 10:42:46",
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

✅ **Status esperado:** 200 OK

---

### 👤 PASSO 2: Criar Cliente

**Antes de criar propostas, precisamos ter um cliente**

```
Método: POST
URL: http://localhost:8080/api/v1/clientes
Headers:
  Content-Type: application/json

Body (raw JSON):
{
  "nome": "João Silva",
  "email": "joao.silva@example.com",
  "documento": "79365500036"
}
```

**Resposta Esperada:**
```json
{
    "message": "Cliente criado com sucesso",
    "data": {
        "id": "1",
        "nome": "João Silva",
        "email": "joao.silva@example.com",
        "documento": "79365500036",
        "created_at": "2026-02-11 10:42:46",
        "updated_at": "2026-02-11 10:42:46"
    }
}
```

✅ **Status esperado:** 201 Created
📝 **Anote o ID:** Você vai precisar desse ID para criar propostas!

---

### 👤 PASSO 3: Buscar Cliente

**Verificar se o cliente foi criado corretamente**

```
Método: GET
URL: http://localhost:8080/api/v1/clientes/1
```

**Resposta Esperada:**
```json
{
    "message": "Success",
    "data": {
        "id": "1",
        "nome": "João Silva",
        "email": "joao.silva@example.com",
        "documento": "79365500036",
        "created_at": "2026-02-11 10:42:46",
        "updated_at": "2026-02-11 10:42:46"
    }
}
```

✅ **Status esperado:** 200 OK

---

### 📝 PASSO 4: Criar Proposta (Primeira)

**Agora vamos criar nossa primeira proposta**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: proposta-1-teste

Body (raw JSON):
{
  "cliente_id": 1,
  "produto": "Plano Premium",
  "valor_mensal": 199.90,
  "origem": "API"
}
```

**Resposta Esperada:**
```json
{
    "message": "Proposta criada com sucesso",
    "data": {
        "id": "1",
        "cliente_id": "1",
        "produto": "Plano Premium",
        "valor_mensal": "199.90",
        "status": "DRAFT",
        "origem": "API",
        "versao": "0",
        "created_at": "2026-02-11 10:42:46",
        "updated_at": "2026-02-11 10:42:46",
        "deleted_at": null
    }
}
```

✅ **Status esperado:** 201 Created
📝 **Anote o ID e a versão:** Você vai precisar para atualizar!

---

### 🔄 PASSO 5: Testar Idempotência

**Enviar a MESMA requisição com a MESMA Idempotency-Key**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: proposta-1-teste  ⚠️ MESMA KEY DO PASSO 4

Body (raw JSON):
{
  "cliente_id": 1,
  "produto": "Plano Premium",
  "valor_mensal": 199.90,
  "origem": "API"
}
```

**Resposta Esperada:**
- ✅ Deve retornar a MESMA proposta (mesmo ID) do Passo 4
- ✅ NÃO deve criar uma nova proposta
- ✅ Status: 200 OK (não 201)

**Se criar uma nova proposta = Idempotência NÃO está funcionando!**

---

### 📝 PASSO 6: Criar Mais Propostas

**Vamos criar mais propostas para testar listagem e filtros**

#### Proposta 2:
```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: proposta-2-teste

Body:
{
  "cliente_id": 1,
  "produto": "Plano Básico",
  "valor_mensal": 99.90,
  "origem": "WEB"
}
```

#### Proposta 3:
```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: proposta-3-teste

Body:
{
  "cliente_id": 1,
  "produto": "Plano Enterprise",
  "valor_mensal": 499.90,
  "origem": "MOBILE"
}
```

---

### 🔍 PASSO 7: Buscar Proposta Individual

**Buscar uma proposta específica**

```
Método: GET
URL: http://localhost:8080/api/v1/propostas/1
```

**Resposta Esperada:**
```json
{
    "message": "Success",
    "data": {
        "id": "1",
        "cliente_id": "1",
        "produto": "Plano Premium",
        "valor_mensal": "199.90",
        "status": "DRAFT",
        "origem": "API",
        "versao": "0",
        "created_at": "2026-02-11 10:42:46",
        "updated_at": "2026-02-11 10:42:46",
        "deleted_at": null
    }
}
```

✅ **Status esperado:** 200 OK

---

### 📋 PASSO 8: Listar Todas as Propostas

**Ver todas as propostas com paginação**

```
Método: GET
URL: http://localhost:8080/api/v1/propostas?page=1&per_page=10
```

**Resposta Esperada:**
```json
{
    "message": "Success",
    "data": {
        "data": [
            { "id": "3", "produto": "Plano Enterprise", ... },
            { "id": "2", "produto": "Plano Básico", ... },
            { "id": "1", "produto": "Plano Premium", ... }
        ],
        "pagination": {
            "total": 3,
            "page": 1,
            "per_page": 10,
            "total_pages": 1
        }
    }
}
```

✅ **Status esperado:** 200 OK

---

### 🔎 PASSO 9: Testar Filtros

#### 9.1 Filtrar por Status
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?status=DRAFT
```

#### 9.2 Filtrar por Cliente
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?cliente_id=1
```

#### 9.3 Filtrar por Faixa de Valor
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?valor_min=100&valor_max=300
```

#### 9.4 Filtrar por Origem
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?origem=API
```

#### 9.5 Ordenar por Data
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?sort=created_at:desc
```

#### 9.6 Combinar Filtros
```
Método: GET
URL: http://localhost:8080/api/v1/propostas?status=DRAFT&cliente_id=1&valor_min=100&sort=valor_mensal:desc
```

✅ **Status esperado:** 200 OK para todos

---

### ✏️ PASSO 10: Atualizar Proposta

**Modificar os dados de uma proposta (com Optimistic Locking)**

```
Método: PUT
URL: http://localhost:8080/api/v1/propostas/1
Headers:
  Content-Type: application/json

Body (raw JSON):
{
  "produto": "Plano Premium Plus",
  "valor_mensal": 299.90,
  "versao": 0
}
```

**Resposta Esperada:**
```json
{
    "message": "Proposta atualizada com sucesso",
    "data": {
        "id": "1",
        "produto": "Plano Premium Plus",
        "valor_mensal": "299.90",
        "status": "DRAFT",
        "versao": "1",  ⬅️ Versão incrementada!
        ...
    }
}
```

✅ **Status esperado:** 200 OK
📝 **Observe:** A versão mudou de 0 para 1!

---

### ⚠️ PASSO 11: Testar Conflito de Versão

**Tentar atualizar com versão antiga**

```
Método: PUT
URL: http://localhost:8080/api/v1/propostas/1
Headers:
  Content-Type: application/json

Body (raw JSON):
{
  "produto": "Tentando atualizar com versão antiga",
  "versao": 0  ⚠️ Versão antiga (atual é 1)
}
```

**Resposta Esperada:**
```json
{
    "error": "Conflito de versão. A proposta foi modificada por outro processo.",
    "code": 409
}
```

✅ **Status esperado:** 409 Conflict
🎯 **Isso prova que o Optimistic Locking está funcionando!**

---

### 🔄 PASSO 12: Fluxo de Status - Submit

**Enviar proposta para revisão (DRAFT → SUBMITTED)**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas/2/submit
Headers:
  Content-Type: application/json
  Idempotency-Key: submit-proposta-2

Body (raw JSON):
{
  "versao": 0
}
```

**Resposta Esperada:**
```json
{
    "message": "Proposta enviada para revisão",
    "data": {
        "id": "2",
        "status": "SUBMITTED",  ⬅️ Status mudou!
        "versao": "1",
        ...
    }
}
```

✅ **Status esperado:** 200 OK

---

### ✅ PASSO 13: Fluxo de Status - Approve

**Aprovar proposta (SUBMITTED → APPROVED)**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas/2/approve
```

**Resposta Esperada:**
```json
{
    "message": "Proposta aprovada",
    "data": {
        "id": "2",
        "status": "APPROVED",  ⬅️ Status mudou!
        ...
    }
}
```

✅ **Status esperado:** 200 OK

---

### ❌ PASSO 14: Fluxo de Status - Reject

**Criar uma nova proposta e rejeitar**

1. Crie uma nova proposta (Passo 4)
2. Submeta para revisão (Passo 12)
3. Depois rejeite:

```
Método: POST
URL: http://localhost:8080/api/v1/propostas/4/reject
```

**Resposta Esperada:**
```json
{
    "message": "Proposta rejeitada",
    "data": {
        "id": "4",
        "status": "REJECTED",
        ...
    }
}
```

✅ **Status esperado:** 200 OK

---

### 🚫 PASSO 15: Cancelar Proposta

**Cancelar uma proposta em DRAFT**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas/3/cancel
```

**Resposta Esperada:**
```json
{
    "message": "Proposta cancelada",
    "data": {
        "id": "3",
        "status": "CANCELLED",
        ...
    }
}
```

✅ **Status esperado:** 200 OK

---

### ⚠️ PASSO 16: Testar Transição Inválida

**Tentar transição não permitida (ex: APPROVED → REJECTED)**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas/2/reject
```

**Resposta Esperada:**
```json
{
    "error": "Transição de status inválida: APPROVED → REJECTED",
    "code": 400
}
```

✅ **Status esperado:** 400 Bad Request

---

### 📜 PASSO 17: Buscar Auditoria

**Ver histórico completo de uma proposta**

```
Método: GET
URL: http://localhost:8080/api/v1/propostas/2/auditoria
```

**Resposta Esperada:**
```json
{
    "message": "Success",
    "data": [
        {
            "id": "5",
            "proposta_id": "2",
            "actor": "system",
            "evento": "APPROVED",
            "payload": { ... },
            "created_at": "2026-02-11 10:45:00"
        },
        {
            "id": "4",
            "proposta_id": "2",
            "actor": "system",
            "evento": "SUBMITTED",
            "payload": { ... },
            "created_at": "2026-02-11 10:44:00"
        },
        {
            "id": "3",
            "proposta_id": "2",
            "actor": "system",
            "evento": "CREATED",
            "payload": { ... },
            "created_at": "2026-02-11 10:43:00"
        }
    ]
}
```

✅ **Status esperado:** 200 OK
🎯 **Você verá todos os eventos:** CREATED → SUBMITTED → APPROVED

---

## ❌ TESTES DE VALIDAÇÃO

### 🚨 PASSO 18: Dados Inválidos

**Tentar criar proposta com dados incorretos**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: proposta-invalida

Body (raw JSON):
{
  "cliente_id": "abc",
  "produto": "",
  "valor_mensal": -10,
  "origem": "INVALIDA"
}
```

**Resposta Esperada:**
```json
{
    "error": "Erro de validação",
    "code": 422,
    "details": {
        "cliente_id": "Cliente inválido",
        "produto": "Produto é obrigatório",
        "valor_mensal": "Valor mensal deve ser maior que zero",
        "origem": "Origem inválida"
    }
}
```

✅ **Status esperado:** 422 Unprocessable Entity

---

### 🔍 PASSO 19: Cliente Inexistente

**Tentar criar proposta para cliente que não existe**

```
Método: POST
URL: http://localhost:8080/api/v1/propostas
Headers:
  Content-Type: application/json
  Idempotency-Key: cliente-inexistente

Body (raw JSON):
{
  "cliente_id": 99999,
  "produto": "Plano Teste",
  "valor_mensal": 100.00,
  "origem": "API"
}
```

**Resposta Esperada:**
```json
{
    "title": "CodeIgniter\\Database\\Exceptions\\DatabaseException",
    "code": 500,
    "message": "Cannot add or update a child row: a foreign key constraint fails..."
}
```

✅ **Status esperado:** 500 Internal Server Error
🎯 **Isso mostra que a foreign key está funcionando!**

---

### 🔍 PASSO 20: Proposta Inexistente

**Buscar proposta que não existe**

```
Método: GET
URL: http://localhost:8080/api/v1/propostas/99999
```

**Resposta Esperada:**
```json
{
    "error": "Proposta não encontrada",
    "code": 404
}
```

✅ **Status esperado:** 404 Not Found

---

## 📊 CHECKLIST COMPLETO

Use este checklist para garantir que testou tudo:

- [ ] 1. Health Check (200 OK)
- [ ] 2. Criar Cliente (201 Created)
- [ ] 3. Buscar Cliente (200 OK)
- [ ] 4. Criar Proposta 1 (201 Created)
- [ ] 5. Testar Idempotência (200 OK, mesmo ID)
- [ ] 6. Criar Propostas 2 e 3 (201 Created)
- [ ] 7. Buscar Proposta Individual (200 OK)
- [ ] 8. Listar Propostas com Paginação (200 OK)
- [ ] 9. Testar Filtros (todos 200 OK)
- [ ] 10. Atualizar Proposta (200 OK, versão incrementa)
- [ ] 11. Conflito de Versão (409 Conflict)
- [ ] 12. Submit Proposta (200 OK, status SUBMITTED)
- [ ] 13. Aprovar Proposta (200 OK, status APPROVED)
- [ ] 14. Rejeitar Proposta (200 OK, status REJECTED)
- [ ] 15. Cancelar Proposta (200 OK, status CANCELLED)
- [ ] 16. Transição Inválida (400 Bad Request)
- [ ] 17. Buscar Auditoria (200 OK, eventos listados)
- [ ] 18. Dados Inválidos (422 Unprocessable)
- [ ] 19. Cliente Inexistente (500 Error)
- [ ] 20. Proposta Inexistente (404 Not Found)

---

## 🎯 DICAS IMPORTANTES

### Headers Obrigatórios

Para POST/PUT:
```
Content-Type: application/json
```

Para criar/submeter propostas:
```
Idempotency-Key: valor-unico
```

### Valores de `origem` Permitidos
- `API`
- `WEB`
- `MOBILE`

### Fluxo de Status Permitido
```
DRAFT → SUBMITTED → APPROVED
DRAFT → SUBMITTED → REJECTED
DRAFT → CANCELLED
SUBMITTED → CANCELLED
```

### Optimistic Locking
- Sempre envie o campo `versao` ao atualizar
- A versão incrementa a cada atualização
- Se a versão estiver desatualizada = 409 Conflict

---

## 📝 Documentação Swagger

Para ver todos os endpoints interativamente:

```
http://localhost:8080/api/docs
```

---

## ✅ Pronto para o Teste Técnico!

Seguindo este guia, você testa **TODAS** as funcionalidades da API:
- ✅ CRUD completo
- ✅ Validações
- ✅ Fluxos de status
- ✅ Idempotência
- ✅ Optimistic Locking
- ✅ Auditoria
- ✅ Filtros e paginação
- ✅ Tratamento de erros

**Boa sorte! 🚀**
