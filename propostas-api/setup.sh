#!/bin/bash

echo "🚀 Iniciando setup da API de Propostas..."

# Stop any running containers
echo "⏹️  Parando containers existentes..."
docker-compose down

# Build and start containers
echo "🔨 Construindo e iniciando containers..."
docker-compose up -d --build

# Wait for PostgreSQL to be ready
echo "⏳ Aguardando PostgreSQL ficar pronto..."
sleep 5

# Run migrations
echo "📊 Executando migrations..."
docker-compose exec app php spark migrate --all

# Run seeders
echo "🌱 Populando banco de dados..."
docker-compose exec app php spark db:seed DatabaseSeeder

echo ""
echo "✅ Setup concluído com sucesso!"
echo ""
echo "📍 API disponível em: http://localhost:8080"
echo "🐘 PostgreSQL disponível em: localhost:5432"
echo "📦 Redis disponível em: localhost:6379"
echo ""
echo "📝 Comandos úteis:"
echo "   docker-compose logs -f app      # Ver logs da aplicação"
echo "   docker-compose exec app bash    # Acessar container"
echo "   docker-compose down             # Parar todos os containers"
echo ""
