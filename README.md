# MyShop / CloudCart — Original-style PHP / Azure SQL demonstration shop

This version deliberately preserves the original **MyShop** homepage, registration form, and success page layout, colours, inline CSS, and PHP whitespace style. The additional pages use the same Arial, grey background, navy header, simple blue buttons, and white panels. It extends the supplied **MyShop** starter pages into the seven-feature **CloudCart** e-commerce platform described in the DAT6007 Cloud Computing Deployment assessment brief (2026/27).

**Provided:** product catalog and admin updates, cart, transactionally safe checkout with virtual card/wallet/bank simulations, password login and sign-up, optional Google OAuth, TOTP multi-factor authentication, order timeline/status updates, customer wishlists, a read-only REST API, a health endpoint, tests, and Azure deployment guidance.

**Not provided automatically:** a live Azure deployment, actual cloud autoscaling/CDN/load balancer setup, real payment processing, production-hardening / GDPR certification or a completed 3000-word report. Those require your own Azure resources, configuration, testing and report evidence. See [`docs/DEPLOY_AZURE.md`](docs/DEPLOY_AZURE.md) and [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Requirements

PHP **8.2+** with extensions `sqlsrv`, `mbstring`, `curl`, plus an available Microsoft SQL Server / Azure SQL database. The `sqlsrv` extension must be installed/configured separately. Standard Apache/Nginx or PHP development server can serve the PHP pages.

## Quick start

1. Create an empty SQL Server/Azure SQL database. Execute `sql/schema.sql` then `sql/seed.sql`.
2. Set environment variables from `.env.example` in your host (the example file is **not loaded automatically**).
3. Start the server at this directory, e.g., `php -S localhost:8000` (testing only).
4. Open `http://localhost:8000/`, register and sign in. To grant admin status, change your user's `role` to `admin` using the SQL command in `docs/DEPLOY_AZURE.md`.
5. For optional Google OAuth, configure your OAuth client and matching HTTPS redirect in environment variables.
6. Run `php tests/unit.php`; lint with `find . -name '*.php' -print0 | xargs -0 -n1 php -l`.

## Virtual payment modes

Checkout offers **Demo card**, **Demo wallet**, and **Demo bank transfer**. Choose `SUCCESS` to create a simulated-paid order, or `DECLINE` to test a rejected payment. Do not enter real account details. Orders do not charge funds or integrate with an external gateway.

## API

`GET /api_products.php` returns the active catalog as JSON. `GET /health.php` checks SQL connectivity. Both are read-only; authentication, checkout and admin flows are handled by PHP forms.

## Safe secrets and migrations

The uploaded starter's database password is intentionally **not copied**. Rotate those exposed credentials; configure new ones in host environment variables or Azure Key Vault. The setup SQL is intended for a **new empty database**; if you already have a `shopusers` table, back up its data and migrate with care rather than running `CREATE TABLE` over it.

## Academic integrity

The attached assessment brief specifies **AI ACKNOWLEDGED**: learn, adapt, understand and acknowledge AI assistance in your submission. Verify the complete implementation, deployment and test evidence yourself, and write your own report with references.

## Original formatting and layout

`index.php` keeps the original welcome text, header and Create Your Account button, and **adds** navigation and the catalog underneath. `register.php` and `success.php` retain their supplied structure, with small functional additions. `process_register.php` follows the supplied comments, indentation, blank lines, and SQL parameter style. The extra pages share `includes/layout.php` and the simple CSS in `assets/style.css`. The originally exposed database password is deliberately not retained; set `DB_*` environment variables instead.
