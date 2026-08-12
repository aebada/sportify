#!/usr/bin/env python3
"""Build partners-*-expand.json from curated orgs + impressum email extraction.
Only keeps real emails found on fetched pages or explicitly curated with source_url.
Does NOT invent emails from local-part patterns.
"""
from __future__ import annotations

import json
import re
import ssl
import time
import urllib.error
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SEEDS = ROOT / "database" / "seeds"
UA = "SportifyPartnerResearch/1.0 (+https://sportifyplus.de; partnership research)"
EMAIL_RE = re.compile(
    r"[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}",
    re.I,
)
# Prefer public org inboxes; skip obvious personal/private patterns later
PREF_LOCAL = (
    "presse", "medien", "media", "press", "pressoffice", "kommunikation",
    "redaktion", "info", "kontakt", "contact", "office", "hello", "partnerships",
    "partnership", "partner", "partners", "business", "pr", "comms", "communications",
    "stampa", "prensa", "premsa", "pers", "persdienst", "service", "mail",
    "webmaster", "post", "poststelle", "verwaltung", "geschaeftsstelle",
    "geschaeftsfuehrung", "marketing", "sponsors", "sponsorship", "commercial",
    "mediendesk", "pressdesk", "pressebuero", "presseteam", "akkreditierung",
    "accreditation", "acreditaciones", "mediaroom", "newsroom", "editorial",
)
SKIP_LOCAL_EXACT = {
    "noreply", "no-reply", "donotreply", "do-not-reply", "mailer-daemon",
    "abuse", "postmaster", "privacy", "dsgvo", "datenschutz", "job", "jobs",
    "bewerbung", "karriere", "career", "careers", "support-ticket",
}

CTX = ssl.create_default_context()


def load_existing():
    emails, names = set(), set()
    for path in SEEDS.glob("partners-*.json"):
        if "expand" in path.name:
            continue
        data = json.loads(path.read_text())
        for row in data:
            if row.get("email"):
                emails.add(row["email"].strip().lower())
            if row.get("name"):
                names.add(row["name"].strip().lower())
    return emails, names


def fetch(url: str, timeout: int = 12) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": UA, "Accept": "text/html"})
    with urllib.request.urlopen(req, timeout=timeout, context=CTX) as resp:
        raw = resp.read(800_000)
        charset = "utf-8"
        ctype = resp.headers.get_content_charset()
        if ctype:
            charset = ctype
        return raw.decode(charset, errors="ignore")


def extract_emails(html: str) -> list[str]:
    # Unescape common obfuscations
    text = html.replace("[at]", "@").replace("(at)", "@").replace(" [at] ", "@")
    text = re.sub(r"\s*@\s*", "@", text)
    found = []
    seen = set()
    for m in EMAIL_RE.findall(text):
        e = m.strip().lower().rstrip(".,;:)>\"'")
        if e in seen or e.endswith((".png", ".jpg", ".gif", ".svg", ".webp")):
            continue
        local, _, domain = e.partition("@")
        if not domain or "." not in domain:
            continue
        if local in SKIP_LOCAL_EXACT or local.startswith("noreply"):
            continue
        # skip long random tokens
        if len(local) > 40:
            continue
        seen.add(e)
        found.append(e)
    return found


def score_email(email: str) -> int:
    local = email.split("@", 1)[0]
    base = local.split("+", 1)[0]
    if base in PREF_LOCAL:
        return 100
    for p in PREF_LOCAL:
        if base.startswith(p) or base.endswith(p):
            return 80
    if "." in base or "-" in base:
        return 10  # likely personal
    return 40


def pick_best(emails: list[str]) -> str | None:
    if not emails:
        return None
    ranked = sorted(emails, key=score_email, reverse=True)
    best = ranked[0]
    if score_email(best) < 40:
        return None
    return best


def candidate_urls(website: str) -> list[str]:
    base = website.rstrip("/")
    paths = [
        "",
        "/impressum",
        "/impressum/",
        "/imprint",
        "/kontakt",
        "/kontakt/",
        "/contact",
        "/contact/",
        "/presse",
        "/presse/",
        "/media",
        "/medien",
        "/de/impressum",
        "/de/kontakt",
        "/de/presse",
        "/en/imprint",
        "/en/contact",
        "/club/kontakt",
        "/verein/impressum",
    ]
    return [base + p for p in paths]


