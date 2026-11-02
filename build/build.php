<?php

/**
 * Builds pkg_hotspots-<version>.zip from the site/ mirror.
 *
 * Usage: php build/build.php [version]
 *
 * site/ holds the files where Joomla installs them. For every extension this script reads its manifest,
 * copies each listed file from its installed location to the path the manifest expects inside the zip,
 * and fails when a listed file is missing. Without a version argument the version in pkg_hotspots.xml is used;
 * the component, module and plugin manifests must have the same version.
 *
 * @copyright  (C) 2026 TLWebdesign
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

declare(strict_types=1);

const ROOT = __DIR__ . '/..';
const SITE = ROOT . '/site';
const DIST = ROOT . '/dist';

/**
 * Extensions in the package, in install order. "base" is where Joomla installs files that the manifest
 * lists without a folder attribute; "lang" is the language folder of the extension's client.
 */
const EXTENSIONS = [
    'lib_compojoom' => [
        'manifest' => 'administrator/manifests/libraries/compojoom.xml', 'zipManifest' => 'compojoom.xml',
        'base' => 'libraries/compojoom', 'lang' => 'language', 'versionCheck' => false,
        'pkg' => '<file type="library" id="compojoom">lib_compojoom.zip</file>',
    ],
    'com_hotspots' => [
        'manifest' => 'administrator/components/com_hotspots/hotspots.xml', 'zipManifest' => 'hotspots.xml',
        'base' => 'administrator/components/com_hotspots', 'lang' => 'language', 'versionCheck' => true,
        'pkg' => '<file type="component" id="com_hotspots">com_hotspots.zip</file>',
    ],
    'mod_hotspots_list' => [
        'manifest' => 'modules/mod_hotspots_list/mod_hotspots_list.xml', 'zipManifest' => 'mod_hotspots_list.xml',
        'base' => 'modules/mod_hotspots_list', 'lang' => 'language', 'versionCheck' => true,
        'pkg' => '<file type="module" id="mod_hotspots_list" client="site">mod_hotspots_list.zip</file>',
    ],
    'plg_content_hotspots' => [
        'manifest' => 'plugins/content/hotspots/hotspots.xml', 'zipManifest' => 'hotspots.xml',
        'base' => 'plugins/content/hotspots', 'lang' => 'administrator/language', 'versionCheck' => true,
        'pkg' => '<file type="plugin" id="hotspots" group="content">plg_content_hotspots.zip</file>',
    ],
    'plg_content_hotspotscategory' => [
        'manifest' => 'plugins/content/hotspotscategory/hotspotscategory.xml', 'zipManifest' => 'hotspotscategory.xml',
        'base' => 'plugins/content/hotspotscategory', 'lang' => 'administrator/language', 'versionCheck' => true,
        'pkg' => '<file type="plugin" id="hotspotscategory" group="content">plg_content_hotspotscategory.zip</file>',
    ],
    'plg_hotspotslinks_content' => [
        'manifest' => 'plugins/hotspotslinks/content/content.xml', 'zipManifest' => 'content.xml',
        'base' => 'plugins/hotspotslinks/content', 'lang' => 'administrator/language', 'versionCheck' => true,
        'pkg' => '<file type="plugin" id="content" group="hotspotslinks">plg_hotspotslinks_content.zip</file>',
    ],
    'plg_hotspotslinks_external' => [
        'manifest' => 'plugins/hotspotslinks/external/external.xml', 'zipManifest' => 'external.xml',
        'base' => 'plugins/hotspotslinks/external', 'lang' => 'administrator/language', 'versionCheck' => true,
        'pkg' => '<file type="plugin" id="external" group="hotspotslinks">plg_hotspotslinks_external.zip</file>',
    ],
    'files_com_hotspots-fr-FR' => [
        'manifest' => 'administrator/manifests/files/com_hotspots-fr-FR.xml', 'zipManifest' => 'com_hotspots-fr-FR.xml',
        'base' => '', 'lang' => '', 'versionCheck' => false,
        'pkg' => '<file type="file" id="com_hotspots-fr-FR">files_com_hotspots-fr-FR.zip</file>',
    ],
];

function fail(string $message): never
{
    fwrite(STDERR, "build: $message\n");
    exit(1);
}

function loadManifest(string $path): SimpleXMLElement
{
    $xml = @simplexml_load_file($path);

    if (!$xml) {
        fail("cannot read manifest $path");
    }

    return $xml;
}

/**
 * Collects [source path under site/ => path inside the zip] for one extension.
 *
 * @return array<string, string>
 */
