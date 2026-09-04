<?php

/**
 * @file
 * Imports redirects from a CSV export of the legacy WordPress site.
 *
 * Creates `redirect` module entities (301 by default).
 *
 * Usage:
 *   ddev drush php:script scripts/import-redirects.php -- path/to/export.csv
 *
 * CSV format (header required), see docs/migration/redirects-wordpress.csv:
 *   old_path,new_path,status_code
 *   /ancienne-page/,/demarches/nouvelle-page,301
 *
 * - old_path: path on the legacy WordPress site (leading slash, no domain).
 * - new_path: internal path or URL alias on the new Drupal site, OR an
 *   absolute URL (http/https) for external redirects.
 * - status_code: 301 (permanent) or 302 (temporary). Defaults to 301 if
 *   empty.
 *
 * Existing redirects for the same source path are left untouched (the
 * script is safe to re-run on an updated export); remove them manually
 * first if they need to be replaced.
 *
 * @see docs/migration/redirects-wordpress.csv
 */

use Drupal\redirect\Entity\Redirect;

$csv_path = $extra[0] ?? NULL;
if ($csv_path && !is_file($csv_path) && defined('DRUPAL_ROOT')) {
  // Drush's php:script runs with the Drupal document root (web/) as the
  // working directory, not the repository root: retry relative to it.
  $repo_root_candidate = dirname(DRUPAL_ROOT) . '/' . $csv_path;
  if (is_file($repo_root_candidate)) {
    $csv_path = $repo_root_candidate;
  }
}
if (!$csv_path || !is_file($csv_path)) {
  echo "Usage: drush php:script scripts/import-redirects.php -- path/to/export.csv\n";
  return;
}

$handle = fopen($csv_path, 'r');
if (!$handle) {
  echo "Unable to open $csv_path\n";
  return;
}

$header = fgetcsv($handle);
$expected = ['old_path', 'new_path', 'status_code'];
if (array_map('trim', $header) !== $expected) {
  echo "Unexpected CSV header. Expected: " . implode(',', $expected) . "\n";
  fclose($handle);
  return;
}

$redirect_repository = \Drupal::service('redirect.repository');
$created = 0;
$skipped = 0;
$line = 1;

while (($row = fgetcsv($handle)) !== FALSE) {
  $line++;
  if (count($row) < 2) {
    continue;
  }
  [$old_path, $new_path, $status_code] = $row + [2 => '301'];
  $old_path = trim($old_path);
  $new_path = trim($new_path);
  $status_code = (int) trim((string) $status_code) ?: 301;

  if ($old_path === '' || $new_path === '') {
    echo "Line $line: skipped (empty old_path or new_path)\n";
    $skipped++;
    continue;
  }
  $source_path = ltrim($old_path, '/');

  $existing = $redirect_repository->findMatchingRedirect($source_path);
  if ($existing) {
    echo "Line $line: /$source_path already has a redirect (id {$existing->id()}), skipped.\n";
    $skipped++;
    continue;
  }

  $redirect_url = str_starts_with($new_path, 'http://') || str_starts_with($new_path, 'https://')
    ? $new_path
    : 'internal:/' . ltrim($new_path, '/');

  $redirect = Redirect::create([
    'redirect_source' => ['path' => $source_path],
    'redirect_redirect' => ['uri' => $redirect_url],
    'status_code' => $status_code,
    'language' => 'fr',
  ]);
  $redirect->save();
  $created++;
  echo "Line $line: /$source_path -> $new_path ($status_code) created.\n";
}

fclose($handle);

echo "\nDone. $created redirect(s) created, $skipped skipped.\n";
