This is a plugin for using database migrations, written in SQL, in a CakePHP
application. The feature set is minimal; downward migrations are not supported,
and there is no error handling. Databases other than MySQL are not supported.

# Installation

Clone this repository into a directory called MysqlMigrations in the app/Plugins directory of your CakePHP application.

# Usage

This plugin is used through the `cake` command-line tool supplied with CakePHP.
There are three possible invocations:

* `cake MysqlMigration.migrate create <name>`: creates an new empty migration with the given name. The resulting file is placed in the Config/SQLMigrations directory of your CakePHP application, and has the current timestamp as part of its name. This is where the SQL code for the migration should be put.
* `cake MysqlMigration.migrate up`: runs all migrations that have not yet been run.
* `cake MysqlMigration.migrate up <timestamp>`: runs all migrations that have not yet been run and whose timestamps are less than or equal to the given timestamp.

# Why evolutions?

When you use a relational database, you need a way to track and organize your database schema evolutions. Typically there are several situation where you need a more sophisticated way to track your database schema changes:

* When you work within a team of developers, each person needs to know about any schema change.
* When you deploy on a production server, you need to have a robust way to upgrade your database schema.
* If you work on several machines, you need to keep all database schemas synchronized.

# Managing Evolutions scripts

This tool allows you to track your database evolutions using several evolutions script. These scripts are written in plain old SQL and should be located in the app/Config/SQLMigrations directory of your application.

The scripts names are based on the timestamps of when they were created, e.g 20121012155552_SomeName.sql

Each script contains SQL code which performs a single, well defined schema change, such as creating a new table, or editing an existing table.

For example, take a look at this first evolution script that bootstrap a basic application:

    # Creates a table for the User model.
     
    CREATE TABLE User (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        password varchar(255) NOT NULL,
        fullname varchar(255) NOT NULL,
        isAdmin boolean NOT NULL,
        PRIMARY KEY (id)
    );

When the `cake MysqlMigration.migrate up` console command is run, this plugin will check your database schema state. If your database schema is not up to date, the plugin will run all of the evolutions need to bring the DB up to date.

# License
This readme file was based on the Play documentation at http://www.playframework.org/documentation/1.2.5/evolutions, modified under the Apache 2 License.
The plugin itself is based on the plugin at https://github.com/DexterTheDragon/CakePHP-Migrate-script
