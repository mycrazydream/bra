<?php
/**
 * Abstract database connectivity class.
 * Sub-classes of this implement the actual database connection libraries
 * @package framework
 * @subpackage model
 */
abstract class SS_Database {
	/**
	 * Connection object to the database.
	 * @param resource
	 */
	static $globalConn;
	
	/**
	 * @var boolean Check tables when running /dev/build, and repair them if necessary. 
	 * In case of large databases or more fine-grained control on how to handle
	 * data corruption in tables, you can disable this behaviour and handle it
	 * outside of this class, e.g. through a nightly system task with extended logging capabilities.
	 */
	static $check_and_repair_on_build = true;
	
	/**
	 * If this is false, then information about database operations
	 * will be displayed, eg creation of tables.
	 * @param boolean
	 */
	protected $supressOutput = false;
	
	/**
	 * Execute the given SQL query.
	 * This abstract function must be defined by subclasses as part of the actual implementation.
	 * It should return a subclass of SS_Query as the result.
	 * @param string $sql The SQL query to execute
	 * @param int $errorLevel The level of error reporting to enable for the query
	 * @return SS_Query
	 */
	abstract function query($sql, $errorLevel = E_USER_ERROR);
	
	/**
	 * Get the autogenerated ID from the previous INSERT query.
	 * @return int
	 */
	abstract function getGeneratedID($table);
	
	/**
	 * Check if the connection to the database is active.
	 * @return boolean
	 */
	abstract function isActive();
	
	/**
	 * Create the database and connect to it. This can be called if the
	 * initial database connection is not successful because the database
	 * does not exist.
	 * 
	 * It takes no parameters, and should create the database from the information
	 * specified in the constructor.
	 * 
	 * @return boolean Returns true if successful
	 */
	abstract function createDatabase();
	
	/**
	 * Build the connection string from input
	 * @param array $parameters The connection details
	 * @return string $connect The connection string
	 **/
	abstract function getConnect($parameters);
	
	/**
	 * Create a new table.
	 * @param $tableName The name of the table
	 * @param $fields A map of field names to field types
	 * @param $indexes A map of indexes
	 * @param $options An map of additional options.  The available keys are as follows:
	 *   - 'MSSQLDatabase'/'MySQLDatabase'/'PostgreSQLDatabase' - database-specific options such as "engine" for MySQL.
	 *   - 'temporary' - If true, then a temporary table will be created
	 * @return The table name generated.  This may be different from the table name, for example with temporary tables.
	 */
	abstract function createTable($table, $fields = null, $indexes = null, $options = null, $advancedOptions = null);
	
	/**
	 * Alter a table's schema.
	 */
	abstract function alterTable($table, $newFields = null, $newIndexes = null, $alteredFields = null, $alteredIndexes = null, $alteredOptions=null, $advancedOptions=null);
	
	/**
	 * Rename a table.
	 * @param string $oldTableName The old table name.
	 * @param string $newTableName The new table name.
	 */
	abstract function renameTable($oldTableName, $newTableName);
	
	/**
	 * Create a new field on a table.
	 * @param string $table Name of the table.
	 * @param string $field Name of the field to add.
	 * @param string $spec The field specification, eg 'INTEGER NOT NULL'
	 */
	abstract function createField($table, $field, $spec);
	
	/**
	 * Change the database column name of the given field.
	 * 
	 * @param string $tableName The name of the tbale the field is in.
	 * @param string $oldName The name of the field to change.
	 * @param string $newName The new name of the field
	 */
	abstract function renameField($tableName, $oldName, $newName);

	/**
	 * Get a list of all the fields for the given table.
	 * Returns a map of field name => field spec.
	 * @param string $table The table name.
	 * @return array
	 */
	protected abstract function fieldList($table);
	
	/**
	 * Returns a list of all tables in the database.
	 * Keys are table names in lower case, values are table names in case that
	 * database expects.
	 * @return array
	 */
	
	/**
	 *
	 * This is a stub function.  Postgres caches the fieldlist results.
	 *
	 * @param string $tableName
	 *
	 * @return boolean
	 */
	function clearCachedFieldlist($tableName=false){
		return true;
	}
	
	protected abstract function tableList();
	
	
	/**
	 * Returns true if the given table exists in the database
	 */
	abstract function hasTable($tableName);
	
	/**
	 * Returns the enum values available on the given field
	 */
	abstract function enumValuesForField($tableName, $fieldName);
	
	/**
	 * Returns an escaped string.
	 *
	 * @param string
	 * @return string - escaped string
	 */
	abstract function addslashes($val);
	
	/**
	 * The table list, generated by the tableList() function.
	 * Used by the requireTable() function.
	 * @var array
	 */
	protected $tableList;
	
