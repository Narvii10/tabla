# Tabla

Sistema de gestión para Charcutería [nombre]. Laravel + MySQL en Docker.

## Levantar el proyecto

```
git clone https://github.com/TU_USUARIO/tabla.git
cd tabla
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Abrir http://localhost:8003