# Curated org directories (website required). Emails filled by scrape or known-public.
DE_ORGS: list[dict] = [
    # Landesverbände
    {"name": "Bayerischer Fußball-Verband (BFV)", "type": "association", "country": "DE", "website": "https://www.bfv.de", "city": "München"},
    {"name": "Württembergischer Fußballverband (WFV)", "type": "association", "country": "DE", "website": "https://www.wfv.de", "city": "Stuttgart"},
    {"name": "Badischer Fußballverband (bfv)", "type": "association", "country": "DE", "website": "https://www.badfv.de", "city": "Karlsruhe"},
    {"name": "Südbadischer Fußballverband (SBFV)", "type": "association", "country": "DE", "website": "https://www.sbfv.de", "city": "Freiburg"},
    {"name": "Hessischer Fußball-Verband (HFV)", "type": "association", "country": "DE", "website": "https://www.hfv-online.de", "city": "Frankfurt"},
    {"name": "Fußballverband Rheinland (FVR)", "type": "association", "country": "DE", "website": "https://www.fv-rheinland.de", "city": "Koblenz"},
    {"name": "Saarländischer Fußballverband (SFV)", "type": "association", "country": "DE", "website": "https://www.saar-fv.de", "city": "Saarbrücken"},
    {"name": "Fußball-Verband Mittelrhein (FVM)", "type": "association", "country": "DE", "website": "https://www.fvm.de", "city": "Köln"},
    {"name": "Fußballverband Niederrhein (FVN)", "type": "association", "country": "DE", "website": "https://www.fvn.de", "city": "Duisburg"},
    {"name": "Westdeutscher Fußballverband (WDFV)", "type": "association", "country": "DE", "website": "https://www.wdfv.de", "city": "Duisburg"},
    {"name": "Niedersächsischer Fußballverband (NFV)", "type": "association", "country": "DE", "website": "https://www.nfv.de", "city": "Barsinghausen"},
    {"name": "Bremer Fußball-Verband (BFV Bremen)", "type": "association", "country": "DE", "website": "https://www.bremerfv.de", "city": "Bremen"},
    {"name": "Hamburger Fußball-Verband (HFV HH)", "type": "association", "country": "DE", "website": "https://www.hfv.de", "city": "Hamburg"},
    {"name": "Schleswig-Holsteinischer Fußballverband (SHFV)", "type": "association", "country": "DE", "website": "https://www.shfv-kiel.de", "city": "Kiel"},
    {"name": "Berliner Fußball-Verband (BFV Berlin)", "type": "association", "country": "DE", "website": "https://www.berlinerfv.de", "city": "Berlin"},
    {"name": "Fußball-Landesverband Brandenburg (FLB)", "type": "association", "country": "DE", "website": "https://www.flb.de", "city": "Cottbus"},
    {"name": "Fußballverband Sachsen-Anhalt (FSA)", "type": "association", "country": "DE", "website": "https://www.fsa-online.de", "city": "Magdeburg"},
    {"name": "Thüringer Fußball-Verband (TFV)", "type": "association", "country": "DE", "website": "https://www.tfv-erfurt.de", "city": "Erfurt"},
    {"name": "Sächsischer Fußball-Verband (SFV Sachsen)", "type": "association", "country": "DE", "website": "https://www.sfv-online.de", "city": "Leipzig"},
    {"name": "Fußballverband Mecklenburg-Vorpommern (LFVMV)", "type": "association", "country": "DE", "website": "https://www.lfvm-v.de", "city": "Rostock"},
    {"name": "Nordostdeutscher Fußballverband (NOFV)", "type": "association", "country": "DE", "website": "https://www.nofv-online.de", "city": "Berlin"},
    {"name": "Südwestdeutscher Fußballverband (SWFV)", "type": "association", "country": "DE", "website": "https://www.swfv.de", "city": "Mainz"},
    # 3. Liga / Regionalliga clubs (sample with public sites)
    {"name": "1. FC Saarbrücken", "type": "club", "country": "DE", "website": "https://www.fc-saarbruecken.de", "league": "3. Liga", "city": "Saarbrücken"},
    {"name": "FC Erzgebirge Aue", "type": "club", "country": "DE", "website": "https://www.fc-erzgebirge.de", "league": "3. Liga", "city": "Aue"},
    {"name": "SC Verl", "type": "club", "country": "DE", "website": "https://www.sportclub-verl.de", "league": "3. Liga", "city": "Verl"},
    {"name": "SV Waldhof Mannheim", "type": "club", "country": "DE", "website": "https://www.svw07.de", "league": "3. Liga", "city": "Mannheim"},
    {"name": "TSV 1860 München", "type": "club", "country": "DE", "website": "https://www.tsv1860.de", "league": "3. Liga", "city": "München"},
    {"name": "FC Viktoria Köln", "type": "club", "country": "DE", "website": "https://viktoria1904.de", "league": "3. Liga", "city": "Köln"},
    {"name": "Borussia Dortmund II", "type": "club", "country": "DE", "website": "https://www.bvb.de", "league": "3. Liga", "city": "Dortmund"},
    {"name": "VfB Stuttgart II", "type": "club", "country": "DE", "website": "https://www.vfb.de", "league": "3. Liga", "city": "Stuttgart"},
    {"name": "1. FC Schweinfurt 05", "type": "club", "country": "DE", "website": "https://fcschweinfurt1905.de", "league": "3. Liga", "city": "Schweinfurt"},
    {"name": "TSG 1899 Hoffenheim II", "type": "club", "country": "DE", "website": "https://www.tsg-hoffenheim.de", "league": "3. Liga", "city": "Sinsheim"},
    {"name": "Kickers Offenbach", "type": "club", "country": "DE", "website": "https://www.offenbach-kickers.de", "league": "Regionalliga Südwest", "city": "Offenbach"},
    {"name": "SG Barockstadt Fulda-Lehnerz", "type": "club", "country": "DE", "website": "https://www.sg-barockstadt.de", "league": "Regionalliga Südwest", "city": "Fulda"},
    {"name": "FC 08 Homburg", "type": "club", "country": "DE", "website": "https://www.fc08homburg.de", "league": "Regionalliga Südwest", "city": "Homburg"},
    {"name": "FSV Frankfurt", "type": "club", "country": "DE", "website": "https://www.fsv-frankfurt.de", "league": "Regionalliga Südwest", "city": "Frankfurt"},
    {"name": "Mainz 05 II", "type": "club", "country": "DE", "website": "https://www.mainz05.de", "league": "Regionalliga Südwest", "city": "Mainz"},
    {"name": "FC Astoria Walldorf", "type": "club", "country": "DE", "website": "https://www.fc-astoria-walldorf.de", "league": "Regionalliga Südwest", "city": "Walldorf"},
    {"name": "TSV Steinbach Haiger", "type": "club", "country": "DE", "website": "https://www.tsv-steinbach.de", "league": "Regionalliga Südwest", "city": "Haiger"},
    {"name": "Bahlinger SC", "type": "club", "country": "DE", "website": "https://www.bahlinger-sc.de", "league": "Regionalliga Südwest", "city": "Bahlingen"},
    {"name": "SGV Freiberg", "type": "club", "country": "DE", "website": "https://www.sgv-freiberg.de", "league": "Regionalliga Südwest", "city": "Freiberg"},
    {"name": "Stuttgarter Kickers", "type": "club", "country": "DE", "website": "https://www.stuttgarter-kickers.de", "league": "Regionalliga Südwest", "city": "Stuttgart"},
    {"name": "FC Bayern München II", "type": "club", "country": "DE", "website": "https://fcbayern.com", "league": "Regionalliga Bayern", "city": "München"},
    {"name": "Würzburger Kickers", "type": "club", "country": "DE", "website": "https://www.wuerzburger-kickers.de", "league": "Regionalliga Bayern", "city": "Würzburg"},
    {"name": "SpVgg Bayreuth", "type": "club", "country": "DE", "website": "https://www.spvgg-bayreuth.de", "league": "Regionalliga Bayern", "city": "Bayreuth"},
    {"name": "FV Illertissen", "type": "club", "country": "DE", "website": "https://www.fvillertissen.de", "league": "Regionalliga Bayern", "city": "Illertissen"},
    {"name": "FC Augsburg II", "type": "club", "country": "DE", "website": "https://www.fcaugsburg.de", "league": "Regionalliga Bayern", "city": "Augsburg"},
    {"name": "Türkgücü München", "type": "club", "country": "DE", "website": "https://www.tuerkguecue-muenchen.de", "league": "Regionalliga Bayern", "city": "München"},
    {"name": "SV Wacker Burghausen", "type": "club", "country": "DE", "website": "https://www.wackerburghausen.de", "league": "Regionalliga Bayern", "city": "Burghausen"},
    {"name": "FC Eintracht Bamberg", "type": "club", "country": "DE", "website": "https://www.fc-eintracht-bamberg.de", "league": "Regionalliga Bayern", "city": "Bamberg"},
    {"name": "DJK Vilzing", "type": "club", "country": "DE", "website": "https://www.djkvilzing.de", "league": "Regionalliga Bayern", "city": "Vilzing"},
    {"name": "SpVgg Ansbach", "type": "club", "country": "DE", "website": "https://www.spvgg-ansbach.de", "league": "Regionalliga Bayern", "city": "Ansbach"},
    {"name": "FC Schweinfurt 05 U23", "type": "club", "country": "DE", "website": "https://fcschweinfurt1905.de", "league": "Bayernliga", "city": "Schweinfurt"},
    {"name": "Rot-Weiss Essen U23", "type": "club", "country": "DE", "website": "https://www.rot-weiss-essen.de", "league": "Oberliga", "city": "Essen"},
    {"name": "Wuppertaler SV", "type": "club", "country": "DE", "website": "https://www.wuppertalersv.com", "league": "Regionalliga West", "city": "Wuppertal"},
    {"name": "Fortuna Köln", "type": "club", "country": "DE", "website": "https://www.fortuna-koeln.de", "league": "Regionalliga West", "city": "Köln"},
    {"name": "1. FC Bocholt", "type": "club", "country": "DE", "website": "https://www.fcbocholt.de", "league": "Regionalliga West", "city": "Bocholt"},
    {"name": "SC Wiedenbrück", "type": "club", "country": "DE", "website": "https://www.scwiedenbrueck.de", "league": "Regionalliga West", "city": "Rheda-Wiedenbrück"},
    {"name": "SV Rödinghausen", "type": "club", "country": "DE", "website": "https://www.svr.info", "league": "Regionalliga West", "city": "Rödinghausen"},
    {"name": "FC Gütersloh", "type": "club", "country": "DE", "website": "https://www.fcguetersloh.de", "league": "Regionalliga West", "city": "Gütersloh"},
    {"name": "Sportfreunde Lotte", "type": "club", "country": "DE", "website": "https://www.sf-lotte.de", "league": "Regionalliga West", "city": "Lotte"},
    {"name": "1. FC Düren", "type": "club", "country": "DE", "website": "https://www.1-fc-dueren.de", "league": "Regionalliga West", "city": "Düren"},
    {"name": "FC Schalke 04 II", "type": "club", "country": "DE", "website": "https://schalke04.de", "league": "Regionalliga West", "city": "Gelsenkirchen"},
    {"name": "Borussia Mönchengladbach II", "type": "club", "country": "DE", "website": "https://www.borussia.de", "league": "Regionalliga West", "city": "Mönchengladbach"},
    {"name": "SV Lippstadt 08", "type": "club", "country": "DE", "website": "https://www.svlippstadt08.de", "league": "Oberliga Westfalen", "city": "Lippstadt"},
    {"name": "FC Wegberg-Beeck", "type": "club", "country": "DE", "website": "https://www.fc-wegberg-beeck.de", "league": "Mittelrheinliga", "city": "Wegberg"},
    {"name": "SV Straelen", "type": "club", "country": "DE", "website": "https://www.svstraelen.de", "league": "Oberliga Niederrhein", "city": "Straelen"},
    {"name": "Teutonia Ottensen", "type": "club", "country": "DE", "website": "https://www.teutonia-ottensen.de", "league": "Regionalliga Nord", "city": "Hamburg"},
    {"name": "FC St. Pauli II", "type": "club", "country": "DE", "website": "https://www.fcstpauli.com", "league": "Regionalliga Nord", "city": "Hamburg"},
    {"name": "Hamburger SV II", "type": "club", "country": "DE", "website": "https://www.hsv.de", "league": "Regionalliga Nord", "city": "Hamburg"},
    {"name": "Werder Bremen II", "type": "club", "country": "DE", "website": "https://www.werder.de", "league": "Regionalliga Nord", "city": "Bremen"},
    {"name": "Holstein Kiel II", "type": "club", "country": "DE", "website": "https://www.holstein-kiel.de", "league": "Regionalliga Nord", "city": "Kiel"},
    {"name": "VfB Lübeck", "type": "club", "country": "DE", "website": "https://www.vfb-luebeck.de", "league": "Regionalliga Nord", "city": "Lübeck"},
    {"name": "SV Meppen", "type": "club", "country": "DE", "website": "https://www.svmeppen.de", "league": "Regionalliga Nord", "city": "Meppen"},
    {"name": "BSV Kickers Emden", "type": "club", "country": "DE", "website": "https://www.kickers-emden.de", "league": "Regionalliga Nord", "city": "Emden"},
    {"name": "Phonix Lübeck", "type": "club", "country": "DE", "website": "https://www.1fcphuenix.de", "league": "Regionalliga Nord", "city": "Lübeck"},
    {"name": "SV Todesfelde", "type": "club", "country": "DE", "website": "https://www.svtodesfelde.de", "league": "Regionalliga Nord", "city": "Todesfelde"},
    {"name": "Bremer SV", "type": "club", "country": "DE", "website": "https://www.bremersv.de", "league": "Regionalliga Nord", "city": "Bremen"},
    {"name": "SC Weiche Flensburg 08", "type": "club", "country": "DE", "website": "https://www.scweiche08.de", "league": "Regionalliga Nord", "city": "Flensburg"},
    {"name": "Eintracht Norderstedt", "type": "club", "country": "DE", "website": "https://www.fc-eintracht-norderstedt.de", "league": "Regionalliga Nord", "city": "Norderstedt"},
    {"name": "FC Carl Zeiss Jena", "type": "club", "country": "DE", "website": "https://www.fc-carlzeiss-jena.de", "league": "Regionalliga Nordost", "city": "Jena"},
    {"name": "Chemnitzer FC", "type": "club", "country": "DE", "website": "https://www.chemnitzerfc.de", "league": "Regionalliga Nordost", "city": "Chemnitz"},
    {"name": "Lokomotive Leipzig", "type": "club", "country": "DE", "website": "https://www.lok-leipzig.com", "league": "Regionalliga Nordost", "city": "Leipzig"},
    {"name": "BFC Dynamo", "type": "club", "country": "DE", "website": "https://www.bfc.com", "league": "Regionalliga Nordost", "city": "Berlin"},
    {"name": "Hertha BSC II", "type": "club", "country": "DE", "website": "https://www.herthabsc.com", "league": "Regionalliga Nordost", "city": "Berlin"},
    {"name": "Union Berlin II", "type": "club", "country": "DE", "website": "https://www.fc-union-berlin.de", "league": "Regionalliga Nordost", "city": "Berlin"},
    {"name": "ZFC Meuselwitz", "type": "club", "country": "DE", "website": "https://www.zfc-meuselwitz.de", "league": "Regionalliga Nordost", "city": "Meuselwitz"},
    {"name": "FC Eilenburg", "type": "club", "country": "DE", "website": "https://www.fc-eilenburg.de", "league": "Regionalliga Nordost", "city": "Eilenburg"},
    {"name": "Greifswalder FC", "type": "club", "country": "DE", "website": "https://www.greifswalder-fc.de", "league": "Regionalliga Nordost", "city": "Greifswald"},
    {"name": "FSV Zwickau", "type": "club", "country": "DE", "website": "https://www.fsv-zwickau.de", "league": "Regionalliga Nordost", "city": "Zwickau"},
    {"name": "Babelsberg 03", "type": "club", "country": "DE", "website": "https://www.babelsberg03.de", "league": "Regionalliga Nordost", "city": "Potsdam"},
    {"name": "Luckenwalde", "type": "club", "country": "DE", "website": "https://www.fcluckenwalde.de", "league": "Regionalliga Nordost", "city": "Luckenwalde"},
    {"name": "Hallescher FC", "type": "club", "country": "DE", "website": "https://www.hallescherfc.de", "league": "Regionalliga Nordost", "city": "Halle"},
    {"name": "Rot-Weiß Erfurt", "type": "club", "country": "DE", "website": "https://www.rot-weiss-erfurt.de", "league": "Regionalliga Nordost", "city": "Erfurt"},
    # Stadiums / facilities
    {"name": "Allianz Arena", "type": "facility", "country": "DE", "website": "https://allianz-arena.com", "city": "München"},
    {"name": "Signal Iduna Park", "type": "facility", "country": "DE", "website": "https://www.signal-iduna-park.de", "city": "Dortmund"},
    {"name": "Deutsche Bank Park", "type": "facility", "country": "DE", "website": "https://www.deutschebankpark.de", "city": "Frankfurt"},
    {"name": "Mercedes-Benz Arena Stuttgart", "type": "facility", "country": "DE", "website": "https://www.mercedes-benz-arena-stuttgart.de", "city": "Stuttgart"},
    {"name": "Olympiastadion Berlin", "type": "facility", "country": "DE", "website": "https://olympiastadion.berlin", "city": "Berlin"},
    {"name": "Veltins-Arena", "type": "facility", "country": "DE", "website": "https://www.veltins-arena.de", "city": "Gelsenkirchen"},
    {"name": "Red Bull Arena Leipzig", "type": "facility", "country": "DE", "website": "https://www.redbullarena-leipzig.com", "city": "Leipzig"},
    {"name": "BayArena", "type": "facility", "country": "DE", "website": "https://www.bayer04.de", "city": "Leverkusen"},
    {"name": "Volkswagen Arena", "type": "facility", "country": "DE", "website": "https://www.volkswagen-arena.de", "city": "Wolfsburg"},
    {"name": "MHPArena", "type": "facility", "country": "DE", "website": "https://www.mhparena.de", "city": "Stuttgart"},
    # DE media / radio / regional sport desks
    {"name": "BR Sport", "type": "media", "country": "DE", "website": "https://www.br.de/sport", "city": "München"},
    {"name": "WDR Sport", "type": "media", "country": "DE", "website": "https://www1.wdr.de/sport", "city": "Köln"},
    {"name": "NDR Sport", "type": "media", "country": "DE", "website": "https://www.ndr.de/sport", "city": "Hamburg"},
    {"name": "SWR Sport", "type": "media", "country": "DE", "website": "https://www.swr.de/sport", "city": "Stuttgart"},
    {"name": "HR Sport", "type": "media", "country": "DE", "website": "https://www.hr.de/sport", "city": "Frankfurt"},
    {"name": "rbb Sport", "type": "media", "country": "DE", "website": "https://www.rbb24.de/sport", "city": "Berlin"},
    {"name": "MDR Sport", "type": "media", "country": "DE", "website": "https://www.mdr.de/sport", "city": "Leipzig"},
    {"name": "SR Sport", "type": "media", "country": "DE", "website": "https://www.sr.de/sport", "city": "Saarbrücken"},
    {"name": "Radio Bremen Sport", "type": "media", "country": "DE", "website": "https://www.radiobremen.de/sport", "city": "Bremen"},
    {"name": "Sportschau", "type": "media", "country": "DE", "website": "https://www.sportschau.de", "city": "Köln"},
    {"name": "Transfermarkt", "type": "media", "country": "DE", "website": "https://www.transfermarkt.de", "city": "Hamburg"},
    {"name": "Fussball.de", "type": "media", "country": "DE", "website": "https://www.fussball.de", "city": "Frankfurt"},
    {"name": "DFB Podcast / DFB Medien", "type": "media", "country": "DE", "website": "https://www.dfb.de", "city": "Frankfurt"},
    {"name": "11 Freunde", "type": "media", "country": "DE", "website": "https://www.11freunde.de", "city": "Berlin"},
    {"name": "Spox", "type": "media", "country": "DE", "website": "https://www.spox.com", "city": "München"},
    {"name": "Goal Deutschland", "type": "media", "country": "DE", "website": "https://www.goal.com/de", "city": "München"},
    {"name": "90min Deutschland", "type": "media", "country": "DE", "website": "https://www.90min.com/de", "city": "Berlin"},
    {"name": "Ligainsider", "type": "media", "country": "DE", "website": "https://www.ligainsider.de", "city": "Berlin"},
    {"name": "Fussballtransfers", "type": "media", "country": "DE", "website": "https://www.fussballtransfers.com", "city": "Berlin"},
    {"name": "Revierreporter", "type": "media", "country": "DE", "website": "https://www.revierreporter.de", "city": "Essen"},
    {"name": "Ruhr Nachrichten Sport", "type": "media", "country": "DE", "website": "https://www.ruhrnachrichten.de", "city": "Dortmund"},
    {"name": "WAZ Sport", "type": "media", "country": "DE", "website": "https://www.waz.de", "city": "Essen"},
    {"name": "Rheinische Post Sport", "type": "media", "country": "DE", "website": "https://rp-online.de", "city": "Düsseldorf"},
    {"name": "Kölner Stadt-Anzeiger Sport", "type": "media", "country": "DE", "website": "https://www.ksta.de", "city": "Köln"},
    {"name": "Frankfurter Rundschau Sport", "type": "media", "country": "DE", "website": "https://www.fr.de", "city": "Frankfurt"},
    {"name": "Stuttgarter Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.stuttgarter-zeitung.de", "city": "Stuttgart"},
    {"name": "Süddeutsche Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.sueddeutsche.de", "city": "München"},
    {"name": "Die Welt Sport", "type": "media", "country": "DE", "website": "https://www.welt.de", "city": "Berlin"},
    {"name": "FAZ Sport", "type": "media", "country": "DE", "website": "https://www.faz.net", "city": "Frankfurt"},
    {"name": "Tagesspiegel Sport", "type": "media", "country": "DE", "website": "https://www.tagesspiegel.de", "city": "Berlin"},
    {"name": "Morgenpost Sport", "type": "media", "country": "DE", "website": "https://www.morgenpost.de", "city": "Berlin"},
    {"name": "BZ Berlin Sport", "type": "media", "country": "DE", "website": "https://www.bz-berlin.de", "city": "Berlin"},
    {"name": "Bild Sportredaktion", "type": "media", "country": "DE", "website": "https://www.bild.de", "city": "Berlin"},
    {"name": "Abendzeitung München Sport", "type": "media", "country": "DE", "website": "https://www.abendzeitung-muenchen.de", "city": "München"},
    {"name": "tz München Sport", "type": "media", "country": "DE", "website": "https://www.tz.de", "city": "München"},
    {"name": "Münchner Merkur Sport", "type": "media", "country": "DE", "website": "https://www.merkur.de", "city": "München"},
    {"name": "Nürnberger Nachrichten Sport", "type": "media", "country": "DE", "website": "https://www.nn.de", "city": "Nürnberg"},
    {"name": "Leipziger Volkszeitung Sport", "type": "media", "country": "DE", "website": "https://www.lvz.de", "city": "Leipzig"},
    {"name": "Mitteldeutsche Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.mz.de", "city": "Halle"},
    {"name": "Ostsee-Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.ostsee-zeitung.de", "city": "Rostock"},
    {"name": "Kieler Nachrichten Sport", "type": "media", "country": "DE", "website": "https://www.kn-online.de", "city": "Kiel"},
    {"name": "Hamburger Abendblatt Sport", "type": "media", "country": "DE", "website": "https://www.abendblatt.de", "city": "Hamburg"},
    {"name": "Weser-Kurier Sport", "type": "media", "country": "DE", "website": "https://www.weser-kurier.de", "city": "Bremen"},
    {"name": "Neue Osnabrücker Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.noz.de", "city": "Osnabrück"},
    {"name": "Augsburger Allgemeine Sport", "type": "media", "country": "DE", "website": "https://www.augsburger-allgemeine.de", "city": "Augsburg"},
    {"name": "Main-Post Sport", "type": "media", "country": "DE", "website": "https://www.mainpost.de", "city": "Würzburg"},
    {"name": "Badische Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.badische-zeitung.de", "city": "Freiburg"},
    {"name": "Mannheimer Morgen Sport", "type": "media", "country": "DE", "website": "https://www.mannheimer-morgen.de", "city": "Mannheim"},
    {"name": "Rhein-Neckar-Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.rnz.de", "city": "Heidelberg"},
    {"name": "Saarbrücker Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.saarbruecker-zeitung.de", "city": "Saarbrücken"},
    {"name": "Trierischer Volksfreund Sport", "type": "media", "country": "DE", "website": "https://www.volksfreund.de", "city": "Trier"},
    {"name": "Lausitzer Rundschau Sport", "type": "media", "country": "DE", "website": "https://www.lr-online.de", "city": "Cottbus"},
    {"name": "Märkische Allgemeine Sport", "type": "media", "country": "DE", "website": "https://www.maz-online.de", "city": "Potsdam"},
    {"name": "Thüringer Allgemeine Sport", "type": "media", "country": "DE", "website": "https://www.thueringer-allgemeine.de", "city": "Erfurt"},
    {"name": "Freie Presse Chemnitz Sport", "type": "media", "country": "DE", "website": "https://www.freiepresse.de", "city": "Chemnitz"},
    {"name": "Dresdner Neueste Nachrichten Sport", "type": "media", "country": "DE", "website": "https://www.dnn.de", "city": "Dresden"},
    {"name": "Sächsische Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.saechsische.de", "city": "Dresden"},
    {"name": "Volksstimme Magdeburg Sport", "type": "media", "country": "DE", "website": "https://www.volksstimme.de", "city": "Magdeburg"},
    {"name": "Aachener Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.aachener-zeitung.de", "city": "Aachen"},
    {"name": "Bonner General-Anzeiger Sport", "type": "media", "country": "DE", "website": "https://www.general-anzeiger-bonn.de", "city": "Bonn"},
    {"name": "Express Köln Sport", "type": "media", "country": "DE", "website": "https://www.express.de", "city": "Köln"},
    {"name": "Westfalenpost Sport", "type": "media", "country": "DE", "website": "https://www.wp.de", "city": "Hagen"},
    {"name": "Westfälische Nachrichten Sport", "type": "media", "country": "DE", "website": "https://www.wn.de", "city": "Münster"},
    {"name": "Neue Westfälische Sport", "type": "media", "country": "DE", "website": "https://www.nw.de", "city": "Bielefeld"},
    {"name": "Mindener Tageblatt Sport", "type": "media", "country": "DE", "website": "https://www.mt.de", "city": "Minden"},
    {"name": "Göttinger Tageblatt Sport", "type": "media", "country": "DE", "website": "https://www.goettinger-tageblatt.de", "city": "Göttingen"},
    {"name": "Hannoversche Allgemeine Sport", "type": "media", "country": "DE", "website": "https://www.haz.de", "city": "Hannover"},
    {"name": "Braunschweiger Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.braunschweiger-zeitung.de", "city": "Braunschweig"},
    {"name": "Cellesche Zeitung Sport", "type": "media", "country": "DE", "website": "https://www.cellesche-zeitung.de", "city": "Celle"},
]

