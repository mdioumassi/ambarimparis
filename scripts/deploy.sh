#!/usr/bin/env bash
#
# Deploys the currently checked-out code to the target environment and
# brings the site up to date with it (config import, database updates,
# cache rebuild). Meant to be run *on the target server*, from the
# deployed code root — see .gitlab-ci.yml's deploy:production job, which
# rsyncs the build artifact there before invoking this script over SSH.
#
# Usage: ./scripts/deploy.sh

set -euo pipefail

cd "$(dirname "$0")/.."

echo "==> Enabling maintenance mode"
vendor/bin/drush state:set system.maintenance_mode 1 --input-format=integer

echo "==> Running deploy hooks (update.php, config import, cache rebuild)"
vendor/bin/drush deploy -y

echo "==> Disabling maintenance mode"
vendor/bin/drush state:set system.maintenance_mode 0 --input-format=integer

echo "==> Deploy complete."
