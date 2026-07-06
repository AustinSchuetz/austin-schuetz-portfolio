#!/usr/bin/env bash
# scrape_archive.sh
#
# Re-runnable scraper for the client-archive collection on austinschuetz.com.
# Produces raw source images into media/uploads/archive/_raw/ from two sources:
#
#   1. Wayback Machine thumbnails (austinschuetz.com/assets/*.png as captured
#      in the 2024-02-24 portfolio snapshot), downloaded via plain curl.
#   2. Fresh Playwright screenshots of live sites Austin still hosts/maintains,
#      plus a few Wayback Machine *page* snapshots for sites with no surviving
#      thumbnail asset (rendered as a screenshot of the archived page itself).
#
# Run from repo root:
#   bash scripts/scrape_archive.sh
#
# Requires: curl (Git Bash), Node + npx with `playwright` installed
# (`npx playwright install chromium` once).
#
# After running, process the raw images with Pillow (see process step in the
# task notes / README) into media/uploads/archive/<slug>.webp + _thumb.webp.

set -uo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RAW_DIR="$REPO_ROOT/media/uploads/archive/_raw"
mkdir -p "$RAW_DIR"

WAYBACK_TS="20240224031751"
VIEWPORT="1440,1080"

log() { echo "[scrape_archive] $*"; }

# --- SOURCE 1: Wayback thumbnail assets -------------------------------------
# slug:filename pairs served at
# https://web.archive.org/web/${WAYBACK_TS}im_/https://austinschuetz.com/assets/<filename>.png
wayback_thumb() {
    local slug="$1" filename="$2"
    local out="$RAW_DIR/${slug}.png"
    if [ -f "$out" ]; then
        log "skip (exists): $slug"
        return 0
    fi
    local url="https://web.archive.org/web/${WAYBACK_TS}im_/https://austinschuetz.com/assets/${filename}.png"
    log "wayback thumb: $slug <- $url"
    local code
    code=$(curl -sL -o "$out" -w "%{http_code}" "$url")
    if [ "$code" != "200" ]; then
        log "  FAILED ($code) for $slug"
        rm -f "$out"
    fi
    sleep 2
}

# --- SOURCE 1b: Wayback full-page snapshot (screenshot via Playwright) ------
wayback_page_shot() {
    local slug="$1" timestamp="$2" original_url="$3"
    local out="$RAW_DIR/${slug}.png"
    if [ -f "$out" ]; then
        log "skip (exists): $slug"
        return 0
    fi
    local url="https://web.archive.org/web/${timestamp}/${original_url}"
    log "wayback page shot: $slug <- $url"
    npx playwright screenshot --viewport-size="$VIEWPORT" --timeout=60000 "$url" "$out" || log "  FAILED for $slug"
    sleep 2
}

# --- SOURCE 2: Fresh screenshot of a live site -------------------------------
live_shot() {
    local slug="$1" url="$2"
    local out="$RAW_DIR/${slug}.png"
    if [ -f "$out" ]; then
        log "skip (exists): $slug"
        return 0
    fi
    log "live shot: $slug <- $url"
    npx playwright screenshot --viewport-size="$VIEWPORT" --timeout=45000 --ignore-https-errors "$url" "$out" || log "  FAILED for $slug"
}

log "== Wayback thumbnail assets =="
wayback_thumb koda-crossfit-gyms KodaIronView
wayback_thumb account-media accountmedia
wayback_thumb newlake-capital newlake
wayback_thumb geneva-glass-works genevaglass
wayback_thumb crossfit-sdg crossfit-sdg
wayback_thumb koda-competitor KodaCompetitor

log "== Wayback full-page screenshots (no surviving thumb asset) =="
wayback_page_shot taylor-dilk 20191014174219 "https://taylor-dilk.com/"
wayback_page_shot koda-crossfit-tulsa 20190917214059 "https://kodacrossfittulsa.com/"
wayback_page_shot erin-schuetz 20200805130958 "http://erinschuetz.com/"
wayback_page_shot bearson 20190516123419 "http://bearson.austinschuetz.com/"

log "== Fresh screenshots: live sites Austin still hosts/maintains =="
live_shot crossfit-sdg-fresh "https://crossfit-sdg.com"
live_shot attune-collective "https://attune-collective.com"
live_shot ground-creations "https://groundcreationscolorado.com"

log "== Fresh screenshots: demo subdomains (expired cert, ignore-https-errors) =="
live_shot sphera "https://sphera.austinschuetz.com"
live_shot rivo "https://rivo.austinschuetz.com"
live_shot contelligent "https://contelligent.austinschuetz.com"

log "== Fresh screenshots: other live client sites =="
live_shot lanai "http://lovelanai.com"
live_shot todays-jam "http://todaysjam.com"
live_shot crossfit-1055 "https://crossfit1055.com"
live_shot oneplus-systems "https://oneplussystems.com"
live_shot tamara-edwards "https://tamaraedwards.co"
live_shot vms-professionals "https://vmsprofessionals.com"

log "Done. Raw images in $RAW_DIR"
