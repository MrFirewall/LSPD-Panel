<?php

namespace Database\Seeders;

use App\Models\Fine;
use Illuminate\Database\Seeder;

class FineSeeder extends Seeder
{
    public function run()
    {
        $fines = [
            // --- [I] StVO: 1. Geschwindigkeit ---
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => 'bis 10 km/h zu schnell', 'amount' => 500, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => '11 - 20 km/h zu schnell', 'amount' => 1000, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => '21 - 30 km/h zu schnell', 'amount' => 2500, 'jail_time' => 0, 'points' => 1, 'remark' => ''],
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => '31 - 50 km/h zu schnell', 'amount' => 5000, 'jail_time' => 15, 'points' => 2, 'remark' => '1 Monat (15 Min) Fahrverbot'],
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => '51 - 70 km/h zu schnell', 'amount' => 10000, 'jail_time' => 30, 'points' => 3, 'remark' => '2 Monate (30 Min) Fahrverbot'],
            ['catalog_section' => 'StVO - Geschwindigkeit', 'offense' => 'über 70 km/h zu schnell', 'amount' => 20000, 'jail_time' => 0, 'points' => 4, 'remark' => 'Entzug Fahrerlaubnis'],

            // --- [I] StVO: 2. Allgemeines Verkehrsverhalten ---
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Parken im Halteverbot / auf Gehweg', 'amount' => 500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Abschleppen mgl.'],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Parken vor Hydranten / Einfahrten', 'amount' => 1000, 'jail_time' => 0, 'points' => 0, 'remark' => 'Abschleppen zwingend'],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Missachtung "Rechts vor Links"', 'amount' => 1500, 'jail_time' => 0, 'points' => 1, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Überfahren einer roten Ampel', 'amount' => 2500, 'jail_time' => 0, 'points' => 1, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Rotlichtverstoß mit Gefährdung', 'amount' => 5000, 'jail_time' => 0, 'points' => 2, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Fahren entgegen der Fahrtrichtung', 'amount' => 7500, 'jail_time' => 0, 'points' => 3, 'remark' => 'Geisterfahrer'],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Wenden an unzulässigen Stellen', 'amount' => 1500, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Nichtbeachten von Sondersignalen', 'amount' => 5000, 'jail_time' => 0, 'points' => 2, 'remark' => 'Blaulicht/Horn'],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Behindern von Einsatzfahrzeugen', 'amount' => 10000, 'jail_time' => 10, 'points' => 3, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Fahren ohne Licht bei Dunkelheit', 'amount' => 1000, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Handy am Steuer', 'amount' => 2500, 'jail_time' => 0, 'points' => 1, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Nichtanlegen des Sicherheitsgurtes', 'amount' => 500, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Fahren abseits befestigter Straßen', 'amount' => 3500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Offroad in Stadt'],
            ['catalog_section' => 'StVO - Allgemein', 'offense' => 'Unnützes Hin- und Herfahren', 'amount' => 1000, 'jail_time' => 0, 'points' => 0, 'remark' => 'Lärmbelästigung'],

            // --- [I] StVO: 3. Fahrzeugmängel & Dokumente ---
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Fahren ohne Fahrerlaubnis', 'amount' => 15000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Fahren trotz Fahrverbot', 'amount' => 25000, 'jail_time' => 30, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Fahren ohne Zulassung/Versicherung', 'amount' => 5000, 'jail_time' => 0, 'points' => 0, 'remark' => 'KFZ-Stilllegung'],
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Erloschene Betriebserlaubnis', 'amount' => 7500, 'jail_time' => 0, 'points' => 2, 'remark' => 'Tuning, Stilllegung'],
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Verbandskasten fehlt / abgelaufen', 'amount' => 250, 'jail_time' => 0, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StVO - Dokumente/Mängel', 'offense' => 'Kennzeichen fehlt / unleserlich', 'amount' => 5000, 'jail_time' => 0, 'points' => 1, 'remark' => ''],

            // --- [I] StVO: 4. Verkehrsstraftaten ---
            ['catalog_section' => 'StVO - Straftaten', 'offense' => 'Unerlaubtes Entfernen vom Unfallort', 'amount' => 15000, 'jail_time' => 20, 'points' => 0, 'remark' => '+ Sachschaden'],
            ['catalog_section' => 'StVO - Straftaten', 'offense' => 'Gefährlicher Eingriff in Straßenverkehr', 'amount' => 20000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Führerscheinentzug'],
            ['catalog_section' => 'StVO - Straftaten', 'offense' => 'Trunkenheit im Verkehr (erste Mal)', 'amount' => 10000, 'jail_time' => 10, 'points' => 0, 'remark' => 'Fahrverbot'],
            ['catalog_section' => 'StVO - Straftaten', 'offense' => 'Trunkenheit im Verkehr (wiederholt)', 'amount' => 25000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Führerscheinentzug'],
            ['catalog_section' => 'StVO - Straftaten', 'offense' => 'Illegales Straßenrennen (Teilnahme)', 'amount' => 50000, 'jail_time' => 45, 'points' => 0, 'remark' => 'KFZ-Einzug + FS-Entzug'],

            // --- [II] StGB: 1. Eigentumsdelikte ---
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Sachbeschädigung (leicht)', 'amount' => 2500, 'jail_time' => 0, 'points' => 0, 'remark' => 'z.B. Kratzer, Graffiti'],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Sachbeschädigung (schwer/Staat)', 'amount' => 10000, 'jail_time' => 15, 'points' => 0, 'remark' => 'z.B. Auto zerstören'],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Diebstahl (Versuch)', 'amount' => 2500, 'jail_time' => 5, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Diebstahl (geringfügig)', 'amount' => 5000, 'jail_time' => 10, 'points' => 0, 'remark' => 'Taschendiebstahl'],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Diebstahl (schwer / KFZ-Diebstahl)', 'amount' => 15000, 'jail_time' => 30, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Diebstahl von Einsatzfahrzeugen', 'amount' => 50000, 'jail_time' => 60, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Einbruch / Hausfriedensbruch', 'amount' => 20000, 'jail_time' => 25, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Eigentum', 'offense' => 'Hehlerei (Besitz von Diebesgut)', 'amount' => 10000, 'jail_time' => 20, 'points' => 0, 'remark' => '+ Warenwert'],

            // --- [II] StGB: 2. Öffentliche Ordnung ---
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Missachtung Platzverweis', 'amount' => 5000, 'jail_time' => 10, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Erregung öffentl. Ärgernisses', 'amount' => 2500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Nacktheit/Urinieren'],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Vermummung in der Öffentlichkeit', 'amount' => 3500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Maske abnehmen!'],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Missbrauch von Notrufen', 'amount' => 10000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Vortäuschen einer Straftat', 'amount' => 15000, 'jail_time' => 20, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Unterlassene Hilfeleistung', 'amount' => 10000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Amtsanmaßung (Falscher Polizist)', 'amount' => 30000, 'jail_time' => 40, 'points' => 0, 'remark' => 'Kleidung beschlagnahmen'],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Widerstand gegen Vollstreckungsbeamte', 'amount' => 15000, 'jail_time' => 30, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Beleidigung (Zivilist)', 'amount' => 2500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Zivilklage möglich'],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Beleidigung (Beamter)', 'amount' => 7500, 'jail_time' => 10, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Flucht vor der Polizei (zu Fuß)', 'amount' => 5000, 'jail_time' => 10, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Öffentliche Ordnung', 'offense' => 'Flucht vor der Polizei (mit KFZ)', 'amount' => 20000, 'jail_time' => 30, 'points' => 0, 'remark' => '+ Verkehrsverstöße'],

            // --- [III] StGB: 1. Körperverletzung ---
            ['catalog_section' => 'StGB - Gewalt', 'offense' => 'Leichte Körperverletzung', 'amount' => 5000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Gewalt', 'offense' => 'Gefährliche Körperverletzung (Waffe)', 'amount' => 15000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Waffenschein-Entzug'],
            ['catalog_section' => 'StGB - Gewalt', 'offense' => 'Schwere Körperverletzung', 'amount' => 30000, 'jail_time' => 50, 'points' => 0, 'remark' => 'Bleibende Schäden'],
            ['catalog_section' => 'StGB - Gewalt', 'offense' => 'Körperverletzung an einem Beamten', 'amount' => 40000, 'jail_time' => 60, 'points' => 0, 'remark' => ''],

            // --- [III] StGB: 2. Freiheit & Nötigung ---
            ['catalog_section' => 'StGB - Freiheit', 'offense' => 'Nötigung / Drohung', 'amount' => 5000, 'jail_time' => 10, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Freiheit', 'offense' => 'Bedrohung mit Schusswaffe', 'amount' => 15000, 'jail_time' => 25, 'points' => 0, 'remark' => 'Waffenschein-Entzug'],
            ['catalog_section' => 'StGB - Freiheit', 'offense' => 'Freiheitsberaubung (Zivilist)', 'amount' => 30000, 'jail_time' => 45, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Freiheit', 'offense' => 'Freiheitsberaubung (Beamter)', 'amount' => 60000, 'jail_time' => 80, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Freiheit', 'offense' => 'Geiselnahme / Menschenraub', 'amount' => 100000, 'jail_time' => 100, 'points' => 0, 'remark' => 'Pro Geisel'],

            // --- [III] StGB: 3. Raubüberfälle ---
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Raubüberfall auf Person', 'amount' => 10000, 'jail_time' => 20, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Ladenraub (Shop/Tankstelle)', 'amount' => 25000, 'jail_time' => 40, 'points' => 0, 'remark' => '+ Beuteabnahme'],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Überfall auf Geldtransporter', 'amount' => 60000, 'jail_time' => 80, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Bankraub (Kleine Bank)', 'amount' => 80000, 'jail_time' => 90, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Bankraub (Zentralbank / Staatsbank)', 'amount' => 150000, 'jail_time' => 120, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Raubüberfall auf Juwelier', 'amount' => 70000, 'jail_time' => 75, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Raub', 'offense' => 'Raubüberfall auf Waffenladen', 'amount' => 90000, 'jail_time' => 90, 'points' => 0, 'remark' => ''],

            // --- [III] StGB: 4. Tötungsdelikte ---
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Fahrlässige Tötung', 'amount' => 50000, 'jail_time' => 60, 'points' => 0, 'remark' => 'z.B. Unfall'],
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Totschlag (Affekt)', 'amount' => 100000, 'jail_time' => 100, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Versuchter Mord', 'amount' => 150000, 'jail_time' => 120, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Mord', 'amount' => 250000, 'jail_time' => 180, 'points' => 0, 'remark' => 'Lebenslänglich mgl.'],
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Mord an einem Beamten (Versuch)', 'amount' => 200000, 'jail_time' => 150, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'StGB - Tötung', 'offense' => 'Mord an einem Beamten (Vollendet)', 'amount' => 500000, 'jail_time' => 300, 'points' => 0, 'remark' => 'Lebenslänglich'],

            // --- [IV] BtMG ---
            ['catalog_section' => 'BtMG', 'offense' => 'Besitz (Eigenbedarf)', 'amount' => 1000, 'jail_time' => 0, 'points' => 0, 'remark' => 'Einzug der Ware'],
            ['catalog_section' => 'BtMG', 'offense' => 'Besitz (Geringe Menge)', 'amount' => 5000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'BtMG', 'offense' => 'Besitz (Handelsmenge)', 'amount' => 15000, 'jail_time' => 45, 'points' => 0, 'remark' => 'je 50 Stk + 10 HE'],
            ['catalog_section' => 'BtMG', 'offense' => 'Anbau / Herstellung', 'amount' => 50000, 'jail_time' => 60, 'points' => 0, 'remark' => '+ Anlagenabbau'],
            ['catalog_section' => 'BtMG', 'offense' => 'Verkauf von BTM', 'amount' => 10000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Zusätzlich zu Besitz'],

            // --- [V] WaffG ---
            ['catalog_section' => 'WaffG', 'offense' => 'Führen ohne Waffenschein (Kl. Schein)', 'amount' => 2500, 'jail_time' => 0, 'points' => 0, 'remark' => 'Waffe beschlagnahmt'],
            ['catalog_section' => 'WaffG', 'offense' => 'Führen ohne WBK (Scharfe Pistole)', 'amount' => 15000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Waffe beschlagnahmt'],
            ['catalog_section' => 'WaffG', 'offense' => 'Offenes Tragen (Open Carry)', 'amount' => 5000, 'jail_time' => 0, 'points' => 0, 'remark' => 'Trotz WBK verboten'],
            ['catalog_section' => 'WaffG', 'offense' => 'Besitz illegaler Waffen (Langwaffen)', 'amount' => 40000, 'jail_time' => 60, 'points' => 0, 'remark' => 'Sturmgewehre etc.'],
            ['catalog_section' => 'WaffG', 'offense' => 'Besitz von Sprengstoff / Granaten', 'amount' => 100000, 'jail_time' => 120, 'points' => 0, 'remark' => 'Terrorismusverdacht'],
            ['catalog_section' => 'WaffG', 'offense' => 'Waffenhandel (Verkauf)', 'amount' => 60000, 'jail_time' => 90, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'WaffG', 'offense' => 'Besitz von Polizeiequipment', 'amount' => 20000, 'jail_time' => 40, 'points' => 0, 'remark' => 'Taser, Schutzweste'],

            // --- [VI] Sondergesetze: 1. Luftfahrt ---
            ['catalog_section' => 'Sondergesetze - Luft', 'offense' => 'Landen an unzulässigen Orten', 'amount' => 15000, 'jail_time' => 10, 'points' => 0, 'remark' => 'Lizenzentzug Flug'],
            ['catalog_section' => 'Sondergesetze - Luft', 'offense' => 'Unterschreitung Mindesthöhe', 'amount' => 10000, 'jail_time' => 5, 'points' => 0, 'remark' => 'Innerorts'],
            ['catalog_section' => 'Sondergesetze - Luft', 'offense' => 'Eindringen in Flugverbotszone', 'amount' => 50000, 'jail_time' => 60, 'points' => 0, 'remark' => 'Beschussfreigabe'],
            ['catalog_section' => 'Sondergesetze - Luft', 'offense' => 'Fliegen ohne Lizenz', 'amount' => 30000, 'jail_time' => 30, 'points' => 0, 'remark' => 'Beschlagnahmung Heli'],

            // --- [VI] Sondergesetze: 2. Schifffahrt ---
            ['catalog_section' => 'Sondergesetze - Wasser', 'offense' => 'Fahren in Sperrgebieten', 'amount' => 20000, 'jail_time' => 20, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'Sondergesetze - Wasser', 'offense' => 'Umweltverschmutzung (Öl/Müll)', 'amount' => 15000, 'jail_time' => 10, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'Sondergesetze - Wasser', 'offense' => 'Illegales Tauchen im Hafen', 'amount' => 5000, 'jail_time' => 0, 'points' => 0, 'remark' => ''],

            // --- [VI] Sondergesetze: 3. Sonstiges ---
            ['catalog_section' => 'Sondergesetze - Sonstiges', 'offense' => 'Gefängnisausbruch (Versuch)', 'amount' => 20000, 'jail_time' => 30, 'points' => 0, 'remark' => '+ Reststrafe'],
            ['catalog_section' => 'Sondergesetze - Sonstiges', 'offense' => 'Gefängnisausbruch (Vollendet)', 'amount' => 50000, 'jail_time' => 60, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'Sondergesetze - Sonstiges', 'offense' => 'Betreten von Sperrzonen (LSPD/FIB)', 'amount' => 10000, 'jail_time' => 15, 'points' => 0, 'remark' => ''],
            ['catalog_section' => 'Sondergesetze - Sonstiges', 'offense' => 'Korruption (Amtsträger)', 'amount' => 150000, 'jail_time' => 200, 'points' => 0, 'remark' => 'Jobverlust + Sperre'],
        ];

        foreach ($fines as $fine) {
            Fine::create($fine);
        }
    }
}