#!/bin/sh
set -e

echo "🚀 Iniciando API de Propostas..."

# Wait for MySQL to be ready
echo "⏳ Aguardando MySQL ficar pronto..."
until php -r "new PDO('mysql:host=mysql;dbname=propostas_db', 'propostas_user', 'propostas_pass');" 2>/dev/null; do
  echo "MySQL não está pronto ainda - aguardando..."
  sleep 2
done

echo "✅ MySQL está pronto!"

# Run migrations
echo "📊 Executando migrations..."
php spark migrate --all

# Run seeders
echo "🌱 Populando banco de dados..."
php spark db:seed DatabaseSeeder

echo "✅ Setup concluído!"
echo "📍 API disponível em: http://localhost:8080"

# Start PHP built-in server
echo "🚀 Iniciando servidor PHP..."
php spark serve --host=0.0.0.0 --port=8080
