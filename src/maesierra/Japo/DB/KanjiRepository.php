<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 10/09/2018
 * Time: 20:37
 */

namespace maesierra\Japo\DB;


use Doctrine\ORM\EntityManager;
use maesierra\Japo\Entity\KanjiCatalog;
use Monolog\Logger;

class KanjiRepository {

    /** @var  EntityManager */
    public $entityManager;

    /** @var  Logger */
    public $logger;

    /**
     * KanjiRepository constructor.
     * @param EntityManager $entityManager
     * @param Logger $logger
     */
    public function __construct($entityManager, $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @return KanjiCatalog[]
     */
    public function listCatalogs() {
        return $this->entityManager->getRepository(KanjiCatalog::class)->findAll();
    }


}