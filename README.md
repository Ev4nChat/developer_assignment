## Vacation Management Portal

A full-stack web application that allows company employees to submit vacation requests and enables managers to manage
users and approve/reject those requests.
Built using React for the frontend and Symfony (PHP) & Doctrine for the backend with JWT authentication.

### 🚀 Features

#### 🧑‍💼 Manager

- Login with credentials 
- View and manage employee vacation requests
- Approve or reject pending vacation requests
- Create, edit, and delete user accounts

#### 👨‍💻 Employee

- Login with credentials 
- Submit vacation requests with start/end dates and reason 
- View status of requests 
- Delete own pending vacation requests

#### 🔐 Authentication
- JWT-based token auth 
- Role-based routing (ROLE_EMPLOYEE, ROLE_MANAGER)

### ⚙️ Installation
1. Clone the repo:

   `git clone https://github.com/Ev4nChat/developer_assignment.git` and then from your IDE switch to 
branch `developer-assignment-v1`

2. Set up:

   `docker-compose up -d`

3. Create an `.env` file within the backend directory with the following:

    ```
    APP_ENV=dev
    DATABASE_URL="mysql://root:root@mysql:3306/vacation?serverVersion=8.0"
    JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
    JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
    JWT_PASSPHRASE=bXktZW5jb2RlZC1qd3QtcGFzc3BocmFzZQ==
    ```

4. Run `docker-compose exec php composer install`

5. Run migrations:

   `docker-compose exec php bin/console doctrine:migrations:migrate`

6. JWT Key Setup:
   This project uses JWT for authentication, which requires a private/public key pair.

    > These keys are not created automatically, so you must generate them manually.

    Generate the keys:

    Run the following commands from the root of the Symfony backend project:
    
    ```
    mkdir -p backend/config/jwt
    openssl genrsa -aes256 -out backend/config/jwt/private.pem 4096
    openssl rsa -pubout -in backend/config/jwt/private.pem -out backend/config/jwt/public.pem
    
    ```
   Once you are asked for the passphrase, use the `JWT_PASSPHRASE` from the `.env` file.

7. Run database seeder
   `docker-compose exec php bin/console app:seed-database`

8. Login with credentials: owner@example.com / password


### 💻 Usage

- Go to http://localhost:3000
- Login with manager or employee credentials 
- Use the role-specific dashboards to manage or request vacations

### 🧹 Code Quality Tools

This project uses PHPStan and PHP_CodeSniffer for static analysis and coding standards.

- To run PHPStan:
  `vendor/bin/phpstan analyse`
- To run CodeSniffer:
  `vendor/bin/phpcs`


