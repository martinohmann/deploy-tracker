deploy-tracker
==============

A dashboard which aggregates information about application deployments. It also
provides an API which makes it easy to publish deployment statuses to the
tracker. There is also a [library for integration with capistrano](https://github.com/martinohmann/capistrano-deploy-tracker).

Development
-----------

```
$ docker-compose up -d
$ composer install
$ bin/console doctrine:schema:create
$ bin/console server:run
```

Publish deployment to the API
-----------------------------

Via curl:

```shell
$ curl -i -XPOST http://localhost:8001/api/publish\?auth_token\=thisisatoken -d 'application=theapplicationname&branch=master&stage=production&commit_hash=deadbeef&project_url=https://github.com/somenamespace/somerepo&deployer=deployername&status=success'
```
