<?php

require dirname(__DIR__) . '/bootstrap.php';

$connection = \Cake\Datasource\ConnectionManager::get('default');

$columnResult = $connection->execute("SHOW COLUMNS FROM conventionseasonevents LIKE 'current_record'")->fetchAll('assoc');
if (empty($columnResult)) {
    $connection->execute('ALTER TABLE conventionseasonevents ADD COLUMN current_record VARCHAR(255) NULL AFTER qualifying_score');
}

echo "Migration completed successfully.\n";