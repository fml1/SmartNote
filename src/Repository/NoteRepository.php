<?php

namespace App\Repository;

use App\Entity\Sphinx\Note;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 */
class NoteRepository extends ServiceEntityRepository
{
    /**
     * NoteRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * @param mixed $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return object|void|null
     * @throws DBALException
     * @throws ExceptionInterface
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        $stmt = $this->getEntityManager()
            ->getConnection()
            ->prepare(
                "SELECT * FROM " . $this->_class->getTableName() . " WHERE id = :id");
        $stmt->bindValue('id', $id, ParameterType::INTEGER);
        $stmt->execute();
        $res = $stmt->fetch();

        if ($res !== false) {
            $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
            $serializer = new Serializer([new PropertyNormalizer(), $normalizer]);

            return $serializer->denormalize($res, Note::class);
        }
        return;
    }

}
