# Docker Configuration for Crypto Tracker

This directory contains the Docker configuration for the Crypto Tracker Symfony application.

## Services

- **PHP-FPM**: Runs the Symfony application
- **Nginx**: Web server that routes requests to PHP-FPM
- **MongoDB**: Database service (MongoDB Atlas Local)

## Quick Start

### Prerequisites
- Docker and Docker Compose installed

### Setup

1. **Navigate to the docker directory**:
   ```bash
   cd docker
   ```

2. **Configure environment variables** (optional):
   ```bash
   cp .env.docker ../.env.local
   # Edit ../.env.local to customize settings
   ```

3. **Start the services**:
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies**:
   ```bash
   docker-compose exec php composer install
   ```

5. **Access the application**:
   - Web: http://localhost
   - MongoDB: localhost:27017

### Available Commands

**View logs**:
```bash
docker-compose logs -f
# Or specific service
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mongodb
```

**Execute commands in PHP container**:
```bash
docker-compose exec php php bin/console <command>
docker-compose exec php composer <command>
```

**Stop services**:
```bash
docker-compose down
```

**Stop and remove volumes** (WARNING: deletes database):
```bash
docker-compose down -v
```

## Environment Configuration

Edit the `.env.docker` file or pass environment variables via `.env.local`:

- `NGINX_PORT`: Nginx listening port (default: 80)
- `MONGODB_DB`: Database name (default: symfony)
- `MONGODB_USERNAME`: MongoDB admin username (default: admin)
- `MONGODB_PASSWORD`: MongoDB admin password (default: password)
- `MONGODB_PORT`: MongoDB port (default: 27017)
- `APP_ENV`: Symfony environment (default: dev)

## Volumes

- `mongodb_data`: Persists MongoDB data between container restarts

## Notes

- MongoDB uses the MongoDB Atlas Local image with built-in authentication
- Default MongoDB credentials are **NOT production-ready**. Update them in production.
- The PHP container installs dependencies from the Symfony project's `composer.json`
- Nginx configuration is loaded from `nginx/default.conf`
