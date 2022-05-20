# The BatBlog's Project

## Installation

```
git clone https://github.com/AntoineLudwigGalli/TheBatBlog.git
```
### Modifier les paramètres d'environnement dans les fichiers .env pour les faire correspondre à votre environnement (accès base de données, clés Google Recaptcha, etc...)

```
# Accès base de données à modifier

DATABASE_URL="mysql://db_user:@db_password@127.0.0.1:3306/db_name?serverVersion=5.7.33&charset=utf8mb4"


# Clés Recaptcha à modifier
GOOGLE_RECAPTCHA_SITE_KEY=XXXXXXXXXXXXXXXXXXXX
GOOGLE_RECAPTCHA_PRIVATE_KEY=XXXXXXXXXXXXXXXXXXXXXXXXX

```

### Déplacer le terminal dans le dossier cloné du projet
```
cd TheBatBlog
```

### Taper les commandes suivantes :
```
composer install
symfony console doctrine:database:create
symfony console make:migration
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load
symfony console assets:install public
```
Les fixtures créeront :
* Un compte admin (email: admin@a.a, mot de passe : aA1!aaaa)
* 10 comptes utilisateurs (email aléatoire, mot de passe : aA1!aaaa)
* 200 articles
* Entre 0 et 10 commentaires par article

### Démarrer le serveur Symfony :
```
symfony serve
```


