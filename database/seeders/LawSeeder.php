<?php

namespace Database\Seeders;

use App\Models\Law;
use Illuminate\Database\Seeder;

class LawSeeder extends Seeder
{
    public function run()
    {
        $laws = [
            // --- Abschnitt A: Verfassung ---
            ['book' => 'Verfassung', 'paragraph' => 'Präambel', 'title' => 'Einleitung', 'content' => 'Die Bürger der Hansestadt Hamburg geben sich dieses Gesetzbuch, um dem inneren Frieden, der Gerechtigkeit und der Freiheit zu dienen.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 1', 'title' => 'Menschenwürde & Schutzpflicht', 'content' => '(1) Die Würde des Menschen ist unantastbar. Sie zu achten und zu schützen ist Verpflichtung aller staatlichen Gewalt. (2) Niemand darf einer grausamen, unmenschlichen oder erniedrigenden Behandlung oder Strafe unterworfen werden.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 2', 'title' => 'Allgemeine Handlungsfreiheit', 'content' => '(1) Jeder hat das Recht auf die freie Entfaltung seiner Persönlichkeit, soweit er nicht die Rechte anderer verletzt und nicht gegen die verfassungsmäßige Ordnung verstößt. (2) Jeder hat das Recht auf Leben und körperliche Unversehrtheit. Die Freiheit der Person ist unverletzlich.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 3', 'title' => 'Gleichheit', 'content' => '(1) Alle Menschen sind vor dem Gesetz gleich. (2) Diskriminierung aufgrund von Geschlecht, Abstammung, Sprache, Heimat, Glauben oder politischer Anschauung ist verboten.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 4', 'title' => 'Glaubensfreiheit', 'content' => 'Die Freiheit des Glaubens, des Gewissens und die Freiheit des religiösen und weltanschaulichen Bekenntnisses sind unverletzlich.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 5', 'title' => 'Meinungs- und Pressefreiheit', 'content' => '(1) Jeder hat das Recht, seine Meinung in Wort, Schrift und Bild frei zu äußern. (2) Die Pressefreiheit wird gewährleistet. Eine Zensur findet nicht statt.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 6', 'title' => 'Ehe und Familie', 'content' => 'Ehe und Familie stehen unter dem besonderen Schutze der staatlichen Ordnung.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 7', 'title' => 'Versammlungsfreiheit', 'content' => 'Alle Deutschen haben das Recht, sich ohne Anmeldung oder Erlaubnis friedlich und ohne Waffen zu versammeln.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 8', 'title' => 'Briefgeheimnis', 'content' => 'Das Briefgeheimnis sowie das Post- und Fernmeldegeheimnis sind unverletzlich. Beschränkungen dürfen nur auf Grund eines Gesetzes angeordnet werden.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 9', 'title' => 'Freizügigkeit', 'content' => 'Alle Deutschen genießen Freizügigkeit im ganzen Stadtgebiet.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 10', 'title' => 'Berufsfreiheit', 'content' => '(1) Alle Deutschen haben das Recht, Beruf, Arbeitsplatz und Ausbildungsstätte frei zu wählen. (2) Die Berufsausübung kann durch Gesetz geregelt werden.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 11', 'title' => 'Unverletzlichkeit der Wohnung', 'content' => '(1) Die Wohnung ist unverletzlich. (2) Durchsuchungen dürfen nur durch den Richter, bei Gefahr im Verzug auch durch die Polizei angeordnet werden.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 12', 'title' => 'Eigentum', 'content' => '(1) Das Eigentum und das Erbrecht werden gewährleistet. (2) Eigentum verpflichtet. Sein Gebrauch soll zugleich dem Wohle der Allgemeinheit dienen.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 13', 'title' => 'Rechtsweggarantie', 'content' => 'Wird jemand durch die öffentliche Gewalt in seinen Rechten verletzt, so steht ihm der Rechtsweg offen.'],
            ['book' => 'Verfassung', 'paragraph' => 'Art. 14', 'title' => 'Gewaltenteilung', 'content' => 'Die Staatsgewalt wird durch besondere Organe der Gesetzgebung (Legislative), der vollziehenden Gewalt (Exekutive/Polizei) und der Rechtsprechung (Judikative) ausgeübt.'],

            // --- Abschnitt B: SOG ---
            ['book' => 'SOG', 'paragraph' => '§ 1', 'title' => 'Aufgaben der Polizei', 'content' => 'Die Polizei hat die Aufgabe, Gefahren für die öffentliche Sicherheit oder Ordnung abzuwehren (Gefahrenabwehr) und Straftaten zu verfolgen (Strafverfolgung).'],
            ['book' => 'SOG', 'paragraph' => '§ 2', 'title' => 'Verhältnismäßigkeit', 'content' => 'Von mehreren möglichen Maßnahmen hat die Polizei diejenige zu treffen, die den Einzelnen und die Allgemeinheit am wenigsten beeinträchtigt.'],
            ['book' => 'SOG', 'paragraph' => '§ 3', 'title' => 'Identitätsfeststellung', 'content' => 'Die Polizei darf die Identität einer Person feststellen, wenn dies zur Abwehr einer Gefahr erforderlich ist oder die Person verdächtig ist.'],
            ['book' => 'SOG', 'paragraph' => '§ 4', 'title' => 'Platzverweis', 'content' => 'Die Polizei kann zur Abwehr einer Gefahr eine Person vorübergehend von einem Ort verweisen.'],
            ['book' => 'SOG', 'paragraph' => '§ 5', 'title' => 'Gewahrsam', 'content' => 'Eine Person darf in Gewahrsam genommen werden, wenn dies zum Schutz der Person erforderlich ist oder um die Begehung einer Straftat zu verhindern. Dauer: Max. 48 Stunden.'],
            ['book' => 'SOG', 'paragraph' => '§ 6', 'title' => 'Durchsuchung von Personen', 'content' => 'Eine Person darf durchsucht werden bei Festnahme, zum Eigenschutz der Beamten oder bei Verdacht auf Mitführen gefährlicher Gegenstände.'],
            ['book' => 'SOG', 'paragraph' => '§ 7', 'title' => 'Durchsuchung von Sachen', 'content' => 'Fahrzeuge und Behältnisse dürfen durchsucht werden, wenn Tatsachen die Annahme rechtfertigen, dass sich darin Sachen befinden, die sichergestellt werden dürfen.'],
            ['book' => 'SOG', 'paragraph' => '§ 8', 'title' => 'Betreten und Durchsuchen von Wohnungen', 'content' => 'Wohnungen dürfen nur bei Gefahr im Verzug oder mit richterlichem Beschluss betreten werden.'],
            ['book' => 'SOG', 'paragraph' => '§ 9', 'title' => 'Sicherstellung', 'content' => 'Die Polizei kann Sachen sicherstellen, um eine Gefahr abzuwehren oder den Eigentümer vor Verlust zu schützen.'],
            ['book' => 'SOG', 'paragraph' => '§ 10', 'title' => 'Datenerhebung', 'content' => 'Die Polizei darf personenbezogene Daten erheben, soweit dies zur Erfüllung ihrer Aufgaben erforderlich ist (z.B. Zeugenbefragung, Videoüberwachung an Kriminalitätsschwerpunkten).'],
            ['book' => 'SOG', 'paragraph' => '§ 11', 'title' => 'Erkennungsdienstliche Maßnahmen', 'content' => 'Bei Beschuldigten dürfen Fotos (Lichtbilder) und Fingerabdrücke genommen werden.'],
            ['book' => 'SOG', 'paragraph' => '§ 12', 'title' => 'Vorladung', 'content' => 'Die Polizei kann Personen vorladen, wenn dies zur Sachverhaltsklärung nötig ist.'],
            ['book' => 'SOG', 'paragraph' => '§ 13', 'title' => 'Fesselung', 'content' => 'Personen dürfen gefesselt werden bei Fluchtgefahr, Widerstand oder Selbstgefährdung.'],
            ['book' => 'SOG', 'paragraph' => '§ 14', 'title' => 'Unmittelbarer Zwang', 'content' => 'Die Polizei darf Zwangsmittel anwenden (körperliche Gewalt, Hilfsmittel, Waffen), um Maßnahmen durchzusetzen.'],
            ['book' => 'SOG', 'paragraph' => '§ 15', 'title' => 'Schusswaffengebrauch', 'content' => 'Schusswaffen dürfen nur als letztes Mittel (Ultima Ratio) eingesetzt werden, um eine gegenwärtige Gefahr für Leib oder Leben abzuwehren.'],
            ['book' => 'SOG', 'paragraph' => '§ 16', 'title' => 'Inanspruchnahme von Nichtstörern', 'content' => 'Die Polizei kann unbeteiligte Dritte zur Hilfeleistung verpflichten, wenn die eigenen Kräfte nicht ausreichen (z.B. Arzt bei Verletzten).'],

            // --- Abschnitt C: StPO ---
            ['book' => 'StPO', 'paragraph' => '§ 1', 'title' => 'Legalitätsprinzip', 'content' => 'Staatsanwaltschaft und Polizei sind verpflichtet, bei Verdacht einer Straftat zu ermitteln.'],
            ['book' => 'StPO', 'paragraph' => '§ 2', 'title' => 'Rechte des Beschuldigten', 'content' => 'Der Beschuldigte hat das Recht zu schweigen, einen Anwalt zu konsultieren und Beweise zu seiner Entlastung zu beantragen.'],
            ['book' => 'StPO', 'paragraph' => '§ 3', 'title' => 'Belehrungspflicht', 'content' => 'Vor jeder Vernehmung ist der Beschuldigte über seine Rechte zu belehren.'],
            ['book' => 'StPO', 'paragraph' => '§ 4', 'title' => 'Untersuchungshaft', 'content' => 'U-Haft ist zulässig bei Fluchtgefahr, Verdunkelungsgefahr oder Wiederholungsgefahr bei schweren Delikten.'],
            ['book' => 'StPO', 'paragraph' => '§ 5', 'title' => 'Akteneinsicht', 'content' => 'Der Verteidiger hat das Recht, die Ermittlungsakten einzusehen.'],
            ['book' => 'StPO', 'paragraph' => '§ 6', 'title' => 'Beweismittel', 'content' => 'Zulässige Beweise: Zeugen, Sachverständige, Urkunden, Augenschein (Videos, Waffen). Verbotene Methoden (Folter) führen zu Beweisverwertungsverboten.'],
            ['book' => 'StPO', 'paragraph' => '§ 7', 'title' => 'Zeugenpflichten', 'content' => 'Zeugen sind verpflichtet, wahrheitsgemäß auszusagen, sofern ihnen kein Zeugnisverweigerungsrecht (z.B. Ehepartner) zusteht.'],
            ['book' => 'StPO', 'paragraph' => '§ 8', 'title' => 'Jedermann-Festnahme', 'content' => 'Jeder darf eine Person vorläufig festnehmen, wenn diese auf frischer Tat betroffen ist und Fluchtgefahr besteht.'],

            // --- Abschnitt D: GVG ---
            ['book' => 'GVG', 'paragraph' => '§ 1', 'title' => 'Unabhängigkeit', 'content' => 'Richter sind unabhängig und nur dem Gesetz unterworfen.'],
            ['book' => 'GVG', 'paragraph' => '§ 2', 'title' => 'Zuständigkeiten', 'content' => '(1) Amtsgericht: Strafsachen bis 4 Jahre Haft. (2) Landgericht: Schwere Verbrechen (Mord, Raub).'],
            ['book' => 'GVG', 'paragraph' => '§ 3', 'title' => 'Staatsanwaltschaft', 'content' => 'Sie leitet das Ermittlungsverfahren und vertritt die Anklage vor Gericht.'],
            ['book' => 'GVG', 'paragraph' => '§ 4', 'title' => 'Öffentlichkeit', 'content' => 'Verhandlungen sind öffentlich. Bei Gefährdung der Staatssicherheit oder schutzwürdigen Interessen kann die Öffentlichkeit ausgeschlossen werden.'],

            // --- Abschnitt E: JVollzG ---
            ['book' => 'JVollzG', 'paragraph' => '§ 1', 'title' => 'Ziel des Vollzugs', 'content' => 'Der Vollzug dient dem Schutz der Allgemeinheit und der Resozialisierung des Gefangenen.'],
            ['book' => 'JVollzG', 'paragraph' => '§ 2', 'title' => 'Trennungsprinzip', 'content' => 'Männliche und weibliche Gefangene sowie Jugendliche und Erwachsene sind getrennt unterzubringen.'],
            ['book' => 'JVollzG', 'paragraph' => '§ 3', 'title' => 'Besuchsrecht', 'content' => 'Gefangene haben das Recht auf Besuch, der aus Sicherheitsgründen überwacht werden kann.'],
            ['book' => 'JVollzG', 'paragraph' => '§ 4', 'title' => 'Disziplinarmaßnahmen', 'content' => 'Bei Verstößen gegen die Anstaltsordnung können Maßnahmen verhängt werden (Einschluss, Streichung von Freizeit).'],
            ['book' => 'JVollzG', 'paragraph' => '§ 5', 'title' => 'Verbotene Gegenstände', 'content' => 'Waffen, Drogen, Mobiltelefone und Ausbruchswerkzeuge sind in der JVA streng verboten.'],

            // --- Abschnitt F: StGB ---
            // Allgemein
            ['book' => 'StGB', 'paragraph' => '§ 1', 'title' => 'Keine Strafe ohne Gesetz', 'content' => 'Rückwirkungsverbot.'],
            ['book' => 'StGB', 'paragraph' => '§ 15', 'title' => 'Vorsatz und Fahrlässigkeit', 'content' => 'Strafbar ist nur vorsätzliches Handeln, wenn nicht das Gesetz fahrlässiges Handeln ausdrücklich unter Strafe stellt.'],
            ['book' => 'StGB', 'paragraph' => '§ 22', 'title' => 'Versuch', 'content' => 'Der Versuch eines Verbrechens ist stets strafbar.'],
            ['book' => 'StGB', 'paragraph' => '§ 25', 'title' => 'Täterschaft', 'content' => 'Als Täter wird bestraft, wer die Tat selbst oder durch einen anderen begeht.'],
            ['book' => 'StGB', 'paragraph' => '§ 26', 'title' => 'Anstiftung', 'content' => 'Gleiche Strafe wie der Täter.'],
            ['book' => 'StGB', 'paragraph' => '§ 27', 'title' => 'Beihilfe', 'content' => 'Strafe kann gemildert werden.'],
            ['book' => 'StGB', 'paragraph' => '§ 32', 'title' => 'Notwehr', 'content' => 'Verteidigung gegen einen rechtswidrigen Angriff.'],
            ['book' => 'StGB', 'paragraph' => '§ 34', 'title' => 'Rechtfertigender Notstand', 'content' => 'Abwägung von Gütern zur Gefahrenabwehr.'],
            // Öffentlicher Frieden
            ['book' => 'StGB', 'paragraph' => '§ 113', 'title' => 'Widerstand gegen Vollstreckungsbeamte', 'content' => 'Gewalt/Drohung gegen Amtsträger.'],
            ['book' => 'StGB', 'paragraph' => '§ 123', 'title' => 'Hausfriedensbruch', 'content' => 'Eindringen in fremdes Besitztum.'],
            ['book' => 'StGB', 'paragraph' => '§ 125', 'title' => 'Landfriedensbruch', 'content' => 'Gewalttätigkeiten aus einer Menschenmenge.'],
            ['book' => 'StGB', 'paragraph' => '§ 126', 'title' => 'Störung des öffentlichen Friedens', 'content' => 'Androhung von Straftaten.'],
            ['book' => 'StGB', 'paragraph' => '§ 129', 'title' => 'Bildung krimineller Vereinigungen', 'content' => 'Gründung/Mitgliedschaft in einer Gang/Mafia.'],
            ['book' => 'StGB', 'paragraph' => '§ 132', 'title' => 'Amtsanmaßung', 'content' => 'Unbefugtes Handeln als Amtsträger.'],
            ['book' => 'StGB', 'paragraph' => '§ 138', 'title' => 'Nichtanzeige geplanter Straftaten', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 140', 'title' => 'Belohnung und Billigung von Straftaten', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 145d', 'title' => 'Vortäuschen einer Straftat / Missbrauch von Notrufen', 'content' => ''],
            // Rechtspflege
            ['book' => 'StGB', 'paragraph' => '§ 153', 'title' => 'Falsche uneidliche Aussage', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 154', 'title' => 'Meineid', 'content' => 'Falschaussage unter Eid.'],
            ['book' => 'StGB', 'paragraph' => '§ 164', 'title' => 'Falsche Verdächtigung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 258', 'title' => 'Strafvereitelung', 'content' => 'Beweise vernichten, Täter verstecken.'],
            // Sexuelle Selbstbestimmung
            ['book' => 'StGB', 'paragraph' => '§ 177', 'title' => 'Sexueller Übergriff / Nötigung / Vergewaltigung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 183', 'title' => 'Exhibitionistische Handlungen', 'content' => ''],
            // Beleidigung
            ['book' => 'StGB', 'paragraph' => '§ 185', 'title' => 'Beleidigung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 186', 'title' => 'Üble Nachrede', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 187', 'title' => 'Verleumdung', 'content' => ''],
            // Gegen das Leben
            ['book' => 'StGB', 'paragraph' => '§ 211', 'title' => 'Mord', 'content' => 'Heimtücke, Habgier, Mordlust.'],
            ['book' => 'StGB', 'paragraph' => '§ 212', 'title' => 'Totschlag', 'content' => 'Tötung ohne Mordmerkmale.'],
            ['book' => 'StGB', 'paragraph' => '§ 216', 'title' => 'Tötung auf Verlangen', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 221', 'title' => 'Aussetzung', 'content' => 'Jemanden in hilfloser Lage im Stich lassen.'],
            ['book' => 'StGB', 'paragraph' => '§ 222', 'title' => 'Fahrlässige Tötung', 'content' => ''],
            // Körperverletzung
            ['book' => 'StGB', 'paragraph' => '§ 223', 'title' => 'Körperverletzung', 'content' => 'Einfache Misshandlung.'],
            ['book' => 'StGB', 'paragraph' => '§ 224', 'title' => 'Gefährliche Körperverletzung', 'content' => 'Mit Waffe, gemeinschaftlich.'],
            ['book' => 'StGB', 'paragraph' => '§ 226', 'title' => 'Schwere Körperverletzung', 'content' => 'Dauerhafte Schäden (Verlust Gliedmaßen).'],
            ['book' => 'StGB', 'paragraph' => '§ 227', 'title' => 'Körperverletzung mit Todesfolge', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 229', 'title' => 'Fahrlässige Körperverletzung', 'content' => ''],
            // Persönliche Freiheit
            ['book' => 'StGB', 'paragraph' => '§ 238', 'title' => 'Nachstellung', 'content' => 'Stalking.'],
            ['book' => 'StGB', 'paragraph' => '§ 239', 'title' => 'Freiheitsberaubung', 'content' => 'Einsperren.'],
            ['book' => 'StGB', 'paragraph' => '§ 239a', 'title' => 'Erpresserischer Menschenraub', 'content' => 'Entführung für Lösegeld.'],
            ['book' => 'StGB', 'paragraph' => '§ 239b', 'title' => 'Geiselnahme', 'content' => 'Entführung um Dritte zu nötigen (z.B. Polizei).'],
            ['book' => 'StGB', 'paragraph' => '§ 240', 'title' => 'Nötigung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 241', 'title' => 'Bedrohung', 'content' => ''],
            // Diebstahl & Unterschlagung
            ['book' => 'StGB', 'paragraph' => '§ 242', 'title' => 'Diebstahl', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 243', 'title' => 'Besonders schwerer Fall', 'content' => 'Einbruch, gewerbsmäßig.'],
            ['book' => 'StGB', 'paragraph' => '§ 244', 'title' => 'Diebstahl mit Waffen', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 246', 'title' => 'Unterschlagung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 248b', 'title' => 'Unbefugter Gebrauch eines Fahrzeugs', 'content' => ''],
            // Raub & Erpressung
            ['book' => 'StGB', 'paragraph' => '§ 249', 'title' => 'Raub', 'content' => 'Gewalt + Wegnahme.'],
            ['book' => 'StGB', 'paragraph' => '§ 250', 'title' => 'Schwerer Raub', 'content' => 'Mit Schusswaffe oder Banden.'],
            ['book' => 'StGB', 'paragraph' => '§ 252', 'title' => 'Räuberischer Diebstahl', 'content' => 'Gewaltanwendung bei Flucht mit Beute.'],
            ['book' => 'StGB', 'paragraph' => '§ 253', 'title' => 'Erpressung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 255', 'title' => 'Räuberische Erpressung', 'content' => ''],
            // Betrug & Untreue
            ['book' => 'StGB', 'paragraph' => '§ 261', 'title' => 'Geldwäsche', 'content' => 'Verschleierung illegaler Vermögenswerte.'],
            ['book' => 'StGB', 'paragraph' => '§ 263', 'title' => 'Betrug', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 263a', 'title' => 'Computerbetrug', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 265b', 'title' => 'Kreditbetrug', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 266', 'title' => 'Untreue', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 267', 'title' => 'Urkundenfälschung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 274', 'title' => 'Urkundenunterdrückung', 'content' => ''],
            // Sachbeschädigung
            ['book' => 'StGB', 'paragraph' => '§ 303', 'title' => 'Sachbeschädigung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 304', 'title' => 'Gemeinschädliche Sachbeschädigung', 'content' => 'Öffentliche Sachen.'],
            ['book' => 'StGB', 'paragraph' => '§ 305', 'title' => 'Zerstörung von Bauwerken', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 306', 'title' => 'Brandstiftung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 306a', 'title' => 'Schwere Brandstiftung', 'content' => 'Wenn Menschen gefährdet sind.'],
            ['book' => 'StGB', 'paragraph' => '§ 308', 'title' => 'Herbeiführen einer Sprengstoffexplosion', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 315b', 'title' => 'Gefährlicher Eingriff in den Straßenverkehr', 'content' => 'Steine werfen, Hindernisse.'],
            ['book' => 'StGB', 'paragraph' => '§ 315c', 'title' => 'Gefährdung des Straßenverkehrs', 'content' => 'Rauschfahrt + Gefährdung.'],
            ['book' => 'StGB', 'paragraph' => '§ 315d', 'title' => 'Verbotene Kraftfahrzeugrennen', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 316', 'title' => 'Trunkenheit im Verkehr', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 323c', 'title' => 'Unterlassene Hilfeleistung', 'content' => ''],
            // Amtsdelikte
            ['book' => 'StGB', 'paragraph' => '§ 331', 'title' => 'Vorteilsannahme', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 332', 'title' => 'Bestechlichkeit', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 333', 'title' => 'Vorteilsgewährung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 334', 'title' => 'Bestechung', 'content' => ''],
            ['book' => 'StGB', 'paragraph' => '§ 340', 'title' => 'Körperverletzung im Amt', 'content' => ''],

            // --- Abschnitt G: BtMG ---
            ['book' => 'BtMG', 'paragraph' => '§ 1', 'title' => 'Verbotene Substanzen', 'content' => 'Der Umgang mit Drogen (Weed, Kokain, Meth, LSD, Heroin) ist verboten.'],
            ['book' => 'BtMG', 'paragraph' => '§ 2', 'title' => 'Grunddelikte', 'content' => 'Strafbar ist: Anbau, Herstellung, Handel, Einfuhr, Ausfuhr, Veräußerung, Abgabe, Erwerb und Besitz.'],
            ['book' => 'BtMG', 'paragraph' => '§ 3', 'title' => 'Schwere Fälle', 'content' => 'Handel mit nicht geringen Mengen oder Abgabe an Minderjährige wird als Verbrechen bestraft.'],
            ['book' => 'BtMG', 'paragraph' => '§ 4', 'title' => 'Eigenbedarf', 'content' => 'Bei geringen Mengen zum Eigenverbrauch kann die Staatsanwaltschaft von einer Verfolgung absehen (Substanz wird dennoch eingezogen).'],

            // --- Abschnitt H: WaffG ---
            ['book' => 'WaffG', 'paragraph' => '§ 1', 'title' => 'Gegenstand', 'content' => 'Regelt den Umgang mit Waffen und Munition.'],
            ['book' => 'WaffG', 'paragraph' => '§ 2', 'title' => 'Erlaubnispflicht', 'content' => 'Für den Erwerb und Besitz von Schusswaffen ist eine Waffenbesitzkarte (WBK) erforderlich.'],
            ['book' => 'WaffG', 'paragraph' => '§ 3', 'title' => 'Kleiner Waffenschein', 'content' => 'Berechtigt zum Führen von Schreckschusswaffen.'],
            ['book' => 'WaffG', 'paragraph' => '§ 4', 'title' => 'Großer Waffenschein', 'content' => 'Berechtigt zum Führen von scharfen Schusswaffen (sehr selten, nur bei besonderer Gefährdung).'],
            ['book' => 'WaffG', 'paragraph' => '§ 5', 'title' => 'Verbotene Waffen', 'content' => 'Vollautomaten (AK-47), Pumpguns, Schlagringe, Butterflymesser sind verboten.'],
            ['book' => 'WaffG', 'paragraph' => '§ 6', 'title' => 'Anscheinswaffen', 'content' => 'Das Führen von Waffenattrappen in der Öffentlichkeit ist verboten.'],

            // --- Abschnitt I: StVO ---
            ['book' => 'StVO', 'paragraph' => '§ 1', 'title' => 'Grundregeln', 'content' => 'Ständige Vorsicht und gegenseitige Rücksichtnahme.'],
            ['book' => 'StVO', 'paragraph' => '§ 2', 'title' => 'Straßenbenutzung', 'content' => 'Rechtsfahrgebot.'],
            ['book' => 'StVO', 'paragraph' => '§ 3', 'title' => 'Geschwindigkeit', 'content' => 'Geschwindigkeit ist den Straßen-, Verkehrs- und Wetterverhältnissen anzupassen.'],
            ['book' => 'StVO', 'paragraph' => '§ 4', 'title' => 'Abstand', 'content' => 'Ausreichender Sicherheitsabstand zum Vordermann.'],
            ['book' => 'StVO', 'paragraph' => '§ 5', 'title' => 'Überholen', 'content' => 'Überholen nur, wenn Behinderung des Gegenverkehrs ausgeschlossen ist.'],
            ['book' => 'StVO', 'paragraph' => '§ 6', 'title' => 'Vorfahrt', 'content' => 'Rechts vor Links, sofern nicht anders geregelt.'],
            ['book' => 'StVO', 'paragraph' => '§ 7', 'title' => 'Abbiegen', 'content' => 'Rechtzeitig blinken, Schulterblick.'],
            ['book' => 'StVO', 'paragraph' => '§ 8', 'title' => 'Halten und Parken', 'content' => 'Verboten an engen Stellen, Kurven, Feuerwehrzufahrten.'],
            ['book' => 'StVO', 'paragraph' => '§ 9', 'title' => 'Beleuchtung', 'content' => 'Bei Dämmerung und Dunkelheit ist mit Licht zu fahren.'],
            ['book' => 'StVO', 'paragraph' => '§ 10', 'title' => 'Sonderrechte', 'content' => 'Polizei und Feuerwehr sind bei Einsätzen von der StVO befreit.'],
            ['book' => 'StVO', 'paragraph' => '§ 11', 'title' => 'Wegerecht', 'content' => 'Blaues Blinklicht und Horn ordnen an: "Sofort freie Bahn schaffen".'],
            ['book' => 'StVO', 'paragraph' => '§ 12', 'title' => 'Umwelt', 'content' => 'Unnötiger Lärm und Abgase sind zu vermeiden (Motor abstellen).'],

            // --- Abschnitt J: VersG ---
            ['book' => 'VersG', 'paragraph' => '§ 1', 'title' => 'Recht auf Versammlung', 'content' => 'Friedliche Versammlungen sind gestattet.'],
            ['book' => 'VersG', 'paragraph' => '§ 2', 'title' => 'Anmeldepflicht', 'content' => 'Öffentliche Demos müssen 48h vorher bei der Polizei angemeldet werden.'],
            ['book' => 'VersG', 'paragraph' => '§ 3', 'title' => 'Auflösung', 'content' => 'Nicht angemeldete oder gewalttätige Versammlungen können aufgelöst werden.'],
            ['book' => 'VersG', 'paragraph' => '§ 4', 'title' => 'Waffenverbot', 'content' => 'Waffen sind auf Versammlungen verboten.'],
            ['book' => 'VersG', 'paragraph' => '§ 5', 'title' => 'Vermummungsverbot', 'content' => 'Teilnehmer dürfen sich nicht vermummen, um ihre Identität zu verschleiern.'],
            ['book' => 'VersG', 'paragraph' => '§ 6', 'title' => 'Schutzwaffenverbot', 'content' => 'Das Tragen von Helmen, Schilden oder Schutzwesten ("Passivbewaffnung") ist verboten.'],

            // --- Abschnitt K: GewO ---
            ['book' => 'GewO', 'paragraph' => '§ 1', 'title' => 'Gewerbefreiheit', 'content' => 'Jeder darf ein Gewerbe betreiben.'],
            ['book' => 'GewO', 'paragraph' => '§ 2', 'title' => 'Anzeigepflicht', 'content' => 'Jedes Gewerbe muss beim Amt angemeldet werden.'],
            ['book' => 'GewO', 'paragraph' => '§ 3', 'title' => 'Zuverlässigkeit', 'content' => 'Bei Vorstrafen kann das Gewerbe untersagt werden.'],
            ['book' => 'GewO', 'paragraph' => '§ 4', 'title' => 'Gaststätten', 'content' => 'Benötigen eine Konzession (Alkohol) und Gesundheitszeugnis.'],
            ['book' => 'GewO', 'paragraph' => '§ 5', 'title' => 'Bewachungsgewerbe', 'content' => 'Security-Firmen brauchen eine besondere Erlaubnis und polizeiliches Führungszeugnis.'],
            ['book' => 'GewO', 'paragraph' => '§ 6', 'title' => 'Glücksspiel', 'content' => 'Das Aufstellen von Automaten bedarf gesonderter Genehmigung.'],

            // --- Abschnitt L: LuftVG ---
            ['book' => 'LuftVG', 'paragraph' => '§ 1', 'title' => 'Erlaubnispflicht', 'content' => 'Fliegen erfordert eine Lizenz (Pilotenschein).'],
            ['book' => 'LuftVG', 'paragraph' => '§ 2', 'title' => 'Sicherheitsmindesthöhe', 'content' => 'Über Städten min. 1000 Fuß (300m), außer bei Start/Landung.'],
            ['book' => 'LuftVG', 'paragraph' => '§ 3', 'title' => 'Flugverbotszonen', 'content' => 'Über JVA, Regierungsgebäuden und Atomkraftwerken ist das Fliegen verboten.'],
            ['book' => 'LuftVG', 'paragraph' => '§ 4', 'title' => 'Drohnen', 'content' => 'Drohnenflug über Menschenmengen ist verboten.'],
            ['book' => 'LuftVG', 'paragraph' => '§ 5', 'title' => 'Anweisungen', 'content' => 'Den Anweisungen des Towers (Flugsicherung) ist Folge zu leisten.'],
            ['book' => 'LuftVG', 'paragraph' => '§ 6', 'title' => 'Landezwang', 'content' => 'Bei Aufforderung durch Polizeihubschrauber ist sofort zu landen.'],
            ['book' => 'LuftVG', 'paragraph' => '§ 7', 'title' => 'Abwurf von Gegenständen', 'content' => 'Der Abwurf von Gegenständen oder Fallschirmspringern über der Stadt ist genehmigungspflichtig.'],

            // --- Abschnitt M: HafenVO ---
            ['book' => 'HafenVO', 'paragraph' => '§ 1', 'title' => 'Geltungsbereich', 'content' => 'Gilt für alle Wasserflächen im Stadtgebiet Hamburg.'],
            ['book' => 'HafenVO', 'paragraph' => '§ 2', 'title' => 'Vorrang', 'content' => 'Berufsschifffahrt hat Vorrang vor Sportbooten.'],
            ['book' => 'HafenVO', 'paragraph' => '§ 3', 'title' => 'Geschwindigkeit', 'content' => 'Im Hafenbecken gilt Schrittgeschwindigkeit (Sogenschlag vermeiden).'],
            ['book' => 'HafenVO', 'paragraph' => '§ 4', 'title' => 'Anlegen', 'content' => 'Nur an ausgewiesenen Plätzen erlaubt. Behörden-Anleger sind tabu.'],
            ['book' => 'HafenVO', 'paragraph' => '§ 5', 'title' => 'Umschlag', 'content' => 'Warenumschlag nur an lizenzierten Kais (Zollkontrolle).'],
            ['book' => 'HafenVO', 'paragraph' => '§ 6', 'title' => 'Tauchen', 'content' => 'Tauchen im Hafenbecken ist verboten (Lebensgefahr).'],
            ['book' => 'HafenVO', 'paragraph' => '§ 7', 'title' => 'Umwelt', 'content' => 'Einleiten von Öl/Abfall ist streng verboten.']
        ];

        foreach ($laws as $law) {
            Law::create($law);
        }
    }
}