	/**
	 * The field list, generated by the fieldList() function.
	 * An array of maps of field name => field spec, indexed
	 * by table name.
	 * @var array
	 */
	protected $fieldList;
	
	/**
	 * The index list for each table, generated by the indexList() function.
	 * An map from table name to an array of index names.
	 * @var array
	 */
	protected $indexList;
	
	
	/**
	 * Large array structure that represents a schema update transaction
	 */
	protected $schemaUpdateTransaction;

	/**
	 * Start a schema-updating transaction.
	 * All calls to requireTable/Field/Index will keep track of the changes requested, but not actually do anything.
	 * Once	
	 */
	function beginSchemaUpdate() {
		$this->tableList = array();
		$tables = $this->tableList();
		foreach($tables as $table) $this->tableList[strtolower($table)] = $table;

		$this->indexList = null;
		$this->fieldList = null;
		$this->schemaUpdateTransaction = array();
	}
	
	/**
	 * Completes a schema-updated transaction, executing all the schema chagnes.
	 */
	function endSchemaUpdate() {
		foreach($this->schemaUpdateTransaction as $tableName => $changes) {
			switch($changes['command']) {
				case 'create':
					$this->createTable($tableName, $changes['newFields'], $changes['newIndexes'], $changes['options'], @$changes['advancedOptions']);
					break;
				
				case 'alter':
					$this->alterTable($tableName, $changes['newFields'], $changes['newIndexes'],
						$changes['alteredFields'], $changes['alteredIndexes'], $changes['alteredOptions'], @$changes['advancedOptions']);
					break;
			}
		}
		$this->schemaUpdateTransaction = null;
	}

	/**
	 * Cancels the schema updates requested after a beginSchemaUpdate() call.
	 */
	function cancelSchemaUpdate() {
		$this->schemaUpdateTransaction = null;
	}

	/**
	 * Returns true if schema modifications were requested after a beginSchemaUpdate() call.
	 */
	function doesSchemaNeedUpdating() {
		return (bool)$this->schemaUpdateTransaction;
	}
	
	// Transactional schema altering functions - they don't do anyhting except for update schemaUpdateTransaction
	
	/**
	 * @param string $table
	 * @param string $options
	 */
	function transCreateTable($table, $options = null, $advanced_options = null) {
		$this->schemaUpdateTransaction[$table] = array('command' => 'create', 'newFields' => array(), 'newIndexes' => array(), 'options' => $options, 'advancedOptions' => $advanced_options);
	}
	
	/**
	 * @param string $table
	 * @param array $options
	 */
	function transAlterTable($table, $options, $advanced_options) {
		$this->transInitTable($table);
		$this->schemaUpdateTransaction[$table]['alteredOptions'] = $options;
		$this->schemaUpdateTransaction[$table]['advancedOptions'] = $advanced_options;
	}
	
	function transCreateField($table, $field, $schema) {
		$this->transInitTable($table);
		$this->schemaUpdateTransaction[$table]['newFields'][$field] = $schema;
	}
	function transCreateIndex($table, $index, $schema) {
		$this->transInitTable($table);
		$this->schemaUpdateTransaction[$table]['newIndexes'][$index] = $schema;
	}
	function transAlterField($table, $field, $schema) {
		$this->transInitTable($table);
		$this->schemaUpdateTransaction[$table]['alteredFields'][$field] = $schema;
	}
	function transAlterIndex($table, $index, $schema) {
		$this->transInitTable($table);
		$this->schemaUpdateTransaction[$table]['alteredIndexes'][$index] = $schema;
	}
	
