<?php

namespace Framework\Database;

use Exception;
use ReflectionClass;

abstract class AbstractModel
{
    protected $dbConnection;
    protected $table;
    protected $entityClass;
    protected $assocArrayMode = false;

    public function __construct(DatabaseInterface $db, $assocArrayMode = null)
    {
        $this->dbConnection = $db->getConnection();
        $this->assocArrayMode = $assocArrayMode;
        $this->setTable();
        $this->setEntityClass();
    }

    public function setAssocArrayMode(bool $assocArrayMode)
    {
        $this->assocArrayMode = $assocArrayMode;
    }

    public function save($data)
    {
        $sql = "INSERT INTO $this->table (";
        foreach ($data as $key => $value) {
            $sql .= "$key, ";
        }
        $sql = rtrim($sql, ', ');
        $sql .= ') VALUES (';
        foreach ($data as $key => $value) {
            $sql .= ":$key, ";
        }
        $sql = rtrim($sql, ', ');
        $sql .= ')';
        try {
            $stmt = $this->dbConnection->prepare($sql);
            $res = $stmt->execute($data);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            exit;
        }
        if ($res) {
            return $this->find($this->dbConnection->lastInsertId());
        }
        return null;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->dbConnection->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function update($id, $data)
    {
        $data = array_merge($data, ['id' => $id]);
        $sql = "UPDATE $this->table SET ";
        foreach ($data as $key => $value) {
            $sql .= "$key = :$key, ";
        }
        $sql = rtrim($sql, ', ');
        $sql .= " WHERE id = :id";
        $stmt = $this->dbConnection->prepare($sql);
        return $stmt->execute($data);
    }

    public function findByQuery(array $query, $assocArrayMode = null, $number = null, $offset = null)
    {
        $sql = "SELECT * FROM $this->table";

        if (!empty($query)) {
            $sql .= " WHERE ";

            $sql .= implode(' AND ', array_map(function ($key) use ($query) {
                $cond = "";
                // add the key to the condition
                if (isset($query[$key]['keyFn'])) {
                    $cond = "{$query[$key]['keyFn']}($key";
                    if (isset($query[$key]['keyFnArgs'])) {
                        $cond = ', ';
                        $cond .= implode(', ', $query[$key]['keyFnArgs']);
                    }
                    $cond .= ') ';
                } else {
                    $cond = "$key ";
                }
                // add the operator to the condition
                $cond = isset($query[$key]['op']) ? $cond . $query[$key]['op'] : $cond . '=';
                // add the key to the condition
                $cond .= " :$key";
                return $cond;
            }, array_keys($query)));
        }

        if ($number) {
            $sql .= " LIMIT $number";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        $stmt = $this->dbConnection->prepare($sql);
        $values = [];
        foreach ($query as $key => $params) {
            $values[$key] = $params["value"];
        }

        if (empty($values)) {
            $stmt->execute();
        } else {
            $stmt->execute($values);
        }

        $dbData = $stmt->fetchAll();
        return $this->getFormatedRecords($dbData, $assocArrayMode);
    }

    public function findOneByQuery(array $query, $assocArrayMode = null)
    {
        return $this->findByQuery($query, $assocArrayMode, 1)[0] ?? null;
    }

    public function findAll($asAssocArray = null)
    {
        return $this->findByQuery([], $asAssocArray);
    }

    public function find($id, $assocArrayMode = null)
    {
        return $this->findByQuery(['id' => ['value' => $id]], $assocArrayMode, 1)[0] ?? null;
    }

    public function findByColumn($column, $value, $assocArrayMode = null, $number = null, $offset = null)
    {
        return $this->findByQuery(
            [$column => ['value' => $value]],
            $assocArrayMode,
            $number,
            $offset
        );
    }

    public function getOneByColumn($column, $value, $assocArrayMode = null)
    {
        return $this->findByColumn($column, $value, $assocArrayMode, 1)[0] ?? null;
    }

    protected function createEntity($dbData)
    {
        return new $this->entityClass($dbData);
    }

    protected function getFormatedRecords($dbData, $asAssocArray = null)
    {
        if (!$dbData) {
            return [];
        }

        $entities = [];
        foreach ($dbData as $data) {
            $record = $this->formatRecord($data, $asAssocArray);
            $id = $data['id'];
            if ($id && !isset($entities[$id])) {
                // record has not been added to the array yet
                $entities[$id] = $record;
            } elseif ($id) {
                // the index is already taken. we move the current record to the end of the array
                $entities[] = $entities[$id];
                $entities[$id] = $record;
            } else {
                $entities[] = $record;
            }
        }
        return $entities;
    }

    protected function formatRecord($data, $asAssocArray = null)
    {
        if ($this->assocArrayMode || $asAssocArray) {
            return $this->createAssoc($data);
        }
        return $this->createEntity($data);
    }

    protected function createAssoc($data)
    {
        $assoc = [];
        foreach ($data as $key => $value) {
            if (!is_numeric($key)) {
                $assoc[$key] = $value;
            }
        }
        return $assoc;
    }

    protected function setTable()
    {
        $className = $this->getMainClassName();
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        $this->table = str_ends_with($table, 'y') ? substr($table, 0, -1) . 'ies' : $table . 's';
    }

    protected function setEntityClass()
    {
        $this->entityClass = 'App\\Entities\\' . $this->getMainClassName();
    }

    protected function getMainClassName()
    {
        $className = (new ReflectionClass($this))->getShortName();
        return str_replace('Model', '', $className);
    }
}