EU_ORGS: list[dict] = [
    # More PL / La Liga / Serie A / Ligue 1 / Eredivisie / Belgium / Austria / Swiss
    {"name": "Aston Villa", "type": "club", "country": "GB", "website": "https://www.avfc.co.uk", "league": "Premier League"},
    {"name": "Newcastle United", "type": "club", "country": "GB", "website": "https://www.nufc.co.uk", "league": "Premier League"},
    {"name": "West Ham United", "type": "club", "country": "GB", "website": "https://www.whufc.com", "league": "Premier League"},
    {"name": "Brighton & Hove Albion", "type": "club", "country": "GB", "website": "https://www.brightonandhovealbion.com", "league": "Premier League"},
    {"name": "Crystal Palace", "type": "club", "country": "GB", "website": "https://www.cpfc.co.uk", "league": "Premier League"},
    {"name": "Fulham FC", "type": "club", "country": "GB", "website": "https://www.fulhamfc.com", "league": "Premier League"},
    {"name": "Brentford FC", "type": "club", "country": "GB", "website": "https://www.brentfordfc.com", "league": "Premier League"},
    {"name": "Wolverhampton Wanderers", "type": "club", "country": "GB", "website": "https://www.wolves.co.uk", "league": "Premier League"},
    {"name": "Everton FC", "type": "club", "country": "GB", "website": "https://www.evertonfc.com", "league": "Premier League"},
    {"name": "Nottingham Forest", "type": "club", "country": "GB", "website": "https://www.nottinghamforest.co.uk", "league": "Premier League"},
    {"name": "AFC Bournemouth", "type": "club", "country": "GB", "website": "https://www.afcb.co.uk", "league": "Premier League"},
    {"name": "Leicester City", "type": "club", "country": "GB", "website": "https://www.lcfc.com", "league": "Championship"},
    {"name": "Leeds United", "type": "club", "country": "GB", "website": "https://www.leedsunited.com", "league": "Championship"},
    {"name": "Southampton FC", "type": "club", "country": "GB", "website": "https://www.southamptonfc.com", "league": "Championship"},
    {"name": "Ipswich Town", "type": "club", "country": "GB", "website": "https://www.itfc.co.uk", "league": "Championship"},
    {"name": "Sevilla FC", "type": "club", "country": "ES", "website": "https://www.sevillafc.es", "league": "La Liga"},
    {"name": "Real Sociedad", "type": "club", "country": "ES", "website": "https://www.realsociedad.eus", "league": "La Liga"},
    {"name": "Athletic Club", "type": "club", "country": "ES", "website": "https://www.athletic-club.eus", "league": "La Liga"},
    {"name": "Real Betis", "type": "club", "country": "ES", "website": "https://www.realbetisbalompie.es", "league": "La Liga"},
    {"name": "Valencia CF", "type": "club", "country": "ES", "website": "https://www.valenciacf.com", "league": "La Liga"},
    {"name": "Girona FC", "type": "club", "country": "ES", "website": "https://www.gironafc.cat", "league": "La Liga"},
    {"name": "Osasuna", "type": "club", "country": "ES", "website": "https://www.osasuna.es", "league": "La Liga"},
    {"name": "Celta Vigo", "type": "club", "country": "ES", "website": "https://rccelta.es", "league": "La Liga"},
    {"name": "Getafe CF", "type": "club", "country": "ES", "website": "https://www.getafecf.com", "league": "La Liga"},
    {"name": "RCD Mallorca", "type": "club", "country": "ES", "website": "https://www.rcdmallorca.es", "league": "La Liga"},
    {"name": "RCD Espanyol", "type": "club", "country": "ES", "website": "https://www.rcdespanyol.com", "league": "La Liga"},
    {"name": "AS Roma", "type": "club", "country": "IT", "website": "https://www.asroma.com", "league": "Serie A"},
    {"name": "SS Lazio", "type": "club", "country": "IT", "website": "https://www.sslazio.it", "league": "Serie A"},
    {"name": "ACF Fiorentina", "type": "club", "country": "IT", "website": "https://www.acffiorentina.com", "league": "Serie A"},
    {"name": "Atalanta BC", "type": "club", "country": "IT", "website": "https://www.atalanta.it", "league": "Serie A"},
    {"name": "Torino FC", "type": "club", "country": "IT", "website": "https://www.torinofc.it", "league": "Serie A"},
    {"name": "Bologna FC", "type": "club", "country": "IT", "website": "https://www.bolognafc.it", "league": "Serie A"},
    {"name": "Udinese Calcio", "type": "club", "country": "IT", "website": "https://www.udinese.it", "league": "Serie A"},
    {"name": "Genoa CFC", "type": "club", "country": "IT", "website": "https://genoacfc.it", "league": "Serie A"},
    {"name": "Cagliari Calcio", "type": "club", "country": "IT", "website": "https://www.cagliaricalcio.com", "league": "Serie A"},
    {"name": "Olympique Lyonnais", "type": "club", "country": "FR", "website": "https://www.ol.fr", "league": "Ligue 1"},
    {"name": "AS Monaco", "type": "club", "country": "FR", "website": "https://www.asmonaco.com", "league": "Ligue 1"},
    {"name": "OGC Nice", "type": "club", "country": "FR", "website": "https://www.ogcnice.com", "league": "Ligue 1"},
    {"name": "LOS Lille", "type": "club", "country": "FR", "website": "https://www.losc.fr", "league": "Ligue 1"},
    {"name": "Stade Rennais", "type": "club", "country": "FR", "website": "https://www.staderennais.com", "league": "Ligue 1"},
    {"name": "RC Lens", "type": "club", "country": "FR", "website": "https://www.rclens.fr", "league": "Ligue 1"},
    {"name": "RC Strasbourg", "type": "club", "country": "FR", "website": "https://www.rcstrasbourgalsace.fr", "league": "Ligue 1"},
    {"name": "FC Nantes", "type": "club", "country": "FR", "website": "https://www.fcnantes.com", "league": "Ligue 1"},
    {"name": "PSV Eindhoven", "type": "club", "country": "NL", "website": "https://www.psv.nl", "league": "Eredivisie"},
    {"name": "Feyenoord", "type": "club", "country": "NL", "website": "https://www.feyenoord.nl", "league": "Eredivisie"},
    {"name": "AZ Alkmaar", "type": "club", "country": "NL", "website": "https://www.az.nl", "league": "Eredivisie"},
    {"name": "FC Twente", "type": "club", "country": "NL", "website": "https://www.fctwente.nl", "league": "Eredivisie"},
    {"name": "FC Utrecht", "type": "club", "country": "NL", "website": "https://www.fcutrecht.nl", "league": "Eredivisie"},
    {"name": "Club Brugge", "type": "club", "country": "BE", "website": "https://www.clubbrugge.be", "league": "Belgian Pro League"},
    {"name": "RSC Anderlecht", "type": "club", "country": "BE", "website": "https://www.rsca.be", "league": "Belgian Pro League"},
    {"name": "Union Saint-Gilloise", "type": "club", "country": "BE", "website": "https://www.rusg.brussels", "league": "Belgian Pro League"},
    {"name": "KRC Genk", "type": "club", "country": "BE", "website": "https://www.krcgenk.be", "league": "Belgian Pro League"},
    {"name": "Standard Liège", "type": "club", "country": "BE", "website": "https://standard.be", "league": "Belgian Pro League"},
    {"name": "Red Bull Salzburg", "type": "club", "country": "AT", "website": "https://www.redbullsalzburg.at", "league": "Bundesliga AT"},
    {"name": "SK Sturm Graz", "type": "club", "country": "AT", "website": "https://www.sksturm.at", "league": "Bundesliga AT"},
    {"name": "FK Austria Wien", "type": "club", "country": "AT", "website": "https://www.fk-austria.at", "league": "Bundesliga AT"},
    {"name": "Rapid Wien", "type": "club", "country": "AT", "website": "https://www.skrapid.at", "league": "Bundesliga AT"},
    {"name": "LASK", "type": "club", "country": "AT", "website": "https://www.lask.at", "league": "Bundesliga AT"},
    {"name": "Young Boys Bern", "type": "club", "country": "CH", "website": "https://www.bscyb.ch", "league": "Super League"},
    {"name": "FC Basel", "type": "club", "country": "CH", "website": "https://www.fcb.ch", "league": "Super League"},
    {"name": "FC Zürich", "type": "club", "country": "CH", "website": "https://www.fcz.ch", "league": "Super League"},
    {"name": "FC Lugano", "type": "club", "country": "CH", "website": "https://www.fclugano.com", "league": "Super League"},
    {"name": "Servette FC", "type": "club", "country": "CH", "website": "https://www.servettefc.ch", "league": "Super League"},
    {"name": "Sporting CP", "type": "club", "country": "PT", "website": "https://www.sporting.pt", "league": "Primeira Liga"},
    {"name": "SC Braga", "type": "club", "country": "PT", "website": "https://scbraga.pt", "league": "Primeira Liga"},
    {"name": "Vitória SC", "type": "club", "country": "PT", "website": "https://www.vitoriasc.pt", "league": "Primeira Liga"},
    {"name": "Galatasaray", "type": "club", "country": "TR", "website": "https://www.galatasaray.org", "league": "Süper Lig"},
    {"name": "Fenerbahçe", "type": "club", "country": "TR", "website": "https://www.fenerbahce.org", "league": "Süper Lig"},
    {"name": "Beşiktaş", "type": "club", "country": "TR", "website": "https://www.bjk.com.tr", "league": "Süper Lig"},
    {"name": "Shakhtar Donetsk", "type": "club", "country": "UA", "website": "https://shakhtar.com", "league": "Ukrainian Premier League"},
    {"name": "Dynamo Kyiv", "type": "club", "country": "UA", "website": "https://fcdynamo.com", "league": "Ukrainian Premier League"},
    {"name": "Celtic FC Media", "type": "club", "country": "GB", "website": "https://www.celticfc.com", "league": "Scottish Premiership"},
    {"name": "Rangers FC", "type": "club", "country": "GB", "website": "https://www.rangers.co.uk", "league": "Scottish Premiership"},
    {"name": "UEFA Media", "type": "league", "country": "CH", "website": "https://www.uefa.com"},
    {"name": "FIFA Media", "type": "league", "country": "CH", "website": "https://www.fifa.com"},
    {"name": "CONMEBOL", "type": "association", "country": "PY", "website": "https://www.conmebol.com"},
    {"name": "CAF", "type": "association", "country": "EG", "website": "https://www.cafonline.com"},
    {"name": "AFC Asian Football Confederation", "type": "association", "country": "MY", "website": "https://www.the-afc.com"},
    {"name": "CONCACAF", "type": "association", "country": "US", "website": "https://www.concacaf.com"},
    {"name": "Royal Spanish Football Federation (RFEF)", "type": "association", "country": "ES", "website": "https://www.rfef.es"},
    {"name": "FIGC", "type": "association", "country": "IT", "website": "https://www.figc.it"},
    {"name": "FFF", "type": "association", "country": "FR", "website": "https://www.fff.fr"},
    {"name": "KNVB", "type": "association", "country": "NL", "website": "https://www.knvb.nl"},
    {"name": "RBFA Belgium", "type": "association", "country": "BE", "website": "https://www.rbfa.be"},
    {"name": "ÖFB", "type": "association", "country": "AT", "website": "https://www.oefb.at"},
    {"name": "SFV Schweiz", "type": "association", "country": "CH", "website": "https://www.football.ch"},
    {"name": "FPF Portugal", "type": "association", "country": "PT", "website": "https://www.fpf.pt"},
    {"name": "PFSZ Poland", "type": "association", "country": "PL", "website": "https://www.pzpn.pl"},
    {"name": "FAČR Czechia", "type": "association", "country": "CZ", "website": "https://www.fotbal.cz"},
    {"name": "HNS Croatia", "type": "association", "country": "HR", "website": "https://hns-cff.hr"},
    {"name": "FSF Serbia", "type": "association", "country": "RS", "website": "https://fss.rs"},
    {"name": "Danish FA (DBU)", "type": "association", "country": "DK", "website": "https://www.dbu.dk"},
    {"name": "SvFF Sweden", "type": "association", "country": "SE", "website": "https://www.svenskfotboll.se"},
    {"name": "NFF Norway", "type": "association", "country": "NO", "website": "https://www.fotball.no"},
    {"name": "SPL / SPFL", "type": "league", "country": "GB", "website": "https://spfl.co.uk"},
    {"name": "EFL", "type": "league", "country": "GB", "website": "https://www.efl.com"},
    {"name": "Ligue de Football Professionnel", "type": "league", "country": "FR", "website": "https://www.lfp.fr"},
    {"name": "Serie A TIM League", "type": "league", "country": "IT", "website": "https://www.legaseriea.it"},
    {"name": "Eredivisie CV", "type": "league", "country": "NL", "website": "https://eredivisie.nl"},
    {"name": "Pro League Belgium", "type": "league", "country": "BE", "website": "https://www.proleague.be"},
    {"name": "Österreichische Bundesliga", "type": "league", "country": "AT", "website": "https://www.bundesliga.at"},
    {"name": "Swiss Football League", "type": "league", "country": "CH", "website": "https://www.sfl.ch"},
    {"name": "Liga Portugal", "type": "league", "country": "PT", "website": "https://www.ligaportugal.pt"},
    {"name": "Ekstraklasa", "type": "league", "country": "PL", "website": "https://www.ekstraklasa.org"},
    {"name": "ESPN FC", "type": "media", "country": "US", "website": "https://www.espn.com/soccer"},
    {"name": "The Athletic Football", "type": "media", "country": "GB", "website": "https://theathletic.com"},
    {"name": "BBC Sport Football", "type": "media", "country": "GB", "website": "https://www.bbc.com/sport/football"},
    {"name": "Sky Sports", "type": "media", "country": "GB", "website": "https://www.skysports.com"},
    {"name": "BT Sport / TNT Sports", "type": "media", "country": "GB", "website": "https://www.tntsports.co.uk"},
    {"name": "L'Équipe", "type": "media", "country": "FR", "website": "https://www.lequipe.fr"},
    {"name": "Gazzetta dello Sport", "type": "media", "country": "IT", "website": "https://www.gazzetta.it"},
    {"name": "Corriere dello Sport", "type": "media", "country": "IT", "website": "https://www.corrieredellosport.it"},
    {"name": "Marca", "type": "media", "country": "ES", "website": "https://www.marca.com"},
    {"name": "AS.com", "type": "media", "country": "ES", "website": "https://as.com"},
    {"name": "Mundo Deportivo", "type": "media", "country": "ES", "website": "https://www.mundodeportivo.com"},
    {"name": "Sport.es", "type": "media", "country": "ES", "website": "https://www.sport.es"},
    {"name": "Voetbal International", "type": "media", "country": "NL", "website": "https://www.vi.nl"},
    {"name": "AD Sportwereld", "type": "media", "country": "NL", "website": "https://www.ad.nl/sport"},
    {"name": "Het Nieuwsblad Sport", "type": "media", "country": "BE", "website": "https://www.nieuwsblad.be"},
    {"name": "HLN Sport", "type": "media", "country": "BE", "website": "https://www.hln.be"},
    {"name": "Kronen Zeitung Sport", "type": "media", "country": "AT", "website": "https://www.kren.at"},
    {"name": "Blick Sport", "type": "media", "country": "CH", "website": "https://www.blick.ch"},
    {"name": "A Bola", "type": "media", "country": "PT", "website": "https://www.abola.pt"},
    {"name": "Record Portugal", "type": "media", "country": "PT", "website": "https://www.record.pt"},
    {"name": "O Jogo", "type": "media", "country": "PT", "website": "https://www.ojogo.pt"},
    {"name": "FourFourTwo", "type": "media", "country": "GB", "website": "https://www.fourfourtwo.com"},
    {"name": "Squawka", "type": "media", "country": "GB", "website": "https://www.squawka.com"},
    {"name": "WhoScored", "type": "media", "country": "GB", "website": "https://www.whoscored.com"},
    {"name": "Opta / Stats Perform", "type": "tech", "country": "GB", "website": "https://www.statsperform.com"},
    {"name": "Sofascore", "type": "tech", "country": "HR", "website": "https://www.sofascore.com"},
    {"name": "Flashscore", "type": "tech", "country": "CZ", "website": "https://www.flashscore.com"},
    {"name": "FotMob", "type": "tech", "country": "NO", "website": "https://www.fotmob.com"},
]