	/**
	 * Handler for the other transXXX methods - mark the given table as being altered
	 * if it doesn't already exist
	 */
	protected function transInitTable($table) {
		if(!isset($this->schemaUpdateTransaction[$table])) {
			$this->schemaUpdateTransaction[$table] = array(
				'command' => 'alter',
				'newFields' => array(),
				'newIndexes' => array(),
				'alteredFields' => array(),
				'alteredIndexes' => array(),
				'alteredOptions' => ''
			);
		}		
	}
	
	
	/**
	 * Generate the following table in the database, modifying whatever already exists
	 * as necessary.
	 * @todo Change detection for CREATE TABLE $options other than "Engine"
	 * 
	 * @param string $table The name of the table
	 * @param string $fieldSchema A list of the fields to create, in the same form as DataObject::$db
	 * @param string $indexSchema A list of indexes to create. See {@link requireIndex()}
	 * @param array $options
	 */
	function requireTable($table, $fieldSchema = null, $indexSchema = null, $hasAutoIncPK=true, $options = Array(), $extensions=false) {
		
		if(!isset($this->tableList[strtolower($table)])) {
			$this->transCreateTable($table, $options, $extensions);
			$this->alterationMessage("Table $table: created","created");
		} else {
			if(self::$check_and_repair_on_build) $this->checkAndRepairTable($table, $options);
			
			// Check if options changed
			$tableOptionsChanged = false;
			if(isset($options[get_class($this)]) || true) {
				if(isset($options[get_class($this)])) {
					if(preg_match('/ENGINE=([^\s]*)/', $options[get_class($this)], $alteredEngineMatches)) {
						$alteredEngine = $alteredEngineMatches[1];
						$tableStatus = DB::query(sprintf(
							'SHOW TABLE STATUS LIKE \'%s\'',
							$table
						))->first();
						$tableOptionsChanged = ($tableStatus['Engine'] != $alteredEngine);
					}
				}
			}
			
			if($tableOptionsChanged || ($extensions && DB::getConn()->supportsExtensions())) 
				$this->transAlterTable($table, $options, $extensions);
			
		}

		//DB ABSTRACTION: we need to convert this to a db-specific version:
		$this->requireField($table, 'ID', DB::getConn()->IdColumn(false, $hasAutoIncPK));
		
		// Create custom fields
		if($fieldSchema) {
			foreach($fieldSchema as $fieldName => $fieldSpec) {
				
				//Is this an array field?
				$arrayValue='';
				if(strpos($fieldSpec, '[')!==false){
					//If so, remove it and store that info separately
					$pos=strpos($fieldSpec, '[');
					$arrayValue=substr($fieldSpec, $pos);
					$fieldSpec=substr($fieldSpec, 0, $pos);
				}
				
				$fieldObj = Object::create_from_string($fieldSpec, $fieldName);
				$fieldObj->arrayValue=$arrayValue;
				
				$fieldObj->setTable($table);
				$fieldObj->requireField();
			}
		}
		
		// Create custom indexes
		if($indexSchema) {
			foreach($indexSchema as $indexName => $indexDetails) {
				$this->requireIndex($table, $indexName, $indexDetails);
			}
		}
	}

	/**
	 * If the given table exists, move it out of the way by renaming it to _obsolete_(tablename).
	 * @param string $table The table name.
	 */
	function dontRequireTable($table) {
		if(isset($this->tableList[strtolower($table)])) {
			$suffix = '';
			while(isset($this->tableList[strtolower("_obsolete_{$table}$suffix")])) {
				$suffix = $suffix ? ($suffix+1) : 2;
			}
			$this->renameTable($table, "_obsolete_{$table}$suffix");
			$this->alterationMessage("Table $table: renamed to _obsolete_{$table}$suffix","obsolete");
		}
	}
	
	/**
	 * Generate the given index in the database, modifying whatever already exists as necessary.
	 * 
	 * The keys of the array are the names of the index.
	 * The values of the array can be one of:
	 *  - true: Create a single column index on the field named the same as the index.
	 *  - array('type' => 'index|unique|fulltext', 'value' => 'FieldA, FieldB'): This gives you full
	 *    control over the index.
	 * 
	 * @param string $table The table name.
	 * @param string $index The index name.
	 * @param string|boolean $spec The specification of the index. See requireTable() for more information.
	 */
	function requireIndex($table, $index, $spec) {
		$newTable = false;
		
		//DB Abstraction: remove this ===true option as a possibility?
		if($spec === true) {
			$spec = "(\"$index\")";
		}
		
		//Indexes specified as arrays cannot be checked with this line: (it flattens out the array)
		if(!is_array($spec)) {
			$spec = preg_replace('/\s*,\s*/', ',', $spec);
        }

		if(!isset($this->tableList[strtolower($table)])) $newTable = true;

		if(!$newTable && !isset($this->indexList[$table])) {
			$this->indexList[$table] = $this->indexList($table);
		}
						
		//Fix up the index for database purposes
		$index=DB::getConn()->getDbSqlDefinition($table, $index, null, true);
		
		//Fix the key for database purposes
		$index_alt=DB::getConn()->modifyIndex($index, $spec);
				
		if(!$newTable) {
			if(isset($this->indexList[$table][$index_alt])) {
				if(is_array($this->indexList[$table][$index_alt])) {
					$array_spec = $this->indexList[$table][$index_alt]['spec'];
				} else {
					$array_spec = $this->indexList[$table][$index_alt];
				}
			}
		}
		
		//We need to include the name of the fulltext index here so we can trigger a rebuild
		//if either the name or the columns have changed.
		if(is_array($spec) && isset($spec['type'])){
			if($spec['type']=='fulltext'){
				$array_spec="({$spec['name']},{$spec['value']})";
			}
		}
		
		if($newTable || !isset($this->indexList[$table][$index_alt])) {
			$this->transCreateIndex($table, $index, $spec);
			$this->alterationMessage("Index $table.$index: created as " . DB::getConn()->convertIndexSpec($spec),"created");
		} else if($array_spec != DB::getConn()->convertIndexSpec($spec)) {
			$this->transAlterIndex($table, $index, $spec);
			$spec_msg=DB::getConn()->convertIndexSpec($spec);
			$this->alterationMessage("Index $table.$index: changed to $spec_msg <i style=\"color: #AAA\">(from {$array_spec})</i>","changed");			
		}
	}

