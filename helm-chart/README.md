## HELM Chart

This is a helm chart used to easily deploy iTop to a K8S cluster using [HELM](https://helm.sh/).

### Volumes 

In order for iTop to support being upgraded and being restarted, pvc are automatically created.

You can check the [template](./templates/pvc.yaml) for more info.

The folders that are saved are specified in the [deployment.yaml](./templates/deployment.yaml) file : 
```yaml
volumeMounts:
  - mountPath: "/var/www/html/data"
    name: itop-data-pv
  - mountPath: "/var/www/html/env-production"
    name: itop-env-pv
  - mountPath: "/var/www/html/log"
    name: itop-log-pv
  - mountPath: "/var/www/html/conf"
    name: itop-conf-pv
```

### Values.yaml

You can edit the values.yaml file directly or use the set directive with the helm cli tool.

```yaml
image:
  repository: supervisions/itop
  pullPolicy: Always
  tag: 3.0
```

There also the environnement variables that are : 

```yaml
environment:
  db_host: <mysql-server-url> "mysql-chart.database.svc.cluster.local"
  db_name: <mysql-db-name>
  db_pwd: <mysql-db-pwd>
  db_user: <mysql-db-user>
```

These `environment.db_*` values are only used when `mariadb.enabled` is `false` (external database). See the MariaDB section below for the default setup.

### MariaDB (mariadb-operator)

By default (`mariadb.enabled: true`), the chart provisions a dedicated database for this iTop instance using the [mariadb-operator](https://github.com/mariadb-operator/mariadb-operator). This requires the operator to already be installed on the target cluster — it is deployed cluster-wide via FluxCD on `lt-mut-nonprod-scw` and `lt-mut-prod-scw`.

The chart creates, in the release namespace:
- a `MariaDB` resource: the database instance itself (`templates/mariadb/mariadb.yaml`)
- a `Database` resource for the iTop schema
- a `User` and a `Grant` giving that user full rights on the database
- a `Secret` holding the generated root and iTop user passwords (unless `mariadb.auth.existingSecret` is set)

The iTop `Deployment` is wired to this instance automatically: `DB_HOSTNAME` points at the `MariaDB` resource's Service, and `DB_ENV_MYSQL_PASSWORD` is read from the managed secret.

```yaml
mariadb:
  enabled: true
  instance:
    image: mariadb:11.4.3
    replicas: 1
    storage:
      size: 10Gi
      storageClassName: sbs-default
  database:
    name: itop
  auth:
    username: itop
    existingSecret: "" # provide your own secret (keys: root-password, password) instead of letting the chart generate one
```

To use an externally managed database instead, set `mariadb.enabled: false` and fill in `environment.db_*`.

### Pod security

In order to allow the iTop service to access the files on the system, adding specific security context for the pod was needed, those can be found in the deployment.yaml file

```yaml
securityContext:
  runAsUser: 1000
  runAsGroup: 3000
  fsGroup: 2000
```

If you want to know more about the do and don't i invite you to read the k8s documentation [here](https://kubernetes.io/docs/tasks/configure-pod-container/security-context/)


### Adding your own config-itop.php

You can create a configmap and add it to the pod deployment in order to have the config-itop.php file added before installing iTop.

This is not recommended.

### What is not added

A mysql chart link in order to also have a db deployed to go with the iTop instance.

## Installation

At first installation, you need to follow the setup wizard : `/setup/`

During this wizard, the url used for probes will fail during a few seconds. You need to manually edit the deployment to set livenessProbe and readinessProbe to set failureThreshold to 30.

After the setup wizard, remove failureThreshold in readiness and liveness probes,

Then enter container and run:
```
chown www-data: conf/production/config-itop.php
chmod 440 conf/production/config-itop.php
```
