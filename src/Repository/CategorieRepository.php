<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository personnalisé pour l'entité Categorie.
 * Fournit des méthodes spécifiques pour gérer les catégories de formations.
 *
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    /**
     * Ajoute une nouvelle entité Categorie en base de données.
     * @param Categorie $entity
     */
    public function add(Categorie $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

/**
     * Supprime une entité Categorie de la base de données.
     * @param Categorie $entity
     */
    public function remove(Categorie $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
    
    /**
     * Récupère toutes les catégories associées aux formations d'une playlist donnée.
     * @param int $idPlaylist L'identifiant de la playlist.
     * @return Categorie[] Liste des catégories.
     */
    public function findAllForOnePlaylist($idPlaylist): array
    {
        return $this->createQueryBuilder('c')
                ->join('c.formations', 'f')
                ->join('f.playlist', 'p')
                ->where('p.id=:id')
                ->setParameter('id', $idPlaylist)
                ->orderBy('c.name', 'ASC')
                ->getQuery()
                ->getResult();
    }

    /**
     * Retourne toutes les catégories triées par nom.
     * @param string $ordre 'ASC' ou 'DESC'
     * @return Categorie[] Liste triée des catégories.
     */
    public function findAllOrderBy(string $ordre): array{
        return $this->createQueryBuilder('c')
            ->orderBy('c.name', $ordre)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche les catégories dont le nom contient une chaîne donnée.
     * Si la chaîne est vide, retourne toutes les catégories.
     * @param string $valeur La chaîne à rechercher.
     * @return Categorie[] Liste filtrée des catégories.
     */
    public function findByName(string $valeur): array{
        if ($valeur == "") {
            return $this->findAll();
        }

        return $this->createQueryBuilder('c')
            ->where('c.name LIKE :valeur')
            ->orderBy('c.name', 'ASC')
            ->setParameter('valeur', '%'.$valeur.'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une catégorie portant un nom donné existe déjà.
     * @param string $valeur Le nom à vérifier.
     * @return bool true si une catégorie existe déjà, false sinon.
     */
    public function existsByName(string $valeur): bool{
        $categorie = $this->createQueryBuilder('c')
            ->where('c.name = :valeur')
            ->setParameter('valeur', $valeur)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return count($categorie) != 0;
    }
}