BIZ_ORGS: list[dict] = [
    {"name": "Adidas Football", "type": "brand", "country": "DE", "website": "https://www.adidas.com"},
    {"name": "Nike Football", "type": "brand", "country": "US", "website": "https://www.nike.com"},
    {"name": "Puma Football", "type": "brand", "country": "DE", "website": "https://www.puma.com"},
    {"name": "Umbro", "type": "brand", "country": "GB", "website": "https://www.umbro.com"},
    {"name": "New Balance Football", "type": "brand", "country": "US", "website": "https://www.newbalance.com"},
    {"name": "Under Armour", "type": "brand", "country": "US", "website": "https://www.underarmour.com"},
    {"name": "Castore", "type": "brand", "country": "GB", "website": "https://www.castore.com"},
    {"name": "Hummel", "type": "brand", "country": "DK", "website": "https://www.hummel.net"},
    {"name": "Jako", "type": "brand", "country": "DE", "website": "https://www.jako.de"},
    {"name": "Erima", "type": "brand", "country": "DE", "website": "https://www.erima.de"},
    {"name": "Select Sport", "type": "brand", "country": "DK", "website": "https://www.select-sport.com"},
    {"name": "Derbystar", "type": "brand", "country": "DE", "website": "https://www.derbystar.de"},
    {"name": "Uhlsport", "type": "brand", "country": "DE", "website": "https://www.uhlsport.com"},
    {"name": "Macron", "type": "brand", "country": "IT", "website": "https://www.macron.com"},
    {"name": "Kappa", "type": "brand", "country": "IT", "website": "https://www.kappa.com"},
    {"name": "Joma", "type": "brand", "country": "ES", "website": "https://www.joma-sport.com"},
    {"name": "Skechers Football", "type": "brand", "country": "US", "website": "https://www.skechers.com"},
    {"name": "Mizuno Football", "type": "brand", "country": "JP", "website": "https://www.mizuno.com"},
    {"name": "asics Football", "type": "brand", "country": "JP", "website": "https://www.asics.com"},
    {"name": "Rogue Agency / player agencies sample", "type": "agency", "country": "GB", "website": "https://www.stelar.com"},
    {"name": "Stellar Group", "type": "agency", "country": "GB", "website": "https://www.stellargroup.com"},
    {"name": "Unique Sports Management", "type": "agency", "country": "GB", "website": "https://www.uniquesportsmanagement.com"},
    {"name": "Base Soccer Agency", "type": "agency", "country": "GB", "website": "https://www.basesoccer.com"},
    {"name": "Gestifute", "type": "agency", "country": "PT", "website": "https://www.gestifute.com"},
    {"name": "ROGON Sportmanagement", "type": "agency", "country": "DE", "website": "https://www.rogon.de"},
    {"name": "ARETE Sports Group", "type": "agency", "country": "DE", "website": "https://www.arete.de"},
    {"name": "PRO Profil", "type": "agency", "country": "DE", "website": "https://www.proprofil.de"},
    {"name": "Soccer International Agency", "type": "agency", "country": "DE", "website": "https://www.soccer-international.de"},
    {"name": "Wyscout / Hudl", "type": "tech", "country": "IT", "website": "https://wyscout.com"},
    {"name": "Hudl", "type": "tech", "country": "US", "website": "https://www.hudl.com"},
    {"name": "StatsBomb", "type": "tech", "country": "GB", "website": "https://statsbomb.com"},
    {"name": "SciSports", "type": "tech", "country": "NL", "website": "https://www.scisports.com"},
    {"name": "SkillCorner", "type": "tech", "country": "FR", "website": "https://www.skillcorner.com"},
    {"name": "Second Spectrum", "type": "tech", "country": "US", "website": "https://www.secondspectrum.com"},
    {"name": "Catapult Sports", "type": "tech", "country": "AU", "website": "https://www.catapultsports.com"},
    {"name": "STATSports", "type": "tech", "country": "GB", "website": "https://statsports.com"},
    {"name": "PlayerMaker", "type": "tech", "country": "GB", "website": "https://www.playermaker.com"},
    {"name": "Trace Sports", "type": "tech", "country": "US", "website": "https://www.traceup.com"},
    {"name": "Veo", "type": "tech", "country": "DK", "website": "https://www.veo.co"},
    {"name": "Pixellot", "type": "tech", "country": "IL", "website": "https://www.pixellot.tv"},
    {"name": "Sportradar", "type": "tech", "country": "CH", "website": "https://sportradar.com"},
    {"name": "Genius Sports", "type": "tech", "country": "GB", "website": "https://www.geniussports.com"},
    {"name": "Bet365 Partnerships", "type": "brand", "country": "GB", "website": "https://www.bet365.com"},
    {"name": "DraftKings", "type": "brand", "country": "US", "website": "https://www.draftkings.com"},
    {"name": "EA Sports FC", "type": "brand", "country": "US", "website": "https://www.ea.com/games/ea-sports-fc"},
    {"name": "Konami eFootball", "type": "brand", "country": "JP", "website": "https://www.konami.com"},
    {"name": "FIFA+ / FIFA Media Tech", "type": "tech", "country": "CH", "website": "https://www.fifa.com"},
    {"name": "DAZN Global Partnerships", "type": "tech", "country": "GB", "website": "https://www.dazn.com"},
    {"name": "OneFootball", "type": "tech", "country": "DE", "website": "https://onefootball.com"},
    {"name": "Goal.com / Perform", "type": "media", "country": "GB", "website": "https://www.goal.com"},
    {"name": "TransferRoom", "type": "tech", "country": "GB", "website": "https://www.transferroom.com"},
    {"name": "Football Manager / SEGA Sports", "type": "tech", "country": "GB", "website": "https://www.footballmanager.com"},
    {"name": "Kitman Labs", "type": "tech", "country": "IE", "website": "https://kitmanlabs.com"},
    {"name": "Zone7", "type": "tech", "country": "IL", "website": "https://zone7.ai"},
    {"name": "Orreco", "type": "tech", "country": "IE", "website": "https://orreco.com"},
    {"name": "Firstbeat Sports", "type": "tech", "country": "FI", "website": "https://www.firstbeat.com"},
    {"name": "Polar Sports", "type": "tech", "country": "FI", "website": "https://www.polar.com"},
    {"name": "Garmin Sports", "type": "tech", "country": "US", "website": "https://www.garmin.com"},
    {"name": "Whoop", "type": "tech", "country": "US", "website": "https://www.whoop.com"},
    {"name": "Hyperice", "type": "brand", "country": "US", "website": "https://hyperice.com"},
    {"name": "Normatec", "type": "brand", "country": "US", "website": "https://www.normatecrecovery.com"},
    {"name": "Gatorade Sports Science", "type": "brand", "country": "US", "website": "https://www.gatorade.com"},
    {"name": "Red Bull Media House", "type": "media", "country": "AT", "website": "https://www.redbullmediahouse.com"},
    {"name": "Red Bull Soccer", "type": "brand", "country": "AT", "website": "https://www.redbull.com"},
    {"name": "Heineken UEFA Partnerships", "type": "brand", "country": "NL", "website": "https://www.heineken.com"},
    {"name": "Mastercard UEFA", "type": "brand", "country": "US", "website": "https://www.mastercard.com"},
    {"name": "PlayStation UEFA", "type": "brand", "country": "JP", "website": "https://www.playstation.com"},
    {"name": "FedEx Champions League", "type": "brand", "country": "US", "website": "https://www.fedex.com"},
    {"name": "Lay's UEFA", "type": "brand", "country": "US", "website": "https://www.lays.com"},
    {"name": "Just Eat Takeaway UEFA", "type": "brand", "country": "NL", "website": "https://www.justeattakeaway.com"},
    {"name": "Crypto.com Sports", "type": "brand", "country": "SG", "website": "https://crypto.com"},
    {"name": "Binance Sports", "type": "brand", "country": "MT", "website": "https://www.binance.com"},
    {"name": "Socios.com", "type": "tech", "country": "MT", "website": "https://www.socios.com"},
    {"name": "Chiliz", "type": "tech", "country": "MT", "website": "https://www.chiliz.com"},
    {"name": "Sorare", "type": "tech", "country": "FR", "website": "https://sorare.com"},
    {"name": "Fanatics", "type": "brand", "country": "US", "website": "https://www.fanatics.com"},
    {"name": "Panini Group", "type": "brand", "country": "IT", "website": "https://www.panini.com"},
    {"name": "Topps Football", "type": "brand", "country": "US", "website": "https://www.topps.com"},
    {"name": "IMAGO Sport", "type": "media", "country": "DE", "website": "https://www.imago-images.de"},
    {"name": "Getty Images Sport", "type": "media", "country": "US", "website": "https://www.gettyimages.com"},
    {"name": "AP Sports", "type": "media", "country": "US", "website": "https://apnews.com"},
    {"name": "Reuters Sports", "type": "media", "country": "GB", "website": "https://www.reuters.com"},
    {"name": "AFP Sports", "type": "media", "country": "FR", "website": "https://www.afp.com"},
    {"name": "dpa Sport", "type": "media", "country": "DE", "website": "https://www.dpa.com"},
    {"name": "SID Sport-Informations-Dienst", "type": "media", "country": "DE", "website": "https://www.sid.de"},
    {"name": "Sportpresseportal", "type": "media", "country": "DE", "website": "https://www.sportpresseportal.de"},
    {"name": "news aktuell / ots", "type": "media", "country": "DE", "website": "https://www.presseportal.de"},
    {"name": "ISPO Munich", "type": "community", "country": "DE", "website": "https://www.ispo.com"},
    {"name": "SPORTS TECH GERMANY", "type": "community", "country": "DE", "website": "https://sportstechgermany.com"},
    {"name": "German Sports Tech Hub", "type": "community", "country": "DE", "website": "https://www.germansportstech.de"},
    {"name": "Amsterdam Arena / Johan Cruijff ArenA", "type": "facility", "country": "NL", "website": "https://www.johancruijffarena.nl"},
    {"name": "Wembley Stadium", "type": "facility", "country": "GB", "website": "https://www.wembleystadium.com"},
    {"name": "Stade de France", "type": "facility", "country": "FR", "website": "https://www.stadefrance.com"},
    {"name": "San Siro / Meazza", "type": "facility", "country": "IT", "website": "https://www.sansirostadium.com"},
    {"name": "Camp Nou / Spotify Camp Nou", "type": "facility", "country": "ES", "website": "https://www.fcbarcelona.com"},
    {"name": "Santiago Bernabéu", "type": "facility", "country": "ES", "website": "https://www.realmadrid.com"},
    {"name": "Old Trafford", "type": "facility", "country": "GB", "website": "https://www.manutd.com"},
    {"name": "Anfield", "type": "facility", "country": "GB", "website": "https://www.liverpoolfc.com"},
    {"name": "Emirates Stadium", "type": "facility", "country": "GB", "website": "https://www.arsenal.com"},
    {"name": "Stamford Bridge", "type": "facility", "country": "GB", "website": "https://www.chelseafc.com"},
    {"name": "Tottenham Hotspur Stadium", "type": "facility", "country": "GB", "website": "https://www.tottenhamhotspur.com"},
    {"name": "Parc des Princes", "type": "facility", "country": "FR", "website": "https://www.psg.fr"},
    {"name": "Allianz Stadium Turin", "type": "facility", "country": "IT", "website": "https://www.juventus.com"},
    {"name": "Estádio da Luz", "type": "facility", "country": "PT", "website": "https://www.slbenfica.pt"},
    {"name": "Estádio do Dragão", "type": "facility", "country": "PT", "website": "https://www.fcporto.pt"},
    {"name": "Johan Cruyff Academy", "type": "academy", "country": "NL", "website": "https://www.cruyff-foundation.org"},
    {"name": "Clairefontaine (INF)", "type": "academy", "country": "FR", "website": "https://www.fff.fr"},
    {"name": "La Masia", "type": "academy", "country": "ES", "website": "https://www.fcbarcelona.com"},
    {"name": "Ajax Youth Academy", "type": "academy", "country": "NL", "website": "https://www.ajax.nl"},
    {"name": "Benfica Campus", "type": "academy", "country": "PT", "website": "https://www.slbenfica.pt"},
    {"name": "Clairefontaine Partner Centres", "type": "academy", "country": "FR", "website": "https://www.fff.fr"},
    {"name": "IMG Academy Soccer", "type": "academy", "country": "US", "website": "https://www.imgacademy.com"},
    {"name": "Barça Academy Global", "type": "academy", "country": "ES", "website": "https://academy.fcbarcelona.com"},
    {"name": "Real Madrid Foundation Academies", "type": "academy", "country": "ES", "website": "https://www.realmadrid.com"},
    {"name": "Manchester City EDS / City Football Academy", "type": "academy", "country": "GB", "website": "https://www.mancity.com"},
    {"name": "Clairefontaine / CNFE", "type": "academy", "country": "FR", "website": "https://cnfe.fff.fr"},
]


