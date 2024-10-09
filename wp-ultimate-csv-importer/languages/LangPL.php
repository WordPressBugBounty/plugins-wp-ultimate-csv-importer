<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class LangPL {
        private static $pl_instance = null , $media_instance;

        public static function getInstance() {
                if (LangPL::$pl_instance == null) {
                        LangPL::$pl_instance = new LangPL;
                        return LangPL::$pl_instance;
                }
                return LangPL::$pl_instance;
        }

        public static function contents(){
                $response = array('ImportUpdate' => 'PolskiImport',
                'SelectAllImages' => 'Wybierz wszystkie obrazy w pliku importu, aby uniknąć błędów importu',
                'ChooseImagesToImport' => 'Wybierz obrazy do importu. Użyj pola wyboru poniżej, aby zaznaczyć wszystkie obrazy.',
                'FileName' => 'Nazwa pliku:',
                'OK' => 'OK',
                'Exportfiltereddata' => 'Eksportuj przefiltrowane dane',
                'Exportfiltereddatadesc' => 'Pozwala uzyskać tylko wymagane dane za pomocą różnych zaawansowanych filtrów',
                'Backupineditableformat' => 'Kopia zapasowa w formacie edytowalnym',
                'Backupineditableformatdesc' => 'Kopia zapasowa w 4 różnych formatach plików, takich jak CSV XML JSON XLS.',
                'AutoScheduledBackups' => 'Automatyczne zaplanowane kopie zapasowe',
                'AutoScheduledBackupsdesc' => 'Zaplanowany eksport pomaga w regularnych odstępach czasu tworzyć kopie zapasowe w edytowalnym formacie pliku tekstowego.',
                'AutoSchedulewithreusabletemplates' => 'Automatyczny harmonogram z szablonami wielokrotnego użytku',
                'Dashboard' => 'Panel',
                'Manager' => 'Menedżer',
                'csv_importlink' => 'Kliknij tutaj',
                'Export' => 'Eksport',
                'Settings' => 'Ustawienia',
                'Support' => 'Wsparcie',
                'UploadfromDesktop' => 'Prześlij z komputera',
                'UploadfromFTPSFTP' => 'Prześlij z FTP/SFTP',
                'UploadfromFTP' => 'Prześlij z FTP',
                'UploadfromURL' => 'Prześlij z adresu URL',
                'ChoosFileintheServer' => 'Wybierz opcję Plik na serwerze',
                'DragDropyourfilesor' => 'Przeciągnij i upuść swoje pliki lub',
                'Browse' => 'Przeglądać',
                'NewItem' => 'Nowy przedmiot',
                'ExistingItems' => 'Istniejące elementy',
                'ImportEachRecordAs'=> 'Importuj każdy rekord jako',
                'Continue' => 'Kontynuować',
                'Search' => 'Szukaj',
                'FromDate' => 'Od daty',
                'ToDate' => 'Spotykać się z kimś',
                'SEARCH' => 'SZUKAJ',
                'Media' =>'Głoska bezdźwięczna',
                'AccessKey' => 'Klucz dostępu',
                'SavedTemplate' => 'Zapisany szablon',
                'TEMPLATES' => 'SZABLONY',
                'MATCHEDCOLUMNSCOUNT' => 'LICZBA DOPASOWANYCH KOLUMN',
                'MODULE' => 'MODUŁ',
                'CREATEDTIME' => 'STWORZONY CZAS',
                'ACTION' => 'DZIAŁANIE',
                'USETEMPLATE' => 'UŻYJ SZABLONU',
                'CREATENEWMAPPING' => 'UTWÓRZ NOWE MAPOWANIE',
                'BACK' => 'Z POWROTEM',
                'ADVANCEDMODE' => 'TRYB ZAAWANSOWANY',
                'DRAGDROPMODE' => 'TRYB PRZECIĄGNIJ I UPUŚĆ',
                'WordpressFields' => 'Pola wordpress',
                'WPFIELDS' => 'Pola WP',
                'CSVHEADER' => 'Nagłówek CSV',
                'Action' => 'Działanie',
                'Name' => 'Nazwa',
                'HINT' => 'WSKAZÓWKA',
                'Example' => 'Przykład',
                'WordPressCoreFields' => 'Podstawowe pola WordPressa',
                'ACFFreeFields' => 'Wolne pola ACF',
                'ACFFields' => 'Pola ACF',
                'ACFGroupFields' => 'Pola grupy ACF',
                'ACFProFields' => 'Pola ACF Pro',
                'ACFRepeaterFields' => 'Pola wzmacniające ACF',
                'TypesCustomFields' => 'Typy pól niestandardowych',
                'PodsFields' => 'Pola strąków',
                'JobListingFields' => 'Pola z ofertami pracy',
                'CustomFieldSuite' => 'Niestandardowy pakiet terenowy',
                'AllInOneSeoFields' => 'Wszystko w jednym polu SEO',
                'MetaBoxFields' => 'Pola meta-boxów',
                'YoastSeoFields' => 'Pola Yoast Seo',
                'WPMLFields' => 'Pola WPML-a',
                'JetEngineFields' => 'Pola silników odrzutowych',
                'JetEngineRFFields' => 'Pola wzmacniacza silnika odrzutowego',
                'JetEngineCPTFields' => 'Pola CPT silnika odrzutowego',
                'jetEngineCPTRFFields' => 'Pola RF CPT silnika odrzutowego',
                'JetEngineCCTFields' => 'Pola Jet Engine CCT',
                'JetEngineCCTRFFields' => 'Pola powtarzalne Jet Engine CCT',
                'jetEngineTaxonomyFields' => 'Pola taksonomii silników odrzutowych',
                'jetEngineTaxonomyRFFields' => 'Pola powtarzalne taksonomii silników odrzutowych',
                'JetEngineRelationsFields' => 'Pola relacji silników odrzutowych',
                'RankMathFields'=>'Ranking pól matematycznych',
                'RankMathProFields'=>'Ranking pól matematycznych Pro',
                'SeoPressFields' => 'Pola SEOPress',
                'TotalPressFields' => 'Pola TotalPress',
                'replyattributesfields' => 'Pola atrybutów odpowiedzi',
                'forumattributesfields' => 'Pola atrybutów forum',
                'topicattributesfields' => 'Pola atrybutów tematu',
                'BillingAndShippingInformation' => 'Informacje dotyczące rozliczeń i wysyłki',
                'CustomFieldsWPMemberFields' => 'Pola niestandardowe Pola członkowskie WP',
                'CustomFieldsMemberFields' => 'Pola niestandardowe Pola członkowskie',
                'ProductMetaFields' => 'Pola meta produktu',                
                'ProductAttrFields' => 'Pola atrybutów produktu',
                'ProductBundleMetaFields' => 'Pola meta pakietu produktów',
                'WPECommerceCustomFields' => 'Pola niestandardowe WP ECommerce',
                'EventsManagerFields' => 'Pola menedżera wydarzeń',
                'CMB2CustomFields' => 'Pola niestandardowe CMB2',
                'CourseSettingsFields' => 'Pola ustawień kursu',
                'CurriculumSettingsFields' => 'Pola ustawień programu nauczania',
                'QuizSettingsFields' => 'Pola ustawień quizu',
                'LessonSettingsFields' => 'Pola ustawień lekcji',
                'QuestionSettingsFields' => 'Pola ustawień pytań',
                'OrderSettingsFields' => 'Pola ustawień zamówienia',
                'WordPressCustomFields' => 'Pola niestandardowe WordPressa',
                'TermsandTaxonomies' => 'Terminy i taksonomie',
                'IsSerialized' => 'Jest serializowany',
                'NoCustomFieldsFound' => 'Nie znaleziono pól niestandardowych', 
                'MediaUploadFields' => 'Pola przesyłania multimediów',
                'UploadMedia' => 'Prześlij multimedia',
                'UploadedListofFiles' => 'Przesłana lista plików',
                'UploadedMediaFileLists' => 'Listy przesłanych plików multimedialnych',
                'SavethismappingasTemplate' => 'Zapisz to mapowanie jako szablon',
                'Save' => 'Ratować',
                'Doyouneedtoupdatethecurrentmapping' => 'Czy potrzebujesz zaktualizować bieżące mapowanie?',
                'Savethecurrentmappingasnewtemplate' => 'Zapisz bieżące mapowanie jako nowy szablon',
                'Back' => 'Z powrotem',
                'Size' => 'Rozmiar',
                'MediaHandling' => 'Obsługa multimediów',
                'Downloadexternalimagestoyourmedia' => 'Pobierz obrazy zewnętrzne na swoje multimedia',
                'ImageHandling' => 'Obsługa obrazu',
                'Usemediaimagesifalreadyavailable' => 'Użyj obrazów multimedialnych, jeśli są już dostępne',
                'Doyouwanttooverwritetheexistingimages' => 'Czy chcesz zastąpić istniejące obrazy',
                'ImageSizes' => 'Rozmiary obrazów',
                'Thumbnail' => 'Miniaturka',
                'Medium' => 'Średni',
                'MediumLarge' => 'Średniej wielkości',
                'Large' => 'Duży',
                'Custom' => 'Zwyczaj',
                'Slug' => 'Ślimak',
                'Width' => 'Szerokość',
                'Height' => 'Wysokość',
                'SIMPLEMODE' => 'PROSTY TRYB',
                'CustomerReviews' => 'Opinie klientów',
                'Buynow' => 'KUP TERAZ!',
                'Addons' => 'Dodatki',
                'Posts' => 'Posty',
                'Found' => 'Znaleziony',
                'Pages' => 'Strony',
                'CustomPosts' => 'Niestandardowe posty',
                'PostCategories' => 'Kategorie postów',
                'PostTags' => 'Tagi postów',
                'Comments' =>'Uwagi',
                'WooCommerceProducts' => 'Produkty WooCommerce',
                'Taxonomies' => 'Taksonomie',
                'PremiumVersion' => 'Wersja premium',
                'ContactusforPresaleEnquiry' => 'Skontaktuj się z nami, aby uzyskać zapytanie przedsprzedażowe',
                'Thisfeatureisavailable' => 'Ta funkcja jest dostępna w',
                'UltimateCSVImporterPro'=> 'Najlepszy importer CSV Pro',
                'Exporterwithadvancedfilters' => 'Eksporter z zaawansowanymi filtrami',
                'Updateolderpostsfromsingleimport' => 'Zaktualizuj starsze posty z jednego importu',
                'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter'=> 'jetEngine Metabox Zestaw narzędzi ACF pro / Free i Pods Field/Post Importer wtyczek',
                'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'Importer danych wtyczek SEO - RankMath SEOPress Yoast i wszystko w jednym SEO',
                'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst'=> 'Ostrzeżenie: brakuje niektórych dodatków, co jest zalecane',
                'AIOWooCommerceImportSuit' => 'Kombinezon importowy AIO WooCommerce',
                'PostContentImageOption' => 'Opcja obrazu treści posta',
                'installactivate' => 'aby zainstalować i aktywować teraz',
                'EnabletodeletetheitemsnotpresentinCSVXMLfile' => 'Włącz, aby usunąć elementy, których nie ma w pliku CSV/XML',
                'subcribe' => 'Subskrybuj',
                'DeletedatafromWordPress' => 'Usuń dane WordPresa',
                'GetSupport' => 'Uzyskać wsparcie',
                'SampleCSVXML' => 'Przykładowy plik CSV i XML',
                'Scheduledexporthelpsbackupaseditabletextfileformat' => 'Zaplanowany eksport umożliwia obsługę edytowalnego formatu pliku tekstowego w regularnych odstępach czasu.',
                'ExportfeatureisdisabledInstallandactivatetheaddonpluginfirst'=>'Uwaga: funkcja eksportu jest wyłączona. Najpierw zainstaluj i aktywuj dodatkową wtyczkę',
                'Gotoaddons'=>'Przejdź do dodatków',
                'toaform' => 'wspierać forum',
                'Learnfrom' => 'Ucz się z naszych postów na blogu',
                'TechnicalDocumentation' => 'Dokumentacja techniczna',
                'Getsampleandexamplefiles' => 'Pobierz próbki i przykładowe pliki',
                'instruction' => '(Liczba rekordów w pliku)',
                'Createasupport' => 'Utwórz tutaj temat pomocy, aby uzyskać pomoc',
                'CreateTopic' => 'Utwórz temat',
                'WPMLImporter' => 'Importer WPML-a',
                'DownloadPostContentExternalImagestoMedia' => 'Pobierz obrazy zewnętrzne treści publikowanych na nośnikach',
                'Addcustomsizes' => 'Dodaj niestandardowe rozmiary',
                'backupineditableformat' => 'kopia zapasowa w formacie edytowalnym',
                'Letsyougetonlyrequireddatawiththedifferentadvancedfilters' => 'Pozwala uzyskać tylko wymagane dane za pomocą różnych zaawansowanych filtrów',
                'MediaSEOAdvancedOptions' => 'SEO mediów i opcje zaawansowane',
                'SetimageTitle' => 'Ustaw tytuł obrazu',
                'Backupin4differentfileformatslikeCSV' => 'Kopia zapasowa w 4 różnych formatach plików, takich jak CSV XML JSON XLS.',        
                'SetimageCaption' => 'Ustaw podpis obrazu',
                'WooCommerceOrders' => 'Zamówienia WooCommerce',
                'WooCommerceCoupons' => 'Kupony WooCommerce',
                'UltimateExporterPro' => 'Najlepszy Eksporter Pro',
                'SetimageAltText' => 'Ustaw tekst alternatywny obrazu',
                'WooCommerceVariations' => 'Odmiany WooCommerce',
                'WooCommerceRefunds' => 'Zwroty środków WooCommerce',
                'SetimageDescription' => 'Ustaw opis obrazu',
                'Changeimagefilenameto' => 'Zmień nazwę pliku obrazu na',
                'ImportconfigurationSection' => 'Importuj sekcję konfiguracyjną',
                'EnablesafeprestateRollback' => 'Włącz bezpieczne przywracanie stanu wstępnego',
                'Backupbeforeimport' => 'Kopia zapasowa przed importem',
                'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'Czy chcesz WŁĄCZYĆ tryb konserwacji podczas importu',
                'Doyouwanttohandletheduplicateonexistingrecords' => 'Czy chcesz zająć się duplikatem istniejących rekordów?',
                'Mentionthefieldswhichyouwanttohandleduplicates' => 'Wspomnij o polach, które chcesz obsłużyć duplikaty',
                'DoyouwanttoUpdateanexistingrecords' => 'Czy chcesz zaktualizować istniejące rekordy',
                'Updaterecordsbasedon' => 'Aktualizuj rekordy w oparciu o',
                'DoyouwanttoSchedulethisImport' => 'Czy chcesz zaplanować ten import',
                'ScheduleDate' => 'Datę harmonogramu',
                'ScheduleFrequency' => 'Częstotliwość harmonogramu',
                'TimeZone' => 'Strefa czasowa',
                'ScheduleTime' => 'Czas harmonogramu',
                'Schedule' => 'Harmonogram',
                'Import' => 'Rozpocznij import',
                'Format' => 'Format',
                'OneTime' => 'Jeden raz',
                'Daily' => 'Codziennie',
                'Weekly' => 'Co tydzień',
                'Monthly' => 'Miesięczny',
                'Hourly' => 'Cogodzinny',
                'Every30mins'=> 'Co 30 minut',
                'Every15mins' => 'Co 15 minut',
                'Every10mins' => 'Co 10 minut',
                'Every5mins' => 'Co 5 minut',
                'FileName' => 'Nazwa pliku',
                'FileSize' => 'Rozmiar pliku',
                'Process' => 'Proces',
                'Totalnoofrecords' => 'Łączna liczba rekordów',
                'CurrentProcessingRecord' => 'Bieżący zapis przetwarzania',
                'RemainingRecord' => 'Pozostały rekord',
                'Completed' => 'Zakończony',
                'TimeElapsed' => 'Czas, jaki upłynął',
                'approximate' => 'przybliżony',
                'DownloadLog' => 'Wyświetl dziennik',
                'NoRecord' => 'Brak zapisu',
                'UploadedCSVFileLists' => 'Przesłane listy plików CSV',
                'Hostname' => 'Nazwa hosta',
                'HostPort' => 'Port hosta',
                'HostUsername' => 'Nazwa użytkownika hosta',
                'HostPassword' => 'Hasło hosta',
                'HostPath' => 'Ścieżka hosta',
                'DefaultPort' => 'Domyślny port',
                'FTPUsername' => 'Nazwa użytkownika FTP',
                'FTPPassword' => 'Hasło FTP',
                'ConnectionType' => 'Rodzaj połączenia',
                'ImportersActivity' => 'Działalność importerów',
                'ImportStatistics' => 'Importuj statystyki',
                'FileManager' => 'Menedżer plików',
                'SmartSchedule' => 'Inteligentny harmonogram',
                'ScheduledExport' => 'Zaplanowany eksport',
                'Templates' => 'Szablony',
                'LogManager' => 'Menedżer logów',
                'NotSelectedAnyTab' => 'Nie wybrano żadnej karty',
                'EventInfo' => 'Informacje o wydarzeniu',
                'EventDate' => 'Data wydarzenia',
                'EventStatus' => 'Stan wydarzenia',
                'Actions' => 'działania',
                'Date' => 'Data',
                'Purpose' => 'Zamiar',
                'Revision' => 'Rewizja',
                'Select' => 'Wybierać',
                'Inserted' => 'Wstawiono',
                'Updated' => 'Zaktualizowano',
                'Skipped' => 'Pominięte',
                'Delete' => 'Usuwać',
                'Noeventsfound' => 'Nie znaleziono żadnych wydarzeń',
                'ScheduleInfo' => 'Informacje o harmonogramie',
                'ScheduledDate' => 'Zaplanowana data',
                'ScheduledTime' => 'Zaplanowany czas',
                'Youhavenotscheduledanyevent' => 'Nie zaplanowałeś żadnego wydarzenia',
                'Frequency' => 'Częstotliwość',
                'Time' => 'Czas',
                'EditSchedule' => 'Edytuj harmonogram',
                'SaveChanges' => 'Zapisz zmiany',
                'TemplateInfo' => 'Informacje o szablonie',
                'TemplateName' => 'Nazwa szablonu',
                'Module' => 'Moduł',
                'CreatedTime' => 'Stworzony czas',
                'NoTemplateFound' => 'Nie znaleziono szablonu',
                'Download' => 'Pobierać',
                'NoLogRecordFound' => 'Nie znaleziono zapisu dziennika',
                'GeneralSettings' => 'Ustawienia główne',
                'DatabaseOptimization' => 'Optymalizacja bazy danych',
                'SecurityandPerformance' => 'Bezpieczeństwo i wydajność',
                'Documentation' => 'Dokumentacja',
                'MediaReport' => 'Raport medialny',
                'DropTable' => 'Upuść stół',
                'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'Jeśli włączona, dezaktywacja wtyczki spowoduje usunięcie danych wtyczki, których nie można przywrócić.',
                'Scheduledlogmails' => 'Zaplanowane wiadomości e-mail z dziennikiem',
                'Enabletogetscheduledlogmails' => 'Włącz, aby otrzymywać zaplanowane wiadomości e-mail z dziennikiem.',
                'Sendpasswordtouser' => 'Wyślij hasło do użytkownika',
                'Enabletosendpasswordinformationthroughemail' => 'Włącz wysyłanie informacji o haśle e-mailem.',
                'WoocommerceCustomattribute' => 'Atrybut niestandardowy Woocommerce',
                'Enablestoregisterwoocommercecustomattribute' => 'Umożliwia zarejestrowanie niestandardowego atrybutu woocommerce.',
                'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'Przed przystąpieniem do optymalizacji bazy danych upewnij się, że wykonałeś niezbędną kopię zapasową. Utraconych danych nie można przywrócić.',
                'DeleteallorphanedPostPageMeta' => 'Usuń wszystkie osierocone meta postu/strony',
                'Deleteallunassignedtags' => 'Usuń wszystkie nieprzypisane tagi',
                'DeleteallPostPagerevisions' => 'Usuń wszystkie wersje postów/stron',
                'DeleteallautodraftedPostPage' => 'Usuń wszystkie automatycznie opracowane posty/strony',
                'DeleteallPostPageintrash' => 'Usuń wszystkie posty/strony z kosza',
                'DeleteallCommentsintrash' => 'Usuń wszystkie komentarze w koszu',
                'DeleteallUnapprovedComments' => 'Usuń wszystkie niezatwierdzone komentarze',
                'DeleteallPingbackComments' => 'Usuń wszystkie komentarze Pingback',
                'DeleteallTrackbackComments' => 'Usuń wszystkie komentarze Trackback',
                'DeleteallSpamComments' => 'Usuń wszystkie komentarze będące spamem',
                'RunDBOptimizer' => 'Uruchom Optymalizator DB',
                'Hire_us' => 'Zatrudnić nas',
                'DatabaseOptimizationLog' => 'Dziennik optymalizacji bazy danych',
                'noofOrphanedPostPagemetahasbeenremoved' => 'usunięto liczbę osieroconych meta postów/stron.',
                'noofUnassignedtagshasbeenremoved' => 'liczba nieprzypisanych tagów została usunięta.',
                'noofPostPagerevisionhasbeenremoved' => 'nie usunięto żadnych wersji wpisu/strony.',
                'noofAutodraftedPostPagehasbeenremoved' => 'liczba automatycznie przygotowanych postów/stron została usunięta.',
                'noofPostPageintrashhasbeenremoved' => 'liczba postów/stron w koszu została usunięta.',
                'noofSpamcommentshasbeenremoved' => 'nie usunięto żadnych komentarzy zawierających spam.',
                'noofCommentsintrashhasbeenremoved' => 'liczba komentarzy w koszu została usunięta.',
                'noofUnapprovedcommentshasbeenremoved' => 'nie usunięto żadnych niezatwierdzonych komentarzy.',
                'noofPingbackcommentshasbeenremoved' => 'żaden z komentarzy Pingback nie został usunięty.',
                'noofTrackbackcommentshasbeenremoved' => 'nie usunięto żadnych komentarzy Trackback.',
                'Allowauthorseditorstoimport' => 'Zezwalaj autorom/redaktorom na import',
                'Thisenablesauthorseditorstoimport' => 'Umożliwia to autorom/redaktorom importowanie.',
                'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'Minimalne wymagane wartości php.ini (wartości skonfigurowane w Ini)',
                'Variables' => 'Zmienne',
                'SystemValues' => 'Wartości systemowe',
                'MinimumRequirements' => 'Minimalne wymagania',
                'RequiredtoenabledisableLoadersExtentionsandmodules' => 'Wymagane do włączenia/wyłączenia rozszerzeń i modułów modułów ładujących:',
                'DebugInformation' => 'Informacje o debugowaniu:',
                'SmackcodersGuidelines' => 'Wytyczne Smackcodera',
                'DevelopmentNews' => 'Wiadomości rozwojowe',
                'WhatsNew' => 'Co nowego?',
                'YoutubeChannel' => 'Kanał Youtube',
                'OtherWordPressPlugins' => 'Inne wtyczki WordPress',
                'Count' => 'Liczyć',
                'ImageType' => 'Typ obrazu',
                'Status' => 'Status',
                'Loading' => 'Ładowanie',
                'LoveWPUltimateCSVImporterGivea5starreviewon' => 'Uwielbiam WP Ultimate CSV Importer Daj 5-gwiazdkową recenzję na temat',
                'ContactSupport' => 'Skontaktuj się z pomocą techniczną',
                'Email' => 'Email',
                'Supporttype' => 'Typ wsparcia',
                'BugReporting' => 'Zgłaszanie błędów',
                'FeatureEnhancement' => 'Ulepszenie funkcji',
                'Message' => 'Wiadomość',
                'Send' => 'Wysłać',
                'NewsletterSubscription' => 'Subskrypcja newslettera',
                'Subscribe' => 'Subskrybuj',
                'Note' => 'Notatka',
                'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'Zapisz się na listę mailingową Smackcoders (kilka wiadomości rocznie)',
                'Pleasedraftamailto' => 'Proszę napisać maila na adres',
                'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'Jeśli w ciągu godziny nie otrzymasz żadnego potwierdzenia!',
                'Selectyourmoduletoexportthedata' => 'Wybierz moduł do eksportu danych',
                'Toexportdatabasedonthefilters' => 'Aby wyeksportować dane na podstawie filtrów',
                'ExportFileName' => 'Eksportuj nazwę pliku',
                'AdvancedSettings' => 'Zaawansowane ustawienia',
                'ExportType' => 'Typ eksportu',
                'SplittheRecord' => 'Podziel eksport na każde',
                'AdvancedFilters'=> 'Zaawansowane filtry',
                'Exportdatawithautodelimiters' => 'Eksportuj dane z automatycznymi ogranicznikami',
                'Delimiters' => 'Ograniczniki',
                'OtherDelimiters' => 'Inne ograniczniki',
                'Exportdataforthespecificperiod' => 'Eksportuj dane za wybrany okres',
                'StartFrom' => 'Rozpocząć z',
                'EndTo' => 'Koniec do',
                'Exportdatawiththespecificstatus' => 'Eksportuj dane z określonym statusem',
                'All' => 'Wszystko',
                'Publish' => 'Publikować',
                'Sticky' => 'Lepki',
                'Private' => 'Prywatny',
                'Protected' => 'Chroniony',
                'Draft' => 'Projekt',
                'Pending' => 'Aż do',
                'Exportdatabyspecificauthors' => 'Eksportuj dane według konkretnych autorów',
                'Authors' => 'Autorski',
                'ExportdatabasedonspecificInclusions' => 'Eksportuj dane w oparciu o określone włączenia',
                'DoyouwanttoSchedulethisExport' => 'Czy chcesz zaplanować ten eksport',
                'SelectTimeZone' => 'Wybierz Strefa czasowa',
                'ScheduleExport' => 'Zaplanuj eksport',
                'DataExported' => 'Dane wyeksportowane',
                'FilePath' => 'Ścieżka pliku',
                'WPUltimateCSVImporter' => 'WP Ostateczny importer CSV',
                'importwoocommerce' => 'importuj woocommerce',
                'ImportanybulkWooCommerceProductsdatainCSV' => 'Zaimportuj dowolne zbiorcze dane produktów WooCommerce w formacie CSV',
                'Highlights' => 'Przegląd najważniejszych wydarzeń',
                'ProductTypessimplegroupedvariableexternaltypeimport' => 'Typy produktów — prosty import zmiennych grupowanych typu zewnętrznego',
                'FeaturedProductImportfromURL' => 'Import polecanych produktów z adresu URL',
                'Galleryimageimport' => 'Import obrazów z galerii',
                'Duplicatedetection' => 'Wykrywanie duplikatów',
                'FileType' => 'Typ pliku',
                'SupportsUTF_8CSVfile' => 'Obsługuje plik CSV UTF-8',
                'AlreadyInstalled' => 'Już zainstalowane',
                'Install' => 'zainstalować',
                'ImportUsers' => 'Importuj użytkowników',
                'ImportUserinfointoWordPressinbulk' => 'Importuj zbiorczo informacje o użytkowniku do WordPressa',
                'WPMembersaddonsupport' => 'Obsługa dodatków dla członków WP',
                'Defaultcustomfieldsimport' => 'Domyślny import pól niestandardowych',
                'Sendsautomatedpasswordnotificationemailoptional' => 'Wysyła automatyczną wiadomość e-mail z powiadomieniem o haśle (opcjonalnie)',
                'WPUltimateExporter' => 'WP Ostateczny eksporter',
                'ExportallyourWordPressdataasCSVfileforbackup' => 'Wyeksportuj wszystkie dane WordPress jako plik CSV w celu utworzenia kopii zapasowej',
                'Supportsdefaultcustomfields' => 'Obsługuje domyślne pola niestandardowe',
                'UTF8encodedCSVfile' => 'Plik CSV zakodowany w UTF-8',
                'SupportPostPageCustomPost' => 'Strona z postami wsparcia i post niestandardowy',
                'Filteredexportbasedonperiodoftimeauthors' => 'Filtrowany eksport na podstawie okresu i autorów',
                'Users' => 'Użytkownicy',
                'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'Zainstaluj Ultimate Exporter, aby wyeksportować wszystkie dane WordPress w formacie CSV',
                'Clickheretoinstall' => 'Kliknij tutaj, aby zainstalować',
                'LifterCourseSettingsFields' => 'Pola ustawień kursu sztangisty',
                'LifterReviewSettingsFields' => 'Pola ustawień przeglądu podnośnika',
                'LifterCouponSettingsFields' => 'Pola ustawień kuponu podnośnika',
                'LifterLessonSettingsFields' => 'Pola ustawień lekcji podnośnika',
                'FifuPostFields' => 'Pola pocztowe Fifu',
                'FifuPageFields' => 'Pola strony Fifu',
                'polylangfields' => 'Pola ustawień Polylang',
                'BuddyFields' => 'Pola BuddyPress',
                'WPCompleteFields' => 'WPWypełnij pola',
                'ChooseUploadMethod' => 'Wybierz metodę przesyłania',
                'CsvUploadFields' => 'Prześlij plik',
                'Device' => 'Urządzenie',
                'Remote' => 'Zdalny',
                'SelectDeviceZIPfile' => 'Wybierz urządzenie, aby przesłać obrazy bezpośrednio z urządzenia jako plik ZIP.',
                'SelectDeviceCSVfile' => 'Wybierz zdalny, aby zaimportować obrazy z adresów URL zdalnych stron internetowych.',
                'MediaContinue' => 'Kontynuuj',
                'FreshImport' => 'Nowy import',
                'UpdateContent' => 'Aktualizuj treść',
                'UpdateThisMappingAs' => 'Zaktualizuj to mapowanie jako',
                'Overwritetheavailableimages' => 'Nadpisz dostępne obrazy',
                'AlwaysCreateAsNewImage' => 'Zawsze twórz jako nowy obraz',
                'ImportCompleted' => 'Import zakończony!',
                'importHasFinished' => 'Twój import został pomyślnie zakończony. Kliknij poniższy przycisk, aby pobrać i uzyskać dostęp do szczegółowego dziennika importu.',
                'ImportLog' => 'Dziennik importu',
                'FailedMedia' => 'Nieudane media',
                'UseTheFailedImages' => 'Użyj pliku CSV z nieudanymi obrazami, aby poprawić adresy URL i ponownie zaimportować obrazy',
                'FeaturedFields' => 'Metadane obrazu wyróżnionego',
                'Summary' => 'Podsumowanie',

                );
        return $response;
        }
        public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'Uaktualnij do PREMIUM za pomocą kodu',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'Funkcje odblokowania, takie jak import masowy, zaawansowany eksport, harmonogram, aktualizacja zawartości i więcej, plus wsparcie dożywotnia',
                'upgradenow' => 'teraz uaktualnij'
        );
        return $result;
        }
}

