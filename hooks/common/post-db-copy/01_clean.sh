 #!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

drush_alias=$site'.'$target_env
alias drush11=../vendor/bin/drush

# Apex Tools
#uri=apextoolgroupdev.prod.acquia-sites.com
#drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
#drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
#drush9 @$drush_alias cim sync -y --uri=$uri
#drush9 @$drush_alias cr --uri=$uri
#drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

echo "\n\n\nDrush version check...\n"
drush --version

echo "Trying drush11 alias..."
drush11 --version

echo "Trying a direct referencing of ../vendor/bin/drush..."
../vendor/bin/drush --version

echo "\n\nRunning per site deploy commands...\n\n\n\n"

# GEARWRENCH
echo "[SITE] GearWrench North America..."
uri=prod-www.gearwrench.com
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# Crescenttool
echo "[SITE] Crescent North America..."
uri=prod-www.crescenttool.com
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# GEARWRENCH Australia
echo "[SITE] GearWrench Australia..."
uri=prod-www.gearwrench.com.au
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# Crescenttool Australia
echo "[SITE] Crescent Australia..."
uri=prod-www.crescenttool.com.au
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# SATA Brazil
echo "[SITE] SATA Brazil..."
uri=prod-www.sataferramentas.com.br
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# SATA Colombia
echo "[SITE] SATA Colombia..."
uri=prod-www.sata.com.co
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# SATA EMEA
echo "[SITE] SATA EMEA..."
uri=prod-www.satatools.eu
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"

# SATA US (North America)
echo "[SITE] SATA US (North America)..."
uri=prod-www.satatools.us
echo "[NOTICE] Setting maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
echo "[NOTICE] Running database updates."
drush9 @$drush_alias updatedb -y --strict=0 --uri=$uri
echo "[NOTICE] Importing the config for $uri"
drush9 @$drush_alias cim sync -y --uri=$uri
echo "[NOTICE] Clearing cache."
drush9 @$drush_alias cr --uri=$uri
echo "[NOTICE] Leaving maintenance mode."
drush9 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
echo "[DONE] Done with uri: $uri"
