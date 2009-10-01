<?php
App::import('ConnectionManager');
class MigrateShell extends Shell {

    var $path = null;
    var $uses = array('schema_migrations');

    function initialize() {
        $this->path = $this->params['working'] . DS . 'config' . DS . 'sql' . DS . 'migrations';

        $this->_loadDbConfig();
        $this->useDbConfig = $this->DbConfig->default;
    }

    function help() {
        $this->out('Shell to run sql files in '.$this->path);
        $this->out('Runs mysql on the command line');
        $this->out('Depends on the "default" database config');
        $this->hr();
        $this->out('cake migrate create (name)');
        $this->out('    - creates a migration file with the given name');
        $this->out('    - eg. cake migrate up add users table');
        $this->out('cake migrate up');
        $this->out('    - Update to the latest file');
        $this->out('cake migrate up [num]');
        $this->out('    - Update to file [num]');
    }

    function create() {
        if ( empty($this->args) ) {
            $this->error('You need to give a name for this migration file', '');
        }
        date_default_timezone_set('UTC');
        $filename = date('YmdHis') .'_'. implode('_', $this->args) .'.sql';
        $file = new File($this->path . DS .$filename);
        if ( $file->create() ) {
            $this->out("Migration file $filename created");
        } else {
            $this->out('failed to create the file');
        }
    }

    function up() {
        $endVersion = null;
        if ( !empty($this->args[0]) ) {
            $endVersion = $this->args[0];
        }

        // check to make sure the table exists
        $this->checkMigrationTable();

        // pull which numbers have been run and sort them
        $schemaVersions = $this->schema_migrations->query('SELECT * FROM schema_migrations');
        if ( !empty($schemaVersions) ) {
            $schemaVersions = Set::extract($schemaVersions, '/schema_migrations/version');
            sort($schemaVersions);
        }

        $files = $this->listFiles($this->path);
        sort($files);

        $toRun = array();
        foreach ( $files as $file ) {
            $filename = basename($file);
            if ( preg_match('/^([0-9]{14})_(.*).sql$/', $filename, $matches) ) {
                $num = $matches[1];
            } else { // old method
                list($num) = explode('.', $filename);
            }
            if ( !in_array($num, $schemaVersions) ) {
                $toRun[] = $filename;
            }
        }

        foreach ( $toRun as $file ) {
            $filename = basename($file);
            list($num) = explode('.', $filename);

            if ( $num <= $endVersion || $endVersion === null ) {
                $this->out('Running: '. $filename);
                $this->runFile($this->path . DS . $file);
            }
        }
    }

    function runFile($file) {
        $filename = basename($file);
        if ( preg_match('/^([0-9]{14})_(.*).sql$/', $filename, $matches) ) {
            $num = $matches[1];
        } else { // old method
            list($num) = explode('.', $filename);
        }

        $cmd = "mysql -h '{$this->useDbConfig['host']}' -u '{$this->useDbConfig['login']}'";
        if ( !empty($this->useDbConfig['password']) ) {
            $cmd.= " -p'{$this->useDbConfig['password']}'";
        }
        $cmd.= " '{$this->useDbConfig['database']}' < {$file}";
        $cmd.= " 2>&1";

        exec($cmd, $output, $return);
        if ( $return == 0 ) {
            $this->schema_migrations->save(array('version' => $num));
        } else {
            $this->error("File $filename failed", "Reason: \n". implode("\n", $output));
        }
    }

    function listFiles($path = null) {
        $folder = new Folder($path);
        $return = $folder->findRecursive('.*sql');
        return $return;
    }

    // Make sure the table exists, if not create it
    // Then load the model
    function checkMigrationTable() {
        $tables = $this->getAllTables();
        if ( !in_array('schema_migrations', $tables) ) {
            $this->out('Creating schema_migrations table.');

            $db =& ConnectionManager::getDataSource('default');
            $db->cacheSources = false;
            $db->query("CREATE TABLE `schema_migrations` (
                `version` varchar(255) NOT NULL,
                UNIQUE KEY `unique_schema_migrations` (`version`)
            )");
            Cache::clear();
        }
        $this->_loadModels();
    }

	function getAllTables($useDbConfig = 'default') {
		$db =& ConnectionManager::getDataSource($useDbConfig);
		$usePrefix = empty($db->config['prefix']) ? '': $db->config['prefix'];
		if ($usePrefix) {
			$tables = array();
			foreach ($db->listSources() as $table) {
				if (!strncmp($table, $usePrefix, strlen($usePrefix))) {
					$tables[] = substr($table, strlen($usePrefix));
				}
			}
		} else {
			$tables = $db->listSources();
		}
		$this->__tables = $tables;
		return $tables;
	}

}
?>
