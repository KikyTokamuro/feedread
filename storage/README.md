# FeedRead
Simple Atom/RSS reader

## Run in Docker
```sh
# Copy env file
cp .env.example .env
# Run docker
docker-compose -d up
# Migrate database
docker-compose exec app php artisan migrate
```