def resolve_org(org: dict) -> dict | None:
    website = org["website"]
    emails_found: list[str] = []
    source = website
    for url in candidate_urls(website):
        try:
            html = fetch(url)
        except Exception:
            continue
        emails = extract_emails(html)
        if emails:
            emails_found.extend(emails)
            source = url
            # Prefer impressum/contact hits
            if any(k in url for k in ("impressum", "imprint", "kontakt", "contact", "presse", "media")):
                break
        time.sleep(0.05)
    best = pick_best(emails_found)
    row = {
        "name": org["name"],
        "type": org["type"],
        "country": org.get("country", ""),
        "city": org.get("city"),
        "league": org.get("league"),
        "website": website,
        "email": best,
        "email_confidence": "verified" if best else "needs_research",
        "source_url": source if best else website,
        "invite_status": "pending",
        "notes": "Public email extracted from official site imprint/contact pages."
        if best
        else "No public org email found on imprint/contact crawl — needs_research.",
        "tags": ["expand", "imprint_crawl"],
    }
    # Drop nulls for cleaner JSON
    return {k: v for k, v in row.items() if v is not None and v != ""}


def build(orgs: list[dict], out_name: str, existing_emails: set[str], existing_names: set[str]) -> list[dict]:
    results: list[dict] = []
    print(f"Building {out_name} from {len(orgs)} orgs...", flush=True)
    with ThreadPoolExecutor(max_workers=12) as ex:
        futs = {ex.submit(resolve_org, o): o for o in orgs}
        for i, fut in enumerate(as_completed(futs), 1):
            try:
                row = fut.result()
            except Exception as e:
                print("  err", e)
                continue
            if not row:
                continue
            name_l = row["name"].lower()
            email_l = (row.get("email") or "").lower()
            if name_l in existing_names:
                continue
            if email_l and email_l in existing_emails:
                continue
            results.append(row)
            if email_l:
                existing_emails.add(email_l)
            existing_names.add(name_l)
            if i % 25 == 0:
                with_email = sum(1 for r in results if r.get("email"))
                print(f"  {i}/{len(orgs)} done — kept {len(results)} ({with_email} with email)", flush=True)
    path = SEEDS / out_name
    path.write_text(json.dumps(results, ensure_ascii=False, indent=2) + "\n")
    with_email = sum(1 for r in results if r.get("email"))
    print(f"Wrote {path} — {len(results)} rows, {with_email} with email", flush=True)
    return results


