<?php
/**
 * Standalone Waterways Guide Migration Script
 *
 * Migrates data from old tbl* tables on a remote database
 * to the new Joomla 5 waterways_guide_* tables.
 *
 * BEFORE RUNNING:
 * 1. Edit the $config array below with your live database credentials
 * 2. Make sure your live database allows remote connections
 *
 * Usage (from Joomla root):
 *   cd /path/to/joomla
 *   php components/com_waterways_guide/cli/migrate_guides.php
 *
 * Or with explicit Joomla path:
 *   php migrate_guides.php --joomla-root=/path/to/joomla
 */

// =============================================================================
// CONFIGURATION - Edit these values for your source (live) database
// =============================================================================

$config = [
    'source' => [
        'host'     => 'your-live-server.com',  // Remote database host
        'port'     => 3306,
        'database' => 'your_database_name',     // Remote database name
        'user'     => 'your_db_user',           // Remote database user
        'password' => 'your_db_password',       // Remote database password
        'prefix'   => '',                       // Table prefix on source (usually empty for tbl* tables)
    ],
    'tables' => [
        'tblGuides'         => '#__waterways_guide',
        'tblCountry'        => '#__waterways_guide_country',
        'tblGuidesRequests' => '#__waterways_guide_requests',
        'tblChangeLog'      => '#__waterways_guide_changelog',
        'tblServices'       => '#__waterways_guide_services',
    ],
    'batch_size' => 500,     // Records per batch
    'truncate'   => false,   // Set to true to clear destination tables first
];

// =============================================================================
// DO NOT EDIT BELOW THIS LINE
// =============================================================================

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "==============================================\n";
echo "Waterways Guide Migration Script\n";
echo "==============================================\n\n";

// Find Joomla root
$joomlaRoot = null;

// Check command line argument
foreach ($argv as $arg) {
    if (strpos($arg, '--joomla-root=') === 0) {
        $joomlaRoot = substr($arg, 14);
        break;
    }
}

// Try to find Joomla root automatically
if (!$joomlaRoot) {
    $possibleRoots = [
        dirname(__DIR__, 3),                    // Standard component install: components/com_waterways_guide/cli/
        dirname(__DIR__, 5),                    // Dev location: waterways-guide/site/cli/
        getcwd(),                               // Current working directory
    ];

    foreach ($possibleRoots as $root) {
        if (file_exists($root . '/configuration.php')) {
            $joomlaRoot = $root;
            break;
        }
    }
}

if (!$joomlaRoot || !file_exists($joomlaRoot . '/configuration.php')) {
    die("ERROR: Could not find Joomla configuration.php\n" .
        "Please run from Joomla root or use: php migrate_guides.php --joomla-root=/path/to/joomla\n");
}

echo "Joomla root: {$joomlaRoot}\n\n";

// Load Joomla configuration
require_once $joomlaRoot . '/configuration.php';
$jConfig = new JConfig();

// Get destination database prefix
$destPrefix = $jConfig->dbprefix;

echo "Source database: {$config['source']['database']}@{$config['source']['host']}\n";
echo "Destination database: {$jConfig->db}@{$jConfig->host}\n";
echo "Destination table prefix: {$destPrefix}\n\n";

// Replace #__ with actual prefix in table mapping
$tableMapping = [];
foreach ($config['tables'] as $source => $dest) {
    $tableMapping[$source] = str_replace('#__', $destPrefix, $dest);
}

echo "Table mapping:\n";
foreach ($tableMapping as $src => $dst) {
    echo "  {$src} => {$dst}\n";
}
echo "\n";

// Connect to source database
echo "Connecting to source database...\n";
try {
    $sourceDb = new mysqli(
        $config['source']['host'],
        $config['source']['user'],
        $config['source']['password'],
        $config['source']['database'],
        $config['source']['port']
    );

    if ($sourceDb->connect_error) {
        throw new Exception($sourceDb->connect_error);
    }

    $sourceDb->set_charset('utf8mb4');
    echo "  Connected!\n";
} catch (Exception $e) {
    die("ERROR: Failed to connect to source database: " . $e->getMessage() . "\n");
}

