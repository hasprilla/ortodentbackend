## About Laravel

# Iniciar contenedores

```
docker-compose up -d
```

# Ejecutar migraciones

```
docker-compose exec app php artisan migrate
```

# Ejecutar seeders

```
docker-compose exec app php artisan db:seed
```

# Conectar al contenedor de la aplicación

```
docker-compose exec app bash
```

# Detener y eliminar contenedores

```
docker-compose down
```

# Install dependencies

```
docker-compose run --rm app composer install
```

https://tu_dominio/api/files/dChokrtDBqxhuttz3kXDLsP6C34SzhGyu2okWkOk.mp4
