<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class LangNL {
        private static $dutch_instance = null , $media_instance;

        public static function getInstance() {
                if (LangNL::$dutch_instance == null) {
                        LangNL::$dutch_instance = new LangNL;
                        return LangNL::$dutch_instance;
                }
                return LangNL::$dutch_instance;
        }

        public static function contents(){
                $response = array('ImportUpdate' => 'Importeren / bijwerken',
                        'SelectAllImages' => 'Selecteer alle afbeeldingen in uw importbestand om importfouten te voorkomen',
                        'ChooseImagesToImport' => 'Kies de afbeeldingen om te importeren. Gebruik het onderstaande selectievakje om alle afbeeldingen te selecteren.',
                        'FileName' => 'Bestandsnaam:',
                        'OK' => 'OK',
                        'Dashboard' => 'Dashboard',
                        'Manager' => 'Manager',
                        'Exportfiltereddata' => 'Exporteer gefilterde gegevens',
                        'Exportfiltereddatadesc' => 'Hiermee krijgt u alleen de vereiste gegevens met de verschillende geavanceerde filters',
                        'Backupineditableformat' => 'Back-up in bewerkbaar formaat',
                        'Backupineditableformatdesc' => 'Back-up in 4 verschillende bestandsformaten zoals CSV, XML, JSON,XLS.',
                        'AutoScheduledBackups' => 'Automatisch geplande back-ups',
                        'AutoScheduledBackupsdesc' => 'Geplande export helpt bij het maken van back-ups als bewerkbare tekstbestandsindeling met regelmatige tussenpozen.',
                        'Export' => 'Exporteren',
                        'GetSupport' => 'KRIJG ONDERSTEUNING',
                        'SampleCSVXML' => 'Voorbeeld CSV en XML',
                        'Getsample' => 'Ontvang een voorbeeld- en voorbeeldbestand',
                        'TechnicalDocumentation' => 'Technische documentatie',
                        'Learnfrom' => 'Leer van onze blogposts',
                        'Settings' => 'Instellingen',
                        'Support' => 'Ondersteuning',
                        'UploadfromDesktop' => 'Uploaden vanaf desktop',
                        'UploadfromFTPSFTP' => 'Uploaden vanaf FTP / SFTP',
                        'UploadfromURL' => 'Upload vanaf URL',
                        'ChoosFileintheServer' => 'Kies Bestand op de server',
                        'Drag&Dropyourfilesor' => 'Sleep uw bestanden of',
                        'Browse' => 'Bladeren',
                        'NewItem' => 'Nieuw item',
                        'Import' => 'Importeren',
                        'Update' => 'Bijwerken',
                        'Import-Update' => 'Import-Update',
                        'ExistingItems' => 'Bestaande items',
                        'ImportEachRecordAs'=> 'Importeer elk record als',
                        'Continue' => 'Doorgaan met',
                        'Search' => 'Zoeken',
                        'FromDate' => 'Van datum',
                        'ToDate' => 'Uitgaan',
                        'SEARCH' => 'ZOEKEN',
                        'SavedTemplate' => 'Opgeslagen sjabloon',
                        'TEMPLATES' => 'SJABLONEN',
                        'MATCHEDCOLUMNSCOUNT' => 'OVEREENKOMENDE KOLOMMEN TELLEN',
                        'MODULE' => 'MODULE',
                        'CREATEDTIME' => 'GEMAAKTE TIJD',
                        'ACTION' => 'ACTIE',
                        'USETEMPLATE' => 'GEBRUIK SJABLOON',
                        'CREATENEWMAPPING' => 'MAAK NIEUWE MAPPING',
                        'BACK' => 'TERUG',
                        'SIMPLEMODE' => 'SIMPELE MODUS',
                        'ADVANCEDMODE' => 'GEAVANCEERDE MODUS',
                        'DRAGDROPMODE' => 'SLEPEN & DALEN MODUS',
                        'WordpressFields' => 'Wordpress Fields',
                        'WPFIELDS' => 'WP-velden',
                        'CSVHEADER' => 'CSV-koptekst',
                        'Action' => 'Actie',
                        'Name' => 'Naam',
                        'HINT' => 'HINT',
                        'Example' => 'Voorbeeld',
                        'WordPressCoreFields' => 'WordPress kernvelden',
                        'ACFFreeFields' => 'ACF vrije velden',
                        'ACFFields' => 'ACF-velden',
                        'ACFImageMetaFields' => 'ACF afbeelding metavelden',
                        'ACFGroupFields' => 'ACF-groepsvelden',
                        'ACFProFields' => 'ACF Pro-velden',
                        'ACFRepeaterFields' => 'ACF-repeatervelden',
                        'ACFFlexibleFields' => 'ACF flexibele velden',
                        'TypesCustomFields' => 'Typen aangepaste velden',
                        'TypesImageMetaFields' => 'Typen afbeelding metavelden',
                        'PodsFields' => 'Pods Velden',
                        'JobListingFields' => 'Vacaturelijstvelden',
                        'PodsImageMetaFields' => 'Pods Image Meta Fields',
                        'CustomFieldSuite' => 'Aangepaste veldsuite',
                        'AllInOneSeoFields' => 'Alles in één SEO-velden',
                        'MetaBoxFields' => 'MetaBox-velden',
                        'YoastSeoFields' => 'Yoast Seo Fields',
                        'WPMLFields' => 'WPML-velden',
                        'RankMathFields'=>'Rank Math Fields',
                        'RankMathProFields'=>'Rank Math Pro Fields',
                        'BillingAndShippingInformation' => 'Factuur- en verzendinformatie',
                        'CustomFieldsWPMemberFields' => 'Aangepaste velden WP-ledenvelden',
                        'CustomFieldsMemberFields' => 'Aangepaste velden Ledenvelden',
                        'ProductMetaFields' => 'Product-metavelden',
                        'ProductAttrFields' => 'Productattr-velden',
                        'ProductBundleMetaFields' => 'Productbundel metavelden',
                        'ProductImageMetaFields' => 'Productgalerij metavelden',
                        'OrderMetaFields' => 'Bestel metavelden',
                        'CouponMetaFields' => 'Coupon-metavelden',
                        'RefundMetaFields' => 'Metavelden voor terugbetaling',
                        'WPECommerceCustomFields' => 'WP e-commerce aangepaste velden',
                        'EventsManagerFields' => 'Velden van Events Manager',
                        'NextGENGalleryFields' => 'NextGEN Gallery Fields',
                        'WPMLFields' => 'WPML-velden',
                        'CMB2CustomFields' => 'CMB2 aangepaste velden',
                        'JetEngineFields' => 'Straalmotor Velden',
                        'JetEngineRFFields' => 'Jet Engine Repeater-velden',
                        'JetEngineCPTFields' => 'Straalmotor CPT-velden',
                        'jetEngineCPTRFFields' => 'Straalmotor CPT-repeatervelden',
                        'JetEngineCCTFields' => 'Straalmotor CCT-velden',
                        'JetEngineCCTRFFields' => 'Straalmotor CCT RF-velden',
                        'jetEngineTaxonomyFields' => 'Taxonomievelden voor straalmotoren',
                        'jetEngineTaxonomyRFFields' => 'Jet Engine Taxonomy Repeater-velden',
                        'jetEngineRelationsFields' => 'Gebied Jet Engine Relations',                        
                        'CourseSettingsFields' => 'Velden met cursusinstellingen',
                        'CurriculumSettingsFields' => 'Velden voor curriculuminstellingen',
                        'QuizSettingsFields' => 'Velden met quizinstellingen',
                        'LessonSettingsFields' => 'Lesinstellingen velden',
                        'QuestionSettingsFields' => 'Velden met vraaginstellingen',
                        'OrderSettingsFields' => 'Velden met orderinstellingen',
                        'replyattributesfields' => 'Velden voor antwoordattributen',
                        'forumattributesfields' => 'Velden voor forumkenmerken',
                        'topicattributesfields' => 'Velden met onderwerpkenmerken',
                        'polylangfields'=>'Polylang-instellingenvelden',
                        'WordPressCustomFields' => 'WordPress aangepaste velden',
                        'DirectoryProFields' => 'Directory Pro-velden',
                        'TermsandTaxonomies' => 'Termen en taxonomieën',
                        'IsSerialized' => 'Is geserialiseerd',
                        'NoCustomFieldsFound' => 'Geen aangepaste velden gevonden', 
                        'MediaUploadFields' => 'Media upload velden',
                        'UploadMedia' => 'Upload Media',
                        'UploadedListofFiles' => 'Geüploade lijst met bestanden',
                        'UploadedMediaFileLists' => 'Lijsten met geüploade mediabestanden',
                        'SavethismappingasTemplate' => 'Sla deze toewijzing op als sjabloon',
                        'Save' => 'Opslaan',
                        'Doyouneedtoupdatethecurrentmapping' => 'Moet u de huidige mapping bijwerken?',
                        'Savethecurrentmappingasnewtemplate' => 'Sla de huidige toewijzing op als een nieuwe sjabloon',
                        'Back' => 'Terug',
                        'Size' => 'Grootte',
                        'MediaHandling' => 'Uitgelichte afbeelding mediaverwerking', 
                        'Downloadexternalimagestoyourmedia' => 'Download externe afbeeldingen naar uw media',
                        'ImageHandling' => 'Omgaan met afbeeldingen',
                        'Usemediaimagesifalreadyavailable' => 'Gebruik mediabeelden als deze al beschikbaar zijn',
                        'Doyouwanttooverwritetheexistingimages' => 'Wilt u de bestaande afbeeldingen overschrijven?',
                        'ImageSizes' => 'Afbeeldingsformaten',
                        'Thumbnail' => 'Thumbnail',
                        'Medium' => 'Medium',
                        'MediumLarge' => 'Medium Large',
                        'Large' => 'Groot',
                        'Custom' => 'Op maat',
                        'Slug' => 'Naaktslak',
                        'Width' => 'Breedte',
                        'Height' => 'Hoogte',
                        'Addcustomsizes' => 'Voeg aangepaste formaten toe',
                        'PostContentImageOption' => 'Post Content Image Option',
                        'DownloadPostContentExternalImagestoMedia' => 'Download postinhoud externe afbeeldingen naar media',
                        'MediaSEOAdvancedOptions' => 'Media SEO en geavanceerde opties',
                        'SetimageTitle' => 'Stel afbeeldingstitel in',
                        'SetimageCaption' => 'Stel afbeelding bijschrift in',
                        'SetimageAltText' => 'Stel afbeelding alternatieve tekst in',
                        'SetimageDescription' => 'Stel afbeelding Beschrijving in',
                        'Changeimagefilenameto' => 'Wijzig de naam van het afbeeldingsbestand in',
                        'ImportconfigurationSection' => 'Import configuratie sectie',
                        'EnablesafeprestateRollback' => 'Schakel veilige prestate Rollback in',
                        'Backupbeforeimport' => 'Maak een back-up voor het importeren',
                        'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'Wilt u tijdens het importeren de onderhoudsmodus INSCHAKELEN',
                        'Doyouwanttohandletheduplicateonexistingrecords' => 'Wilt u het duplicaat op bestaande records afhandelen',
                        'Mentionthefieldswhichyouwanttohandleduplicates' => 'Noem de velden die u duplicaten wilt behandelen',
                        'DoyouwanttoUpdateanexistingrecords' => 'Wilt u bestaande records bijwerken',
                        'Updaterecordsbasedon' => 'Update records op basis van',
                        'DoyouwanttoSchedulethisImport' => 'Wilt u deze import plannen?',
                        'ScheduleDate' => 'Geplande datum',
                        'ScheduleFrequency' => 'Plan frequentie',
                        'TimeZone' => 'Tijdzone',
                        'ScheduleTime' => 'Plan tijd',
                        'Schedule' => 'Schema',
                        'Scheduled' => 'Gepland',
                        'Import' => 'Importeren',
                        'Format' => 'Formaat',
                        'OneTime' => 'Een keer',
                        'Daily' => 'Dagelijks',
                        'Weekly' => 'Wekelijks',
                        'Monthly' => 'Maandelijks',
                        'Hourly' => 'Elk uur',
                        'Every4hours' => 'Elke 4 uur',
                        'Every2hours' => 'Elke 2 uur',
                        'Every30mins'=> 'Elke 30 min',
                        'Every15mins' => 'Elke 15 minuten',
                        'Every10mins' => 'Elke 10 minuten',
                        'Every5mins' => 'Elke 5 minuten',
                        'FileName' => 'Bestandsnaam',
                        'FileSize' => 'Bestandsgrootte',
                        'Process' => 'Werkwijze',
                        'Totalnoofrecords' => 'Totaal aantal records',
                        'CurrentProcessingRecord' => 'Huidig ​​verwerkingsrecord',
                        'RemainingRecord' => 'Resterend record',
                        'Completed' => 'Voltooid',
                        'TimeElapsed' => 'Verstreken tijd',
                        'approximate' => 'bij benadering',
                        'DownloadLog' => 'Bekijk Log',
                        'NoRecord' => 'Geen record',
                        'UploadedCSVFileLists' => 'Geüploade CSV-bestandslijsten',
                        'Hostname' => 'Hostnaam',
                        'HostPort' => 'Host-poort',
                        'HostUsername' => 'Host gebruikersnaam',
                        'HostPassword' => 'Hostwachtwoord',
                        'HostPath' => 'Hostpad',
                        'DefaultPort' => 'Standaardpoort',
                        'FTPUsername' => 'FTP-gebruikersnaam',
                        'FTPPassword' => 'FTP-wachtwoord',
                        'ConnectionType' => 'Connectie type',
                        'ImportersActivity' => 'Importeurs Activiteit',
                        'ImportStatistics' => 'Statistieken importeren',
                        'FileManager' => 'Bestandsbeheer',
                        'SmartSchedule' => 'Slimme planning',
                        'ScheduledExport' => 'Geplande export',
                        'Templates' => 'Sjablonen',
                        'LogManager' => 'Log Manager',
                        'NotSelectedAnyTab' => 'Niet geselecteerd Elk tabblad',
                        'EventInfo' => 'Evenement Info',
                        'EventDate' => 'Datum evenement',
                        'EventStatus' => 'Gebeurtenisstatus',
                        'Actions' => 'Acties',
                        'Date' => 'Datum',
                        'Purpose' => 'Doel',
                        'Revision' => 'Herziening',
                        'Select' => 'Selecteer',
                        'Inserted' => 'Ingevoegd',
                        'Updated' => 'Bijgewerkt',
                        'Skipped' => 'Overgeslagen',
                        'Delete' => 'Verwijderen',
                        'Noeventsfound' => 'Geen evenementen gevonden',
                        'ScheduleInfo' => 'Info plannen',
                        'ScheduledDate' => 'Geplande datum',
                        'ScheduledTime' => 'Geplande tijd',
                        'Youhavenotscheduledanyevent' => 'Je hebt geen evenement gepland',
                        'Frequency' => 'Frequentie',
                        'Time' => 'Tijd',
                        'EditSchedule' => 'Bewerk schema',
                        'SaveChanges' => 'Wijzigingen opslaan',
                        'TemplateInfo' => 'Sjablooninformatie',
                        'TemplateName' => 'Sjabloonnaam',
                        'Module' => 'Module',
                        'CreatedTime' => 'Gemaakt tijd',
                        'NoTemplateFound' => 'Geen sjabloon gevonden',
                        'Download' => 'Downloaden',
                        'NoLogRecordFound' => 'Geen logboekrecord gevonden',
                        'GeneralSettings' => 'Algemene instellingen',
                        'DatabaseOptimization' => 'Database-optimalisatie',
                        'Media' =>'Media',
                        'AccessKey' => 'Toegangssleutel',
                        'SecurityandPerformance' => 'Beveiliging en prestaties',
                        'Documentation' => 'Documentatie',
                        'MediaReport' => 'Mediarapport',
                        'DropTable' => 'Laat de tafel vallen',
                        'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'Als geactiveerde deactivering van plug-ins de plug-ingegevens zal verwijderen, dit kan niet worden hersteld.',
                        'Scheduledlogmails' => 'Geplande log-mails',
                        'Enabletogetscheduledlogmails' => 'Schakel in om geplande log-mails te ontvangen.',
                        'Sendpasswordtouser' => 'Stuur wachtwoord naar gebruiker',
                        'Enabletosendpasswordinformationthroughemail' => 'Schakel in om wachtwoordinformatie via e-mail te verzenden.',
                        'WoocommerceCustomattribute' => 'Woocommerce Aangepast kenmerk',
                        'Enablestoregisterwoocommercecustomattribute' => 'Maakt het mogelijk om woocommerce aangepast kenmerk te registreren.',
                        'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'Zorg ervoor dat u de nodige back-up maakt voordat u doorgaat met database-optimalisatie. De verloren gegevens kunnen niet worden teruggedraaid.',
                        'DeleteallorphanedPostPageMeta' => 'Verwijder alle verweesde post / pagina-meta',
                        'Deleteallunassignedtags' => 'Verwijder alle niet-toegewezen tags',
                        'DeleteallPostPagerevisions' => 'Verwijder alle post- / paginarevisies',
                        'DeleteallautodraftedPostPage' => "Verwijder alle automatisch opgestelde berichten / pagina's",
                        'DeleteallPostPageintrash' => "Verwijder alle berichten / pagina's in de prullenbak",
                        'DeleteallCommentsintrash' => 'Verwijder alle reacties in de prullenbak',
                        'DeleteallUnapprovedComments' => 'Verwijder alle niet-goedgekeurde opmerkingen',
                        'DeleteallPingbackComments' => 'Verwijder alle Pingback-opmerkingen',
                        'DeleteallTrackbackComments' => 'Verwijder alle Trackback-opmerkingen',
                        'DeleteallSpamComments' => 'Verwijder alle spamreacties',
                        'RunDBOptimizer' => 'Voer DB Optimizer uit',
                        'DatabaseOptimizationLog' => 'Database-optimalisatielogboek',
                        'noofOrphanedPostPagemetahasbeenremoved' => 'geen van de meta van weespost / pagina is verwijderd.',
                        'noofUnassignedtagshasbeenremoved' => 'geen van de niet-toegewezen tags is verwijderd.',
                        'noofPostPagerevisionhasbeenremoved' => 'geen van de post- / paginarevisies is verwijderd.',
                        'noofAutodraftedPostPagehasbeenremoved' => 'geen van de automatisch opgestelde post / pagina is verwijderd.',
                        'noofPostPageintrashhasbeenremoved' => "geen van de berichten / pagina's in de prullenbak is verwijderd.",
                        'noofSpamcommentshasbeenremoved' => 'geen van de spamreacties is verwijderd.',
                        'noofCommentsintrashhasbeenremoved' => 'Geen van de reacties in de prullenbak is verwijderd.',
                        'noofUnapprovedcommentshasbeenremoved' => 'geen van de niet-goedgekeurde opmerkingen is verwijderd.',
                        'noofPingbackcommentshasbeenremoved' => 'geen van de Pingback-opmerkingen is verwijderd.',
                        'noofTrackbackcommentshasbeenremoved' => 'geen van de Trackback-opmerkingen is verwijderd.',
                        'Allowauthorseditorstoimport' => 'Laat auteurs / editors importeren',
                        'Allowauthorseditorstoimport' => 'Laat auteurs / editors importeren',
                        'Thisenablesauthorseditorstoimport' => 'Hierdoor kunnen auteurs / editors importeren.',
                        'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'Minimaal vereiste php.ini-waarden (Ini geconfigureerde waarden)',
                        'Variables' => 'Variabelen',
                        'SystemValues' => 'Systeemwaarden',
                        'MinimumRequirements' => 'Minimale vereisten',
                        'RequiredtoenabledisableLoadersExtentionsandmodules' => 'Vereist om laders, uitbreidingen en modules in / uit te schakelen:',
                        'DebugInformation' => 'Foutopsporingsinformatie:',
                        'SmackcodersGuidelines' => 'Richtlijnen voor Smackcoders',
                        'DevelopmentNews' => 'Ontwikkelingsnieuws',
                        'WhatsNew' => 'Wat is er nieuw?',
                        'YoutubeChannel' => 'Youtube kanaal',
                        'OtherWordPressPlugins' => 'Andere WordPress-plug-ins',
                        'Count' => 'Tellen',
                        'toaform' => '
                        om het forum te steunen',
                        'subcribe' => 'Abonneren',
                        'Createasupport' => 'Maak hier een ondersteuningsonderwerp aan voor hulp',
                        'backupineditableformat' => 'back-up in bewerkbaar formaat',
                        'Buynow' => 'Nu kopen!',
                        'CreateTopic' => 'onderwerp maken',
                        'DeletedatafromWordPress' => 'Verwijder WordPress-gegevens',
                        'Addons' => 'toevoegingen',
                        'Exportfiltereddata' => 'Exporteer gefilterde gegevens',
                        'Found' => 'gevonden',
                        'AutoScheduledBackups' => 'Automatisch geplande back-ups',
                        'Letsyougetonlyrequireddatawiththedifferentadvancedfilters' => 'Hiermee kunt u alleen de vereiste gegevens verkrijgen met de verschillende geavanceerde filters',
                        'UltimateExporterPro' => 'Ultieme exporteur Pro',
                        'Gotoaddons'=>'ga naar plug-ins',
                        'Scheduledexporthelpsbackupaseditabletextfileformat' => 'Geplande export ondersteunt als bewerkbare tekstbestandsindeling
                        met regelmatige tussenpozen.',
                        'EnabletodeletetheitemsnotpresentinCSVXMLfile' => 'Schakel in om de elementen te verwijderen die niet aanwezig zijn in het CSV/XML-bestand',
                        'Backupin4differentfileformatslikeCSV' => 'Back-up in 4 verschillende bestandsindelingen zoals CSV, XML, JSON, XLS.',   
                        'CustomerReviews' => 'klanten feedback',
                        'Comments' =>'Opmerkingen',
                        'WooCommerceProducts' => 'WooCommerce-producten',
                        'Posts' => 'publicaties',
                        'PostCategories' => 'Categorieën posten',
                        'Posttags' => 'Postlabels',
                        'WooCommerceRefunds' => 'Woo-Commerce-restituties',
                        'Pages' => 'Pagina',
                        'ExportfeatureisdisabledInstallandactivatetheaddonpluginfirst'=>'Waarschuwing: de exportfunctie is uitgeschakeld. Installeer en activeer eerst de extra plug-i',
                        'Continue' => 'Doorgaan',
                        'WooCommerceCoupons' => 'WooCommerce-coupons',
                        'WooCommerceOrders' => 'Woo Commerce-varianten',
                        'CustomPosts' => 'aangepaste berichten',
                        'ImageType' => 'Beeldtype',
                        'Status' => 'Toestand',
                        'Loading' => 'Bezig met laden',
                        'Exporterwithadvancedfilters' => 'Exporteur met geavanceerde filters',
                        'AutoSchedulewithreusabletemplates' => 'Automatisch plannen met herbruikbare sjablonen',
                        'LoveWPUltimateCSVImporterGivea5starreviewon' => 'Love WP Ultimate CSV Importer, Geef een 5-sterrenrecensie op',
                        'ContactSupport' => 'Contact opnemen met ondersteuning',
                        'Email' => 'E-mail',
                        'WooCommerceVariations' => 'Woo Commerce-variaties',
                        'Supporttype' => 'Type ondersteuning',
                        'BugReporting' => 'Bugrapportage',
                        'FeatureEnhancement' => 'Functieverbetering',
                        'Message' => 'Bericht',
                        'Send' => 'Sturen',
                        'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'SEO Plugin Data Importer: RankMath, Yoast en SEO in één',
                        'WPMLImporter' => 'WPML-importeur',
                        'AIOWooCommerceImportSuit' => 'AIO WooCommerce-importpak',
                        'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter' => 'JetEngine, Metabox, Toolset Types, ACF pro / Free en Pods Field/Pos Plugin Importer',
                        'UltimateCSVImporterPro' => 'UltimateCSVImporterPro',
                        'Updateolderpostsfromsingleimport' => 'Update oudere berichten vanuit een enkele import',
                        'NewsletterSubscription' => 'Nieuwsbrief abonnement',
                        'Subscribe' => 'Abonneren',
                        'Note' => 'Notitie',
                        'csv_importlink' => 'Klik hier',
                        'installactivate' => 'om nu te installeren en te activeren',
                        'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'Abonneer je op Smackcoders mailinglijst (een paar berichten per jaar)',
                        'Pleasedraftamailto' => 'Stel een e-mail op naar',
                        'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'Als u binnen een uur geen bevestiging krijgt!',
                        'Selectyourmoduletoexportthedata' => 'Selecteer uw module om gegevens te exporteren',
                        'Toexportdatabasedonthefilters' => 'Om gegevens te exporteren op basis van de filters',
                        'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'Waarschuwing: sommige plug-ins ontbreken, aanbevolen',
                        'ExportFileName' => 'Export bestandsnaam',
                        'AdvancedSettings' => 'Geavanceerde instellingen',
                        'ExportType' => 'Exporttype',
                        'SplittheRecord' => 'Splits het record',
                        'AdvancedFilters'=> 'Geavanceerde filters',
                        'Exportdatawithautodelimiters' => 'Exporteer gegevens met automatische scheidingstekens',
                        'Delimiters' => 'Scheidingstekens',
                        'OtherDelimiters' => 'Andere scheidingstekens',
                        'Exportdataforthespecificperiod' => 'Exporteer gegevens voor de specifieke periode',
                        'StartFrom' => 'Start van',
                        'EndTo' => 'Einde aan',
                        'Exportdatawiththespecificstatus' => 'Exporteer gegevens met de specifieke status',
                        'All' => 'Alle',
                        'Publish' => 'Publiceren',
                        'Sticky' => 'Kleverig',
                        'Private' => 'Privaat',
                        'Protected' => 'Beschermd',
                        'Draft' => 'Droogte',
                        'Pending' => 'In afwachting',
                        'Exportdatabyspecificauthors' => 'Exporteer gegevens door specifieke auteurs',
                        'Authors' => 'Auteurs',
                        'Exportdatabyspecificcategory' => 'Exporteer gegevens per specifieke categorie',
                        'Category' => 'Categorie',
                        'ExportdatabasedonspecificInclusions' => 'Exporteer gegevens op basis van specifieke insluitsels',
                        'DoyouwanttoSchedulethisExport' => 'Wilt u deze export plannen?',
                        'SelectTimeZone' => 'Selecteer TimeZone',
                        'ScheduleExport' => 'Export plannen',
                        'DataExported' => 'Gegevens geëxporteerd',
                        'FilePath' => 'Bestandspad',
                        'UltimateCSVImporterPro' => 'Ultieme CSV-importeur Pro',
			'loginfo' => 'log informatie',
			'ContactusforPresaleEnquiry' => 'Neem contact met ons op voor een voorverkooponderzoek',
			'PremiumVersion' => 'Premium-versie',
			'Thisfeatureisavailablein' => 'Deze functie is beschikbaar in',
			'WPUltimateCSVImporter' => 'WP Ultimate CSV-importeur',
			'SampleCSV' => 'Voorbeeld CSV',
			'Poweredby' => 'Aangedreven door',
			'AlreadyInstalled' => 'Al geïnstalleerd',
			'importwoocommerce' => 'woocommerce importeren',
			'ImportanybulkWooCommerceProductsdatainCSV' => 'Importeer bulkgegevens van WooCommerce-producten in CSV.',
			'Highlights' => 'Hoogtepunten',
			'ProductTypessimplegroupedvariableexternaltypeimport' => 'Producttypen eenvoudig, gegroepeerd, variabel, extern type importeren.',
			'FeaturedProductImportfromURL' => 'Uitgelichte productimport van URL',
			'Galleryimageimport' => 'Galerij afbeelding importeren',
			'Duplicatedetection' => 'Dubbele detectie',
			'FileType' => 'Bestandstype',
			'SupportsUTF_8CSVfile' => 'Ondersteunt UTF-8 CSV-bestanden',
			'Install' => 'Installeren',
			'ImportUsers' => 'Gebruikers importeren',
			'ImportUserinfointoWordPressinbulk' => 'Importeer gebruikersgegevens in bulk in WordPress',
			'WPMembersaddonsupport' => 'WP Members add-on ondersteuning',
			'Defaultcustomfieldsimport' => 'Standaard aangepaste velden importeren',
			'Sendsautomatedpasswordnotificationemailoptional' => 'Stuurt geautomatiseerde e-mail met wachtwoordmelding (optioneel)',
			'WPUltimateExporter' => 'WP Ultimate-exporteur',
			'ExportallyourWordPressdataasCSVfileforbackup' => 'Exporteer al uw WordPress-gegevens als CSV-bestand voor back-up',
			'Supportsdefaultcustomfields' => 'Ondersteunt standaard aangepaste velden',
			'UTF8encodedCSVfile' => 'UTF-8 gecodeerd CSV-bestand',
			'SupportPostPageCustomPost' => 'Ondersteuningsbericht, pagina en aangepast bericht',
			'Filteredexportbasedonperiodoftimeauthors' => 'Gefilterde export op basis van tijdsperiode & auteurs',
			'Addons' => 'Add-ons',
			'Posts' => 'Berichten',
			'CustomPosts' => 'Aangepaste berichten',
			'PostTags' => 'Labels plaatsen',
			'PostCategories' => 'Categorieën posten',
			'Users' => 'Gebruikers',
			'Taxonomies' => 'Taxonomieën',
			'Comments' => 'Opmerkingen',
			'CustomerReviews' => 'klanten-reviews',
			'WooCommerceCoupons' => 'WooCommerce-coupons',
			'WooCommerceRefunds' => 'WooCommerce-restituties',
			'WooCommerceVariations' => 'WooCommerce-variaties',
			'Found' => 'Gevonden',
			'CreateTopic' => 'Onderwerp maken',
			'Createasupport' => 'Maak hier een ondersteuningsonderwerp aan voor hulp',
			'Learnfrom' => 'Leer van onze blogposts',
			'TechnicalDocumentation' => 'Technische documentatie',
			'Getsampleandexamplefiles' => 'Ontvang voorbeeld- en voorbeeldbestanden',
			'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'Installeer de Ultimate Exporter om al uw WordPress-gegevens als CSV te exporteren',
			'Clickheretoinstall' => 'Klik hier om te installeren',
			'poweredBy' => 'aangedreven door',
			'Hire_us' => 'Huur ons in',
			'GetSupport' => 'Krijg ondersteuning',
			'SampleCSVXML' => 'Voorbeeld van CSV en XML',
			'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'Waarschuwing: sommige add-ons ontbreken, het wordt aanbevolen om dit te doen',
			'DragDropyourfilesor' => 'Drag & Drop uw bestanden of',
                        'WPCompleteFields' => 'WPVolledige velden',
                        'ChooseUploadMethod' => 'Kies uploadmethode',
                        'Media' => 'Media',
                        'CsvUploadFields' => 'Bestand uploaden',
                        'Device' => 'Apparaat',
                        'Remote' => 'Afstand',
                        'SelectDeviceZIPfile' => 'Selecteer Apparaat om afbeeldingen direct vanaf uw apparaat als een ZIP-bestand te uploaden.',
                        'SelectDeviceCSVfile' => 'Kies Afstand om afbeeldingen te importeren van URL\'s van externe websites.',
                        'MediaContinue' => 'Doorgaan',
                        'FreshImport' => 'Nieuwe import',
                        'UpdateContent' => 'Inhoud bijwerken',
                        'UpdateThisMappingAs' => 'Deze mapping bijwerken als',
                        'Overwritetheavailableimages' => 'Overschrijf de beschikbare afbeeldingen',
                        'AlwaysCreateAsNewImage' => 'Altijd als nieuwe afbeelding aanmaken',
                        'ImportCompleted' => 'Import voltooid!',
                        'importHasFinished' => 'Uw import is succesvol voltooid. Klik op de knop hieronder om een gedetailleerd importlogboek te downloaden en te openen.',
                        'ImportLog' => 'Importlogboek',
                        'FailedMedia' => 'Mislukte media',
                        'UseTheFailedImages' => 'Gebruik de CSV van mislukte afbeeldingen om de URL\'s te corrigeren en de afbeeldingen opnieuw te importeren',
                        'FeaturedFields' => 'Uitgelichte Afbeelding Metadata',
                        'Summary' => 'Samenvatting',
                );
        return $response;
        }
        public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'Upgrade naar PREMIUM met code',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'Ontgrendeling functies zoals bulk import, geavanceerde export, planning, inhoud update, en meer, plus levenslange ondersteuning',
                'upgradenow' => 'upgrade nu'
        );
        return $result;
        }
}