// Connect to destination database
echo "Connecting to destination database...\n";
try {
    $destDb = new mysqli(
        $jConfig->host,
        $jConfig->user,
        $jConfig->password,
        $jConfig->db
    );

    if ($destDb->connect_error) {
        throw new Exception($destDb->connect_error);
    }

    $destDb->set_charset('utf8mb4');
    echo "  Connected!\n";
} catch (Exception $e) {
    die("ERROR: Failed to connect to destination database: " . $e->getMessage() . "\n");
}

echo "\n";

// Confirmation
echo "WARNING: This will migrate data from the source database to the destination.\n";
if ($config['truncate']) {
    echo "         Destination tables WILL be truncated first!\n";
}
echo "\nType 'yes' to proceed: ";

$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) !== 'yes') {
    die("\nMigration cancelled.\n");
}

echo "\n";

// Migration
$totalMigrated = 0;
$errors = [];

foreach ($tableMapping as $sourceTable => $destTable) {
    echo "==============================================\n";
    echo "Migrating: {$sourceTable} => {$destTable}\n";
    echo "==============================================\n";

    // Check source table exists
    $result = $sourceDb->query("SHOW TABLES LIKE '{$sourceTable}'");
    if ($result->num_rows === 0) {
        echo "  WARNING: Source table does not exist, skipping\n\n";
        continue;
    }

    // Count source records
    $result = $sourceDb->query("SELECT COUNT(*) as cnt FROM `{$sourceTable}`");
    $row = $result->fetch_assoc();
    $sourceCount = (int) $row['cnt'];
    echo "  Source records: {$sourceCount}\n";

    if ($sourceCount === 0) {
        echo "  No records to migrate\n\n";
        continue;
    }

    // Truncate if configured
    if ($config['truncate']) {
        echo "  Truncating destination table...\n";
        $destDb->query("TRUNCATE TABLE `{$destTable}`");
    }

    // Get columns
    $result = $sourceDb->query("SHOW COLUMNS FROM `{$sourceTable}`");
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    // Migrate in batches
    $offset = 0;
    $migrated = 0;
    $skipped = 0;

    while ($offset < $sourceCount) {
        $result = $sourceDb->query("SELECT * FROM `{$sourceTable}` LIMIT {$offset}, {$config['batch_size']}");

        while ($row = $result->fetch_assoc()) {
            $columnNames = [];
            $values = [];

            foreach ($row as $col => $val) {
                $columnNames[] = "`{$col}`";
                $values[] = $val === null ? 'NULL' : "'" . $destDb->real_escape_string($val) . "'";
            }

            $sql = "INSERT INTO `{$destTable}` (" . implode(',', $columnNames) . ") VALUES (" . implode(',', $values) . ")";

            if ($destDb->query($sql)) {
                $migrated++;
            } else {
                // Check if it's a duplicate key error
                if ($destDb->errno === 1062) {
                    $skipped++;
                } else {
                    $errors[] = "{$sourceTable}: " . $destDb->error;
                }
            }
        }

        $offset += $config['batch_size'];
        $progress = min(100, round(($offset / $sourceCount) * 100));
        echo "  Progress: {$progress}% ({$migrated} migrated";
        if ($skipped > 0) {
            echo ", {$skipped} skipped (duplicates)";
        }
        echo ")\r";
    }

    echo "\n  Completed: {$migrated} records migrated";
    if ($skipped > 0) {
        echo ", {$skipped} skipped";
    }
    echo "\n\n";

    $totalMigrated += $migrated;
}

// Summary
echo "==============================================\n";
echo "MIGRATION SUMMARY\n";
echo "==============================================\n";
echo "Total records migrated: {$totalMigrated}\n";

if (!empty($errors)) {
    echo "\nErrors encountered:\n";
    foreach (array_unique($errors) as $error) {
        echo "  - {$error}\n";
    }
}

echo "\nMigration completed!\n";

// Close connections
$sourceDb->close();
$destDb->close();
