# Laravel Project

### About This Project

The goal of this project is to create a micro-banking system, using Laravel which enables users to select one of their bank accounts and pick a date range, and then view the account's daily rolling balance for the selected period.

### Prerequisites

Before you begin, ensure you have met the following requirements:

-   **PHP**: ^8.2
-   **Laravel**: ^11.9
-   **Node.js**: ^18.8.0
-   **MySQL**: Ensure you have a MySQL database set up.

### Installation

To get started with this project, follow these steps:

1. Clone the repository:

    ```
    git clone https://github.com/TaylorLok/NovaBank.git
    ```

2. Navigate to the project directory:

    ```
    cd NovaBank
    ```

3. Install dependencies:

    ```
    composer install
    ```

4. Copy the `.env.example` file to `.env` and configure your environment variables for the development environment:

    ```
    cp .env.example .env
    ```

    For the testing environment, copy the `.env copy.example` file to `.env.testing` and configure your environment variables accordingly:

    ```
    cp .env copy.example .env.testing
    ```

5. Generate an application key and copy it to both `.env` file:

    ```
    php artisan key:generate
    ```

    Then, copy the generated key to the `APP_KEY` variable in the `.env` and `.env.testing` file.

6. Install NPM dependencies:

    ```
    npm install
    ```

7. Compile assets:

    ```
    npm run dev
    ```

    or

    ```
    npm run build
    ```

8. Run migrations with seeder:

    ```
    php artisan migrate --seed
    ```

9. Run the development server and compile assets:

    ```
    php artisan serve
    ```

### Testing

To run tests specifically for the `AccountController`, use the following command:

    ```
    php artisan test --filter AccountControllerTest
    ```
