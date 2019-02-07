<?php
declare(strict_types=1);

namespace Webiik\Database;

class Database
{
    /**
     * @var array
     */
    private $database = [];

    /**
     * @param string $name
     * @param string $driver
     * @param string $host
     * @param string $nameDB
     * @param string $user
     * @param string $password
     * @param array $options
     * @param array $execute
     */
    public function add(
        string $name,
        string $driver,
        string $host,
        string $nameDB,
        string $user,
        string $password,
        array $options = [],
        array $execute = []
    ): void {
        $this->database[$name] = [
            'config' => [
                $driver,
                $host,
                $nameDB,
                $user,
                $password,
                $options,
            ],
            'exec' => $execute,
            'pdo' => null,
        ];
    }

    /**
     * @param string $name
     * @return \PDO
     */
    public function connect(string $name = ''): \PDO
    {
        if (!$name) {
            $name = $this->getDefaultDatabaseName();
        }

        if ($this->database[$name]['pdo'] === null) {
            $this->database[$name]['pdo'] = new \PDO(
                $this->database[$name]['config'][0] .
                ':host=' . $this->database[$name]['config'][1] .
                ';dbname=' . $this->database[$name]['config'][2],
                $this->database[$name]['config'][3],
                $this->database[$name]['config'][4],
                $this->database[$name]['config'][5]
            );
        }

        foreach ($this->database[$name]['exec'] as $row) {
            $q = $this->database[$name]['pdo']->prepare($row);
            $q->execute();
        }

        return $this->database[$name]['pdo'];
    }

    /**
     * @param string $name
     */
    public function disconnect(string $name = ''): void
    {
        if (!$name) {
            $name = $this->getDefaultDatabaseName();
        }
        $this->database[$name]['pdo'] = null;
    }

    /**
     * @return string
     */
    private function getDefaultDatabaseName(): string
    {
        $name = '';
        foreach ($this->database as $name => $arr) {
            break;
        }
        return $name;
    }
}
