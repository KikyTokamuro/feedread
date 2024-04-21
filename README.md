# FeedRead
Simple Atom/RSS reader

## Run in Docker
```sh
# Copy env file
cp .env.docker .env
# Run docker
docker-compose up -d
# Generate key
docker-compose exec app php artisan key:generate
# Migrate database
docker-compose exec app php artisan migrate
# Go to http://localhost:8000/
```
