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

# Apex Tools
#uri=apextoolgroupdev.prod.acquia-sites.com
#drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
#drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
#drush10 @$drush_alias cim sync -y --uri=$uri
#drush10 @$drush_alias cr --uri=$uri
#drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# GEARWRENCH
echo "GearWrench North America..."
uri=prod-www.gearwrench.com
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# Crescenttool
echo "Crescent North America..."
uri=prod-www.crescenttool.com
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# GEARWRENCH Australia
echo "GearWrench Australia..."
uri=prod-www.gearwrench.com.au
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# Crescenttool Australia
echo "Crescent Australia..."
uri=prod-www.crescenttool.com.au
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# SATA Brazil
echo "SATA Brazil..."
uri=prod-www.sataferramentas.com.br
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri

# SATA Colombia
echo "SATA Colombia..."
uri=prod-www.sata.com.co
drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
drush10 @$drush_alias cim sync -y --uri=$uri
drush10 @$drush_alias cr --uri=$uri
drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
ct=0 --uri=$uri

# SATA EMEA
#echo "SATA EMEA..."
#uri=prod-www.satatools.eu
#drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
#drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
#drush10 @$drush_alias cim sync -y --uri=$uri
#drush10 @$drush_alias cr --uri=$uri
#drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
#ct=0 --uri=$uri

# SATA US (North America)
#echo "SATA US (North America)..."
#uri=prod-www.satatools.us
#drush10 @$drush_alias sset system.maintenance_mode 1 --strict=0 --uri=$uri
#drush10 @$drush_alias updatedb -y --strict=0 --uri=$uri
#drush10 @$drush_alias cim sync -y --uri=$uri
#drush10 @$drush_alias cr --uri=$uri
#drush10 @$drush_alias sset system.maintenance_mode 0 --strict=0 --uri=$uri
