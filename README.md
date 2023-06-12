# Projet Le Blog de Batman

### Cloner le projet

```
git clone https://github.com/vincent110421/leblogdebatman.git

### Déplacer le terminal dans le dossier cloné
```
cd leblogdebatman
```

### Installer les vendors (pour recréer le dossier vendor)
```
composer install
```

### Création base de données
Configurer la connexion à la base de données dans le fichier .env (voir cours), puis taper les commandes suivantes :
```
symfony console doctrine:database:create
symfony console doctrine:migration:migrate
symfony console doctrine:fixtures:load
``` 
### Création des fixtures
```
Cette commande créera :
* Un compte admin (email: a@a.a, password : 'Azerty123!' )
* 10 compte utilisateurs (email aléatoire , password , 'Azerty123!' )
* 50 articles


### Installation fichier front bundles (CKEditor)
```
symfony console assets:install public
```


### Lancer le serveur
```
symfony serve
```