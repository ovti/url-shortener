# Aplikacja do skracania adresów URL

### Aplikacja będzie pozwalać użytkownikom na skracanie adresów URL.

#### Planowane funkcje obejmują:
* Obsługa kont użytkowników (rejestracja, role)
  * Konto administratora, który będzie mieć możliwość zarządzania całą treścią w serwisie, w tym kontami użytkowników (zmiana hasła i danych, zmiana danych administratora) i skracanymi adresami (czasowe blokowanie).
  * Konto użytkownika (rejestracja, edycja danych i zmiana hasła). Będzie mieć możliwość zarządzania dodanymi przez siebie adresami poprzez aktualizację tagów i usuwanie rekordów.
  * Użytkownik niezalogowany, który będzie mógł wygenerować skrót URL (ograniczony do 10 adresów w ciągu doby, formularz będzie zawierać adres e-mail użytkownika, URL do skrócenia i opcjonalne tagi dla adresu) oraz będzie posiadać dostęp do listy ostatnio dodanych adresów, najczęściej klikanych itp.
* CRUD dla adresów i tagów. Możliwość tagowania adresów i wyświetlanie dla danego tagu listy adresów
* Możliwość wyświetlenia listy adresów od najnowszego do najstarszego z paginacją po 10 rekordów na stronie oraz najczęściej odwiedzanych adresów

Administrator będzie mógł ustawić wartość `is_blocked` w tabeli urls na true albo false i ustawić czas, do kiedy adres ma pozostać zablokowany w polu `block_expiration`.

Tabela urls_visited będzie służyć gromadzeniu informacji ile razy dany adres został odwiedzony i kiedy.

* Sposób na ograniczenie niezalogowanego użytkownika do wygenerowania maksymalnie 10 adresów w ciągu doby:
  * email niezalogowanego użytkownika będzie zapisywany do tabeli guest_users
  * podczas dodawania adresów będzie sprawdzane czy nie dodał do tabeli urls więcej niż 10 adresów w ciągu ostatniej doby

## Diagram bazy danych
![screen](./diagram.png)

# Docker Symfony Starter Kit

Starter kit is based on [The perfect kit starter for a Symfony 4 project with Docker and PHP 7.2](https://medium.com/@romaricp/the-perfect-kit-starter-for-a-symfony-4-project-with-docker-and-php-7-2-fda447b6bca1).

## What is inside?

* Apache 2.4.25 (Debian)
* PHP 8.1 FPM
* MySQL 8.0.x (5.7)
* NodeJS LTS (latest)
* Composer
* Symfony CLI 
* xdebug
* djfarrelly/maildev

## Requirements

* Install [Docker](https://www.docker.com/products/docker-desktop) and [Docker Compose](https://docs.docker.com/compose/install) on your machine 

## Installation

* (optional) Add 

```bash
127.0.0.1   symfony.local
```
in your `host` file.

* Run `build-env.sh` (or `build-env.ps1` on Windows box)

* Enter the PHP container:

```bash
docker-compose exec php bash
```

* To install Symfony LTS inside container execute:

```bash
cd app
rm .gitkeep
git config --global user.email "you@example.com"
symfony new ../app --full --version=lts
chown -R dev.dev *
```

## Container URLs and ports

* Project URL

```bash
http://localhost:8000
```

or 

```bash
http://symfony.local:8000
```

* MySQL

    * inside container: host is `mysql`, port: `3306`
    * outside container: host is `localhost`, port: `3307`
    * passwords, db name are in `docker-compose.yml`
    
* djfarrelly/maildev i available from the browser on port `8001`

* xdebug i available remotely on port `9000`

* Database connection in Symfony `.env` file:
```yaml
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
```

## Useful commands

* `docker-compose up -d` - start containers
* `docker-compose down` - stop contaniners
* `docker-compose exec php bash` - enter into PHP container
* `docker-compose exec mysql bash` - enter into MySQL container
* `docker-compose exec apache bash` - enter into Apache2 container