	/**
	 * Generate the given field on the table, modifying whatever already exists as necessary.
	 * @param string $table The table name.
	 * @param string $field The field name.
	 * @param array|string $spec The field specification. If passed in array syntax, the specific database
	 * 	driver takes care of the ALTER TABLE syntax. If passed as a string, its assumed to
	 * 	be prepared as a direct SQL framgment ready for insertion into ALTER TABLE. In this case you'll
	 * 	need to take care of database abstraction in your DBField subclass.  
	 */
	function requireField($table, $field, $spec) {
		//TODO: this is starting to get extremely fragmented.
		//There are two different versions of $spec floating around, and their content changes depending
		//on how they are structured.  This needs to be tidied up.
		$fieldValue = null;
		$newTable = false;
		
		Profiler::mark('requireField');
		
		// backwards compatibility patch for pre 2.4 requireField() calls
		$spec_orig=$spec;
		
		if(!is_string($spec)) {
			$spec['parts']['name'] = $field;
			$spec_orig['parts']['name'] = $field;
			//Convert the $spec array into a database-specific string
			$spec=DB::getConn()->$spec['type']($spec['parts'], true);
		}
		
		// Collations didn't come in until MySQL 4.1.  Anything earlier will throw a syntax error if you try and use
		// collations.
		// TODO: move this to the MySQLDatabase file, or drop it altogether?
		if(!$this->supportsCollations()) {
			$spec = preg_replace('/ *character set [^ ]+( collate [^ ]+)?( |$)/', '\\2', $spec);
		}
		
		if(!isset($this->tableList[strtolower($table)])) $newTable = true;

		if(!$newTable && !isset($this->fieldList[$table])) {
			$this->fieldList[$table] = $this->fieldList($table);
		}

		if(is_array($spec)) {
			$specValue = DB::getConn()->$spec_orig['type']($spec_orig['parts']);
		} else {
			$specValue = $spec;
		}

		// We need to get db-specific versions of the ID column:
		if($spec_orig==DB::getConn()->IdColumn() || $spec_orig==DB::getConn()->IdColumn(true))
			$specValue=DB::getConn()->IdColumn(true);
		
		if(!$newTable) {
			if(isset($this->fieldList[$table][$field])) {
				if(is_array($this->fieldList[$table][$field])) {
					$fieldValue = $this->fieldList[$table][$field]['data_type'];
				} else {
					$fieldValue = $this->fieldList[$table][$field];
				}
			}
		}
		
		// Get the version of the field as we would create it. This is used for comparison purposes to see if the
		// existing field is different to what we now want
		if(is_array($spec_orig)) {
			$spec_orig=DB::getConn()->$spec_orig['type']($spec_orig['parts']);
		}
		
		if($newTable || $fieldValue=='') {
			Profiler::mark('createField');
			
			$this->transCreateField($table, $field, $spec_orig);
			Profiler::unmark('createField');
			$this->alterationMessage("Field $table.$field: created as $spec_orig","created");
		} else if($fieldValue != $specValue) {
			// If enums/sets are being modified, then we need to fix existing data in the table.
			// Update any records where the enum is set to a legacy value to be set to the default.
			// One hard-coded exception is SiteTree - the default for this is Page.
			foreach(array('enum','set') as $enumtype) {
				if(preg_match("/^$enumtype/i",$specValue)) {
					$newStr = preg_replace("/(^$enumtype\s*\(')|('$\).*)/i","",$spec_orig);
					$new = preg_split("/'\s*,\s*'/", $newStr);
				
					$oldStr = preg_replace("/(^$enumtype\s*\(')|('$\).*)/i","", $fieldValue);
					$old = preg_split("/'\s*,\s*'/", $newStr);

					$holder = array();
					foreach($old as $check) {
						if(!in_array($check, $new)) {
							$holder[] = $check;
						}
					}
					if(count($holder)) {
						$default = explode('default ', $spec_orig);
						$default = $default[1];
						if($default == "'SiteTree'") $default = "'Page'";
						$query = "UPDATE \"$table\" SET $field=$default WHERE $field IN (";
						for($i=0;$i+1<count($holder);$i++) {
							$query .= "'{$holder[$i]}', ";
						}
						$query .= "'{$holder[$i]}')";
						DB::query($query);
						$amount = DB::affectedRows();
						$this->alterationMessage("Changed $amount rows to default value of field $field (Value: $default)");
					}
				}
			}
			Profiler::mark('alterField');
			$this->transAlterField($table, $field, $spec_orig);
			Profiler::unmark('alterField');
			$this->alterationMessage("Field $table.$field: changed to $specValue <i style=\"color: #AAA\">(from {$fieldValue})</i>","changed");
		}
		Profiler::unmark('requireField');
	}
	
