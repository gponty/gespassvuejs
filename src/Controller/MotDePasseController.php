<?php

/*
 * g.ponty@dev-web.io
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\MotDePasse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MotDePasseController extends AbstractController
{
    private $entityManager;

    /**
     * ProjectController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Retourne la liste de tous les mots de passe.
     *
     * @Route("/passwords", name="password_liste")
     */
    public function index()
    {
        $password = $this->entityManager->getRepository(MotDePasse::class)->findAll();

        $jsonContent = $this->serializeObject($password);

        return new Response($jsonContent, Response::HTTP_OK);
    }

    /**
     * Sauvegarde un nouveau mot de passe.
     *
     * @return Response
     * @Route("/motdepasse/new", name="password_new")
     */
    public function newPassword(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        if ($content['titre']) {
            $pass = new MotDePasse();
            $pass->setTitre($content['titre']);
            if (isset($content['motDePasse'])) {
                $pass->setPassword($content['motDePasse']);
            }
            if (isset($content['username'])) {
                $pass->setUsername($content['username']);
            }
            if (isset($content['note'])) {
                $pass->setNote($content['note']);
            }
            if (isset($content['url'])) {
                $pass->setUrl($content['url']);
            }
            $this->entityManager->persist($pass);
            $this->entityManager->flush();

            // Serialize object into Json format
            $jsonContent = $this->serializeObject($pass);

            return new Response($jsonContent, Response::HTTP_OK);
        }

        return new Response('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Sauvegarde un mot de passe existant.
     *
     * @param MotDePasse $id
     *
     * @return Response
     * @Route("/motdepasse/save/{id}", name="password_edit")
     */
    public function savePassword(Request $request, $id)
    {
        $content = json_decode($request->getContent(), true);

        $pass = $this->entityManager->getRepository(MotDePasse::class)->findOneBy(['id' => $id]);

        if (null !== $pass && isset($content['titre'])) {
            $pass->setTitre($content['titre']);
            if (isset($content['motDePasse'])) {
                $pass->setPassword($content['motDePasse']);
            }
            if (isset($content['username'])) {
                $pass->setUsername($content['username']);
            }
            if (isset($content['note'])) {
                $pass->setNote($content['note']);
            }
            if (isset($content['url'])) {
                $pass->setUrl($content['url']);
            }
            $this->entityManager->flush();

            // Serialize object into Json format
            $jsonContent = $this->serializeObject($pass);

            return new Response($jsonContent, Response::HTTP_OK);
        }

        return new Response('Error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Supprime un mot de passe.
     *
     * @param MotDePasse $id
     *
     * @return Response
     * @Route("/motdepasse/delete/{id}", name="password_delete")
     */
    public function deletePassword(Request $request, $id)
    {
        $pass = $this->entityManager->getRepository(MotDePasse::class)->findOneBy(['id' => $id]);

        $this->entityManager->remove($pass);
        $this->entityManager->flush();

        return new Response('ok', Response::HTTP_OK);
    }

    /**
     * Génére un mot de passe aleatoire.
     *
     * @return Response
     * @Route("/motdepasse/generate", name="password_generate")
     */
    public function generatePassword(Request $request)
    {
        $mot_de_passe = '';

        $chaine = 'abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ023456789+@!$%?&';
        $longeur_chaine = \strlen($chaine);
        $nb_caractere = 8;
        for ($i = 1; $i <= $nb_caractere; ++$i) {
            $place_aleatoire = random_int(0, ($longeur_chaine - 1));
            $mot_de_passe .= $chaine[$place_aleatoire];
        }

        return new Response($mot_de_passe, Response::HTTP_OK);
    }

    // Serialize l'entité
    public function serializeObject($object)
    {
        $encoders = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];

        $normalizers = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizers], [$encoders]);

        return $serializer->serialize($object, 'json');
    }
}
