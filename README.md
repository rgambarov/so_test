# Smart Orange Test Project

A Laravel development project running in Docker.

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
