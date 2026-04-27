# Installation du projet

1. Cloner le projet :
    - ```git clone https://github.com/enuossian/bloom-atelier.git```

2. Installer les dépendances : 
    - ```composer install```

3. Créer un fichier .env.local et configurer la base de données :
    - ```DATABASE_URL="mysql://root:root@127.0.0.1:3306/bloom_atelier"```

2. Créer la base de donénes : 
    - ```symfony console doctrine:database:create```

1. Lancer les migrations :
    - ```symfony console doctrine:migrations:migrate```

2. Lancer le serveur local : 
    - ```symfony serve```

3. Créer un fichier .env.local et configurer la base de données :
    - ```DATABASE_URL="mysql://root:root@127.0.0.1:3306/bloom_atelier"```

2. Créer la base de donénes : 
    - ```symfony console doctrine:database:create```

# Initialisation du projet

1. Générer la clé secrète avec la commande :
    - ```symfony console app:generate-local-secret-key```


# Chargement des paramètres du site

1. Générer les paramètres globaux du site (email et téléphone de contact) :
    - ```symfony console doctrine:fixtures:load```


2. Créer un super admin avec la commande : 
    - ```symfony console app:create-super-admin```