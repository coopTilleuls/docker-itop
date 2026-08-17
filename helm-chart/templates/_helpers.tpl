{{/*
Expand the name of the chart.
*/}}
{{- define "itop-chart.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "itop-chart.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "itop-chart.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "itop-chart.labels" -}}
helm.sh/chart: {{ include "itop-chart.chart" . }}
{{ include "itop-chart.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "itop-chart.selectorLabels" -}}
app.kubernetes.io/name: {{ include "itop-chart.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "itop-chart.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "itop-chart.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

{{/*
Name of the itop web app Deployment.
*/}}
{{- define "itop-chart.app.fullname" -}}
{{- printf "%s-app" (include "itop-chart.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Name of the MariaDB instance (mariadb-operator MariaDB resource) backing this release.
This also becomes the DNS name of the Service the operator creates for it.
*/}}
{{- define "itop-chart.mariadb.fullname" -}}
{{- printf "%s-mariadb" (include "itop-chart.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Name of the secret holding the MariaDB root and iTop user passwords.
Uses .Values.mariadb.auth.existingSecret when set, otherwise a chart-managed secret.
*/}}
{{- define "itop-chart.mariadb.secretName" -}}
{{- default (printf "%s-auth" (include "itop-chart.mariadb.fullname" .)) .Values.mariadb.auth.existingSecret }}
{{- end }}
