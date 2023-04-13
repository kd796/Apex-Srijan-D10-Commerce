<?php

namespace ADE;

// require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

// if (!defined('DRUPAL_DIR')) {
//   define('DRUPAL_DIR', $_SERVER['DOCUMENT_ROOT']);
// }

// use Drupal;
// use Drupal\Core\Language\LanguageInterface;
// use Drupal\Core\DrupalKernel;
// use Symfony\Component\HttpFoundation\Request;

// require_once DRUPAL_DIR . '/core/includes/database.inc';
// require_once DRUPAL_DIR . '/core/includes/schema.inc';

// // Specify relative path to the drupal root.
// $autoloader = require_once DRUPAL_DIR . '/autoload.php';
// $request = Request::createFromGlobals();

// // Bootstrap drupal to different levels
// $kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
// $kernel->boot();
// $kernel->prepareLegacyRequest($request);

// $language = Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

switch ($_SERVER['SERVER_NAME']) {
    case 'www.clecotools.de':
    case 'staging.clecotools.de':
    case 'de.clecotools.ddev.site' :
    case 'dev-www.clecotools.de' :
    case 'qa-www.clecotools.de' :
    case 'stg-www.clecotools.de' :
    case 'prod-www.clecotools.de' :
        $language = 'de';
        break;
    case 'www.clecotools.co.uk':
    case 'staging.clecotools.co.uk':
    case 'uk.clecotools.ddev.site' :
    case 'dev-www.clecotools.co.uk' :
    case 'qa-www.clecotools.co.uk' :
    case 'stg-www.clecotools.co.uk' :
    case 'prod-www.clecotools.co.uk' :
        $language = 'gb';
        break;
    case 'www.clecotools.com':
    case 'staging.clecotools.com':
    case 'clecotools.ddev.site' :
    case 'dev-www.clecotools.com' :
    case 'qa-www.clecotools.com' :
    case 'stg-www.clecotools.com' :
    case 'prod-www.clecotools.com' :
        $language = 'en';
        break;
}