	/**
	 * If the given field exists, move it out of the way by renaming it to _obsolete_(fieldname).
	 * 
	 * @param string $table
	 * @param string $fieldName
	 */
	function dontRequireField($table, $fieldName) {
		$fieldList = $this->fieldList($table);
		if(array_key_exists($fieldName, $fieldList)) {
			$suffix = '';
			while(isset($fieldList[strtolower("_obsolete_{$fieldName}$suffix")])) {
				$suffix = $suffix ? ($suffix+1) : 2;
			}
			$this->renameField($table, $fieldName, "_obsolete_{$fieldName}$suffix");
			$this->alterationMessage("Field $table.$fieldName: renamed to $table._obsolete_{$fieldName}$suffix","obsolete");
		}
	}

	/**
	 * Execute a complex manipulation on the database.
	 * A manipulation is an array of insert / or update sequences.  The keys of the array are table names,
	 * and the values are map containing 'command' and 'fields'.  Command should be 'insert' or 'update',
	 * and fields should be a map of field names to field values, including quotes.  The field value can
	 * also be a SQL function or similar.
	 * @param array $manipulation
	 */
	function manipulate($manipulation) {
		if($manipulation) foreach($manipulation as $table => $writeInfo) {
			
			if(isset($writeInfo['fields']) && $writeInfo['fields']) {
				$fieldList = $columnList = $valueList = array();
				foreach($writeInfo['fields'] as $fieldName => $fieldVal) {
					$fieldList[] = "\"$fieldName\" = $fieldVal";
					$columnList[] = "\"$fieldName\"";

					// Empty strings inserted as null in INSERTs.  Replacement of SS_Database::replace_with_null().
					if($fieldVal === "''") $valueList[] = "null";
					else $valueList[] = $fieldVal;
				}
				
				if(!isset($writeInfo['where']) && isset($writeInfo['id'])) {
					$writeInfo['where'] = "\"ID\" = " . (int)$writeInfo['id'];
				}
				
				switch($writeInfo['command']) {
					case "update":
						// Test to see if this update query shouldn't, in fact, be an insert
						if($this->query("SELECT \"ID\" FROM \"$table\" WHERE $writeInfo[where]")->value()) {
							$fieldList = implode(", ", $fieldList);
							$sql = "UPDATE \"$table\" SET $fieldList where $writeInfo[where]";
							$this->query($sql);
							break;
						}
						
						// ...if not, we'll skip on to the insert code

					case "insert":
						if(!isset($writeInfo['fields']['ID']) && isset($writeInfo['id'])) {
							$columnList[] = "\"ID\"";
							$valueList[] = (int)$writeInfo['id'];
						}
						
						$columnList = implode(", ", $columnList);
						$valueList = implode(", ", $valueList);
						$sql = "INSERT INTO \"$table\" ($columnList) VALUES ($valueList)";
						$this->query($sql);
						break;

					default:
						$sql = null;
						user_error("SS_Database::manipulate() Can't recognise command '$writeInfo[command]'", E_USER_ERROR);
				}
			}
		}
	}
	
	/** Replaces "\'\'" with "null", recursively walks through the given array. 
	 * @param string $array Array where the replacement should happen
	 */
	static function replace_with_null(&$array) {
		$array = preg_replace('/= *\'\'/', '= null', $array);

		if(is_array($array)) {
			foreach($array as $key => $value) {
				if(is_array($value)) {
					array_walk($array, array(SS_Database, 'replace_with_null'));
				}
			}
		}
		
		return $array;
	} 

	/**
	 * Error handler for database errors.
	 * All database errors will call this function to report the error.  It isn't a static function;
	 * it will be called on the object itself and as such can be overridden in a subclass.
	 * @todo hook this into a more well-structured error handling system.
	 * @param string $msg The error message.
	 * @param int $errorLevel The level of the error to throw.
	 */
	function databaseError($msg, $errorLevel = E_USER_ERROR) {
		user_error($msg, $errorLevel);
	}
	
	/**
	 * Enable supression of database messages.
	 */
	function quiet() {
		$this->supressOutput = true;
	}
	
