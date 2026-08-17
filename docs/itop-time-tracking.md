# Suivi du temps dans iTop — extension `itop-time-tracking`

## Contexte

iTop ne fournit **pas nativement** de suivi du temps utilisateur ("Track my time").
Cette fonctionnalité est apportée par l'extension officielle payante **Time Tracking**
(Combodo), disponible sur le [store iTop Hub](https://store.itophub.io/en_US/products/itop-time-tracking).

C'est cette extension que l'on va utiliser.

## Ce que fournit l'extension

- Un menu **"Suivi de mon temps"** : saisie du temps via calendrier (type Google Calendar)
  ou chronomètre start/stop.
- Un onglet **"Suivi de temps"** directement sur les tickets (User Request / Incident
  uniquement dans l'implémentation standard).
- Un menu **"Rapport de suivi temps"** (réservé aux managers) : vue sur le temps passé
  par tous les utilisateurs, graphiques par utilisateur/client.
- Deux nouveaux profils : **Time Tracker** (suivi perso) et **Time Tracking Manager**
  (vue globale).
- Un nouveau trigger déclenché quand un chronomètre dépasse `stopwatch_max_time`.

Classes ajoutées au datamodel : `TimeSpent`, `TimeSpentBackground`, `Activity`.

## Prérequis

- Compte sur [itophub.io](https://www.itophub.io) (store).
- **Licence payante : 129 €** (constaté sur la fiche produit, à vérifier/valider côté
  achat avant de commander).
- Version iTop **≥ 3.2.0** (notre image `3.2-custum` convient).
- PHP **≤ 8.3** (notre image est en PHP 7.4 → compatible).

## Étapes

### 1. Achat / téléchargement

1. Se connecter sur https://store.itophub.io avec un compte iTop Hub.
2. Aller sur https://store.itophub.io/en_US/products/itop-time-tracking, cliquer
   **"Get it!"**, finaliser l'achat.
3. Télécharger le zip de l'extension (version 2.4.0 recommandée) depuis "My iTop Hub".

### 2. Déploiement dans notre infra Kubernetes

Notre chart helm monte déjà un PVC dédié aux extensions :

```yaml
# helm-chart/templates/deployment.yaml
- mountPath: "/var/www/html/extensions"
  name: itop-ext-pv   # PVC itop-ext-pvc
```

Donc pas besoin de rebuild d'image : il suffit de déposer le contenu du zip décompressé
dans ce volume, namespace par namespace (nonprod puis prod).

```bash
# décompresser en local
unzip itop-time-tracking-2.4.0.zip -d /tmp/itop-time-tracking

# copier dans le pod (namespace courant, adapter le nom du pod)
kubectl cp /tmp/itop-time-tracking/itop-time-tracking \
  itop-app-6bb75bbc77-q7sv5:/var/www/html/extensions/itop-time-tracking

# vérifier
kubectl exec itop-app-6bb75bbc77-q7sv5 -- ls /var/www/html/extensions
```

> Le PVC `itop-ext-pvc` est en `RWO` (voir `helm-chart/templates/pvc.yaml`), donc une
> seule copie/pod à la fois. Si plusieurs replicas, désactiver l'autoscaling le temps
> du déploiement de l'extension.

### 3. Activation de l'extension dans iTop

Deux options :

- **Recommandé** : relancer le wizard de setup (`/setup`) sur l'instance → il détecte
  automatiquement les nouvelles extensions présentes dans `extensions/` et propose de
  les (dés)activer. Choisir "Continuer" / mode mise à jour, cocher `itop-time-tracking`.
- Alternative en ligne de commande dans le pod : `php webservices/setup.php` /
  `toolkit.php` selon la version (à confirmer selon doc Combodo au moment de l'exécution).

⚠️ Faire d'abord un test complet sur **nonprod** avant de reproduire sur prod.

### 4. Configuration post-installation

Dans l'admin iTop (`Administration > Paramètres` ou fichier `config-itop.php` de
l'extension) :

| Paramètre | Rôle |
|---|---|
| `allowed_classes` | Classes activant le suivi (par défaut `UserRequest`, `Incident`) |
| `stopwatch_max_time` | Durée max du chronomètre avant arrêt auto |
| `default_event_duration` | Durée par défaut d'une activité (ex : 30 min) |
| `day_start_time` / `day_end_time` | Plage horaire affichée dans le calendrier |
| `delete_max_event_age` | Nb de jours après lesquels une entrée n'est plus modifiable |
| `manager_report_query` | Requête OQL définissant le périmètre du rapport manager |

Attribuer le profil **Time Tracker** aux utilisateurs concernés, et **Time Tracking
Manager** aux managers qui doivent voir le rapport global.

### 5. Vérification

- Se connecter avec un utilisateur ayant le profil "Time Tracker".
- Vérifier la présence du menu **"Suivi de mon temps"**.
- Ouvrir un ticket (User Request/Incident) → vérifier l'onglet **"Suivi de temps"**.
- Avec un compte "Time Tracking Manager" → vérifier **"Rapport de suivi temps"**.

## Points d'attention

- Extension **payante** (129 €) — à valider côté budget/achat avant d'aller plus loin.
- Limitée en standard aux tickets User Request / Incident (pas les autres classes,
  sauf customisation via `allowed_classes`).
- Si une v1.0 de l'extension existait déjà (pas notre cas actuellement), une migration
  SQL est nécessaire — non applicable ici, première installation.
- Le rapport manager ne filtre pas toujours par organisation sélectionnée sur les
  graphiques globaux (limitation connue, remontée par la communauté).

## Sources

- [Time Tracking — doc officielle iTop](https://www.itophub.io/wiki/page?id=extensions:itop-time-tracking)
- [Fiche produit store iTop Hub](https://store.itophub.io/en_US/products/itop-time-tracking)
