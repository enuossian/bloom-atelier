<?php

namespace App\Controller\Admin\Setting;

use App\Entity\Setting;
use App\Form\Admin\SettingFormType;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class SettingController extends AbstractController
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/setting', name: 'app_admin_setting_index', methods: ['GET'])]
    public function index(): Response
    {
        // Un tableau vide [] signifie aucun critère, donc retourne le premier enregistrement trouvé en base sans condition particulière
        $setting = $this->settingRepository->findOneBy([]);

        return $this->render('pages/admin/setting/index.html.twig', [
            'setting' => $setting,
        ]);
    }

    #[Route('/setting/{id<\d+>}/edit', name: 'app_admin_setting_edit', methods: ['GET', 'POST'])]
    public function edit(Setting $setting, Request $request): Response
    {
        $form = $this->createForm(SettingFormType::class, $setting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $setting->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->persist($setting);
            $this->entityManager->flush();

            $this->addFlash('success', 'Les réglages ont été modifiées avec succès.');

            return $this->redirectToRoute('app_admin_setting_index');
        }

        return $this->render('pages/admin/setting/edit.html.twig', [
            'settingForm' => $form,
        ]);
    }
}
