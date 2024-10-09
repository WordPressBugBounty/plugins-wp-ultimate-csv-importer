<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class LangEN_ZA {
	private static $en_ZA_instance = null;

	public static function getInstance() {
		if (LangEN_ZA::$en_ZA_instance == null) {
			LangEN_ZA::$en_ZA_instance = new LangEN_ZA;
			return LangEN_ZA::$en_ZA_instance;
		}
		return LangEN_ZA::$en_ZA_instance;
	}

        public static function contents(){
                $response = array('ImportUpdate' => 'Import',
                        'SelectAllImages' => 'Select all images in your import file to avoid import failures',
                        'ChooseImagesToImport' => 'Choose images to import. Utilize the checkbox below to select all images.',
                        'FileName' => 'File Name:',
                        'OK' => 'OK',
                        'Buynow' => 'Buy now!',
                        'Exportfiltereddata' => 'Export filtered data',
                        'Exportfiltereddatadesc' => 'Lets you get only required data with the different advanced filters',
                        'Backupineditableformat' => 'Backup in editable format',
                        'Backupineditableformatdesc' => 'Backup in 4 different file formats like CSV, XML, JSON,XLS.',
                        'AutoScheduledBackups' => 'Auto Scheduled Backups',
                        'AutoScheduledBackupsdesc' => 'Scheduled export helps backup as editable text file format in regular interval.',
                        'Exporterwithadvancedfilters' => 'Exporter with advanced filters',
                        'Updateolderpostsfromsingleimport' => 'Update older posts from a single import',
                        'AutoSchedulewithreusabletemplates' => 'Auto Schedule with reusable templates',
                        'AIOWooCommerceImportSuit' => 'AIO WooCommerce Import Suit',
                        'WPMLImporter' => 'WPML Importer',
                        'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'SEO Plugins Data Importer - RankMath, SEOPress, Yoast and All in One SEO',
                        'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter' => 
                        'JetEngine, Metabox, Toolset Types, ACF pro / Free and Pods Field/Post Plugins Importer',
                        'Dashboard' => 'Dashboard',
                        'Manager' => 'Manager',
                        'Export' => 'Export',
                        'Settings' => 'Settings',
                        'Support' => 'Support',
                        'OrderMetaFields' => 'Order Meta Fields',
                        'CouponMetaFields' => 'Coupon Meta Fields',
                        'UploadfromDesktop' => 'Upload from Desktop',
                        'UploadfromFTPSFTP' => 'Upload from FTP / SFTP',
                        'UploadfromFTP' => 'Upload from FTP',
                        'UploadfromURL' => 'Upload from URL',
                        'ChoosFileintheServer' => 'Choose File in the Server',
                        'DragDropyourfilesor' => 'Drag & Drop your files or',
                        'Browse' => 'Browse',
                        'NewItem' => 'New Item',
                        'ExistingItems' => 'Existing Items',
                        'ImportEachRecordAs'=> 'Import each record as',
                        'Continue' => 'Continue',
                        'Search' => 'Search',
                        'FromDate' => 'From Date',
                        'BuddyFields' =>'BuddyPress Fields',
                        'ToDate' => 'To Date',
                        'SEARCH' => 'SEARCH',
                        'AccessKey' => 'AccessKey',
                        'SavedTemplate' => 'Saved Template',
                        'TEMPLATES' => 'TEMPLATES',
                        'MATCHEDCOLUMNSCOUNT' => 'MATCHED COLUMNS COUNT',
                        'MODULE' => 'MODULE',
                        'CREATEDTIME' => 'CREATED TIME',
                        'ACTION' => 'ACTION',
                        'USETEMPLATE' => 'USE TEMPLATE',
                        'CREATENEWMAPPING' => 'CREATE NEW MAPPING',
                        'BACK' => 'BACK',
                        'ADVANCEDMODE' => 'ADVANCED MODE',
                        'DRAGDROPMODE' => 'DRAG & DROP MODE',
                        'SIMPLEMODE' => 'SIMPLE MODE',
                        'WordpressFields' => 'Wordpress Fields',
                        'WPFIELDS' => 'WP Fields',
                        'CSVHEADER' => 'CSV Header',
                        'Action' => 'Action',
                        'Name' => 'Name',
                        'HINT' => 'HINT',
                        'Example' => 'Example',
                        'WordPressCoreFields' => 'WordPress Core Fields',
                        'ACFFreeFields' => 'ACF Free Fields',
                        'ACFFields' => 'ACF Fields',
                        'ACFGroupFields' => 'ACF Group Fields',
                        'ACFProFields' => 'ACF Pro Fields',
                        'ACFRepeaterFields' => 'ACF Repeater Fields',
                        'TypesCustomFields' => 'Types Custom Fields',
                        'PodsFields' => 'Pods Fields',
                        'JobListingFields' => 'Job Listing Fields',
                        'CustomFieldSuite' => 'Custom Field Suite',
                        'AllInOneSeoFields' => 'All In One Seo Fields',
                        'MetaBoxFields' => 'MetaBox-velde',
                        'YoastSeoFields' => 'Yoast Seo Fields',
                        'WPMLFields' => 'WPML Fields',
                        'RankMathFields'=>'Rank Math Fields',
                        'RankMathProFields'=>'Rank Math Pro Fields',
                        'replyattributesfields' => 'Reply Attributes Fields',
                        'forumattributesfields' => 'Forum Attributes Fields',
                        'topicattributesfields' => 'Topic Attributes Fields',
                        'polylangfields'=>'Polylang Settings Fields',
                        'BillingAndShippingInformation' => 'Billing and Shipping Information',
                        'CustomFieldsWPMemberFields' => 'Custom Fields WP Member Fields',
                        'CustomFieldsMemberFields' => 'Custom Fields Member Fields',
                        'ProductMetaFields' => 'Product Meta Fields',
                        'ProductAttrFields' => 'Product Attribute Fields',
                        'ProductBundleMetaFields' => 'Product Bundle Meta Fields',
                        'WPECommerceCustomFields' => 'WP ECommerce Custom Fields',
                        'EventsManagerFields' => 'Events Manager Fields',
                        'CMB2CustomFields' => 'CMB2 Custom Fields',
                        'JetEngineFields' => 'Jet Engine Fields',
                        'JetEngineRFFields' => 'Jet Engine Repeater Fields',
                        'JetEngineCPTFields' => 'Jet Engine CPT Fields',
                        'JetEngineCPTRFFields' => 'Jet Engine CPT Repeater Fields',
                        'JetEngineCCTFields' => 'Jet Engine CCT Fields',
                        'JetEngineCCTRFFields' => 'Jet Engine CCT Repeater Fields',
                        'JetEngineTaxonomyFields' => 'Jet Engine Taxonomy Fields',
                        'JetEngineTaxonomyRFFields' => 'Jet Engine Taxonomy Repeater Fields',
                        'JetEngineRelationsFields' => 'Jet Engine Relations Fields',

                        'CourseSettingsFields' => 'Course Settings Fields',
                        'CurriculumSettingsFields' => 'Curriculum Settings Fields',
                        'QuizSettingsFields' => 'Quiz Settings Fields',
                        'LessonSettingsFields' => 'Lesson Settings Fields',
                        'QuestionSettingsFields' => 'Question Settings Fields',
                        'OrderSettingsFields' => 'Order Settings Fields',
                        'WordPressCustomFields' => 'WordPress Custom Fields',
                        'TermsandTaxonomies' => 'Terms and Taxonomies',
                        'IsSerialized' => 'Is Serialized',
                        'NoCustomFieldsFound' => 'No Custom Fields Found', 
                        'MediaUploadFields' => 'Media Upload Fields',
                        'UploadMedia' => 'Upload Media',
                        'UploadedListofFiles' => 'Uploaded List of Files',
                        'UploadedMediaFileLists' => 'Uploaded Media File Lists',
                        'SavethismappingasTemplate' => 'Save this mapping as Template',
                        'Save' => 'Save',
                        'Doyouneedtoupdatethecurrentmapping' => 'Do you need to update the current mapping ?',
                        'Savethecurrentmappingasnewtemplate' => 'Save the current mapping as new template',
                        'Back' => 'Back',
                        'Size' => 'Size',
                        'MediaHandling' => 'Media Handling',
                        'Downloadexternalimagestoyourmedia' => 'Download external images to your media',
                        'ImageHandling' => 'Image Handling',
                        'Usemediaimagesifalreadyavailable' => 'Use media images if already available',
                        'Doyouwanttooverwritetheexistingimages' => 'Do you want to overwrite the existing images',
                        'ImageSizes' => 'Image Sizes',
                        'Thumbnail' => 'Thumbnail',
                        'Medium' => 'Medium',
                        'MediumLarge' => 'Medium Large',
                        'Large' => 'Large',
                        'Custom' => 'Custom',
                        'Slug' => 'Slug',
                        'Width' => 'Width',
                        'Height' => 'Height',
                        'PostContentImageOption' => 'Post Content Image Option',
                        'DownloadPostContentExternalImagestoMedia' => 'Download Post Content External Images to Media',
                        'Addcustomsizes' => 'Add custom sizes',
                        'MediaSEOAdvancedOptions' => 'Media SEO & Advanced Options',
                        'SetimageTitle' => 'Set image Title',
                        'SetimageCaption' => 'Set image Caption',
                        'SetimageAltText' => 'Set image Alt Text',
                        'SetimageDescription' => 'Set image Description',
                        'Changeimagefilenameto' => 'Change image file name to',
                        'ImportconfigurationSection' => 'Import configuration Section',
                        'EnablesafeprestateRollback' => 'Enable safe prestate Rollback',
                        'Backupbeforeimport' => 'Backup before import',
                        'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'Do you want to SWITCH ON Maintenance mode while import',
                        'Doyouwanttohandletheduplicateonexistingrecords' => 'Do you want to handle the duplicate on existing records',
                        'Mentionthefieldswhichyouwanttohandleduplicates' => 'Mention the fields which you want to handle duplicates',
                        'DoyouwanttoUpdateanexistingrecords' => 'Do you want to Update an existing records',
                        'Updaterecordsbasedon' => 'Update records based on',
                        'DeletedatafromWordPress' => 'Delete data from WordPress',
                        'EnabletodeletetheitemsnotpresentinCSVXMLfile' => 'Enable to delete the items not present in CSV/XML file',
                        'DoyouwanttoSchedulethisImport' => 'Do you want to Schedule this Import',
                        'DeletedatafromWordPress' => 'Delete data from WordPress',
                        'EnabletodeletetheitemsnotpresentinCSVXMLfile' => 'Enable to delete the items not present in CSV/XML file',
                        'ScheduleDate' => 'Schedule Date',
                        'ScheduleFrequency' => 'Schedule Frequency',
                        'TimeZone' => 'Time Zone',
                        'ScheduleTime' => 'Schedule Time',
                        'Schedule' => 'Schedule',
                        'Import' => 'Start Import',
                        'Format' => 'Format',
                        'OneTime' => 'OneTime',
                        'Daily' => 'Daily',
                        'Weekly' => 'Weekly',
                        'Monthly' => 'Monthly',
                        'Hourly' => 'Hourly',
                        'Every30mins'=> 'Every 30 mins',
                        'Every15mins' => 'Every 15 mins',
                        'Every10mins' => 'Every 10 mins',
                        'Every5mins' => 'Every 5 mins',
                        'FileName' => 'File Name',
                        'FileSize' => 'File Size',
                        'Process' => 'Process',
                        'Totalnoofrecords' => 'Total no of records',
                        'CurrentProcessingRecord' => 'Current Processing Record',
                        'RemainingRecord' => 'Remaining Record',
                        'Completed' => 'Completed',
                        'TimeElapsed' => 'Time Elapsed',
                        'approximate' => 'approximate',
                        'DownloadLog' => 'View Log',
                        'NoRecord' => 'No Record',
                        'UploadedCSVFileLists' => 'Uploaded CSV File Lists',
                        'Hostname' => 'Host Name',
                        'HostPort' => 'Host Port',
                        'HostUsername' => 'Host Username',
                        'HostPassword' => 'HostPassword',
                        'HostPath' => 'HostPath',
                        'DefaultPort' => 'Default Port',
                        'FTPUsername' => 'FTP Username',
                        'FTPPassword' => 'FTP Password',
                        'ConnectionType' => 'Connection Type',
                        'ImportersActivity' => 'Importers Activity',
                        'ImportStatistics' => 'Import Statistics',
                        'FileManager' => 'File Manager',
                        'SmartSchedule' => 'Smart Schedule',
                        'ScheduledExport' => 'Scheduled Export',
                        'Templates' => 'Templates',
                        'LogManager' => 'Log Manager',
                        'NotSelectedAnyTab' => 'Not Selected Any Tab',
                        'EventInfo' => 'Event Info',
                        'EventDate' => 'Event Date',
                        'EventStatus' => 'Event Status',
                        'Actions' => 'Actions',
                        'Date' => 'Date',
                        'Purpose' => 'Purpose',
                        'Revision' => 'Revision',
                        'Select' => 'Select',
                        'Inserted' => 'Inserted',
                        'Updated' => 'Updated',
                        'Skipped' => 'Skipped',
                        'Delete' => 'Verwijderen',
                        'Noeventsfound' => 'No events found',
                        'ScheduleInfo' => 'Schedule Info',
                        'ScheduledDate' => 'Scheduled Date',
                        'ScheduledTime' => 'Scheduled Time',
                        'Youhavenotscheduledanyevent' => 'You haven’t scheduled any event',
                        'Frequency' => 'Frequency',
                        'Time' => 'Time',
                        'EditSchedule' => 'Edit Schedule',
                        'SaveChanges' => 'Save Changes',
                        'TemplateInfo' => 'Template Info',
                        'TemplateName' => 'Template Name',
                        'Module' => 'Module',
                        'CreatedTime' => 'Created Time',
                        'NoTemplateFound' => 'No Template Found',
                        'Download' => 'Download',
                        'NoLogRecordFound' => 'No Log Record Found',
                        'GeneralSettings' => 'General Settings',
                        'DatabaseOptimization' => 'Database Optimization',
                        'SecurityandPerformance' => 'Security and Performance',
                        'Documentation' => 'Documentation',
                        'MediaReport' => 'Media Report',
                        'DropTable' => 'Drop Table',
                        'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'If enabled plugin deactivation will remove plugin data, this cannot be restored.',
                        'Scheduledlogmails' => 'Scheduled log mails',
                        'Enabletogetscheduledlogmails' => 'Enable to get scheduled log mails.',
                        'Sendpasswordtouser' => 'Send password to user',
                        'Enabletosendpasswordinformationthroughemail' => 'Enable to send password information through email.',
                        'WoocommerceCustomattribute' => 'Woocommerce Custom attribute',
                        'Enablestoregisterwoocommercecustomattribute' => 'Enables to register woocommerce custom attribute.',
                        'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'Please make sure that you take necessary backup before proceeding with database optimization. The data lost cannot be reverted.',
                        'DeleteallorphanedPostPageMeta' => 'Delete all orphaned Post/Page Meta',
                        'Deleteallunassignedtags' => 'Delete all unassigned tags',
                        'DeleteallPostPagerevisions' => 'Delete all Post/Page revisions',
                        'DeleteallautodraftedPostPage' => 'Delete all auto drafted Post/Page',
                        'DeleteallPostPageintrash' => 'Delete all Post/Page in trash',
                        'DeleteallCommentsintrash' => 'Delete all Comments in trash',
                        'DeleteallUnapprovedComments' => 'Delete all Unapproved Comments',
                        'DeleteallPingbackComments' => 'Delete all Pingback Comments',
                        'DeleteallTrackbackComments' => 'Delete all Trackback Comments',
                        'DeleteallSpamComments' => 'Delete all Spam Comments',
                        'RunDBOptimizer' => 'Run DB Optimizer',
                        'DatabaseOptimizationLog' => 'Database Optimization Log',
                        'noofOrphanedPostPagemetahasbeenremoved' => 'no of Orphaned Post/Page meta has been removed.',
                        'noofUnassignedtagshasbeenremoved' => 'no of Unassigned tags has been removed.',
                        'noofPostPagerevisionhasbeenremoved' => 'no of Post/Page revisions has been removed.',
                        'noofAutodraftedPostPagehasbeenremoved' => 'no of Auto drafted Post/Page has been removed.',
                        'noofPostPageintrashhasbeenremoved' => 'no of Post/Page in trash has been removed.',
                        'noofSpamcommentshasbeenremoved' => 'no of Spam comments has been removed.',
                        'noofCommentsintrashhasbeenremoved' => 'no of Comments in trash has been removed.',
                        'noofUnapprovedcommentshasbeenremoved' => 'no of Unapproved comments has been removed.',
                        'noofPingbackcommentshasbeenremoved' => 'no of Pingback comments has been removed.',
                        'noofTrackbackcommentshasbeenremoved' => 'no of Trackback comments has been removed.',
                        'Allowauthorseditorstoimport' => 'Allow authors/editors to import',
                        'Allowauthorseditorstoimport' => 'Allow authors/editors to import',
                        'Thisenablesauthorseditorstoimport' => 'This enables authors/editors to import.',
                        'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'Minimum required php.ini values (Ini configured values)',
                        'Variables' => 'Variables',
                        'SystemValues' => 'System Values',
                        'MinimumRequirements' => 'Minimum Requirements',
                        'RequiredtoenabledisableLoadersExtentionsandmodules' => 'Required to enable/disable Loaders, Extentions and modules:',
                        'DebugInformation' => 'Debug Information:',
                        'SmackcodersGuidelines' => 'Smackcoders Guidelines',
                        'DevelopmentNews' => 'Development News',
                        'WhatsNew' => 'Whats New?',
                        'YoutubeChannel' => 'Youtube Channel',
                        'OtherWordPressPlugins' => 'Other WordPress Plugins',
                        'Count' => 'Count',
                        'ImageType' => 'Image Type',
                        'Status' => 'Status',
                        'Loading' => 'Loading',
                        'LoveWPUltimateCSVImporterGivea5starreviewon' => 'Love WP Ultimate CSV Importer, Give a 5 star review on',
                        'ContactSupport' => 'Contact Support',
                        'Email' => 'Email',
                        'Supporttype' => 'Support type',
                        'BugReporting' => 'Bug Reporting',
                        'FeatureEnhancement' => 'Feature Enhancement',
                        'Message' => 'Message',
                        'Send' => 'Send',
                        'NewsletterSubscription' => 'Newsletter Subscription',
                        'Subscribe' => 'Subscribe',
                        'Note' => 'Note',
                        'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'Subscribe to Smackcoders Mailing list (a few messages a year)',
                        'Pleasedraftamailto' => 'Please draft a mail to',
                        'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'If you does not get any acknowledgement within an hour!',
                        'Selectyourmoduletoexportthedata' => 'Select the module to Export Data',
                        'Toexportdatabasedonthefilters' => 'To export data based on the filters',
                        'ExportFileName' => 'Export File Name',
                        'AdvancedSettings' => 'Advanced Settings',
                        'ExportType' => 'Export Type',
                        'SplittheRecord' => 'Split the Record',
                        'AdvancedFilters'=> 'Advanced Filters',
                        'Exportdatawithautodelimiters' => 'Export data with auto delimiters',
                        'Delimiters' => 'Delimiters',
                        'OtherDelimiters' => 'Other Delimiters',
                        'Exportdataforthespecificperiod' => 'Export data for the specific period',
                        'StartFrom' => 'Start From',
                        'EndTo' => 'End To',
                        'Exportdatawiththespecificstatus' => 'Export data with the specific status',
                        'All' => 'All',
                        'Publish' => 'Publish',
                        'Sticky' => 'Sticky',
                        'Private' => 'Private',
                        'Protected' => 'Protected',
                        'Draft' => 'Draft',
                        'Pending' => 'Pending',
                        'Exportdatabyspecificauthors' => 'Export data by specific authors',
                        'Authors' => 'Authors',
                        'ExportdatabasedonspecificInclusions' => 'Export data based on specific Inclusions',
                        'DoyouwanttoSchedulethisExport' => 'Do you want to Schedule this Export',
                        'SelectTimeZone' => 'Select TimeZone',
                        'ScheduleExport' => 'Schedule Export',
                        'DataExported' => 'Data Exported',
                        'FilePath' => 'File Path',
                        'Thisfeatureisavailablein' => 'This feature is available on',
                        'WPUltimateCSVImporter' => 'WP Ultimate CSV Importer',
                        'PremiumVersion' => 'Premium Version',
                        'ContactusforPresaleEnquiry' => 'Contact us for Presale Enquiry',
                        'importwoocommerce' => 'import woocommerce',
                        'ImportanybulkWooCommerceProductsdatainCSV' => 'Import any large WooCommerce products data in CSV format',
                        'Highlights' => 'Highlights',
                        'ProductTypessimplegroupedvariableexternaltypeimport' => 'Import van produkkategorieë eenvoudig, gegroepeer, veranderlik, eksterne tipe',
                        'FeaturedProductImportfromURL' => 'Invoer van uitgeligte produkte vanaf URL',
                        'Galleryimageimport' => 'Invoer van galery-beeld',
                        'Duplicatedetection' => 'Duplikaat-opsporing',
                        'FileType' => 'Tipe lêer',
                        'SupportsUTF_8CSVfile' => 'Ondersteun UTF-8 CSV-lêer',
                        'AlreadyInstalled' => 'Reeds geïnstalleer',
                        'Install' => 'Installeer',
                        'ImportUsers' => 'Invoer gebruikers',
                        'ImportUserinfointoWordPressinbulk' => 'Invoer van gebruikersinligting in grootmaat na WordPress',
                        'WPMembersaddonsupport' => 'WP-Members byvoegselondersteuning',
                        'Defaultcustomfieldsimport' => 'Standaard invoer van aangepaste velde',
                        'Sendsautomatedpasswordnotificationemailoptional' => 'Stuur outomatiese wagwoordkennisgewings-e-pos (opsioneel)',
                        'WPUltimateExporter' => 'WP Ultimate Uitvoerder',
                        'ExportallyourWordPressdataasCSVfileforbackup' => 'Voer al jou WordPress-data uit as n CSV-lêer vir rugsteun',
                        'Supportsdefaultcustomfields' => 'Ondersteun standaard aangepaste velde',
                        'UTF8encodedCSVfile' => 'CSV-lêer in UTF-8-kodering',
                        'SupportPostPageCustomPost' => 'Ondersteun Plasing, Bladsy en Aangepaste Plasing',
                        'Filteredexportbasedonperiodoftimeauthors' => 'Gefiltreerde uitvoer gebaseer op tydperk en outeurs',
                        'Addons' => 'bylaes',
                        'Posts' => 'plasings',
                        'CustomPosts' => 'Bespoke Articles',
                        'PostTags' => 'Article Tags',
                        'PostCategories' => 'Plaas Kategorieë',
                        'Users' => 'Gebruikers',
                        'Taxonomies' => 'Taksonomieë',
                        'Comments' => 'Opmerkings',
                        'CustomerReviews' => 'Kliënteresensies',
                        'WooCommerceCoupons' => 'WooCommerce-koepons',
                        'WooCommerceRefunds' => 'WooCommerce-terugbetalings',
                        'WooCommerceVariations' => 'WooCommerce-variasies',
                        'Found' => 'Gevind',
                        'CreateTopic' => 'Skep Onderwerp',
                        'Createasupport' => 'Skep n ondersteuning onderwerp hier vir hulp',
                        'Learnfrom' => 'Leer uit ons blogberigte',
                        'TechnicalDocumentation' => 'Tegniese dokumentasie',
                        'Getsampleandexamplefiles' => 'Kry voorbeeld- en voorbeeldlêers',
                        'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'Installeer asseblief die Ultimate Exporter om al jou WordPress data as CSV te uitvoer',
                        'Clickheretoinstall' => 'Klik hier om te installeer',
                        'Hire_us' => 'Employ us',
                        'GetSupport' => 'Get Assistance',
                        'SampleCSVXML' => 'Example CSV & XML',
                        'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'Caution: Some add-ons are missing, it is advisable to',
                        'WPCompleteFields' => 'WPComplete Fields',
                        'ChooseUploadMethod' => 'Choose upload method',
                        'Media' => 'Media',
                        'CsvUploadFields' => 'Upload file',
                        'Device' => 'Device',
                        'Remote' => 'Remote',
                        'SelectDeviceZIPfile' => 'Select Device to upload images directly from your device as a ZIP file.',
                        'SelectDeviceCSVfile' => 'Choose Remote to import images from URLs of remote websites.',
                        'MediaContinue' => 'Continue',
                        'FreshImport' => 'Fresh import',
                        'UpdateContent' => 'Update content',
                        'UpdateThisMappingAs' => 'Update this mapping as',
                        'Overwritetheavailableimages' => 'Overwrite the available images',
                        'AlwaysCreateAsNewImage' => 'Always create as new image',
                        'ImportCompleted' => 'Import completed!',
                        'importHasFinished' => 'Your import has successfully finished. Click the button below to download and access a detailed import log.',
                        'ImportLog' => 'Import log',
                        'FailedMedia' => 'Failed media',
                        'UseTheFailedImages' => 'Use the failed images CSV to correct the URLs and re-import the images',
                        'FeaturedFields' => 'Featured Image Meta',
                        'Summary' => 'Summary',

                );
        return $response;
        }
        public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'Upgrade to PREMIUM using code',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'Unlock features like bulk import, advanced export, scheduling, content update, & more, plus lifetime support',
                'upgradenow' => 'upgrade now'
        );
        return $result;
        }
}