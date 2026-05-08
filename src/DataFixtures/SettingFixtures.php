<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $setting = $this->createSetting();

        $manager->persist($setting);
        $manager->flush();
    }

    /**
     * Permet de créer l'instance représentant les paramètres du site.
     */
    public function createSetting(): Setting
    {
        $setting = new Setting();

        $setting->setEmail('hello@bloomatelier.site');
        $setting->setPhone('06 07 08 09 10');
        $setting->setCreatedAt(new \DateTimeImmutable());
        $setting->setUpdatedAt(new \DateTimeImmutable());

        return $setting;
    }
}
