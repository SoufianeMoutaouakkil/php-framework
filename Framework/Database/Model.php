<?php

namespace Framework\Database;

use Exception;
use ReflectionClass;

class Model
{
    protected $db;
    protected $table;
    protected $entityClass;
    protected $assocArrayMode = false;

    public function __construct($db, $assocArrayMode = false)
    {
        $this->db = $db;
        $this->assocArrayMode = $assocArrayMode;
        $this->setTable();
        $this->setEntityClass();
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
            $stmt = $this->db->prepare($sql);
            $res = $stmt->execute($data);
        } catch (Exception $e) {
            var_dump($e->getMessage());
            exit;
        }
        if ($res) {
            return $this->find($this->db->lastInsertId());
        }
        return null;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM $this->table WHERE id = :id";
        $stmt = $this->db->prepare($sql);
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
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function findAll($asAssocArray = null)
    {
        $sql = "SELECT * FROM $this->table";
        $stmt = $this->db->query($sql);
        $dbData = $stmt->fetchAll();

        return $this->getFormatedRecords($dbData, $asAssocArray);
    }

    public function find($id, $assocArrayMode = null)
    {
        $sql = "SELECT * FROM $this->table WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $dbData = $stmt->fetch();
        if ($dbData) {
            return $this->formatRecord($dbData, $assocArrayMode);
        }
        return null;
    }

    public function findByColumn($column, $value, $assocArrayMode = null, $number = null, $offset = null)
    {
        $sql = "SELECT * FROM $this->table WHERE $column = :$column";
        $sql .= $number ? " LIMIT $number" : '';
        $sql .= $offset ? " OFFSET $offset" : '';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$column => $value]);
        $dbData = $stmt->fetchAll();

        return $this->getFormatedRecords($dbData, $assocArrayMode);
    }

    public function getOneByColumn($column, $value, $assocArrayMode = null)
    {
        return $this->findByColumn($column, $value, $assocArrayMode, 1)[0] ?? null;
    }

    public function findBy(array $query, $assocArrayMode = null, $number = null, $offset = null)
    {
        $sql = "SELECT * FROM $this->table WHERE ";

        $sql .= implode(' AND ', array_map(function ($key) use ($query) {
            return "$key " . $query[$key]['op'] . " :$key";
        }, array_keys($query)));

        if ($number) {
            $sql .= " LIMIT $number";
        }
        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        $stmt = $this->db->prepare($sql);
        $values = [];
        foreach ($query as $key => $params) {
            $values[$key] = $params["value"];
        }
        $stmt->execute($values);
        $dbData = $stmt->fetchAll();
        return $this->getFormatedRecords($dbData, $assocArrayMode);
    }

    public function findOneBy(array $query, $assocArrayMode = null)
    {
        return $this->findBy($query, $assocArrayMode, 1)[0] ?? null;
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
        $this->entityClass = 'entities\\' . $this->getMainClassName();
    }

    protected function getMainClassName()
    {
        $className = (new ReflectionClass($this))->getShortName();
        return str_replace('Model', '', $className);
    }
}