$translations = [
  'language'     => $language,
  'translations' => [
    'tools/advanced-drilling' => [
      'de' => 'werkzeuge/bohren'
    ],
    'Introduction' => [
      'de' => 'Einführung'
    ],
    'Advanced Drilling Equipment Inquiry' => [
      'de' => 'Anfrage einer Bohrvorschubeinheit',
    ],
    'Application request for quotation only, this is not an order.' => [
      'de' => 'Angebotsanforderung für eine Anwendung. Das ist kein Auftrag.',
    ],
    'Required fields.' => [
      'de' => 'Notwendige Felder.',
    ],
    'Business Information' => [
      'de' => 'Firmeninformation',
    ],
    'Contact Name' => [
      'de' => 'Kontaktname',
    ],
    'Company Name' => [
      'de' => 'Firmenname',
    ],
    'Email Address' => [
      'de' => 'E-Mail-Adresse',
    ],
    'Phone' => [
      'de' => 'Telefon',
    ],
    'State' => [
      'de' => 'Staat',
    ],
    'Country' => [
      'de' => 'Land',
    ],
    'Industry' => [
      'de' => 'Marktsegment',
    ],
    'Select' => [
      'de' => 'Auswahl',
    ],
    '- Select -' => [
      'de' => '- Auswahl -',
    ],
    'Aeroplane Final Assembly' => [
      'de' => 'Flugzeugbau Endmontage',
    ],
    'Aeroplane Structure' => [
      'de' => 'Flugzeugbau Struktur',
    ],
    'Helicopter Final Assembly' => [
      'de' => 'Hubschrauber Endmontage',
    ],
    'Other Aerospace' => [
      'de' => 'Andere Bereiche im Flugzeugbau',
    ],
    'Other Industry' => [
      'de' => 'Andere Industriezweige',
    ],
    'Inquiry Type' => [
      'de' => 'Anfrageart',
    ],
    'General Information' => [
      'de' => 'Allgemeine Informationen',
    ],
    'Product Configuration' => [
      'de' => 'Produktanfrage',
    ],
    'Application' => [
      'de' => 'Anwendung',
    ],
    'Application Assistance' => [
      'de' => 'Unterstützung bei Anwendung',
    ],
    'New Application' => [
      'de' => 'Neue Anwendung',
    ],
    'Existing Application' => [
      'de' => 'Vorhandene Anwendung',
    ],
    'Please contact me' => [
      'de' => 'Bitte kontaktiere mich',
    ],
    'I will provide information now' => [
      'de' => 'Ich werde jetzt Auskunft geben',
    ],
    'Next Step' => [
      'de' => 'Nächster Schritt',
    ],
    'Are you sure you want to delete application #@index? This action cannot be undone.' => [
      'de' => 'Möchten Sie die Anwendung Nr. @index wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.',
    ],
    'Product Type' => [
      'de' => 'Produkttyp',
    ],
    'Product Types' => [
      'de' => 'Produkttypen',
    ],
    'More than one selection allowed' => [
      'de' => 'Mehr als eine Auswahl erlaubt',
    ],
    'Application Information' => [
      'de' => 'Anwendung Information',
    ],
    'Application #' => [
      'de' => 'Anwendung Nr.',
    ],
    'Edit' => [
      'de' => 'Bearbeiten',
    ],
    'Delete' => [
      'de' => 'Löschen',
    ],
    'Operation' => [
      'de' => 'Arbeitsvorgang',
    ],
    'Drill' => [
      'de' => 'Bohren',
    ],
    'Drill & Ream' => [
      'de' => 'Bohren & Reiben',
    ],
    'Drill & Countersink' => [
      'de' => 'Bohren & Senken',
    ],
    'Ream Only' => [
      'de' => 'Nur Reiben',
    ],
    'Countersink Only' => [
      'de' => 'Nur Senken',
    ],
    'Other - Please Specify' => [
      'de' => 'Andere Anwendung - bitte spezifizieren',
    ],
    'Please specify...' => [
      'de' => 'Bitte spezifizieren...',
    ],
    'Please specify degrees...' => [
      'de' => 'Bitte spezifizieren Graden...',
    ],
    'Final Hole Size' => [
      'de' => 'Soll Lochdurchmesser',
    ],
    'Select Units' => [
      'de' => 'Auswahl Masseinheiten',
    ],
    'inches' => [
      'de' => 'Zoll',
    ],
    'mm' => [
      'de' => 'mm',
    ],
    'millimeters' => [
      'de' => 'Millimeter',
    ],
    'Final Hole Tolerance' => [
      'de' => 'Loch Toleranz',
    ],
    'Countersink Diameter' => [
      'de' => 'Senkungsdurchmesser',
    ],
    'Countersink Angle' => [
      'de' => 'Senkwinkel',
    ],
    'Predrilled Hole Size' => [
      'de' => 'Durchmesser Vorbohrloch',
    ],
    'Fixturing Being Used' => [
      'de' => 'Klemmvorrichtungen',
    ],
    'Twistlock' => [
      'de' => 'Twistlock',
    ],
    'Concentric Collet' => [
      'de' => 'Konzentrisches Spannfutter',
    ],
    'Template Foot' => [
      'de' => 'Bohrplatte',
    ],
    'C Clamp' => [
      'de' => 'C Clamp',
    ],
    'Vacuum Attachment' => [
      'de' => 'Absaugung',
    ],
    'Not Yet Specified' => [
      'de' => 'Noch nicht festgelegt',
    ],
    'Other/Not Yet Specified' => [
      'de' => 'Andere/Noch nicht festgelegt',
    ],
    'Number of Material Layers' => [
      'de' => 'Anzahl Materialschichten',
    ],
    'One' => [
      'de' => 'Eine',
    ],
    'Two' => [
      'de' => 'Zwei',
    ],
    'Three' => [
      'de' => 'Drei',
    ],
    'Four' => [
      'de' => 'Vier',
    ],
    'Five' => [
      'de' => 'Fünf',
    ],
    'Six' => [
      'de' => 'Sechs',
    ],
    'Seven' => [
      'de' => 'Sieben',
    ],
    'Eight' => [
      'de' => 'Acht',
    ],
    'Nine' => [
      'de' => 'Neun',
    ],
    'Ten' => [
      'de' => 'Zehn',
    ],
    'Layer' => [
      'de' => 'Schicht',
    ],
    'Layer Material' => [
      'de' => 'Material',
    ],
    'Aluminum' => [
      'de' => 'Aluminium',
    ],
    'Titanium' => [
      'de' => 'Titan',
    ],
    'Stainless Steel' => [
      'de' => 'Edelstahl',
    ],
    'CFRP/Carbon Fiber' => [
      'de' => 'CFRP/Carbon Fiber',
    ],
    'Layer Thickness' => [
      'de' => 'Schichtdicke',
    ],
    'Solution Type(s)*' => [
      'de' => 'Lösungsansätze*',
    ],
    'Please select as many as applicable.' => [
      'de' => 'Wählen Sie bitte mögliche Lösungen aus!',
    ],
    'Positive Feed' => [
      'de' => 'Automatischer Vorschub',
    ],
    'Air/Power Feed' => [
      'de' => 'Gesteuerter Vorschub',
    ],
    'Manual/Hand Drill' => [
      'de' => 'Manuell/Handgehaltene Bohrmaschine',
    ],
    'Other' => [
      'de' => 'Andere',
    ],
    'Handles' => [
      'de' => 'Griffe',
    ],
    'Application Orientation' => [
      'de' => 'Anwendungsrichtung',
    ],
    'Horizontal' => [
      'de' => 'Horizontal',
    ],
    'Vertical Up' => [
      'de' => 'Senkrecht von oben',
    ],
    'Vertical Down' => [
      'de' => 'Senkrecht von unten',
    ],
    'Unknown' => [
      'de' => 'Unbekannt',
    ],
    'Access to Application' => [
      'de' => 'Zugangsumgebung zur Anwendung',
    ],
    'Is there restricted access for the application?' => [
      'de' => 'Platzprobleme?',
    ],
    'Yes' => [
      'de' => 'Ja',
    ],
    'No' => [
      'de' => 'Nein',
    ],
    "Don't know" => [
      'de' => 'Ich weiß nicht',
    ],
    'Cutter' => [
      'de' => 'Bohrer',
    ],
    'Cutter Information' => [
      'de' => 'Bohrer Information',
    ],
    'Do you have a cutter you are using or plan to use?' => [
      'de' => 'Gibt es einen Bohrer für diese Anwendung oder planen Sie einen Bohrer?',
    ],
    'Fixture Being Used' => [
      'de' => 'Klemmvorrichtungen',
    ],
    'Add another application' => [
      'de' => 'Fügen Sie eine weitere Anwendung dazu',
    ],
    'Fixturing Information - Twistlock' => [
      'de' => 'Klemmvorrichtung Information - Twistlock',
    ],
    'Indexer for Attachment' => [
      'de' => 'Indexer für Zubehör',
    ],
    'Drill Bushing Series' => [
      'de' => 'Bohrbuchsen Serie',
    ],
    '21000 Series' => [
      'de' => 'Baureihe 21000',
    ],
    '22000 Series' => [
      'de' => 'Baureihe 22000',
    ],
    '23000 Series' => [
      'de' => 'Baureihe 23000',
    ],
    '24000 Series' => [
      'de' => 'Baureihe 24000',
    ],
    'Jig Mounting Hole Diameter' => [
      'de' => 'Bohrlochdurchmesser für die Vorrichtung',
    ],
    'Jig Thickness' => [
      'de' => 'Vorrichtung Materialdicke',
    ],
    'Chip Clearance' => [
      'de' => 'Spanabführung',
    ],
    'Add Ons/Accessories' => [
      'de' => 'weitere Zubehörteile',
    ],
    'Lubricator' => [
      'de' => 'Öler',
    ],
    'Cycle Counter' => [
      'de' => 'Zähler',
    ],
    'Chip Fragmentation' => [
      'de' => 'Spanbrechung',
    ],
    'Chip Vacuum' => [
      'de' => 'Spanabsaugung',
    ],
    'Fixturing Information - Concentric Collet' => [
      'de' => 'Klemmvorrichtung Information - Konzentrisches Spannfutter',
    ],
    'Indexer for Concentric Collet' => [
      'de' => 'Indexer für konzentrisches Spannfutter',
    ],
    'Fixturing Information - Template Foot' => [
      'de' => 'Klemmvorrichtung Information - Bohrplatte',
    ],
    'Template Strip Thickness' => [
      'de' => 'Schablonendicke',
    ],
    'Template Hole Diameter' => [
      'de' => 'Schablonenlochdurchmesser',
    ],
    'Fixturing Information - C Clamp' => [
      'de' => 'Klemmvorrichtung Information- C Clamp',
    ],
    'Access Information' => [
      'de' => 'Zugangsinformation',
    ],
    'Clearance Height H1' => [
      'de' => 'Freiraum Höhe H1',
    ],
    'Clearance Hight H2' => [
      'de' => 'Freiraum Höhe H2',
    ],
    'Side To center Dimension L1' => [
      'de' => 'Abstand Seite zum Center L1',
    ],
    'Reach L2' => [
      'de' => 'Reach L2',
    ],
    'Location Method' => [
      'de' => 'Fixierungsart',
    ],
    'Prehole' => [
      'de' => 'Vorbohrloch',
    ],
    'Prehole Size' => [
      'de' => 'Vorbohrlochdurchmesser ',
    ],
    'Pilot on Cutter' => [
      'de' => 'Bohrer mit Führungszapfen',
    ],
    'Location Pin on Clamp' => [
      'de' => 'Positionierstift an Klemmvorrichtung',
    ],
    'Template Strip' => [
      'de' => 'Schablonenstreifen',
    ],
    'Location Diameter' => [
      'de' => 'Einspanndurchmesser',
    ],
    'Locate on Top Clamp' => [
      'de' => 'Positionierung auf oberer Klemme',
    ],
    'Locate on Bottom Clamp' => [
      'de' => 'Positionierung auf unterer Klemme',
    ],
    'Cutter Information' => [
      'de' => 'Informationen zum Schneidwerkzeug',
    ],
    'Basic Cutter Data' => [
      'de' => 'Basisschneidwerkzeugdaten',
    ],
    'Cutter Type' => [
      'de' => 'Schneidwerkzeugart',
    ],
    'Drill Only' => [
      'de' => 'Nur Bohren',
    ],
    'Drill & Countersink' => [
      'de' => 'Bohren und Senken',
    ],
    'Select mounting type and supply cutter information on Drill & Countersink below.' => [
      'de' => 'Befestigungsart auswählen und Schneidwerkzeuginformationen zu Bohrer und Senker bereitstellen.',
    ],
    'Cutter Mounting Type' => [
      'de' => 'Art der Schneidwerkzeugbefestigung',
    ],
    'Straight Shank' => [
      'de' => 'Zylinderschaft',
    ],
    'External Thread with Seat Angle' => [
      'de' => 'Außengewinde mit Sitzwinkel',
    ],
    'External Thread with Pilot Diameter and Square Face' => [
      'de' => 'Außengewinde mit Zentrierdurchmesser und Vierkant',
    ],
    'External Thread with Pilot Diameter and Seat Angle' => [
      'de' => 'Außengewinde mit Zentrierdurchmesser und Sitzwinkel',
    ],
    'Internal Thread' => [
      'de' => 'Innendurchmesser',
    ],
    'Internal Thread Optional Counterbore Larger Body Diameter' => [
      'de' => 'Innendurchmesser',
    ],
    'Other ' => [
      'de' => 'Sonstige ',
    ],
    'Please provide fully dimensioned drawing.' => [
      'de' => 'Bitte eine Zeichnung mit vollständigen Maßangaben bereitstellen.',
    ],
    'Straight Shank' => [
      'de' => 'Zylinderschaft',
    ],
    'Shank Diameter (D1)' => [
      'de' => 'Schaftdurchmesser (D1)',
    ],
    'Cutter Diameter (D2)' => [
      'de' => 'Schneidwerkzeugdurchmesser (D2)',
    ],
    'Overall Length (L)' => [
      'de' => 'Gesamtlänge (L)',
    ],
    'External Thread with Seat Angle - B' => [
      'de' => 'Außengewinde mit Sitzwinkel - B',
    ],
    'Thread (T)' => [
      'de' => 'Gewinde (T)',
    ],
    'Thread Length (C)' => [
      'de' => 'Gewindelänge (C)',
    ],
    'Seat Angle (A)' => [
      'de' => 'Sitzwinkel (A)',
    ],
    'Body Diameter (D1)' => [
      'de' => 'Körperdurchmesser (D1)',
    ],
    'Body Length (L1)' => [
      'de' => 'Körperlänge (L1)',
    ],
    'Cutter Diameter (D2)' => [
      'de' => 'Schneidwerkzeugdurchmesser (D2)',
    ],
    'Overall Length (L)' => [
      'de' => 'Gesamtlänge (L)',
    ],
    'External Thread with Pilot Diameter and Square Face - C' => [
      'de' => 'Außengewinde mit Zentrierdurchmesser und Vierkant - C',
    ],
    'Pilot Diameter (D3)' => [
      'de' => 'Zentrierdurchmesser (D3)',
    ],
    'Pilot Length (L2)' => [
      'de' => 'Länge Zentrierung (L2)',
    ],
    'External Thread with Pilot Diameter and Seat Angle - D' => [
      'de' => 'Außengewinde mit Zentrierdurchmesser und Sitzwinkel - D',
    ],
    'Degrees' => [
      'de' => 'Grad',
    ],
    'Internal Thread - E' => [
      'de' => 'Innengewinde - E',
    ],
    'Internal Thread (T)' => [
      'de' => 'Innengewinde (T)',
    ],
    'Thread Length (C)' => [
      'de' => 'Gewindelänge (C)',
    ],
    'Diameter (D3)' => [
      'de' => 'Durchmesser (D3)',
    ],
    'Length (L2)' => [
      'de' => 'Länge (L2)',
    ],
    'Counterbore Diameter (CB)' => [
      'de' => 'Senkungsdurchmesser (CB)',
    ],
    'Counterbore Depth (C1)' => [
      'de' => 'Senkbohrtiefe (C1)',
    ],
    'Length (L3)' => [
      'de' => 'Länge (L3)',
    ],
    'Drill & Counterink Information' => [
      'de' => 'Bohrer- und Senkerinformationen',
    ],
    'Drill Length (LC)' => [
      'de' => 'Bohrerlänge (LC)',
    ],
    'Drill Diameter (DC)' => [
      'de' => 'Bohrerdurchmesser (DC)',
    ],
    'Countersink Body Diameter (D2)' => [
      'de' => 'Senkerkörperdurchmesser (D2)',
    ],
    'Countersink Angle (A)' => [
      'de' => 'Senkwinkel',
    ],
    'Accessories' => [
      'de' => 'Zubehör',
    ],
    'Solution Issues' => [
      'de' => 'Lösungsansätze',
    ],
    'Rank in Order of Importance:' => [
      'de' => 'Rang in der Reihenfolge der Bedeutung:',
    ],
    'Please drag to sort solution issues below to rank in order of importance:' => [
      'de' => 'Rang in der Reihenfolge der Bedeutung:',
    ],
    'Hole Quality' => [
      'de' => 'Lochqualität',
    ],
    'Ergonomics' => [
      'de' => 'Ergonomie',
    ],
    'Drill Cycle Time' => [
      'de' => 'Bohrzykluszeit',
    ],
    'Overall Productivity' => [
      'de' => 'Gesamtproduktivität',
    ],
    'Error Proofing' => [
      'de' => 'der Schraubabläufe',
    ],
    'Please describe your current process and provide any additional information on your needs.' => [
      'de' => 'Bitte beschreiben Sie Ihren aktuellen Prozess und stellen Sie alle relevanten zusätzlichen Informationen zu Ihren Anforderungen bereit.',
    ],
    'Submit' => [
      'de' => 'Abschicken',
    ],
    'Complete Request' => [
      'de' => 'Anfrage abschließen',
    ],
    'Thank you for your interest in Advanced Drilling Equipment from Cleco Tools.' => [
      'de' => 'Vielen Dank für Ihr Interesse an den hochwertigen Bohrgeräten von Cleco Tools.',
    ],
    'We have received your submissions and a sales representative will be reviewing your needs. We will contact you shortly to revview and discuss next steps in creative your Cleco Tool Solution. In the meantime, you can download your results or forward on to a colleague.' => [
      'de' => 'Wir haben Ihre Antworten erhalten und ein Vertriebsmitarbeiter wird sich mit Ihren Anforderungen beschäftigen. Wir werden in Kürze Kontakt mit Ihnen aufnehmen, um die nächsten Schritte für die Konfiguration Ihrer Cleco Tool Lösung zu besprechen. In der Zwischenzeit können Sie Ihre Ergebnisse herunterladen und an einen Kollegen weiterleiten.',
    ],
    'Download Results' => [
      'de' => 'Ergebnisse herunterladen',
    ],
    'Start a New Request' => [
      'de' => 'Starten Sie eine neue Anfrage',
    ],
    'Email Results' => [
      'de' => 'Ergebnisse per E-Mail versenden',
    ],
    "Colleague's Email Address" => [
      'de' => 'E-Mail-Adresse Ihres Kollegen',
    ],
    'Brief Message' => [
      'de' => 'Kurze Nachricht',
    ],
    'Forward Results' => [
      'de' => 'Ergebnisse weiterleiten',
    ],
  ],
];

if (defined('ADE_TRANSLATE') && ADE_TRANSLATE === true) {
  return $translations;
} else {
  header('Content-Type: application/json');

  echo json_encode($translations, true);
}
