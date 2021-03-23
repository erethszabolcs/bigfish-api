# REST API telepítés

Telepítés előtt feltételezzük, hogy működőképes Docker és Git is telepítve van a rendszerünkön. 

## Projekt klónozása

Egy általunk választott mappában futtassuk a következő parancsot:

```bash
git clone https://github.com/erethszabolcs/bigfish-api.git
```
Ha nem szeretnénk parancssort használni, akor [innen](https://github.com/erethszabolcs/bigfish-api) letölthetjük zip-ként is.

## Docker Image pull
A következő paranccsal telepítsük a szükséges Docker Image-et.

```bash
docker pull erethszabolcs/bigfish-api
```
Ezután hozzunk létre ebből az Image-ből egy új tag-el ellátott Image-et. A projekt ezt fogja használni a Container-ek felépítéséhez.

```bash
docker tag erethszabolcs/bigfish-api bigfish-api
```

## Laravel Sail

A projekt mappájában (jelen esetben /bigfish-api) a következő paranccsal egy Docker Container segítségével telepítjük a projekthez szükséges Composer csomagokat, köztük a Laravel Sail-t is.

```bash
docker run --rm -v $(pwd):/opt -w /opt laravelsail/php80-composer:latest composer install
```

Ezután már futtathatjuk a következő parancsot, amely az Image alapján felépíti és elindítja a projekthez szükséges (laravel.test, mysql), általunk megadott Container-eket. 

```bash
./vendor/bin/sail up -d laravel.test mysql
```

A várhatóan futó Container-ek alapesetben a következő lokális portokon fognak működni: **80** (Laravel), **3306** (MySQL), **6379** (Redis). Így fontos, hogy ezek a portok ne legyenek foglalva más Container-ek vagy lokális szerverek által. Alternatív megoldásként a .env fájlban környezeti változóként megadhatjuk, ha ezen Container-eknek szeretnénk más lokális portokat dedikálni (*APP_PORT*, *FORWARD_DB_PORT*, *FORWARD_REDIS_PORT*).

A Container-ek elindulásával lefut adatbázis migráció és seed is, így generált adatokkal egyből kipróbálható az API.

## Végpontok

```php
// Get all Users with optional page selection and optional ordering
GET: /api/v1/users
GET: /api/v1/users?page=2&order_by=name&direction=desc

// Create User
POST: /api/v1/users

// Get specific User (if it exists according to the given id)
GET: /api/v1/users/1

// Update User (if it exists according to the given id)
PUT: /api/v1/users/1

// Delete User (if it exists according to the given id)
DELETE: /api/v1/users/1

// Fallback endpoint to handle wrong URLs
GET: /api/{fallbackPlaceholder}
```

## Tesztek

A következő paranccsal futtathatjuk az éppen futó alkalmazás mappájában a teszteket:

```bash
./vendor/bin/sail artisan test
```

## Leállítás
A következő paranccsal megszüntethetjük a futó Container-eket (-v kapcsoló opcionális).
```bash
./vendor/bin/sail down -v
```