	/**
	 * Show a message about database alteration	
	 *
	 * @param string message to display
	 * @param string type one of [created|changed|repaired|obsolete|deleted|error]
	 */
	function alterationMessage($message,$type=""){
		if(!$this->supressOutput) {
			if(Director::is_cli()) {
				switch ($type){
					case "created":
					case "changed":
					case "repaired":
						$sign = "+";
						break;
					case "obsolete":
					case "deleted":
						$sign = '-';
						break;
					case "error":
						$sign = "!";
						break;
					default:
						$sign=" ";
				}
				$message = strip_tags($message);
				echo "  $sign $message\n";
			} else {
				switch ($type){
					case "created":
						$color = "green";
						break;
					case "obsolete":
						$color = "red";
						break;
					case "error":
						$color = "red";
						break;
					case "deleted":
						$color = "red";
						break;
					case "changed":
						$color = "blue";
						break;
					case "repaired":
						$color = "blue";
						break;
					default:
						$color="";
				}
				echo "<li style=\"color: $color\">$message</li>";
			}
		}
	}

	/**
	 * Returns the SELECT clauses ready for inserting into a query.
	 * @param array $select Select columns
	 * @param boolean $distinct Distinct select?
	 * @return string
	 */
	public function sqlSelectToString($select, $distinct = false) {
		$clauses = array();

		foreach($select as $alias => $field) {
			// Don't include redundant aliases.
			if($alias === $field || preg_match('/"' . preg_quote($alias) . '"$/', $field)) $clauses[] = $field;
			else $clauses[] = "$field AS \"$alias\"";
		}

		$text = 'SELECT ';
		if($distinct) $text .= 'DISTINCT ';
		return $text .= implode(', ', $clauses);
	}

	/**
	 * Return the FROM clause ready for inserting into a query.
	 * @return string
	 */
	public function sqlFromToString($from) {
		return ' FROM ' . implode(' ', $from);
	}

	/**
	 * Returns the WHERE clauses ready for inserting into a query.
	 * @return string
	 */
	public function sqlWhereToString($where, $connective) {
		return ' WHERE (' . implode(") {$connective} (" , $where) . ')';
	}

	/**
	 * Returns the ORDER BY clauses ready for inserting into a query.
	 * @return string
	 */
	public function sqlOrderByToString($orderby) {
		$statements = array();

		foreach($orderby as $clause => $dir) {
			$statements[] = trim($clause . ' ' . $dir);
		}

		return ' ORDER BY ' . implode(', ', $statements);
	}

	/**
	 * Returns the GROUP BY clauses ready for inserting into a query.
	 * @return string
	 */
	public function sqlGroupByToString($groupby) {
		return ' GROUP BY ' . implode(', ', $groupby);
	}

	/**
	 * Returns the HAVING clauses ready for inserting into a query.
	 * @return string
	 */
	public function sqlHavingToString($having) {
		return ' HAVING ( ' . implode(' ) AND ( ', $having) . ')';
	}

	/**
	 * Return the LIMIT clause ready for inserting into a query.
	 * @return string
	 */
	public function sqlLimitToString($limit) {
		$clause = '';

		// Pass limit as array or SQL string value
		if(is_array($limit)) {
			if(!array_key_exists('limit', $limit)) throw new InvalidArgumentException('Database::sqlLimitToString(): Wrong format for $limit: ' . var_export($limit, true));

			if(isset($limit['start']) && is_numeric($limit['start']) && isset($limit['limit']) && is_numeric($limit['limit'])) {
				$combinedLimit = $limit['start'] ? "$limit[limit] OFFSET $limit[start]" : "$limit[limit]";
			} elseif(isset($limit['limit']) && is_numeric($limit['limit'])) {
				$combinedLimit = (int) $limit['limit'];
			} else {
				$combinedLimit = false;
			}
			if(!empty($combinedLimit)) $clause .= ' LIMIT ' . $combinedLimit;
		} else {
			$clause .= ' LIMIT ' . $limit;
		}

		return $clause;
	}

	/**
	 * Convert a SQLQuery object into a SQL statement
	 * @param $query SQLQuery
	 */
	public function sqlQueryToString(SQLQuery $query) {
		if($query->getDelete()) {
			$text = 'DELETE ';
		} else {
			$text = $this->sqlSelectToString($query->getSelect(), $query->getDistinct());
		}

		if($query->getFrom()) $text .= $this->sqlFromToString($query->getFrom());
		if($query->getWhere()) $text .= $this->sqlWhereToString($query->getWhere(), $query->getConnective());

		// these clauses only make sense in SELECT queries, not DELETE
		if(!$query->getDelete()) {
			if($query->getGroupBy()) $text .= $this->sqlGroupByToString($query->getGroupBy());
			if($query->getHaving()) $text .= $this->sqlHavingToString($query->getHaving());
			if($query->getOrderBy()) $text .= $this->sqlOrderByToString($query->getOrderBy());
			if($query->getLimit()) $text .= $this->sqlLimitToString($query->getLimit());
		}

		return $text;
	}

