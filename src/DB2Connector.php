<?php

namespace djolecodes\DB2Driver;

use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class DB2Connector extends Connector implements ConnectorInterface
{
    public function connect(array $config): \PDO
    {
        $connection = $this->createConnection(
            $this->getDsn($config),
            $config,
            $this->getOptions($config)
        );

        if (isset($config['schema']) && $config['schema'] !== '') {
            $schema = $config['schema'];

            $connection->prepare('set schema '.$schema)
                ->execute();
        }

        return $connection;
    }

    /**
     * Return properly formatted dsn from config
     */
    public function getDsn(array $config): string
    {
        if ($config['use_ibm_driver']) {
            // Use the IBM driver
            $dsnParts = [
                'ibm:DRIVER='.$config['driverName'],
                'HOST='.$config['host'],
                'PORT='.$config['port'],
                'DATABASE='.$config['database'],
                $config['username'],
                $config['password'],
            ];
        } else {
            // Use the ODBC driver
            $dsnParts = [
                'odbc:DRIVER='.$config['driverName'],
                'System='.$config['host'],
                'Port='.$config['port'],
                'Database='.$config['database'],
                'UserID='.$config['username'],
                'Password='.$config['password'],
            ];
        }

        // Include ODBC Keywords if present
        if (array_key_exists('odbc_keywords', $config)) {
            $keywords = [];

            foreach ($config['odbc_keywords'] as $key => $value) {
                $keywords[] = $key.'='.$value;
            }

            $dsnParts = array_merge($dsnParts, $keywords);
        }

        return implode(';', $dsnParts);
    }
}
