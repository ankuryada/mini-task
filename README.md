# 1. Clone the repository
git clone https://github.com/your-username/task-manager.git
cd task-manager

# 2. Install PHP dependencies
composer install

# 3. Copy .env file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure your DB credentials in `.env`
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 6. Run migrations
php artisan migrate

# 7. Install frontend dependencies
npm install
npm run dev

# 8. Start the development server
php artisan serve
