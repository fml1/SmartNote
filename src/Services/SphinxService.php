<?php


namespace App\Services;

use Javer\SphinxBundle\Logger\SphinxLogger;
use Javer\SphinxBundle\Sphinx\Manager;
use Javer\SphinxBundle\Sphinx\Query;
use PDO;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class SphinxService extends Manager
{
    const INDEX = 'note_1';
    private $conn;

    function __construct()
    {
        $logger = new SphinxLogger();
        $host = '127.0.0.1';
        $port = '9306';
        parent::__construct($logger, $host, $port);
        $this->conn = $this->getConnection();
    }

    /**
     * Базовые настройки
     * @return Query
     */
    public function createNoteQuery()
    {
        return $this
            ->createQuery()
            ->select('*')
            ->from('note_1')
            ->orderBy('attr_order_id', 'ASC')
            ->option('max_matches', 1000);
    }

    /**
     * Получить свободный id для вставки
     * @param $index
     * @return int
     */
    public function getNextId($index)
    {
        $query = "SELECT MAX(id) FROM " . $index;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();

        return (isset($row[0])) ? $row[0] + 1 : 1;
    }

    /**
     * Возвращает запрос вставки
     * @param $index
     * @param $fields
     * @return string
     */
    public function prepareInsert($index, $fields)
    {

        $fieldNames = implode(",", array_keys($fields));
        $fieldValues = "";
        foreach (array_keys($fields) as $name) {
            if (strlen($fieldValues) > 0)
                $fieldValues .= ", ";
            $fieldValues .= ':' . $name;
        }
        return "INSERT INTO " . $index . " ({$fieldNames}) VALUES({$fieldValues})";
    }

    /**
     * Замена
     * @param $index
     * @param $fields
     * @return string
     */
    public function prepareReplace($index, $fields)
    {

        $fieldNames = implode(",", array_keys($fields));
        $fieldValues = "";
        foreach (array_keys($fields) as $name) {
            if (strlen($fieldValues) > 0)
                $fieldValues .= ", ";
            $fieldValues .= ':' . $name;
        }
        return "REPLACE INTO " . $index . " ({$fieldNames}) VALUES({$fieldValues})";
    }

    /**
     * UPDATE
     * @param $index
     * @param $fields
     * @return string
     */
    public function prepareUpdate($index, $fields)
    {
        $fieldSet = "";
        foreach ($fields as $name => $value) {
            if ($name == 'id')
                continue;

            if (strlen($fieldSet) > 0)
                $fieldSet .= ", ";

            $fieldSet .= $name . ' = :' . $name;
        }

        return "UPDATE " . $index . " set {$fieldSet} WHERE id = :id";
    }

    /**
     * Вставить запись в rt индекс
     * Не переданные поля в записи обнуляются
     * @param string $index
     * @param array $fields
     * @return array
     */
    public function insert($index = "", $fields = [])
    {
        $fields['id'] = $this->getNextId($index);

        $stmt = $this->conn->prepare(
            $this->prepareInsert($index, $fields)
        );

        $this->bindValues($stmt, $fields);

        $stmt->execute();

        return $fields;
    }

    /**
     * Обновление fields['id'] записи на имеющеся поля
     * @param string $index
     * @param $fields
     */
    public function update($index = "", $fields)
    {
        $stmt = $this->conn->prepare(
            $this->prepareUpdate($index, $fields)
        );

        $this->bindValues($stmt, $fields);

        $stmt->execute();
    }

    /**
     * @param string $index
     * @param array $fields
     * @return null |null
     */
    public function replace($index = "", $fields = [])
    {
        $stmt = $this->conn->prepare(
            $this->prepareReplace($index, $fields)
        );
        $this->bindValues($stmt, $fields);

        $stmt->execute();

        return $fields;
    }

    /**
     * @param $stmt
     * @param $fields
     */
    public function bindValues($stmt, $fields)
    {
        foreach ($fields as $name => $value) {
            if (is_null($value))
                $value = "";
            if (is_numeric($value))
                $stmt->bindValue($name, $value, PDO::PARAM_INT);
            else
                $stmt->bindValue($name, $value);
        }
    }

    /**
     * @param string $index
     * @param int $id
     * @return bool
     */
    public function remove($index = "", $id = 0)
    {
        $stmt = $this->conn->prepare("DELETE FROM " . $index . " WHERE id = :id");
        $stmt->bindValue('id', $id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            $res['success'] = true;
        } catch (\Exception $e) {
            $res['success'] = false;
            $res['error'] = $e->getMessage();
        }
        return $res;
    }
}