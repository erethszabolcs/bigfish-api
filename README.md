# REST API próbafeladat telepítés

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

A projekt mappájában a következő paranccsal egy Docker Container segítségével telepítjük a projekthez szükséges Composer csomagokat, köztük a Laravel Sail-t is.

```bash
docker run --rm -v $(pwd):/opt -w /opt laravelsail/php80-composer:latest composer install
```

Ezután már futtathatjuk a következő parancsot, amely az Image alapján felépíti és elindítja a projekthez szükséges (laravel.test, mysql), általunk megadott Container-eket. 

```bash
./vendor/bin/sail up -d laravel.test mysql
```
