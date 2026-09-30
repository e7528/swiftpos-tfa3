# SwiftPOS — TFA3 Forms, Validation, and Avatar Upload

SwiftPOS is a CodeIgniter 4 point-of-sale exercise. It stores customer and staff accounts in MySQL. TFA3 adds validated create and edit forms for both account types and a prepared profile image upload for staff accounts. The user list shows each saved avatar, with a placeholder when no avatar has been uploaded.

## Requirements

- PHP 8.2 or later
- Composer 2
- MySQL or MariaDB
- PHP extensions required by CodeIgniter, including `intl`, `mbstring`, and `mysqlnd`/`mysqli`; the default image service also requires `gd` for avatar preparation. The bundled database tests additionally need `sqlite3`.
- Git, for cloning and submission

## Local setup

1. Install the PHP dependencies from the project directory:

   ```bash
   composer install
   ```

2. Create the local environment file:

   ```bash
   cp env .env
   ```

   On Windows Command Prompt, use `copy env .env` instead.

3. Create an empty MySQL database named `swiftpos`:

   ```sql
   CREATE DATABASE swiftpos
       CHARACTER SET utf8mb4
       COLLATE utf8mb4_unicode_ci;
   ```

4. Review the database section in `.env`. The supplied `env` template uses these safe local defaults:

   ```dotenv
   database.default.hostname = localhost
   database.default.database = swiftpos
   database.default.username = root
   database.default.password = ''
   database.default.DBDriver = MySQLi
   database.default.port = 3306
   ```

   Change the username and password in your untracked `.env` if your local MySQL installation uses different credentials. Never commit real database passwords or hosted-environment secrets.

5. Build and populate a **new, empty** database using **one** of the following methods.

   **Option A — migrations and seeders (recommended):**

   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

   **Option B — submitted database export:**

   ```bash
   mysql -u root -p swiftpos < database/swiftpos.sql
   ```

   The SQL export includes the nullable `users.avatar` field. It drops and recreates the application tables, so only import it into a disposable or empty database. Do not run both methods against the same database: SQL imports do not record CodeIgniter migration history.

6. Ensure `public/uploads/avatars/` exists and the web-server process can write to it. Uploaded files are created by the application; do not commit actual profile images. Set PHP's `upload_max_filesize` to at least `2M` and `post_max_size` above that limit, then restart PHP if you change these settings.

7. Start the CodeIgniter development server:

   ```bash
   php spark serve
   ```

