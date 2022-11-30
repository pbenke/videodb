videoDB
=======

VideoDB is a PHP-based web application to manage a personal video collection. Multiple video types are supported, ranging from VHS tapes and DVDs to Blu-ray discs and DivX files on hard-disc. Even video games are supported.

Introduction
------------

### Browse

You can use videoDB to manage your video and CD collection, be it DVD, BluRay or plain Files:

![Browse Movies](https://raw.github.com/andig/videodb/master/doc/screenshots/0.png)

### View

![View Details](https://raw.github.com/andig/videodb/master/doc/screenshots/1.png)

### Edit
All data is editable in nice layed out forms:

![Edit](https://raw.github.com/andig/videodb/master/doc/screenshots/2.png)

### IMDB

New movies are easily added directly from IMDB or other sources:

![IMDB](https://raw.github.com/andig/videodb/master/doc/screenshots/3.png)

### Config

videoDB is also highly customizable- almost every aspect can be changed from template selection to detailed customization:

![Config](https://raw.github.com/andig/videodb/master/doc/screenshots/4.png)

Docker setup
------------

1. Edit hosts file, add new entry:

`127.0.0.1 videodb.test`

2. Init docker/.env and set appropriate values:

`cd docker && cp .env.example .env`

3. Build docker containers:

`docker-compose up -d`

4. Install VideoDB by opening http://videodb.test:8079/install.php in web browser. Use parameters from `docker-compose.yml` file, ie:

```
Server: db
User: root
Password: 12345
Database: videodb
Table prefix: <blank>
```
