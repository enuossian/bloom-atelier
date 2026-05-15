# Installation du projet

## Prérequis
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL
- Symfony CLI (optionnel)

1. Cloner le projet :
    - ```git clone https://github.com/enuossian/bloom-atelier.git```

2. Installer les dépendances PHP et JS: 
    - ```composer install```
    - ```npm install```

3. Compiler les fichiers CSS et JS : 
    - ```npm run build```

4. Créer un fichier .env.local et configurer les variables d'environnement :
    - ```DATABASE_URL="mysql://user:password@127.0.0.1:3306/db_name```
    - ```MAILER_DSN=your_mailer_dsn```
    - ```STRIPE_SECRET_KEY=your_stripe_secret_key```

5. Créer la base de donénes : 
    - ```symfony console doctrine:database:create```

6. Lancer les migrations :
    - ```symfony console doctrine:migrations:migrate```

7. Charger les fixtures (paramètres globaux du site) :
    - ```symfony console doctrine:fixtures:load```

8. Lancer le serveur local : 
    - ```symfony serve```



# Initialisation du projet

1. Générer la clé secrète :
    - ```symfony console app:generate-secret-key```

2. Créer un super admin avec la commande : 
    - ```symfony console app:create-super-admin```