	/**
	 * Wrap a string into DB-specific quotes. MySQL, PostgreSQL and SQLite3 only need single quotes around the string.
	 * MSSQL will overload this and include it's own N prefix to mark the string as unicode, so characters like macrons
	 * are saved correctly.
	 *
	 * @param string $string String to be prepared for database query
	 * @return string Prepared string
	 */
	public function prepStringForDB($string) {
		return "'" . Convert::raw2sql($string) . "'";
	}

	/**
	 * Function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime in a certain format
	 * @param string $date to be formated, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name, e.g. '"SiteTree"."Created"'
	 * @param string $format to be used, supported specifiers:
	 * %Y = Year (four digits)
	 * %m = Month (01..12)
	 * %d = Day (01..31)
	 * %H = Hour (00..23)
	 * %i = Minutes (00..59)
	 * %s = Seconds (00..59)
	 * %U = unix timestamp, can only be used on it's own
	 * @return string SQL datetime expression to query for a formatted datetime
	 */
	abstract function formattedDatetimeClause($date, $format);

	/**
	 * Function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime addition
	 * @param string $date, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name, e.g. '"SiteTree"."Created"'
	 * @param string $interval to be added, use the format [sign][integer] [qualifier], e.g. -1 Day, +15 minutes, +1 YEAR
	 * supported qualifiers:
	 * - years
	 * - months
	 * - days
	 * - hours
	 * - minutes
	 * - seconds
	 * This includes the singular forms as well
	 * @return string SQL datetime expression to query for a datetime (YYYY-MM-DD hh:mm:ss) which is the result of the addition
	 */
	abstract function datetimeIntervalClause($date, $interval);

	/**
	 * Function to return an SQL datetime expression that can be used with the adapter in use
	 * used for querying a datetime substraction
	 * @param string $date1, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name, e.g. '"SiteTree"."Created"'
	 * @param string $date2 to be substracted of $date1, can be either 'now', literal datetime like '1973-10-14 10:30:00' or field name, e.g. '"SiteTree"."Created"'
	 * @return string SQL datetime expression to query for the interval between $date1 and $date2 in seconds which is the result of the substraction
	 */
	abstract function datetimeDifferenceClause($date1, $date2);
	
	/*
	 * Does this database support transactions?
	 * 
	 * @return boolean
	 */
	abstract function supportsTransactions();
	
	/*
	 * Start a prepared transaction
	 * See http://developer.postgresql.org/pgdocs/postgres/sql-set-transaction.html for details on transaction isolation options
	 */
	abstract function transactionStart($transaction_mode=false, $session_characteristics=false);

	/*
	 * Create a savepoint that you can jump back to if you encounter problems
	 */
	abstract function transactionSavepoint($savepoint);

	/*
	 * Rollback or revert to a savepoint if your queries encounter problems
	 * If you encounter a problem at any point during a transaction, you may
	 * need to rollback that particular query, or return to a savepoint
	 */
	abstract function transactionRollback($savepoint=false);

	/*
	 * Commit everything inside this transaction so far
	 */
	abstract function transactionEnd();

	/**
	 * Determines if the used database supports application-level locks,
	 * which is different from table- or row-level locking.
	 * See {@link getLock()} for details.
	 * 
	 * @return boolean
	 */
	function supportsLocks() {
		return false;
	}
	
	/**
	 * Returns if the lock is available.
	 * See {@link supportsLocks()} to check if locking is generally supported.
	 * 
	 * @return Boolean
	 */
	function canLock($name) {
		return false;
	}
	
	/** 
	 * Sets an application-level lock so that no two processes can run at the same time,
	 * also called a "cooperative advisory lock".
	 * 
	 * Return FALSE if acquiring the lock fails; otherwise return TRUE, if lock was acquired successfully.
	 * Lock is automatically released if connection to the database is broken (either normally or abnormally),
	 * making it less prone to deadlocks than session- or file-based locks.
	 * Should be accompanied by a {@link releaseLock()} call after the logic requiring the lock has completed.
	 * Can be called multiple times, in which case locks "stack" (PostgreSQL, SQL Server),
	 * or auto-releases the previous lock (MySQL).
	 * 
	 * Note that this might trigger the database to wait for the lock to be released, delaying further execution.
	 * 
	 * @param String
	 * @param Int Timeout in seconds
	 * @return Boolean
	 */
	function getLock($name, $timeout = 5) {
		return false;
	}
	
	/** 
	 * Remove an application-level lock file to allow another process to run 
	 * (if the execution aborts (e.g. due to an error) all locks are automatically released).
	 * 
	 * @param String
	 * @return Boolean
	 */
	function releaseLock($name) {
		return false;
	}
}

