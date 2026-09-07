# Récupération des modules `itop-time-tracking` depuis l'ancienne prod

## Contexte

Dans le cadre de la migration de l'ancienne instance iTop
(cluster `lt-mut-prod`, namespace `prod-itop`, iTop `3.0.2-1`) vers la nouvelle
infra Kubernetes (cluster `lt-mut-nonprod-scw`, namespace `nonprod-itop`,
iTop `3.0.4`), l'extension **Time Tracking** de l'ancienne instance a été
récupérée telle quelle plutôt que rachetée : la licence Combodo est déjà
acquise pour cette instance de production (voir [Points d'attention](#points-dattention)).

Contrairement à [`itop-time-tracking.md`](itop-time-tracking.md) (achat +
déploiement d'une extension neuve depuis le store), cette page documente la
**récupération d'une extension déjà installée sur une instance existante**.

## Ce qui a été copié

L'extension **Time Tracking** de Combodo est en réalité un bundle de deux
modules iTop distincts, tous les deux nécessaires (le second est déclaré
comme dépendance obligatoire du premier dans
`module.itop-time-tracking.php` : `'dependencies' => array('itop-legacy-search-base/1.0.0')`).

| Module | Version (ancienne prod) | Rôle |
|---|---|---|
| `itop-time-tracking` | 2.3.3 | Calendrier, chronomètre, rapports de temps passé |
| `itop-legacy-search-base` | 1.1.2 | Widget de recherche legacy utilisé par le module ci-dessus |

Erreur initiale à ne pas reproduire : une première copie avait été faite
directement dans `/var/www/html/env-production/` (l'environnement **compilé**
par le setup wizard) au lieu de `/var/www/html/extensions/` (les sources
**non compilées**). Résultat : les fichiers étaient physiquement présents
mais totalement ignorés par iTop, car `data/datamodel-production.xml`
(qui liste les modules réellement actifs) n'est régénéré que par le setup
wizard. Cette copie erronée a été supprimée avant de refaire l'opération
correctement.

## D'où et vers où

**Source** — pod de l'ancienne prod (`lt-mut-prod` / `prod-itop`), répertoire
des modules compilés :

```
/var/www/html/data/production-modules/itop-time-tracking/itop-time-tracking
/var/www/html/data/production-modules/itop-time-tracking/itop-legacy-search-base
```

**Destination** — PVC `itop-ext-pvc` (monté sur `/var/www/html/extensions`)
du pod de la nouvelle instance (`lt-mut-nonprod-scw` / `nonprod-itop`), qui
est le point d'entrée du compilateur iTop pour toute extension :

```
/var/www/html/extensions/itop-time-tracking
/var/www/html/extensions/itop-legacy-search-base
```

## Commandes utilisées

```bash
# 1. Récupération depuis l'ancien pod prod vers une scratch dir locale
kubectl --context lt-mut-prod -n prod-itop cp \
  itop-itop-chart-59dfd574bc-nrwns:/var/www/html/data/production-modules/itop-time-tracking/itop-time-tracking \
  ./scratch/itop-time-tracking -c itop-chart

kubectl --context lt-mut-prod -n prod-itop cp \
  itop-itop-chart-59dfd574bc-nrwns:/var/www/html/data/production-modules/itop-time-tracking/itop-legacy-search-base \
  ./scratch/itop-legacy-search-base -c itop-chart

# 2. Dépôt dans le PVC extensions de la nouvelle instance (nonprod)
kubectl -n nonprod-itop cp ./scratch/itop-time-tracking \
  itop-app-845f94df4c-d89p8:/var/www/html/extensions/itop-time-tracking -c itop-chart

kubectl -n nonprod-itop cp ./scratch/itop-legacy-search-base \
  itop-app-845f94df4c-d89p8:/var/www/html/extensions/itop-legacy-search-base -c itop-chart

# 3. Nettoyage de la copie erronée faite précédemment dans env-production
kubectl -n nonprod-itop exec itop-app-845f94df4c-d89p8 -c itop-chart -- \
  rm -rf /var/www/html/env-production/itop-time-tracking
```

Vérification après coup :

```bash
kubectl -n nonprod-itop exec itop-app-845f94df4c-d89p8 -c itop-chart -- \
  ls -la /var/www/html/extensions/
# doit lister itop-time-tracking et itop-legacy-search-base (propriétaire root:www-data)
```

## Étape suivante (non automatisée)

Les fichiers sont en place mais **pas encore actifs** : il faut relancer le
setup wizard iTop (`/setup/index.php`) en mode mise à jour ("Upgrade") pour
qu'il recompile `env-production` et crée les tables manquantes
(`Activity`, `TimeSpent`, `FavouriteActivity`, `UserColorActivity`,
`TimeSpentBackground`, triggers associés). Cette étape n'a volontairement
pas été scriptée : iTop n'a pas de mode CLI silencieux pour une mise à jour
d'extensions, et l'opération modifie le schéma de la base — elle doit être
pilotée manuellement via le navigateur par un admin.

Voir la section "Vérification" de [`itop-time-tracking.md`](itop-time-tracking.md#5-vérification)
pour valider que le module fonctionne une fois le wizard passé.

## Points d'attention

- **Licence** : la licence Combodo de cette extension limite explicitement
  son usage à *"only one set of production data (i.e. only one instance of
  iTop managing production data)"*. Cette copie s'inscrit dans une
  **migration** (remplacement de l'ancienne instance) : ne pas faire
  tourner les deux instances en production simultanément avec ce module
  actif sur les deux, sous peine de sortir des termes de la licence.
  Décommissionner l'ancienne instance dès la migration validée.
- Versions legacy récupérées telles quelles (`itop-time-tracking` 2.3.3,
  `itop-legacy-search-base` 1.1.2), sur une base iTop plus récente
  (3.0.4 vs 3.0.2-1 à l'origine). Le changelog du module mentionne déjà
  "Fix compatibility with iTop 3.0.1+", mais à surveiller dans les logs du
  setup (`setup.log`) au moment de l'upgrade.
- Le PVC `itop-ext-pvc` est en `RWO` (voir `helm-chart/templates/pvc.yaml`) :
  une seule copie à la fois, donc pas de risque de concurrence ici, mais
  applicable si plusieurs replicas à l'avenir.