function collectFiles(array $ext): array
{
    $xml   = loadManifest(SITE . '/' . $ext['manifest']);
    $files = [];
    $add   = static function (string $source, string $target) use (&$files): void {
        $source = trim($source, '/');

        if (!file_exists(SITE . '/' . $source)) {
            fail("listed in the manifest but missing: site/$source");
        }

        $files[$source] = ltrim($target, '/');
    };

    // <files>: with a folder attribute the folder is the installed location (component, library);
    // without it the files live in the extension's own folder (module, plugin).
    $fileSections = [$xml->files ?? null, $xml->administration->files ?? null];

    foreach ($fileSections as $section) {
        if (!$section) {
            continue;
        }

        $folder = (string) ($section['folder'] ?? '');

        foreach ($section->children() as $child) {
            $name   = (string) $child;
            $target = ($folder !== '' ? $folder . '/' : '') . $name;
            $source = $folder !== '' ? $folder . '/' . $name : $ext['base'] . '/' . $name;
            $add($source, $target);
        }
    }

    // <languages>: installed in the language folder of the client; inside <administration> always admin.
    $languageSections = [
        [$xml->languages ?? null, $ext['lang']],
        [$xml->administration->languages ?? null, 'administrator/language'],
    ];

    foreach ($languageSections as [$section, $langDir]) {
        if (!$section) {
            continue;
        }

        $folder = (string) ($section['folder'] ?? '');

        foreach ($section->language as $language) {
            $path   = (string) $language;
            $target = ($folder !== '' ? $folder . '/' : '') . $path;
            $source = $langDir . '/' . $path;

            if (!file_exists(SITE . '/' . $source) && file_exists(SITE . '/' . $ext['base'] . '/' . $target)) {
                $source = $ext['base'] . '/' . $target;
            }

            $add($source, $target);
        }
    }

    // <media>: installed in media/<destination>.
    if (isset($xml->media)) {
        $folder      = (string) ($xml->media['folder'] ?? '');
        $destination = (string) $xml->media['destination'];

        foreach ($xml->media->children() as $child) {
            $add('media/' . $destination . '/' . $child, ($folder !== '' ? $folder . '/' : '') . $child);
        }
    }

    // <fileset> (file extensions): installed in the target folder.
    if (isset($xml->fileset)) {
        foreach ($xml->fileset->files as $section) {
            $folder = (string) ($section['folder'] ?? '');
            $target = (string) $section['target'];

            foreach ($section->children() as $child) {
                $add($target . '/' . $child, ($folder !== '' ? $folder . '/' : '') . $child);
            }
        }
    }

    // The script file sits next to the manifest of the installed extension.
    if (isset($xml->scriptfile)) {
        $script = (string) $xml->scriptfile;
        $add(dirname($ext['manifest']) . '/' . $script, $script);
    }

    $files[$ext['manifest']] = $ext['zipManifest'];

    return $files;
}

/**
 * Adds a file or folder to the zip, skipping macOS and editor leftovers.
 */
function addToZip(ZipArchive $zip, string $source, string $target): void
{
    $full = SITE . '/' . $source;

    if (is_file($full)) {
        $zip->addFile($full, $target);

        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($full, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $name = $item->getFilename();

        if ($name === '.DS_Store' || str_starts_with($name, '._') || str_ends_with($name, '~')) {
            continue;
        }

        $relative = $target . '/' . substr($item->getPathname(), strlen($full) + 1);

        if ($item->isDir()) {
            $zip->addEmptyDir($relative);
        } else {
            $zip->addFile($item->getPathname(), $relative);
        }
    }
}

function makeZip(string $path, callable $fill): void
{
    $zip = new ZipArchive();

    if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        fail("cannot create $path");
    }

    $fill($zip);
    $zip->close();
}

$pkgManifest = loadManifest(__DIR__ . '/pkg_hotspots.xml');
$version     = $argv[1] ?? (string) $pkgManifest->version;

if (!preg_match('/^\d+\.\d+\.\d+(-[0-9A-Za-z.]+)?$/', $version)) {
    fail("invalid version '$version'");
}

if ((string) $pkgManifest->version !== $version) {
    fail("pkg_hotspots.xml has version {$pkgManifest->version}, the build asks for $version");
}

$work = DIST . '/work';

if (is_dir($work)) {
    $clean = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($work, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($clean as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
}

@mkdir($work . '/packages', 0777, true);

foreach (EXTENSIONS as $name => $ext) {
    if ($ext['versionCheck']) {
        $extVersion = (string) loadManifest(SITE . '/' . $ext['manifest'])->version;

        if ($extVersion !== $version) {
            fail("{$ext['manifest']} has version $extVersion, expected $version");
        }
    }

    $files = collectFiles($ext);
    makeZip($work . "/packages/$name.zip", static function (ZipArchive $zip) use ($files): void {
        foreach ($files as $source => $target) {
            addToZip($zip, $source, $target);
        }
    });

    echo sprintf("  %-30s %3d entries\n", $name . '.zip', count($files));
}

// Check that the package manifest lists exactly the extensions built here.
$listed = [];

foreach ($pkgManifest->files->file as $file) {
    $listed[] = basename((string) $file, '.zip');
}

$missing = array_diff(array_keys(EXTENSIONS), $listed);
$extra   = array_diff($listed, array_keys(EXTENSIONS));

if ($missing || $extra) {
    fail('pkg_hotspots.xml does not match the build list. Missing: ' . implode(', ', $missing) . '. Extra: ' . implode(', ', $extra));
}

$zipName = DIST . "/pkg_hotspots-$version.zip";
makeZip($zipName, static function (ZipArchive $zip) use ($work): void {
    $zip->addFile(__DIR__ . '/pkg_hotspots.xml', 'pkg_hotspots.xml');
    $zip->addFile(__DIR__ . '/pkg_script.php', 'pkg_script.php');

    foreach (glob($work . '/packages/*.zip') as $file) {
        $zip->addFile($file, 'packages/' . basename($file));
    }
});

echo 'Built ' . realpath($zipName) . "\n";
echo 'sha256 ' . hash_file('sha256', $zipName) . "\n";