/**
 * Abstract query-result class.
 * Once again, this should be subclassed by an actual database implementation.  It will only
 * ever be constructed by a subclass of SS_Database.  The result of a database query - an iteratable object that's returned by DB::SS_Query
 *
 * Primarily, the SS_Query class takes care of the iterator plumbing, letting the subclasses focusing
 * on providing the specific data-access methods that are required: {@link nextRecord()}, {@link numRecords()}
 * and {@link seek()}
 * @package framework
 * @subpackage model
 */
abstract class SS_Query implements Iterator {
	/**
	 * The current record in the interator.
	 * @var array
	 */
	private $currentRecord = null;
	
	/**
	 * The number of the current row in the interator.
	 * @var int
	 */
	private $rowNum = -1;
	
	/**
	 * Flag to keep track of whether iteration has begun, to prevent unnecessary seeks
	 */
	private $queryHasBegun = false;

	/**
	 * Return an array containing all the values from a specific column. If no column is set, then the first will be
	 * returned
	 *
	 * @param string $column
	 * @return array
	 */
	public function column($column = null) {
		$result = array();
		
		while($record = $this->next()) {
			if($column) $result[] = $record[$column];
			else $result[] = $record[key($record)];
		}

		return $result;
	}

	/**
	 * Return an array containing all values in the leftmost column, where the keys are the
	 * same as the values.
	 * @return array
	 */
	public function keyedColumn() {
		$column = array();
		foreach($this as $record) {
			$val = $record[key($record)];
			$column[$val] = $val;
		}
		return $column;
	}

	/**
	 * Return a map from the first column to the second column.
	 * @return array
	 */
	public function map() {
		$column = array();
		foreach($this as $record) {
			$key = reset($record);
			$val = next($record);
			$column[$key] = $val;
		}
		return $column;
	}

	/**
	 * Returns the next record in the iterator.
	 * @return array
	 */
	public function record() {
		return $this->next();
	}

	/**
	 * Returns the first column of the first record.
	 * @return string
	 */
	public function value() {
		$record = $this->next();
		if($record) return $record[key($record)];
	}

	/**
	 * Return an HTML table containing the full result-set
	 */
	public function table() {
		$first = true;
		$result = "<table>\n";
		
		foreach($this as $record) {
			if($first) {
				$result .= "<tr>";
				foreach($record as $k => $v) {
					$result .= "<th>" . Convert::raw2xml($k) . "</th> ";
 				}
				$result .= "</tr> \n";
			}

			$result .= "<tr>";
			foreach($record as $k => $v) {
				$result .= "<td>" . Convert::raw2xml($v) . "</td> ";
			}
			$result .= "</tr> \n";
			
			$first = false;
		}
		$result .= "</table>\n";
		
		if($first) return "No records found";
		return $result;
	}
	
	/**
	 * Iterator function implementation. Rewind the iterator to the first item and return it.
	 * Makes use of {@link seek()} and {@link numRecords()}, takes care of the plumbing.
	 * @return array
	 */
	public function rewind() {
		if($this->queryHasBegun && $this->numRecords() > 0) {
			$this->queryHasBegun = false;
			return $this->seek(0);
		}
	}

	/**
	 * Iterator function implementation. Return the current item of the iterator.
	 * @return array
	 */
	public function current() {
		if(!$this->currentRecord) {
			return $this->next();
		} else {
			return $this->currentRecord;
		}
	}

	/**
	 * Iterator function implementation. Return the first item of this iterator.
	 * @return array
	 */
	public function first() {
		$this->rewind();
		return $this->current();
	}

	/**
	 * Iterator function implementation. Return the row number of the current item.
	 * @return int
	 */
	public function key() {
		return $this->rowNum;
	}

	/**
	 * Iterator function implementation. Return the next record in the iterator.
	 * Makes use of {@link nextRecord()}, takes care of the plumbing.
	 * @return array
	 */
	public function next() {
		$this->queryHasBegun = true;
		$this->currentRecord = $this->nextRecord();
		$this->rowNum++;
		return $this->currentRecord;
	}

	/**
	 * Iterator function implementation. Check if the iterator is pointing to a valid item.
	 * @return boolean
	 */
	public function valid() {
		if(!$this->queryHasBegun) $this->next();
	 	return $this->currentRecord !== false;
	}

	/**
	 * Return the next record in the query result.
	 * @return array
	 */
	abstract function nextRecord();

	/**
	 * Return the total number of items in the query result.
	 * @return int
	 */
	abstract function numRecords();

	/**
	 * Go to a specific row number in the query result and return the record.
	 * @param int $rowNum Tow number to go to.
	 * @return array
	 */
	abstract function seek($rowNum);
}


