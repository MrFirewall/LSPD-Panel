<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report Templates / Einsatzbericht-Vorlagen
    |--------------------------------------------------------------------------
    | Diese Vorlagen werden via JS in die Felder geladen.
    | Platzhalter wie [NAME] oder [ORT] müssen vom Beamten ausgefüllt werden.
    |
    */

    // -------------------------------------------------------------------------
    // VERKEHR (StVO)
    // -------------------------------------------------------------------------
    'verkehr_allgemein' => [
        'name' => 'Verkehr: Allgemeine Kontrolle (Routine)',
        'title' => 'Bericht: Allgemeine Verkehrskontrolle',
        'incident_description' => "Am [DATUM/UHRZEIT] wurde im Bereich [ORT] das Fahrzeug [MODELL] mit dem Kennzeichen [KENNZEICHEN] einer allgemeinen Verkehrskontrolle unterzogen.\n\nFahrer: [NAME]\nBeifahrer: [NAME ODER 'Keine']\n\nGrund der Kontrolle: Routinekontrolle / Auffällige Fahrweise.\nEs wurden keine strafrechtlich relevanten Gegenstände gefunden.",
        'actions_taken' => "• Überprüfung von Führerschein und Fahrzeugpapieren\n• Abfrage im Fahndungsregister (negativ)\n• Verbandskasten und Warndreieck kontrolliert\n• Mündliche Verwarnung / Bußgeld ausgestellt\n• Weiterfahrt gestattet",
    ],

    'verkehr_alkohol_drogen' => [
        'name' => 'Verkehr: Trunkenheit / Drogen (DUI)',
        'title' => 'Strafanzeige: Fahren unter Einfluss (BTM/Alkohol)',
        'incident_description' => "Das Fahrzeug [MODELL] ([KENNZEICHEN]) fiel durch Schlangenlinien / überhöhte Geschwindigkeit auf.\nBei der Kontrolle von [NAME] wurde starker Alkoholgeruch / Marihuanageruch wahrgenommen.\n\nDurchgeführter Schnelltest:\n• Alkohol: [WERT] Promille\n• Drogen: Positiv auf [SUBSTANZ]",
        'actions_taken' => "• Untersagung der Weiterfahrt\n• Schlüssel sichergestellt\n• Blutentnahme durch Mediziner angeordnet\n• Führerschein vorläufig beschlagnahmt\n• Transport zur Dienststelle zur Ausnüchterung\n• Anzeige gem. § 315c / § 316 StGB gefertigt",
    ],

    'verkehr_flucht' => [
        'name' => 'Verkehr: Flucht vor Polizei',
        'title' => 'Einsatzbericht: Verfolgungsjagd / Flucht',
        'incident_description' => "Der Tatverdächtige [NAME] entzog sich der Verkehrskontrolle durch Flucht mit dem Fahrzeug [MODELL] ([KENNZEICHEN]).\n\nVerlauf der Flucht:\n• Start: [ORT]\n• Ende: [ORT]\n• Dauer: ca. [MINUTEN] Min.\n• Höchstgeschwindigkeit: [KM/H]\n\nEs kam zu Gefährdungen von Passanten/anderen Fahrern: [JA/NEIN].\nDer TV konnte schließlich durch [METHODE, z.B. Unfall/Pit-Manöver] gestoppt werden.",
        'actions_taken' => "• Felony Stop durchgeführt\n• TV aus Fahrzeug befohlen und gesichert (Handschellen)\n• Rechtsbelehrung verlesen und verstanden\n• Fahrzeug abgeschleppt/beschlagnahmt\n• Vorläufige Festnahme und Transport in das SG",
    ],

    'verkehr_unfall' => [
        'name' => 'Verkehr: Unfallaufnahme',
        'title' => 'Protokoll: Verkehrsunfall mit Sachschaden',
        'incident_description' => "Eintreffen an Unfallstelle [ORT].\n\nBeteiligte:\n1. [NAME A] (Fahrzeug: [MODELL A])\n2. [NAME B] (Fahrzeug: [MODELL B])\n\nHergang: Laut Spurenlage und Zeugenaussagen missachtete Beteiligter A die Vorfahrt / übersah Beteiligten B beim Abbiegen.\nVerletzte Personen: [ANZAHL/KEINE].",
        'actions_taken' => "• Unfallstelle abgesichert\n• Personalien aller Beteiligten aufgenommen\n• Fotos zur Beweissicherung erstellt\n• Platzverweis für Schaulustige erteilt\n• Bußgeldverfahren gegen Verursacher eingeleitet\n• Unfallbericht-Nummer an Beteiligte ausgehändigt",
    ],

    // -------------------------------------------------------------------------
    // KRIMINALITÄT & GEWALT (StGB)
    // -------------------------------------------------------------------------
    'festnahme_haftbefehl' => [
        'name' => 'Festnahme: Haftbefehl offen',
        'title' => 'Vollstreckung Haftbefehl',
        'incident_description' => "Im Rahmen einer Personenkontrolle wurde die Identität von [NAME], geb. am [DATUM], überprüft.\nDas System meldete einen offenen Haftbefehl (Aktenzeichen: [NR]) wegen [GRUND].",
        'actions_taken' => "• Person vorläufig festgenommen\n• Handfesseln angelegt\n• Person und Kleidung durchsucht\n• Rechtsbelehrung erfolgt\n• Transport zur JVA/SG\n• Haftbefehl vollstreckt ([ANZAHL] HE)",
    ],

    'straftat_raub_shop' => [
        'name' => 'Raub: Ladenüberfall/Tankstelle',
        'title' => 'Strafanzeige: Raubüberfall auf [LADENNAME]',
        'incident_description' => "Alarmierung durch Panik-Button / Leitstelle.\nBei Eintreffen am [LADEN] wurde der Täter [NAME] noch vor Ort / auf der Flucht angetroffen.\n\nDer Täter bedrohte den Verkäufer mit einer [WAFFE, z.B. Pistole] und forderte Bargeld.\nBeute: $[BETRAG] (sichergestellt: [JA/NEIN]).",
        'actions_taken' => "• Täter unter Vorhalt der Schusswaffe gestellt\n• Entwaffnung und Fesselung\n• Waffe ([TYP]) und Beute sichergestellt (Asservatenkammer)\n• Videoaufnahmen des Ladens gesichert\n• Verkäufer (Opfer) betreut\n• Anzeige wegen schwerem Raub gefertigt",
    ],

    'straftat_koerperverletzung' => [
        'name' => 'Gewalt: Schlägerei / Körperverletzung',
        'title' => 'Anzeige: Körperverletzung',
        'incident_description' => "Einsatzstichwort 'Schlägerei' am [ORT].\nVor Ort wurde das Opfer [NAME OPFER] mit sichtbaren Verletzungen ([ART DER VERLETZUNG]) angetroffen.\n\nDer Beschuldigte [NAME TÄTER] wurde von Zeugen identifiziert.\nHergang: Nach einem verbalen Streit schlug der Beschuldigte unvermittelt zu.",
        'actions_taken' => "• Parteien getrennt\n• Erste Hilfe geleistet / MD alarmiert\n• Personalien festgestellt\n• Platzverweis für Unbeteiligte\n• Anzeige wegen Körperverletzung (§ 223 StGB) aufgenommen\n• Opfer über Zivilklageweg informiert",
    ],

    'straftat_waffenbesitz' => [
        'name' => 'Waffen: Illegaler Besitz (Langwaffe)',
        'title' => 'Verstoß WaffG: Besitz verbotener Waffen',
        'incident_description' => "Die Person [NAME] führte in der Öffentlichkeit sichtbar eine Langwaffe ([TYP, z.B. Karabiner]) mit sich.\nBei Überprüfung der Lizenzen konnte keine Berechtigung für Waffenkategorie C/D vorgelegt werden.",
        'actions_taken' => "• Waffe und Munition ([ANZAHL] Schuss) beschlagnahmt\n• Sicherheitsüberprüfung der Waffe durchgeführt\n• Person festgenommen\n• Rechtsbelehrung verlesen\n• Eintragung ins Strafregister wegen illegalem Waffenbesitz",
    ],

    // -------------------------------------------------------------------------
    // DROGEN & BTM (BtMG)
    // -------------------------------------------------------------------------
    'btm_besitz_klein' => [
        'name' => 'BTM: Besitz (Geringe Menge)',
        'title' => 'Verstoß BtMG (Eigenbedarf)',
        'incident_description' => "Bei einer Personenkontrolle wirkte [NAME] nervös.\nBei der Durchsuchung (nach § 6 SOG) wurde in der Hosentasche folgende Substanz gefunden:\n\n• Art: [SUBSTANZ, z.B. Cannabis]\n• Menge: ca. [MENGE] Gramm/Stück",
        'actions_taken' => "• Betäubungsmittel sichergestellt und vernichtet\n• Mündliche Verwarnung ausgesprochen\n• Personalien für Datenbank erfasst\n• Platzverweis erteilt",
    ],

    'btm_handel' => [
        'name' => 'BTM: Handel / Große Menge',
        'title' => 'Strafanzeige: Handel mit Betäubungsmitteln',
        'incident_description' => "Observation im Bereich [ORT]. Der TV [NAME] wurde dabei beobachtet, wie er BTM an Dritte übergab.\nBei der anschließenden Festnahme und Durchsuchung wurden gefunden:\n\n• [MENGE]x [SUBSTANZ]\n• [MENGE]x [SUBSTANZ]\n• $[BETRAG] Bargeld (vermutl. Dealgelder)",
        'actions_taken' => "• Festnahme wegen Verdacht auf gewerbsmäßigen Handel\n• Beschlagnahmung aller Substanzen (Asservaten-Nr: [NR])\n• Beschlagnahmung des Bargelds und der Mobiltelefone\n• Transport zum HQ und erkennungsdienstliche Behandlung",
    ],

    // -------------------------------------------------------------------------
    // ORDNUNG & SONSTIGES
    // -------------------------------------------------------------------------
    'sonstiges_platzverweis' => [
        'name' => 'Ordnung: Platzverweis Durchsetzung',
        'title' => 'Bericht: Durchsetzung Platzverweis / Gewahrsam',
        'incident_description' => "Die Person [NAME] störte massiv eine polizeiliche Maßnahme am [ORT].\nEinem mehrfach ausgesprochenen Platzverweis wurde nicht Folge geleistet.\nDie Person verhielt sich weiterhin aggressiv und uneinsichtig.",
        'actions_taken' => "• Ingewahrsamnahme zur Durchsetzung des Platzverweises (§ 5 SOG)\n• Transport zur Ausnüchterungszelle/Dienststelle\n• Dauer des Gewahrsams: Bis [UHRZEIT] (max. 48h)\n• Durchsuchung zum Eigenschutz durchgeführt",
    ],

    'sonstiges_hausfriedensbruch' => [
        'name' => 'Ordnung: Hausfriedensbruch (SG/PD)',
        'title' => 'Strafanzeige: Hausfriedensbruch Sicherheitsbereich',
        'incident_description' => "Die Person [NAME] wurde im Sicherheitsbereich (Sperrzone) des [LSPD/FIB/SG] angetroffen.\nTrotz Beschilderung und Aufforderung durch Beamte weigerte sich die Person, den Bereich zu verlassen.",
        'actions_taken' => "• Vorläufige Festnahme\n• Identitätsfeststellung\n• Durchsuchung auf gefährliche Gegenstände\n• Anzeige wegen Hausfriedensbruch und Widerstand gefertigt",
    ],
];