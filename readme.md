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
