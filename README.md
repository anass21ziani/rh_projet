# Système de Gestion Documentaire RH

## 📋 Description

Ce projet est un système complet de gestion documentaire pour les ressources humaines, développé avec Symfony 7.3. Il implémente une architecture basée sur les rôles avec gestion hiérarchique des employés, contrats, dossiers et documents.

## 🏗️ Architecture

### Structure de Base de Données

Le système utilise une structure hiérarchique : **Employés → Contrats → Dossiers → Placards → Documents**

#### Tables Principales

1. **`employees`** - Table des employés
   - Informations personnelles et professionnelles
   - Authentification et rôles
   - Relations avec contrats et dossiers

2. **`nature_contrat`** - Types de contrats
   - CDI, CDD, Stage, etc.
   - Catalogue normalisé des types

3. **`employee_contrat`** - Contrats des employés
   - Liaison employé-contrat
   - Gestion temporelle et statuts
   - Historique des contrats

4. **`dossiers`** - Dossiers RH
   - Organisation par catégories
   - Types : administratif, médical, juridique, etc.

5. **`placards`** - Emplacements de stockage
   - Gestion physique/virtuelle
   - Localisation des dossiers

6. **`documents`** - Documents finaux
   - Stockage des fichiers
   - Métadonnées et traçabilité

### Système de Rôles

- **`ROLE_ADMINISTRATEUR_RH`** - Contrôle total du système
- **`ROLE_RESPONSABLE_RH`** - Gestion opérationnelle
- **`ROLE_EMPLOYEE`** - Accès limité

## 🚀 Installation

### Prérequis

- PHP 8.2+
- Composer
- PostgreSQL 16
- Docker (optionnel)

### Installation

1. **Cloner le projet**
```bash
git clone https://github.com/anass21ziani/rh_projet.git
cd rh_projet
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configuration de l'environnement**
```bash
cp .env .env.local
# Modifier les paramètres de base de données dans .env.local
```

4. **Base de données**
```bash
# Avec Docker
docker-compose up -d

# Ou avec PostgreSQL local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

5. **Démarrer le serveur**
```bash
symfony serve
# ou
php -S localhost:8000 -t public
```

## 👤 Utilisateurs de Test

- **Administrateur RH** : `admin@uiass.rh` / `password123`
- **Responsable RH** : `rh@uiass.rh` / `password123`
- **Employé** : `employe@uiass.rh` / `password123`

## 📁 Structure du Projet

```
src/
├── Controller/          # Contrôleurs métier
│   ├── AdministrateurRhController.php
│   ├── ResponsableRhController.php
│   └── SecurityController.php
├── Entity/             # Entités Doctrine
│   ├── Employee.php
│   ├── NatureContrat.php
│   ├── EmployeeContrat.php
│   ├── Dossier.php
│   ├── Placard.php
│   └── Document.php
├── Form/               # Formulaires Symfony
├── Repository/         # Repositories de données
├── Security/           # Configuration sécurité
└── DataFixtures/       # Données de test

templates/              # Templates Twig
├── administrateur-rh/  # Interface administrateur
├── responsable-rh/     # Interface responsable
├── security/           # Pages d'authentification
└── base.html.twig      # Template de base
```

## 🔧 Fonctionnalités

### Pour l'Administrateur RH
- ✅ Gestion complète des responsables RH
- ✅ Gestion des employés
- ✅ Configuration des types de contrats
- ✅ Gestion des dossiers
- ✅ Vue d'ensemble du système

### Pour le Responsable RH
- ✅ Gestion des employés
- ✅ Gestion des contrats
- ✅ Gestion des dossiers
- ✅ Upload et gestion des documents
- ✅ Interface opérationnelle

### Fonctionnalités Générales
- ✅ Authentification sécurisée
- ✅ Gestion des rôles et permissions
- ✅ Interface responsive
- ✅ Upload de documents
- ✅ Recherche et filtrage
- ✅ Historique et traçabilité

## 🛠️ Technologies Utilisées

- **Backend** : Symfony 7.3, PHP 8.2+
- **Base de données** : PostgreSQL 16
- **ORM** : Doctrine 3.5
- **Frontend** : Twig, Bootstrap 5, JavaScript
- **Conteneurisation** : Docker Compose
- **Sécurité** : Symfony Security Bundle

## 📊 Cas d'Usage

### Recrutement
1. Créer un employé
2. Créer un contrat
3. Créer un dossier administratif
4. Uploader les documents

### Gestion Documentaire
1. Organiser par types de dossiers
2. Assigner des placards
3. Uploader et référencer les documents
4. Traçabilité complète

### Audit
- Historique des contrats
- Traçabilité des documents
- Logs d'activité

## 🔐 Sécurité

- Authentification par formulaire
- Hachage automatique des mots de passe
- Protection CSRF
- Contrôle d'accès par rôles
- Headers de sécurité
- Protection contre la navigation arrière

## 📈 Évolutions Possibles

- Versioning des documents
- Workflow d'approbation
- Recherche full-text
- API REST
- Notifications
- Rapports et statistiques
- Intégration avec d'autres systèmes

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature
3. Commit les changements
4. Push vers la branche
5. Ouvrir une Pull Request

## 📄 Licence

Ce projet est sous licence propriétaire.

## 📞 Support

Pour toute question ou support, contactez l'équipe de développement.

---

**Développé avec ❤️ pour la gestion des ressources humaines**
