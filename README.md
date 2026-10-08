# Smart Orange Test Project

A Laravel application for importing leads from XLSX files.

The import runs synchronously in a single HTTP request with
`max_execution_time=30`. All source rows are preserved, including
duplicate `external_id` values.

## Technology stack

| Component | Version |
| --- | --- |
| PHP | 8.2 |
| Laravel | 12 |
| MySQL | 8.0 |
| Nginx | 1.28 |

PHP and Composer run inside the application container. You do not need to install PHP, Composer, Node.js, or MySQL on your host machine.

## Requirements

The setup script is intended for Linux, including Ubuntu.

- Docker Engine running on your machine.
- A recent Docker Compose plugin with support for `docker compose up --wait`.
- Bash.
- Make for the shortcuts below, or use the Bash script directly.
- Internet access for the first build and dependency installation.
- Port `8080` available, or choose another port as described below.

Verify Docker before starting:

```bash
docker info
docker compose version
```

Run the project commands as your regular Linux user, without `sudo`. Your user must have permission to access Docker. The setup script uses your user and group IDs to avoid creating root-owned project files.

## Installation

Extract `so_test.zip` and open the project directory:

```bash
unzip so_test.zip
cd so_test
```

Run the initial setup:

```bash
make setup
```

If Make is not installed, run:

```bash
bash bin/setup.sh
```

The setup script:

1. Creates `.env` from `.env.example` if it does not exist.
2. Builds the PHP application image.
3. Starts MySQL and waits until it is ready.
4. Installs Composer dependencies using `composer.lock`.
5. Generates the Laravel application key if it is missing.
6. Runs database migrations.
7. Starts the application and Nginx containers.

Open **http://localhost:8080**.

The web server is bound to `127.0.0.1`, so it is accessible from your local machine.

## Importing leads

1. Open http://localhost:8080.
2. Select the XLSX file supplied with the assignment.
3. Click **Check File** to validate the headers and preview the first 5 data rows.
4. Click **Import Leads** to import the complete file.

Previewing the file is optional and does not save records to the database.
The complete import also validates the headers.

After a successful import, the page displays:

- The number of imported records.
- Elapsed import time in seconds.
- Peak PHP memory usage in MB.

Each import appends records to the database. Importing the same file again
creates another set of records. Duplicate `external_id` values are preserved.

If a validation or database error occurs, the entire import is rolled back.

### Input file

Use the XLSX file supplied with the assignment. The original dataset is not
included in this repository.

Select the file directly from your computer using the upload form.
There is no need to copy it into the project directory.

Only `.xlsx` files up to 32 MB are accepted.

### Expected XLSX structure

Only the first worksheet is processed. Its first row must contain the following
15 column headers in this exact order:

| Position | Column |
| --- | --- |
| 1 | `external_id` |
| 2 | `created_at` |
| 3 | `first_name` |
| 4 | `last_name` |
| 5 | `phone` |
| 6 | `email` |
| 7 | `city` |
| 8 | `source` |
| 9 | `utm_campaign` |
| 10 | `product` |
| 11 | `budget_uah` |
| 12 | `status` |
| 13 | `manager` |
| 14 | `comment` |
| 15 | `next_contact_at` |

Header names are case-sensitive. Leading and trailing whitespace is trimmed.
Missing, extra, or reordered column headers are rejected.

### Data requirements

- All 15 column headers are required.
- `external_id` and `created_at` must have values in every data row.
- Other fields may be empty.
- Date cells must use an Excel date/time format.
- `budget_uah` must be numeric when provided.
- Zero values are preserved; empty optional values are stored as `NULL`.
- The file must contain at least one data row.

## Implementation

- XLSX files are read using OpenSpout.
- Worksheet rows are processed sequentially.
- Shared string tables with up to 250,000 entries are cached in memory
  to avoid repeated disk reads. Larger or unknown tables use OpenSpout's
  default caching strategy.
- Records are inserted using Laravel Query Builder in batches of 1,000.
- The complete import runs within one database transaction.
- Validation or database errors roll back the transaction.
- `external_id` has a non-unique index because the supplied file contains
  repeated values. Each record has its own primary key.

The shared string threshold limits the number of entries, not their
total memory size. This strategy was selected for the supplied dataset.

Database structure is defined by the migrations in `database/migrations`.

## Performance verification

The supplied XLSX file was imported through the web interface into
an empty MySQL table.

| Metric | Result |
| --- | --- |
| Source data rows | 100,000 |
| Inserted records | 100,000 |
| Import time | REPLACE_WITH_MEASURED_VALUE seconds |
| Peak PHP memory | REPLACE_WITH_MEASURED_VALUE MB |
| PHP execution limit | 30 seconds |
| PHP memory limit | 256 MB |

The reported import time covers XLSX reading, row conversion, database
inserts, and transaction commit. It excludes the browser upload time.

Environment: PHP 8.2.34, Laravel 12.69.3, MySQL 8.0, Docker on Ubuntu.

## Daily use

Run these commands from the `so_test` directory:

```bash
make up
make down
make logs
make shell
```

| Command | Action |
| --- | --- |
| `make up` | Start the containers in the background. |
| `make down` | Stop and remove the containers while keeping database data. |
| `make logs` | Follow container logs. Press Ctrl+C to exit. |
| `make shell` | Open Bash inside the PHP container. |
| `make migrate` | Apply new database migrations. |

To check the container status:

```bash
docker compose ps
```

To run a Laravel command:

```bash
docker compose exec app php artisan about
```

Source files are mounted into the containers. PHP and Blade changes are available without rebuilding the image. After changing the Dockerfile or PHP configuration, run `make setup` again to rebuild and recreate the application container.

## Configuration

Local application settings are stored in `.env`. Keep this file out of version control.

The application connects to MySQL using the Compose service name `db`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=leads_import
DB_USERNAME=leads
DB_PASSWORD=local_leads_password
```

These values match `compose.yaml`. MySQL is available inside the Docker network; its port is not published to the host. The included credentials are for local development.

Database data is stored in the named `mysql_data` volume and survives `make down`.

After changing Laravel environment settings, clear the configuration cache:

```bash
docker compose exec app php artisan config:clear
```

## Using a different HTTP port

If port `8080` is already in use:

```bash
HTTP_PORT=8081 make setup
```

Set `APP_URL=http://localhost:8081` in `.env`, then open **http://localhost:8081**.

To keep this port for later runs, also add the following line to `.env`:

```dotenv
HTTP_PORT=8081
```

## Troubleshooting

### Docker is unavailable

Ensure Docker is running and `docker info` succeeds for your regular user before running setup again.

### The application does not respond

Check the services and recent logs:

```bash
docker compose ps
docker compose logs --tail=100 app web db
```

### Setup was interrupted

Run `make setup` again. Existing `.env` settings and the application key are preserved, and Laravel applies only pending migrations.

### Configuration changes are not reflected

Clear Laravel's cached configuration and views:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
```
