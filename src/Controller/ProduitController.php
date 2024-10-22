<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_')]
class ProduitController extends AbstractController
{

    // *[CREATE]*
    
    #[Route('/produits', methods: "POST")]
    #[IsGranted('ROLE_ADMIN')]
    public function create(#[MapRequestPayload(serializationContext:['groups' => ['produits.create']])] Produit $produit, EntityManagerInterface $entityManager)
    {
        $entityManager->persist($produit);
        $entityManager->flush();
        return $this->json($produit, 200, [], ['groups' => ['produits.show']]);
    }

    // *[READ]*

    #[Route('/produits', methods: "GET")]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function show(ProduitRepository $repository, Request $request)
    {
        $produit = $repository->findAll();

        // Uniquement les attributs concernés
        return $this->json($produit, 200, [], ['groups' => ['produits.show']]);
    }

    // Symfony automatically retrueves the data and pass it through the parameter
    #[Route("/produits/{id}", methods: "GET", requirements: ['id' => Requirement::DIGITS])]
    public function findById(Produit $produit)
    {
        return $this->json($produit, 200, [], ['groups' => ['produits.show']]);
    }

    // *[UPDATE]*
    #[Route('/produits/{id}', methods: "PUT")]
    public function update(int $id, Request $request, ProduitRepository $repository, EntityManagerInterface $entityManager, SerializerInterface $serialier)
    {
        // Récupérer le produit éxistant
        $produit = $repository->find($id);
        if(!$produit) throw new NotFoundHttpException("Produit non trouvé");

        // Modification des propriétés concernés dans $produit
        $updatedProduit = $serialier->deserialize(
            $request->getContent(),
            Produit::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $produit, 'groups' => ['produits.update']]
        );

        $entityManager->persist($updatedProduit);
        $entityManager->flush();

        return $this->json($produit, 200, [], ['groups' => ['produits.show']]);
    }

    // *[DELETE]*
    #[Route('/produits/{id}', methods: "DELETE")]
    public function delete(int $id, ProduitRepository $repository, EntityManagerInterface $entityManager)
    {
        $produit = $repository->find($id);
        if(!$produit) throw new NotFoundHttpException("Produit non trouvé");

        $entityManager->remove($produit);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
