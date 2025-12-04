<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GTA 5 Roleplay Police Report Templates
    |--------------------------------------------------------------------------
    | Vollständige Vorlagen für das deutsche Polizeirecht im Roleplay.
    | Abgestimmt auf StVO, StGB, BtMG und WaffG.
    |
    | Legende Platzhalter:
    | [TV]    = Tatverdächtiger / Betroffener
    | [PB]    = Polizeibeamter
    | [DATUM] = Datum und Uhrzeit
    | [ORT]   = Genauer Einsatzort
    |
    */

    // =========================================================================
    // 1. VERKEHRSDELIKTE (StVO)
    // =========================================================================

    'stvo_kontrolle_routine' => [
        'name' => 'StVO: Allgemeine Verkehrskontrolle (Routine)',
        'title' => 'Protokoll: Allgemeine Verkehrskontrolle',
        'incident_description' => "Am [DATUM] um [UHRZEIT] wurde das Fahrzeug [MARKE/MODELL] mit dem Kennzeichen [KENNZEICHEN] im Bereich [ORT] einer allgemeinen Verkehrskontrolle unterzogen.\n\nFahrzeugführer: [NAME DES TV]\nFahrzeughalter: [NAME DES HALTERS]\nMitfahrer: [ANZAHL/NAMEN]\n\nGrund der Maßnahme: Verdachtsunabhängige Kontrolle zur Überprüfung der Verkehrstüchtigkeit.\n\nVerhalten des Fahrers: Kooperativ und ruhig.",
        'actions_taken' => "• Anhaltesignal gegeben (Yelp/Blaulicht)\n• Identitätsfeststellung anhand des Personalausweises\n• Überprüfung der Fahrerlaubnis (Gültigkeit bestätigt)\n• Abgleich der Fahrzeugpapiere und Kennzeichen\n• Fahndungsabfrage Person/KFZ: Negativ\n• Überprüfung Verbandskasten/Warndreieck: Vorhanden\n• Mündliche Verwarnung / Gute Weiterfahrt gewünscht.",
    ],

    'stvo_geschwindigkeitsverstoss' => [
        'name' => 'StVO: Geschwindigkeitsüberschreitung',
        'title' => 'Bußgeldverfahren: Geschwindigkeitsüberschreitung',
        'incident_description' => "Messung mittels Lasermessgerät / Provida-Fahrzeug im Bereich [ORT].\nZulässige Höchstgeschwindigkeit: [ERLAUBT] km/h.\nGemessene Geschwindigkeit (nach Toleranzabzug): [GEMESSEN] km/h.\n\nFahrzeug: [MARKE/MODELL], Kennzeichen: [KENNZEICHEN].\nFahrzeugführer: [NAME DES TV].\n\nDer Fahrer gab an: [AUSSAGE DES FAHRERS, z.B. 'Habe das Schild übersehen'].",
        'actions_taken' => "• Anhalten des Fahrzeugs unter Eigensicherung\n• Vorwurf der Ordnungswidrigkeit eröffnet\n• Belehrung über das Aussageverweigerungsrecht\n• Beweissicherung (Messprotokoll)\n• Ausstellung des Bußgeldbescheides gemäß Katalog (T-Nr. 101-106)\n• Eintragung der Punkte im Register\n• Belehrung über mögliche Fahrverbote bei Wiederholung.",
    ],

    'stvo_alkohol_drogen' => [
        'name' => 'StVO: Trunkenheit / BTM am Steuer (DUI)',
        'title' => 'Strafanzeige: Fahren unter Einfluss berauschender Mittel',
        'incident_description' => "Das Fahrzeug [MARKE/MODELL] ([KENNZEICHEN]) fiel der Streife durch unsichere Fahrweise (Schlangenlinien) und stark überhöhte/niedrige Geschwindigkeit auf.\nBei der Kontrolle von [NAME DES TV] wurden folgende Auffälligkeiten festgestellt:\n\n• Geruch: Starke Alkohol-/Cannabisfahne\n• Pupillen: Vergrößert/verengt/keine Lichtreaktion\n• Sprache: Verwaschen/lallend\n• Motorik: Unsicherer Gang\n\nErgebnis Atemalkoholtest: [WERT] Promille.\nErgebnis Drogenschnelltest: Positiv auf [SUBSTANZ].",
        'actions_taken' => "• Untersagung der Weiterfahrt (Schlüssel sichergestellt)\n• Anordnung einer Blutentnahme (durchgef. von Dr. [NAME ARZT] um [UHRZEIT])\n• Beschlagnahmung des Führerscheins\n• Transport zur Dienststelle zur Ausnüchterung\n• Einleitung Strafverfahren gem. § 316 StGB\n• Fahrzeug wurde verkehrssicher abgestellt / abgeschleppt.",
    ],

    'stvo_unfall_flucht' => [
        'name' => 'StVO: Unfallflucht / Fahrerflucht',
        'title' => 'Strafanzeige: Unerlaubtes Entfernen vom Unfallort',
        'incident_description' => "Am [ORT] kam es zu einem Verkehrsunfall zwischen Fahrzeug A ([KENNZEICHEN A]) und Fahrzeug B ([KENNZEICHEN B]).\n\nDer Fahrer des Fahrzeugs A ([NAME DES TV]) entfernte sich nach der Kollision mit hoher Geschwindigkeit vom Unfallort, ohne die Feststellung seiner Personalien zu ermöglichen.\nZeugen konnten das Kennzeichen notieren. Eine Fahndung wurde eingeleitet.\nDas Fahrzeug konnte kurze Zeit später an der Halteranschrift / im Bereich [ORT] angetroffen werden.\nSchäden am Fahrzeug korrespondieren mit dem Unfallbild.",
        'actions_taken' => "• Unfallaufnahme und Spurensicherung vor Ort\n• Fahndungseinleitung\n• Konfrontation des Halters/Fahrers mit dem Tatvorwurf\n• Dokumentation der Schäden (Fotos)\n• Führerschein sichergestellt\n• Anzeige wegen Unfallflucht (§ 142 StGB) und Sachbeschädigung gefertigt.",
    ],

    'stvo_verfolgungsjagd' => [
        'name' => 'StVO: Verfolgungsjagd (High Speed)',
        'title' => 'Einsatzbericht: Verfolgungsfahrt / Gefährlicher Eingriff',
        'incident_description' => "Der TV [NAME] entzog sich einer polizeilichen Anhalteweisung durch Flucht.\n\nStart der Verfolgung: [STARTORT]\nEnde der Verfolgung: [ZIELORT]\nDauer: [DAUER] Minuten\nHöchstgeschwindigkeit: [KM/H] innerorts/außerorts\n\nVerlauf: Der TV missachtete mehrere rote Ampeln, fuhr auf dem Gehweg und gefährdete aktiv Passanten und andere Verkehrsteilnehmer.\nDas Fahrzeug konnte durch einen Unfall / Einsatz von Nagelbändern / Pit-Manöver gestoppt werden.",
        'actions_taken' => "• Zugriff mittels Felony-Stop-Verfahren\n• TV aus Fahrzeug befohlen, zu Boden gebracht und gefesselt\n• Durchsuchung der Person und des Fahrzeugs\n• Erste Hilfe geleistet (sofern verletzt)\n• Festnahme ausgesprochen\n• Rechtsbelehrung (Miranda) verlesen\n• Fahrzeug beschlagnahmt\n• Anzeigen: § 315d (Verbotene Kraftfahrzeugrennen), § 315c (Gefährdung), § 113 (Widerstand).",
    ],

    // =========================================================================
    // 2. KRIMINALITÄT - EIGENTUM (StGB)
    // =========================================================================

    'krim_fahrzeugdiebstahl' => [
        'name' => 'Krim: Fahrzeugdiebstahl (GTA)',
        'title' => 'Strafanzeige: Besonders schwerer Fall des Diebstahls (KFZ)',
        'incident_description' => "Im Rahmen einer Kennzeichenabfrage fiel das Fahrzeug [MARKE/MODELL], Kennzeichen [KENNZEICHEN], als 'gestohlen gemeldet' auf.\nFahrzeug wurde gestoppt. Der Fahrer [NAME DES TV] konnte keinen Eigentumsnachweis oder Mietvertrag vorlegen.\nKurzschlussspuren / aufgebrochenes Zündschloss waren sichtbar / nicht sichtbar.\nDer TV gab an, das Fahrzeug 'gefunden' zu haben.",
        'actions_taken' => "• Vorläufige Festnahme wegen Verdacht auf Fahrzeugdiebstahl\n• Sicherstellung des KFZ zur Spurensicherung/Rückgabe\n• Durchsuchung des TV nach Aufbruchwerkzeug (Dietriche etc.)\n• Abgleich der Fahrgestellnummer (VIN)\n• Rechtsbelehrung erfolgt\n• Rücksprache mit dem rechtmäßigen Eigentümer [NAME].",
    ],

    'krim_einbruch_haus' => [
        'name' => 'Krim: Einbruchdiebstahl / Hausfriedensbruch',
        'title' => 'Tatortbericht: Einbruch in Wohnobjekt',
        'incident_description' => "Alarmierung durch Alarmanlage / Nachbarn zum Objekt [ADRESSE].\nBei Eintreffen wurde eine aufgebrochene Tür/Fenster festgestellt.\n\nIm Objekt wurde der TV [NAME] angetroffen, während er Wertgegenstände durchsuchte/verstaute.\nDer TV führte Einbruchswerkzeug (Brecheisen, Lockpick) mit sich.\nEs entstand Sachschaden am Eigentum.",
        'actions_taken' => "• Sicherung des Gebäudes (Raumclearing)\n• Festnahme des Täters im Objekt\n• Sicherstellung des Tatwerkzeugs und der Beute\n• Spurensicherung (Fotos der Aufbruchstellen)\n• Verständigung des Eigentümers\n• Anzeige wegen Einbruchdiebstahl und Sachbeschädigung.",
    ],

    'krim_sachbeschaedigung_staat' => [
        'name' => 'Krim: Sachbeschädigung (Staatseigentum)',
        'title' => 'Anzeige: Gemeinschädliche Sachbeschädigung',
        'incident_description' => "Der TV [NAME] wurde dabei beobachtet, wie er vorsätzlich Eigentum der Stadt / Polizei beschädigte.\n\nBetroffenes Objekt: [z.B. Streifenwagen, Parkbank, Laterne]\nArt der Beschädigung: [z.B. Eintreten der Scheinwerfer, Graffiti, Zerstörung]\nGeschätzter Sachschaden: $[SUMME].\nMotiv laut TV: [AUSSAGE/MOTIV].",
        'actions_taken' => "• Identitätsfeststellung\n• Dokumentation der Schäden für Regressforderungen\n• Platzverweis erteilt\n• Strafanzeige gemäß § 303/304 StGB gefertigt\n• Ggf. Ingewahrsamnahme zur Verhinderung weiterer Straftaten.",
    ],

    // =========================================================================
    // 3. KRIMINALITÄT - GEWALT (StGB)
    // =========================================================================

    'gewalt_koerperverletzung' => [
        'name' => 'Gewalt: Körperverletzung (Schlägerei)',
        'title' => 'Strafanzeige: Körperverletzung',
        'incident_description' => "Einsatzort: [ORT]. Gemeldet wurde eine körperliche Auseinandersetzung.\nVor Ort trafen die Beamten auf das Opfer [NAME OPFER] und den TV [NAME TV].\n\nVerletzungen Opfer: Platzwunde / Prellungen / Hämatome.\nHergang: Nach verbaler Auseinandersetzung schlug der TV dem Opfer mit der Faust ins Gesicht.\nAlkoholisiert: TV [JA/NEIN], Opfer [JA/NEIN].",
        'actions_taken' => "• Räumliche Trennung der Parteien\n• Erste Hilfe geleistet, Rettungsdienst (MD) hinzugezogen\n• Personalienfeststellung aller Beteiligten und Zeugen\n• TV vorläufig festgenommen (Wiederholungsgefahr)\n• Fotos der Verletzungen erstellt\n• Anzeige § 223 StGB (Körperverletzung) aufgenommen.",
    ],

    'gewalt_raub_laden' => [
        'name' => 'Gewalt: Raubüberfall (Shop/Tankstelle)',
        'title' => 'Einsatzbericht: Schwerer Raub auf [LADENNAME]',
        'incident_description' => "Alarmierung über Silent-Alarm / Notruf.\nEin maskierter Täter überfiel den [LADEN] in [ORT].\nBedrohung des Angestellten mit einer Schusswaffe ([WAFFENTYP]).\nForderung: Herausgabe des Kasseninhalts.\n\nDer TV [NAME] konnte noch am Tatort / auf der Flucht gestellt werden.\nDie Beute ($[BETRAG]) wurde sichergestellt.",
        'actions_taken' => "• Täter unter Vorhalt der Dienstwaffe gestellt\n• Entwaffnung und Fesselung\n• Sicherung der Waffe (Beweismittel)\n• Rückgabe der Beute an den Besitzer\n• Auswertung der Überwachungskamera\n• Transport ins Hochsicherheitsgefängnis (SG)\n• Anzeige: Schwerer Raub (§ 250 StGB) & Verstoß WaffG.",
    ],

    'gewalt_raub_bank' => [
        'name' => 'Gewalt: Bankraub / Geiselnahme',
        'title' => 'Einsatzprotokoll: Banküberfall mit Geiselnahme',
        'incident_description' => "Überfall auf die [NAME DER BANK] Filiale [ORT].\nAnzahl Täter: [ANZAHL]. Schwer bewaffnet (Langwaffen).\nAnzahl Geiseln: [ANZAHL].\n\nForderungen der Täter: Freier Abzug und keine Verfolgung.\nVerhandlung wurde geführt durch [NAME VERHANDLER].\n\nZugriff / Ausgang: Täter ergaben sich nach Verhandlungen / Zugriff durch SWAT erfolgte.\nGeiseln unverletzt befreit: [JA/NEIN].",
        'actions_taken' => "• Weiträumige Absperrung (Perimeter) errichtet\n• Evakuierung angrenzender Bereiche\n• Verhandlungen geführt\n• Festnahme der Täter [NAMEN]\n• Beschlagnahmung der Waffen ([LISTE]) und Beute ($[SUMME])\n• Identitätsfeststellung der Geiseln\n• Übergabe der TV an die Justizvollzugsanstalt.",
    ],

    'gewalt_mord' => [
        'name' => 'Gewalt: Tötungsdelikt / Mord',
        'title' => 'Ermittlungsakte: Tötungsdelikt / Mordverdacht',
        'incident_description' => "Fund einer leblosen Person am [ORT] um [UHRZEIT].\nOpfer identifiziert als: [NAME OPFER].\nTodesursache (vorl.): Schussverletzungen / stumpfe Gewalt / Stichverletzung.\n\nErmittlungen führten zum TV [NAME TV].\nMotiv: [Z.B. Rache, Habgier, Bandenkrieg].\nBeweislage: Zeugenaussagen, Tatwaffe mit Fingerabdrücken, Videoaufnahmen.",
        'actions_taken' => "• Tatortabsperrung und Spurensicherung (GSR-Test, Hülsen, DNA)\n• Leichenschau durch Koroner/MD veranlasst\n• Festnahme des TV unter dringendem Tatverdacht\n• Schmauchspurentest beim TV durchgeführt: [POSITIV/NEGATIV]\n• Vernehmung des Beschuldigten\n• Inhaftierung und Übergabe an Staatsanwaltschaft (Haftrichtervorführung).",
    ],

    // =========================================================================
    // 4. BETÄUBUNGSMITTEL (BtMG)
    // =========================================================================

    'btm_besitz_gering' => [
        'name' => 'BtMG: Besitz (Eigenbedarf)',
        'title' => 'Verstoß BtMG: Besitz geringer Mengen',
        'incident_description' => "Im Rahmen einer Personenkontrolle wurde bei [NAME DES TV] nervöses Verhalten festgestellt.\nAuf Nachfrage gab die Person den Besitz von BTM zu / verneinte diesen.\n\nBei der Durchsuchung wurde aufgefunden:\n• Substanz: [ART, z.B. Marihuana]\n• Menge: [ANZAHL] Gramm/Joints (Eigenbedarf)\n• Fundort: Hosentasche / Rucksack.",
        'actions_taken' => "• Sicherstellung und Vernichtung der Betäubungsmittel\n• Belehrung nach BtMG\n• Personalienfeststellung\n• Mündliche Verwarnung (da Erstverstoß/Geringfügigkeit) ODER Anzeige erstattet\n• Platzverweis für den Kontrollbereich.",
    ],

    'btm_handel_gross' => [
        'name' => 'BtMG: Handel / Große Menge',
        'title' => 'Strafanzeige: Gewerbsmäßiger Handel mit BTM',
        'incident_description' => "Aufklärung durch Observation / Hinweis.\nDer TV [NAME] führte eine erhebliche Menge Betäubungsmittel mit sich, die auf Handel schließen lässt.\n\nSichergestellte Gegenstände:\n• [MENGE] g/Stk Kokain/Meth/Weed\n• Feinwaage\n• Druckverschlusstüten\n• Bargeld in szenetypischer Stückelung ($[SUMME])\n• Dealer-Smartphone.",
        'actions_taken' => "• Vorläufige Festnahme wegen Verdacht auf Handel (§ 29a BtMG)\n• Beschlagnahmung aller Beweismittel und des Bargelds\n• Durchsuchung des PKW [KENNZEICHEN] (negativ/positiv)\n• Transport zur Dienststelle\n• Erkennungsdienstliche Behandlung (ED-Behandlung)\n• Inhaftierung.",
    ],

    // =========================================================================
    // 5. WAFFENGESETZ (WaffG)
    // =========================================================================

    'waffg_besitz_illegal' => [
        'name' => 'WaffG: Illegaler Waffenbesitz',
        'title' => 'Verstoß WaffG: Führen einer Schusswaffe ohne Erlaubnis',
        'incident_description' => "Bei der Person [NAME] wurde eine Schusswaffe festgestellt.\nTyp: [PISTOLE/REVOLVER], Kaliber [.50 / 9mm].\nZustand: Geladen und zugriffsbereit im Holster / Hosenbund.\n\nÜberprüfung ergab: Person besitzt keinen gültigen Waffenschein (WBK) / Waffenschein wurde entzogen.\nSeriennummer der Waffe: [NUMMER] (registriert auf: [NAME] / als gestohlen gemeldet).",
        'actions_taken' => "• Waffe sichergestellt und entladen\n• Munition ([ANZAHL] Schuss) beschlagnahmt\n• Festnahme gem. Waffengesetz\n• Abfrage, ob Waffe bei Straftaten verwendet wurde\n• Anzeige gefertigt.",
    ],

    'waffg_kriegswaffen' => [
        'name' => 'WaffG: Besitz von Kriegswaffen',
        'title' => 'Verbrechen: Besitz verbotener Kriegswaffen',
        'incident_description' => "Im Fahrzeug / Bei der Person [NAME] wurden Waffen gefunden, die unter das Kriegswaffenkontrollgesetz fallen.\n\nFundstücke:\n• [MODELL, z.B. AK-47, Karabiner]\n• [ANZAHL] Magazine\n• Sprengmittel / Granaten\n\nDer TV gehört vermutlich einer kriminellen Vereinigung an.",
        'actions_taken' => "• Sofortige Festnahme unter erhöhten Sicherheitsvorkehrungen\n• Beschlagnahmung durch Asservatenstelle\n• Hinzuziehung des FIB/CID zur weiteren Ermittlung\n• Transport ins Hochsicherheitsgefängnis\n• Antrag auf lebenslange Haft / Höchststrafe gestellt.",
    ],

    // =========================================================================
    // 6. ÖFFENTLICHE ORDNUNG & BEHÖRDEN
    // =========================================================================

    'ordnung_platzverweis' => [
        'name' => 'Ordnung: Platzverweis / Widerstand',
        'title' => 'Bericht: Durchsetzung Platzverweis / Widerstand',
        'incident_description' => "Die Person [NAME] störte eine laufende Amtshandlung am [ORT] durch Reinrufen / Beleidigungen / körperliche Nähe.\n\nMaßnahmenkette:\n1. Freundliche Aufforderung zu gehen (ignoriert)\n2. Formeller Platzverweis ausgesprochen (ignoriert)\n3. Androhung von Zwangsmitteln (ignoriert/Widerstand geleistet).\n\nBei der Durchsetzung sperrte sich der TV und leistete aktiven Widerstand.",
        'actions_taken' => "• Anwendung unmittelbaren Zwangs (Fesselung)\n• Ingewahrsamnahme zur Durchsetzung des Platzverweises\n• Anzeige wegen Widerstand gegen Vollstreckungsbeamte (§ 113 StGB)\n• Anzeige wegen Beamtenbeleidigung (Wortlaut: '[ZITAT]')\n• Transport zur Zelle.",
    ],

    'ordnung_amtsanmassung' => [
        'name' => 'Ordnung: Amtsanmaßung (Fake Cop)',
        'title' => 'Strafanzeige: Amtsanmaßung',
        'incident_description' => "Der TV [NAME] trat in der Öffentlichkeit als Polizeibeamter auf.\n\nMerkmale:\n• Trug polizeiähnliche Uniform / Schutzweste mit Aufschrift 'POLIZEI'\n• Benutzte ein Fahrzeug mit Blaulicht (ohne Berechtigung)\n• Versuch, Zivilisten zu kontrollieren/festzunehmen.\n\nDer TV ist kein Angehöriger einer Behörde.",
        'actions_taken' => "• Festnahme\n• Sicherstellung der Uniform und Ausrüstungsgegenstände\n• Stilllegung des Fahrzeugs\n• Erkennungsdienstliche Behandlung\n• Anzeige gem. § 132 StGB.",
    ],

    'ordnung_hausfriedensbruch_pd' => [
        'name' => 'Ordnung: Eindringen in Sicherheitsbereich',
        'title' => 'Strafanzeige: Hausfriedensbruch (Sicherheitszone)',
        'incident_description' => "Der TV [NAME] betrat unbefugt den Sicherheitsbereich (Garagen/Zellenblock/Büros) des [LSPD/SG/FIB].\nTrotz Aufforderung verließ er den Bereich nicht oder kehrte sofort zurück.\nDer Bereich ist deutlich als 'Sperrzone' gekennzeichnet.",
        'actions_taken' => "• Vorläufige Festnahme\n• Durchsuchung (Eigensicherung)\n• Überprüfung der Personalien\n• Anzeige wegen Hausfriedensbruch (§ 123 StGB)\n• Erteilung eines dauerhaften Hausverbots für den Sicherheitsbereich.",
    ],
];