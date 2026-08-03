# Rutac Perfume Backoffice

This is the backoffice management system for Rutac Perfume, built using the modern Laravel 11 framework with an Inertia.js and React frontend.

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** React with Inertia.js
- **Styling:** Tailwind CSS
- **UI Components:** Material UI (MUI)
- **Build Tool:** Vite
- **Development Tools:** Laravel Breeze, Pest (testing), ESLint, Prettier

## Key Features

- **Authentication:** Managed via Laravel Sanctum & Breeze.
- **Backend Services:** Built-in queue management with Laravel Horizon.
- **Frontend Interactivity:** Responsive dashboards and forms using React and Material UI.
- **Logging & Debugging:** Enhanced development experience with Laravel Pail.

## Getting Started

1. **Clone the repository:**
   `git clone git@github.com:caturilham05/rutac-backoffice.git`

2. **Install dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment:**
   Copy `.env.example` to `.env` and configure your database and services.

4. **Run the development server:**
   ```bash
   npm run dev
   # Or using the custom script:
   composer run dev
