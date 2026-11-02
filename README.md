# Compojoom Hotspots (maintained by TLWebdesign)

Compojoom has stopped trading. This repo holds the last versions of their extensions as used on the client site, so we can maintain and upgrade them ourselves.

## Source

Pulled from the live site (`the web root` on the client's server) on 2026-11-02. Hotspots was a paid extension that was never published on GitHub, so the live site is the newest copy available.

| Extension | Version | Date |
|---|---|---|
| com_hotspots (Hotspots Professional) + module + plugins | 6.0.13 | 2023-09-19 |
| CB plugin `plug_hotspots` | 2.7.4 | 2023-09-19 |
| lib_compojoom | 5.3.6 | 2023-09-14 (same as github.com/compojoom/lib_compojoom) |

## Layout

`site/` mirrors the Joomla root, so files can be diffed against the live site and copied back one to one. It is not yet an installable package.

Left out: `media/com_hotspots/cache` (1.5 GB of generated files).

## Local changes on top of Compojoom's code

Search for `TLWebdesign patch` to find them.

- **2026-11-02** `administrator/components/com_hotspots/libraries/geocoder/geocoder.php`: the Google geocoding call now uses the Joomla proxy (the server has no direct internet) and has a 3s connect / 5s total timeout. Without it, CB registration hung for about 30 seconds and created no marker.

## Notes

- The update server (compojoom.com, ARS stream id 4) is still listed in Joomla under Update Sites. Disable it once we publish our own builds.
- Integrations installed but switched off on the site: AlphaUserPoints, JomSocial, K2, FlexiContent, SobiPro, Matukio, search, email, hotspotsanywhere.
