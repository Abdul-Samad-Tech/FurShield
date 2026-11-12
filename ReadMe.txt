FurShield — ReadMe

Assumptions
- Payment gateway and physical product delivery are out of scope (SRS).
- Veterinarian credential authentication is out of scope (SRS).
- Optional AI chatbot is not included.
- Local development defaults to SQLite; MySQL is supported via .env.

Installation
1. Open folder: furshield
2. composer install
3. copy .env.example to .env and run: php artisan key:generate
4. php artisan migrate:fresh --seed
5. php artisan storage:link
6. npm install && npm run build
7. php artisan serve

Demo credentials (password for all: password)
- Admin: admin@furshield.test
- Pet Owner: owner@furshield.test
- Veterinarian: vet@furshield.test
- Animal Shelter: shelter@furshield.test

Admin privileges matrix page: /admin/privileges

SQL
- Use Laravel migrations for schema creation.
- Reference notes: database/furshield.sql
