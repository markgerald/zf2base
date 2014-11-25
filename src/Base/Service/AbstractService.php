<?php
namespace Base\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;


/**
 * Class AbstractService
 * @package Base\Service
 */
abstract class AbstractService
{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * @var
     */
    protected $entity;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param $entity
     * @param array $dadosArray
     * @return object
     */
    public function insert($entity = null, $dadosArray = null)
    {
        if (!is_null($dadosArray)) {
            $hydrator = new DoctrineHydrator($this->em);
            $entity = new $this->entity();
            $entity = $hydrator->hydrate($dadosArray, $entity);
        }

        $this->em->persist($entity);
        $this->em->flush();
        return $entity;

    }


    /**
     * @param object $entity
     * @param array $dadosArray
     * @return null|object
     */
    public function update($entity = null, $dadosArray = null)
    {
        if (!is_null($dadosArray)) {
            $hydrator = new DoctrineHydrator($this->em);
            $entity = $hydrator->hydrate($dadosArray, $entity);
        }

        $this->em->flush();
        return $entity;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($id)
    {
        $entity = $this->em->getReference($this->entity, $id);
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
            return $id;
        }
    }

}