def load_extra() -> list[dict]:
    path = SEEDS / "partners-directory-extra.json"
    if not path.is_file():
        return []
    data = json.loads(path.read_text())
    return data if isinstance(data, list) else []


def split_extra(extra: list[dict]) -> tuple[list[dict], list[dict], list[dict]]:
    de, eu, biz = [], [], []
    de_types = {"club", "media", "association", "league", "academy", "facility"}
    biz_types = {"agency", "brand", "tech", "community"}
    for row in extra:
        country = (row.get("country") or "").upper()
        t = row.get("type") or "media"
        if country == "DE" and t in de_types:
            de.append(row)
        elif t in biz_types:
            biz.append(row)
        else:
            eu.append(row)
    return de, eu, biz


def main():
    existing_emails, existing_names = load_existing()
    print(f"Existing: {len(existing_names)} names, {len(existing_emails)} emails")
    extra_de, extra_eu, extra_biz = split_extra(load_extra())
    print(f"Extra directory: DE={len(extra_de)} EU={len(extra_eu)} BIZ={len(extra_biz)}")
    build(DE_ORGS + extra_de, "partners-de-expand.json", existing_emails, existing_names)
    build(EU_ORGS + extra_eu, "partners-eu-intl-expand.json", existing_emails, existing_names)
    build(BIZ_ORGS + extra_biz, "partners-biz-expand.json", existing_emails, existing_names)
    # Final totals
    all_rows = []
    for path in sorted(SEEDS.glob("partners-*.json")):
        if path.name == "partners-directory-extra.json":
            continue
        all_rows.extend(json.loads(path.read_text()))
    emails = {(r.get("email") or "").lower() for r in all_rows if r.get("email")}
    print(f"TOTAL across all partners-*.json: {len(all_rows)} rows, {len(emails)} unique emails")


if __name__ == "__main__":
    main()
