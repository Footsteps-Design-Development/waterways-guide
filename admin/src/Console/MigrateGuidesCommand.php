<?php
/**
 * Waterways Guide Migration CLI Command
 *
 * Migrates data from old tbl* tables on a remote database
 * to the new #__waterways_guide_* tables in the local Joomla database.
 *
 * Usage: php cli/joomla.php waterways:migrate
 */

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Console;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseDriver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateGuidesCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var string
     */
    protected static $defaultName = 'waterways:migrate';

    /**
     * Source database connection
     *
     * @var DatabaseDriver|null
     */
    private ?DatabaseDriver $sourceDb = null;

    /**
     * Table mapping: source => destination
     *
     * @var array
     */
    private array $tableMapping = [
        'tblGuides'         => '#__waterways_guide',
        'tblCountry'        => '#__waterways_guide_country',
        'tblGuidesRequests' => '#__waterways_guide_requests',
        'tblChangeLog'      => '#__waterways_guide_changelog',
        'tblServices'       => '#__waterways_guide_services',
    ];

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Migrate Waterways Guide data from old tbl* tables to new tables');
        $this->setHelp(
            <<<EOF
The <info>%command.name%</info> command migrates data from a remote database with old table names
to the local Joomla database with the new table structure.

<info>php %command.full_name% --host=example.com --database=olddb --user=dbuser --password=secret</info>

You can also specify which tables to migrate:
<info>php %command.full_name% --tables=guides,country</info>

Available table aliases: guides, country, requests, changelog, services
EOF
        );

        $this->addOption('host', null, InputOption::VALUE_REQUIRED, 'Source database host', 'localhost');
        $this->addOption('port', null, InputOption::VALUE_REQUIRED, 'Source database port', '3306');
        $this->addOption('database', null, InputOption::VALUE_REQUIRED, 'Source database name');
        $this->addOption('user', null, InputOption::VALUE_REQUIRED, 'Source database username');
        $this->addOption('password', null, InputOption::VALUE_REQUIRED, 'Source database password');
        $this->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'Source table prefix (if any)', '');
        $this->addOption('tables', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of tables to migrate (guides,country,requests,changelog,services)', 'all');
        $this->addOption('truncate', null, InputOption::VALUE_NONE, 'Truncate destination tables before migration');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be migrated without actually doing it');
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input  The input interface
     * @param OutputInterface $output The output interface
     *
     * @return int
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Waterways Guide Data Migration');

        // Validate required options
        $host = $input->getOption('host');
        $database = $input->getOption('database');
        $user = $input->getOption('user');
        $password = $input->getOption('password');

        if (!$database || !$user) {
            $io->error('You must specify --database and --user options');
            return 1;
        }

        $dryRun = $input->getOption('dry-run');
        $truncate = $input->getOption('truncate');

        if ($dryRun) {
            $io->note('DRY RUN MODE - No changes will be made');
        }

        // Connect to source database
        try {
            $this->sourceDb = $this->connectToSource(
                $host,
                (int) $input->getOption('port'),
                $database,
                $user,
                $password ?? ''
            );
            $io->success("Connected to source database: {$database}@{$host}");
        } catch (\Exception $e) {
            $io->error('Failed to connect to source database: ' . $e->getMessage());
            return 1;
        }

        // Get destination database
        $destDb = Factory::getContainer()->get('DatabaseDriver');

        // Determine which tables to migrate
        $tablesToMigrate = $this->getTablesToMigrate($input->getOption('tables'));

        if (empty($tablesToMigrate)) {
            $io->error('No valid tables specified for migration');
            return 1;
        }

        $io->section('Tables to migrate');
        $io->listing(array_map(fn($src, $dest) => "{$src} → {$dest}",
            array_keys($tablesToMigrate),
            array_values($tablesToMigrate)
        ));

        // Confirm migration
        if (!$dryRun && !$io->confirm('Do you want to proceed with the migration?', false)) {
            $io->warning('Migration cancelled');
            return 0;
        }

        $sourcePrefix = $input->getOption('prefix');
        $totalMigrated = 0;
        $errors = [];

        foreach ($tablesToMigrate as $sourceTable => $destTable) {
            $fullSourceTable = $sourcePrefix . $sourceTable;

            $io->section("Migrating: {$fullSourceTable} → {$destTable}");

            try {
                // Check if source table exists
                $sourceExists = $this->tableExists($this->sourceDb, $fullSourceTable);
                if (!$sourceExists) {
                    $io->warning("Source table '{$fullSourceTable}' does not exist, skipping");
                    continue;
                }

                // Count source records
                $sourceCount = $this->getRecordCount($this->sourceDb, $fullSourceTable);
                $io->text("Source records: {$sourceCount}");

                if ($sourceCount === 0) {
                    $io->text('No records to migrate');
                    continue;
                }

                // Truncate destination if requested
                if ($truncate && !$dryRun) {
                    $io->text('Truncating destination table...');
                    $destDb->truncateTable($destTable);
                }

                if ($dryRun) {
                    $io->text("[DRY RUN] Would migrate {$sourceCount} records");
                    $totalMigrated += $sourceCount;
                    continue;
                }

                // Migrate data in batches
                $migrated = $this->migrateTable(
                    $this->sourceDb,
                    $destDb,
                    $fullSourceTable,
                    $destTable,
                    $io
                );

                $totalMigrated += $migrated;
                $io->success("Migrated {$migrated} records");

            } catch (\Exception $e) {
                $errors[] = "{$sourceTable}: " . $e->getMessage();
                $io->error("Error migrating {$sourceTable}: " . $e->getMessage());
            }
        }

        // Summary
        $io->section('Migration Summary');
        $io->text("Total records migrated: {$totalMigrated}");

        if (!empty($errors)) {
            $io->warning('Some tables had errors:');
            $io->listing($errors);
            return 1;
        }

        $io->success('Migration completed successfully!');
        return 0;
    }

    /**
     * Connect to source database
     */
    private function connectToSource(string $host, int $port, string $database, string $user, string $password): DatabaseDriver
    {
        $options = [
            'driver'   => 'mysqli',
            'host'     => $host,
            'port'     => $port,
            'user'     => $user,
            'password' => $password,
            'database' => $database,
            'prefix'   => '',
            'charset'  => 'utf8mb4',
        ];

        $db = DatabaseDriver::getInstance($options);
        $db->connect();

        return $db;
    }

    /**
     * Get tables to migrate based on input
     */
    private function getTablesToMigrate(string $tables): array
    {
        if ($tables === 'all') {
            return $this->tableMapping;
        }

        $aliases = [
            'guides'    => 'tblGuides',
            'country'   => 'tblCountry',
            'requests'  => 'tblGuidesRequests',
            'changelog' => 'tblChangeLog',
            'services'  => 'tblServices',
        ];

        $result = [];
        $requested = array_map('trim', explode(',', strtolower($tables)));

        foreach ($requested as $alias) {
            if (isset($aliases[$alias])) {
                $sourceTable = $aliases[$alias];
                $result[$sourceTable] = $this->tableMapping[$sourceTable];
            }
        }

        return $result;
    }

    /**
     * Check if a table exists
     */
    private function tableExists(DatabaseDriver $db, string $table): bool
    {
        try {
            $db->setQuery("SHOW TABLES LIKE " . $db->quote($table));
            return $db->loadResult() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get record count from a table
     */
    private function getRecordCount(DatabaseDriver $db, string $table): int
    {
        $db->setQuery("SELECT COUNT(*) FROM `{$table}`");
        return (int) $db->loadResult();
    }

    /**
     * Migrate data from source to destination table
     */
    private function migrateTable(
        DatabaseDriver $sourceDb,
        DatabaseDriver $destDb,
        string $sourceTable,
        string $destTable,
        SymfonyStyle $io
    ): int {
        $batchSize = 500;
        $offset = 0;
        $totalMigrated = 0;

        // Get column names from source table
        $sourceDb->setQuery("SHOW COLUMNS FROM `{$sourceTable}`");
        $columns = array_column($sourceDb->loadAssocList(), 'Field');

        $io->progressStart($this->getRecordCount($sourceDb, $sourceTable));

        while (true) {
            // Fetch batch from source
            $sourceDb->setQuery("SELECT * FROM `{$sourceTable}` LIMIT {$offset}, {$batchSize}");
            $rows = $sourceDb->loadAssocList();

            if (empty($rows)) {
                break;
            }

            // Insert into destination
            foreach ($rows as $row) {
                $query = $destDb->getQuery(true)
                    ->insert($destDb->quoteName($destTable));

                $columnNames = [];
                $values = [];

                foreach ($row as $column => $value) {
                    $columnNames[] = $destDb->quoteName($column);
                    $values[] = $value === null ? 'NULL' : $destDb->quote($value);
                }

                $query->columns($columnNames)
                    ->values(implode(',', $values));

                try {
                    $destDb->setQuery($query)->execute();
                    $totalMigrated++;
                } catch (\Exception $e) {
                    // Handle duplicate key errors gracefully for re-runs
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        throw $e;
                    }
                }

                $io->progressAdvance();
            }

            $offset += $batchSize;
        }

        $io->progressFinish();

        return $totalMigrated;
    }
}