8. Open [http://localhost:8080](http://localhost:8080).

### Upgrade a TFA2 database

If TFA2's tables were created with CodeIgniter migrations, keep the existing data and run `php spark migrate`. The new `2026-09-30-000001_AddUserAvatar.php` migration adds a nullable `VARCHAR(255)` avatar column to `users`; existing records retain a null avatar. Its rollback drops only that column, including any stored filenames.

If TFA2 was populated by importing the old SQL export, there may be no CodeIgniter migration history even though both tables exist. Back up that database, then add the column without reimporting the export:

```sql
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
```

Do not run `php spark migrate` over tables imported from SQL until their migration history is reconciled: it would first try to recreate the existing TFA2 tables.

## Application routes

| Method and route | Purpose |
| --- | --- |
| `GET /` | SwiftPOS dashboard |
| `GET /about` | About page |
| `GET /customers` | List customers from `CustomerModel` |
| `GET /customers/new`, `POST /customers` | New customer form and validated create action |
| `GET /customers/{id}/edit`, `POST /customers/{id}` | Pre-filled customer edit form and validated update action |
| `GET /users` | List staff from `UserModel`, with avatars or placeholders |
| `GET /users/new`, `POST /users` | New user form and validated create action |
| `GET /users/{id}/edit`, `POST /users/{id}` | Pre-filled user edit form and validated update action; optional avatar upload |

Customer forms require a full name and valid email. User forms require a full name and a unique username. New records also require a password of 8 to 72 characters; the edit form accepts a new password but leaves the saved hash unchanged when blank. Invalid submissions show field errors and preserve entered non-password values; edits keep the existing record until validation passes. For an avatar, use the file input on the user edit form: it accepts JPG or PNG images no larger than 2 MB and 4096 × 4096 pixels. The server prepares a 160 × 160 thumbnail, saves it under `public/uploads/avatars/`, and stores only its filename in the database. No upload leaves the current avatar in place.

## Database schema

The table names remain `customers` and `users` to match the laboratory specification. The password and role fields provide groundwork for a later authentication activity.

### `customers`

| Field | Purpose |
| --- | --- |
| `id` | Auto-incrementing primary key |
| `full_name` | Required customer name |
| `email` | Required, unique email address |
| `phone` | Optional phone number, up to 25 characters |
| `password_hash` | Password hash storage for future customer authentication |
| `status` | Account status; defaults to `Active` |
| `created_at` | Record creation date and time |

### `users`

| Field | Purpose |
| --- | --- |
| `id` | Auto-incrementing primary key |
| `username` | Required, unique staff username |
| `full_name` | Required staff name |
| `role` | Stored role: `Admin`, `Store Manager`, `Cashier`, or `Inventory` |
| `password_hash` | Password hash storage for future staff authentication |
| `attendance_status` | Staff status; defaults to `Clocked Out` and may represent `Clocked In`, `PTO`, or `AWOL` |
| `is_verified` | Database-backed verification flag; defaults to false |
| `created_at` | Record creation date and time |
| `avatar` | Nullable filename of the prepared image stored under `public/uploads/avatars/`; no path or binary image data is stored |

Optional values are displayed as `null` when no value is stored.

The User Accounts page derives its display-only access label from the stored role:

| Stored role | Displayed access label |
| --- | --- |
| `Admin` | `Full Access` |
| `Store Manager` | `Management Access` |
| `Cashier` | `View Only` |
| `Inventory` | `Inventory Access` |

## Authentication and sample-data warning

The seeders and SQL export contain demonstration records only. Any sample usernames or password hashes are disposable development data and must not be reused in a real deployment. Password columns store hashes, never plain-text passwords.

This laboratory implements database-backed account forms and image uploads. It does **not** implement a login form, session authentication, role-based route guards, or permissions enforcement. Roles, verification state, attendance status, and access labels are currently stored and displayed; an `Admin` or `Cashier` label does not grant or restrict application actions. Deploy the account-writing routes only in an appropriately controlled environment.

The seeded avatars are null; uploaded images live on disk and are separate from the SQL export. Back up `public/uploads/avatars/` along with the database to preserve profile pictures. On hosting services with disposable local storage, use persistent storage for that directory or uploads will disappear across deployments. Set the hosted `app.baseURL`, database settings, PHP upload limits, and write permissions for the actual host; a working local setup alone does not establish a working public deployment.

## Testing

After `composer install`, run the automated tests with PHP's `sqlite3` extension enabled:

```bash
composer test
```

Manually create and edit a customer and user, try invalid email and duplicate username entries, and confirm that the form shows errors and preserves submitted text. On a user edit page, upload a JPG or PNG within 2 MB; check the user list, saved filename, and file under `public/uploads/avatars/`. Try a non-image file and an image larger than 2 MB and check that neither changes the record or replaces the existing avatar. Confirm that a user without an avatar displays the placeholder. Test a clean SQL import separately from the migrations-and-seeders setup.

## Troubleshooting

- **`Unable to connect to the database`:** Confirm that MySQL is running, the `swiftpos` database exists, and the credentials in `.env` match the local server.
- **`Table ... doesn't exist`:** Run `php spark migrate` and `php spark db:seed DatabaseSeeder`, or import `database/swiftpos.sql`. Use `php spark migrate:status` to inspect migration state.
- **Duplicate-table or duplicate-record errors:** Start with an empty `swiftpos` database and choose either migrations/seeders or the SQL import, not both.
- **Missing framework classes or `vendor/autoload.php`:** Run `composer install` from the repository root.
- **Avatar upload or processing fails:** Check the `gd` extension, `public/uploads/avatars/` write permissions, PHP upload size settings, and `writable/` permissions. File size limits configured in PHP may reject a request before application validation runs.
- **404 responses:** Use the URL printed by `php spark serve`. For Apache or Nginx hosting, set the document root to the project's `public/` directory.
- **Write-permission errors:** Ensure the web-server process can write to CodeIgniter's `writable/` directory.

## Submission checklist

- [ ] Repository contains the raw CodeIgniter project files and a working `.gitignore` for uploaded images.
- [ ] Create and edit pages validate input, preserve entered values on errors, and update existing records.
- [ ] User avatars accept only JPG/PNG up to 2 MB, are prepared for display, and show on the list with a placeholder fallback.
- [ ] The incremental avatar migration, seeders, and `database/swiftpos.sql` are included and recreate the TFA3 schema and sample data by their documented paths.
- [ ] `.env`, real credentials, and other secrets are not committed.
- [ ] Setup, form validation, uploads, and run instructions have been checked against a clean database.
- [ ] Hosted deployment and its public uploads directory work and match the submitted repository.

## Submission links

- GitHub repository: **TODO — add repository URL**
- Hosted application: **TODO — add hosted URL**
