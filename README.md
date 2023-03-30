1. `cp .env.example .env`
2. `docker-compose up --build -d`
3. `docker exec -it spa_comments_laravel.test_1 bash`
4. `php artisan key:generate && php artisan migrate && php artisan db:seed --class=UserSeeder && php artisan db:seed --class=CommentsSeeder && chmod -R 777 /var/www/html/storage/ && chmod -R 777 /var/www/html/public && npm run build`
5. Go to 0.0.0.0