<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Smackcoders\FCSV;

if (!defined('ABSPATH'))
        exit; // Exit if accessed directly

class LangFA
{
        private static $persian_instance = null, $media_instance;

        public static function getInstance()
        {
                if (LangFA::$persian_instance == null) {
                        LangFA::$persian_instance = new LangFA;
                        return LangFA::$persian_instance;
                }
                return LangFA::$persian_instance;
        }

        public static function contents()
        {
                $response = array(
					'ImportUpdate' => 'ImportUpdate',
					'SelectAllImages' => 'تمام تصاویر موجود در فایل واردات خود را انتخاب کنید تا از بروز خطاهای واردات جلوگیری شود',
					'ChooseImagesToImport' => 'تصاویر را برای واردات انتخاب کنید. از جعبه انتخاب زیر برای انتخاب تمام تصاویر استفاده کنید.',
					'FileName' => 'نام فایل:',
					'OK' => 'تایید',
					'Buynow' => 'هم اکنون خریداری کنید!',
					'Exportfiltereddata' => 'صادرات داده های فیلتر شده',
                	'Exportfiltereddatadesc' => 'به شما امکان می دهد فقط داده های مورد نیاز را با فیلترهای مختلف پیشرفته دریافت کنید',
                	'Backupineditableformat' => 'پشتیبان گیری با فرمت قابل ویرایش',
                	'Backupineditableformatdesc' => 'پشتیبان گیری در 4 فرمت فایل مختلف مانند CSV، XML، JSON، XLS.',
                	'AutoScheduledBackups' => 'پشتیبان گیری برنامه ریزی شده خودکار',
                	'AutoScheduledBackupsdesc' => 'صادرات برنامه ریزی شده به پشتیبان گیری به عنوان فرمت فایل متنی قابل ویرایش در بازه زمانی منظم کمک می کند.',
					'AutoSchedulewithreusabletemplates' => 'برنامه ریزی خودکار با قالب های قابل استفاده مجدد',
					'AIOWooCommerceImportSuit' => 'لباس وارداتی AIO WooCommerce',
					'WPMLImporter' => 'وارد کننده WPML',
					'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'واردکننده داده پلاگین سئو - RankMath, SEOPress, Yoast and All in One SEO',
					'Exporterwithadvancedfilters' => 'صادرکننده با فیلترهای پیشرفته',
					'Updateolderpostsfromsingleimport' => 'پست‌های قدیمی‌تر را از یک واردات به‌روزرسانی کنید',
					'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter' => 'JetEngine, Metabox, Toolset Types, ACF pro / Free and Pods Field/Post وارد کننده پلاگین ها',
					'Dashboard' => 'داشبورد',
					'Manager' => 'مدیر',
					'Export' => 'صادرات',
					'Settings' => 'تنظیمات',
					'Support' => 'پشتیبانی',
					'UploadfromDesktop' => 'آپلود از دسکتاپ',
					'UploadfromFTPSFTP' => 'آپلود ازFTPSFTP',
					'UploadfromFTP' => 'آپلود ازFTPSFTP',
					'UploadfromURL' => 'آپلود از URL',
					'ChoosFileintheServer' => 'ChoosFileintheServer',
					'Drag&Dropyourfilesor' => 'Drag&Dropyourfilesor',
					'Browse' => 'مرور کردن',
					'NewItem' => 'گزینه جدید',
					'ExistingItems' => 'موارد موجود',
					'ImportEachRecordAs' => 'ImportEachRecordAs',
					'Continue' => 'ادامه هید',
					'Search' => 'جستجو کردن',
					'FromDate' => 'از تاریخ',
					'SIMPLEMODE' => 'حالت ساده',
					'ToDate' => 'به روز',
					'SEARCH' => 'جستجو کردن',
					'SavedTemplate' => 'Saved Template',
					'TEMPLATES' => 'الگوها',
					'MATCHEDCOLUMNSCOUNT' => 'MATCHEDCOLUMNSCOUNT',
					'MODULE' => 'مدول',
					'CREATEDTIME' => 'CREATEDTIME',
					'ACTION' => 'عمل',
					'USETEMPLATE' => 'USETEMPLATE',
					'CREATENEWMAPPING' => 'CREATENEWMAPPING',
					'BACK' => 'بازگشت',
					'ADVANCEDMODE' => 'حالت پیشرفته',
					'DRAGDROPMODE' => 'DRAGDROPMODE',
					'WordpressFields' => 'وردپرس فیلدز',
					'WPFIELDS' => 'WPFIELDS',
					'CSVHEADER' => 'CSVHEADER',
					'Action' => 'عمل',
					'Name' => 'نام',
					'HINT' => 'اشاره',
					'Example' => 'مثال',
					'WordPressCoreFields' => 'WordPressCoreFields',
					'ACFFreeFields' => 'ACFFreeFields',
					'ACFFields' => 'ACFFields',
					'ACFGroupFields' => 'فیلدهای ACFGroup',
					'ACFProFields' => 'ACFProFields',
					'ACFRepeaterFields' => 'ACFRepeaterFields',
					'TypesCustomFields' => 'TypesCustomFields',
					'PodsFields' => 'PodsFields',
					'JobListingFields' => 'JobListingFields',
					'CustomFieldSuite' => 'CustomFieldSuite',
					'AllInOneSeoFields' => 'AllInOneSeoFields',
					'MetaBoxFields' => 'MetaBoxFields',
					'YoastSeoFields' => 'YoastSeoFields',
					'RankMathFields' => 'RankMathFields',
					'RankMathProFields' => 'RankMathProFields',
					'BillingAndShippingInformation' => 'اطلاعات صورتحساب و ارسال',
					'CustomFieldsWPMemberFields' => 'CustomFieldsWPMemberFields',
					'CustomFieldsMemberFields' => 'CustomFieldsMemberFields',
					'ProductMetaFields' => 'ProductMetaFields',
					'ProductAttrFields' => 'ProductAttrFields',
					'ProductBundleMetaFields' => 'فیلدهای متا بسته محصول',
					'OrderMetaFields' => 'MetaFields را سفارش دهید',
					'CouponMetaFields' => 'CouponMetaFields',
					'RefundMetaFields' => 'بازپرداخت MetaFields',
					'WPECommerceCustomFields' => 'WPECommerceCustomFields',
					'EventsManagerFields' => 'EventsManagerFields',
					'WPMLFields' => 'WPMLFields',
					'CMB2CustomFields' => 'CMB2CustomFields',
					'JetEngineFields' => 'JetEngineFields',
					'JetEngineRFFields' => 'JetEngineRFFfields',
					'JetEngineCPTFields' => 'JetEngineCPTFields',
					'JetEngineCPTRFFields' => 'JetEngineCPTRFFfields',
					'JetEngineCCTFields' => 'Jet Engine CCT Fields',
					'JetEngineCCTRFFields' => 'Jet Engine CCT Repeater Fields',
					'JetEngineTaxonomyFields' => 'JetEngineTaxonomyFields',
					'JetEngineTaxonomyRFFields' => 'JetEngineTaxonomyRFFfields',
					'JetEngineRelationsFields' => 'JetEngineRelationsFields',
					'CourseSettingsFields' => 'CourseSettingsFields',
					'CurriculumSettingsFields' => 'فیلدهای تنظیمات برنامه درسی',
					'QuizSettingsFields' => 'QuizSettingsFields',
					'LessonSettingsFields' => 'LessonSettingsFields',
					'QuestionSettingsFields' => 'QuestionSettingsFields',
					'OrderSettingsFields' => 'OrderSettingsFields',
					'WordPressCustomFields' => 'WordPressCustomFields',
					'TermsandTaxonomies' => 'اصطلاحات و تاکسونومی ها',
					'IsSerialized' => 'سریال شده است',
					'NoCustomFieldsFound' => 'NoCustomFieldsFound',
					'MediaUploadFields' => 'MediaUploadFields',
					'UploadMedia' => 'آپلود رسانه',
					'UploadedListofFiles' => 'UploadedListofFiles',
					'UploadedMediaFileLists' => 'فهرستهای فایل رسانه آپلود شده',
					'SavethismappingasTemplate' => 'SavethismappingasTemplate',
					'Save' => 'صرفه جویی',
					'Doyouneedtoupdatethecurrentmapping' => 'نقشه‌برداری فعلی را به‌روزرسانی کنید',
					'Savethecurrentmappingasnewtemplate' => 'ذخیره نقشه جریان در قالب جدید',
					'Back' => 'بازگشت',
					'Size' => 'اندازه',
					'MediaHandling' => 'مدیا هندلینگ',
					'Downloadexternalimagestoyourmedia' => 'تصاویر خارجی را در رسانه خود دانلود کنید',
					'ImageHandling' => 'Image Handling',
					'Usemediaimagesifalreadyavailable' => 'Usemedia Imagesifreadyavailable',
					'Doyouwanttooverwritetheexistingimages' => 'آیا می خواهید تصاویر موجود را بیش از حد بنویسید',
					'ImageSizes' => 'اندازه های تصویر',
					'Thumbnail' => 'بند انگشتی',
					'Medium' => 'متوسط',
					'MediumLarge' => 'متوسط ​​بزرگ',
					'Large' => 'بزرگ',
					'Custom' => 'سفارشی',
					'Slug' => 'حلزون حرکت کردن',
					'Width' => 'عرض',
					'Height' => 'ارتفاع',
					'Addcustomsizes' => 'اضافه کردن سفارشی',
					'PostContentImageOption' => 'PostContentImageOption',
					'DownloadPostContentExternalImagestoMedia' => 'دانلودPostContentExternalImagestoMedia',
					'MediaSEOAdvancedOptions' => 'MediaSEOAdvancedOptions',
					'polylangfields' => 'پلی لنگفیلدها',
					'SetimageTitle' => 'SetimageTitle',
					'SetimageCaption' => 'SetimageCaption',
					'SetimageAltText' => 'SetimageAltText',
					'SetimageDescription' => 'SetimageDescription',
					'Changeimagefilenameto' => 'تغییر نامفایل به',
					'ImportconfigurationSection' => 'ImportconfigurationSection',
					'EnablesafeprestateRollback' => 'SafeprestateRollback را فعال کنید',
					'Backupbeforeimport' => 'پشتیبان گیری قبل از وارد کردن',
					'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'آیا می خواهید حالت تعمیر و نگهداری درهنگام واردات را تغییر دهید',
					'Doyouwanttohandletheduplicateonexistingrecords' => 'آیا می‌خواهید با پرونده‌های تکراری موجود کنار بیایید',
					'Mentionthefieldswhichyouwanttohandleduplicates' => 'فیلدهایی را که می خواهید با موارد تکراری مدیریت کنید ذکر کنید',
					'DoyouwanttoUpdateanexistingrecords' => 'آیا می خواهید سوابق موجود را به روز کنید',
					'Updaterecordsbasedon' => 'به روز رسانی رکوردها بر اساس',
					'DeletedatafromWordPress' => 'اطلاعات حذف شده از وردپرس',
					'EnabletodeletetheitemsnotpresentinCSVXMLfile' => 'برای حذف مواردی که در فایل CSVXML ارائه نشده اند را فعال کنید',
					'DoyouwanttoSchedulethisImport' => 'آیا می خواهید این واردات را برنامه ریزی کنید',
					'ScheduleDate' => 'ScheduleDate',
					'ScheduleFrequency' => 'زمانبندی فرکانس',
					'TimeZone' => 'منطقه زمانی',
					'ScheduleTime' => 'ScheduleTime',
					'Schedule' => 'برنامه',
					'Import' => 'وارد كردن',
					'Format' => 'قالب',
					'OneTime' => 'سر وقت',
					'Daily' => 'روزانه',
					'Weekly' => 'هفتگی',
					'Monthly' => 'ماهانه',
					'Hourly' => 'ساعتی',
					'Every30mins' => 'هر 30 دقیقه',
					'Every15mins' => 'هر 15 دقیقه',
					'Every10mins' => 'هر 10 دقیقه',
					'Every5mins' => 'هر 5 دقیقه',
					'FileName' => 'نام فایل',
					'FileSize' => 'حجم فایل',
					'Process' => 'روند',
					'Totalnoofrecords' => 'Totalnoofrecords',
					'CurrentProcessingRecord' => 'CurrentProcessingRecord',
					'RemainingRecord' => 'RemainingRecord',
					'Completed' => 'تکمیل شد',
					'TimeElapsed' => 'زمان گذشت',
					'approximate' => 'تقریبی',
					'DownloadLog' => 'DownloadLog',
					'NoRecord' => 'NoRecord',
					'UploadedCSVFileLists' => 'CSVFileLists آپلود شد',
					'Hostname' => 'نام میزبان',
					'HostPort' => 'HostPort',
					'HostUsername' => 'HostUsername',
					'HostPassword' => 'رمز عبور میزبان',
					'HostPath' => 'HostPath',
					'DefaultPort' => 'DefaultPort',
					'FTPUsername' => 'نام کاربری FTPU',
					'FTPPassword' => 'رمز عبور FTP',
					'ConnectionType' => 'نوع اتصال',
					'ImportersActivity' => 'Importers Activity',
					'ImportStatistics' => 'آمار واردات',
					'FileManager' => 'مدیر فایل',
					'SmartSchedule' => 'SmartSchedule',
					'ScheduledExport' => 'ScheduledExport',
					'Templates' => 'قالب ها',
					'LogManager' => 'LogManager',
					'NotSelectedAnyTab' => 'NotSelectedAnyTab',
					'EventInfo' => 'EventInfo',
					'EventDate' => 'تاریخ رویداد',
					'EventStatus' => 'وضعیت رویداد',
					'Actions' => 'اقدامات',
					'Date' => 'تاریخ',
					'Purpose' => 'هدف',
					'Revision' => 'تجدید نظر',
					'Select' => 'انتخاب کنید',
					'Inserted' => 'درج شده است',
					'Updated' => 'به روز شد',
					'Skipped' => 'رد شد',
					'Delete' => 'حذف',
					'Noeventsfound' => 'Noevents پیدا شد',
					'ScheduleInfo' => 'ScheduleInfo',
					'ScheduledDate' => 'تاریخ برنامه ریزی شده',
					'ScheduledTime' => 'زمان برنامه ریزی شده',
					'Youhavenotscheduledanyevent' => 'رویدادی را برنامه ریزی نکرده اید',
					'Frequency' => 'فرکانس',
					'Time' => 'زمان',
					'EditSchedule' => 'ویرایش برنامه',
					'SaveChanges' => 'ذخیره تغییرات',
					'TemplateInfo' => 'اطلاعات الگو',
					'TemplateName' => 'نام الگو',
					'Module' => 'مدول',
					'CreatedTime' => 'CreatedTime',
					'NoTemplateFound' => 'NoTemplateFound',
					'Download' => 'دانلود',
					'NoLogRecordFound' => 'NoLogRecordFound',
					'GeneralSettings' => 'تنظیمات عمومی',
					'DatabaseOptimization' => 'بهینه سازی پایگاه داده',
					'SecurityandPerformance' => 'امنیت و عملکرد',
					'Documentation' => 'مستندات',
					'MediaReport' => 'MediaReport',
					'DropTable' => 'DropTable',
					'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'Ifenabledplugindeactivationwill,PluginData حذف می شوداین قابل بازیابی نیست',
					'Scheduledlogmails' => 'ایمیل های زمان بندی شده',
					'Enabletogetscheduledlogmails' => 'Enabletogetscheduledlogmails',
					'Sendpasswordtouser' => 'Sendpasswordtouser',
					'Enabletosendpasswordinformationthroughemail' => 'فعال کردن ارسال اطلاعات رمز عبور از طریق ایمیل',
					'WoocommerceCustomattribute' => 'WoocommerceCustomattribute',
					'Enablestoregisterwoocommercecustomattribute' => 'Enablestoregisterwoocommercecustomattribute را فعال کنید',
					'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'لطفاً مطمئن شوید که قبل از انجام بهینه‌سازی پایگاه داده‌ها، پشتیبان‌گیری لازم را نداشته باشید،',
					'DeleteallorphanedPostPageMeta' => 'DeleteallorphanedPostPageMeta',
					'Deleteallunassignedtags' => 'حذف همه تگ های اختصاص داده شده',
					'DeleteallPostPagerevisions' => 'DeleteallPostPagerevisions',
					'DeleteallautodraftedPostPage' => 'پاک کردنصفحه پستالautodrafted',
					'DeleteallPostPageintrash' => 'DeleteallPostPageintrash',
					'DeleteallCommentsintrash' => 'DeleteallCommentsintrash',
					'DeleteallUnapprovedComments' => 'حذف همه نظرات تایید نشده',
					'DeleteallPingbackComments' => 'DeleteallPingbackComments',
					'DeleteallTrackbackComments' => 'DeleteallTrackbackComments',
					'DeleteallSpamComments' => 'حذف همه هرزنامه ها',
					'RunDBOptimizer' => 'RunDBOptimizer',
					'DatabaseOptimizationLog' => 'Database Optimization Log',
					'noofOrphanedPostPagemetahasbeenremoved' => 'noofOrphanedPostPagemetahas حذف شده است',
					'noofUnassignedtagshasbeenremoved' => 'noofUnassignedtagshas حذف شده است',
					'noofPostPagerevisionhasbeenremoved' => 'noofPostPagerevision حذف شده است',
					'noofAutodraftedPostPagehasbeenremoved' => 'noofAutodraftedPostPagehas حذف شد',
					'noofPostPageintrashhasbeenremoved' => 'noofPostPageintrash حذف شده است',
					'noofSpamcommentshasbeenremoved' => 'noofSpamcommentحذف شده است',
					'noofCommentsintrashhasbeenremoved' => 'noofCommentsintrashhas حذف شده است',
					'noofUnapprovedcommentshasbeenremoved' => 'noofUnapprovedCommentحذف شده است',
					'noofPingbackcommentshasbeenremoved' => 'noofPingbackcommentحذف شده است',
					'noofTrackbackcommentshasbeenremoved' => 'noofTrackbackcomments حذف شده است',
					'Allowauthorseditorstoimport' => 'Allowauthorseditorstoimport',
					'Allowauthorseditorstoimport' => 'Allowauthorseditorstoimport',
					'Thisenablesauthorseditorstoimport' => 'Thisenablesauthorseditorstoimport',
					'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'MinimumrequiredphpinivaluesInicconfiguredvalues',
					'Variables' => 'متغیرها',
					'SystemValues' => 'SystemValues',
					'MinimumRequirements' => 'حداقل الزامات',
					'RequiredtoenabledisableLoadersExtentionsandmodules' => 'RequiredtoenabledisableLoadersExtentionsandmodules',
					'DebugInformation' => 'DebugInformation',
					'SmackcodersGuidelines' => 'Smackcoders Guidelines',
					'DevelopmentNews' => 'اخبار توسعه',
					'WhatsNew' => 'چه خبر',
					'YoutubeChannel' => 'کانال یوتیوب',
					'OtherWordPressPlugins' => 'پلاگین های دیگر وردپرس',
					'Count' => 'شمردن',
					'ImageType' => 'ImageType',
					'Status' => 'وضعیت',
					'Loading' => 'بارگذاری',
					'LoveWPUltimateCSVImporterGivea5starreviewon' => 'LoveWPUltimateCSVImporterGivea5starreviewon',
					'ContactSupport' => 'پشتیبانی تماس',
					'Email' => 'پست الکترونیک',
					'Supporttype' => 'نوع پشتیبانی',
					'BugReporting' => 'گزارش اشکال',
					'FeatureEnhancement' => 'افزایش ویژگی',
					'Message' => 'پیام',
					'Send' => 'ارسال',
					'NewsletterSubscription' => 'اشتراک خبرنامه',
					'Subscribe' => 'اشتراک در',
					'Note' => 'توجه داشته باشید',
					'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'اشتراک درSmackcodersMailinglistafewmessagesayear',
					'Pleasedraftamailto' => 'خواهشمندیم درفتامیلتو',
					'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'اگر در طول یک ساعت تأیید نخواهید کرد',
					'Selectyourmoduletoexportthedata' => 'ماژول خود را برای صادرات داده ها انتخاب کنید',
					'Toexportdatabasedonthefilters' => 'برای صادرات پایگاه داده بر روی فیلترها',
					'ExportFileName' => 'ExportFileName',
					'AdvancedSettings' => 'تنظیمات پیشرفته',
					'ExportType' => 'نوع صادرات',
					'SplittheRecord' => 'SplittheRecord',
					'AdvancedFilters' => 'فیلترهای پیشرفته',
					'Exportdatawithautodelimiters' => 'صادرات داده ها با جداکننده های خودکار',
					'Delimiters' => 'تعیین کننده ها',
					'OtherDelimiters' => 'سایر مرزها',
					'Exportdataforthespecificperiod' => 'صادرات داده برای دوره خاص',
					'StartFrom' => 'از ... شروع کنید',
					'EndTo' => 'EndTo',
					'Exportdatawiththespecificstatus' => 'صادرات داده با وضعیت خاص',
					'All' => 'همه',
					'Publish' => 'انتشار',
					'Sticky' => 'چسبنده',
					'Private' => 'خصوصی',
					'Protected' => 'حفاظت شده',
					'Draft' => 'پیش نویس',
					'Pending' => 'در انتظار',
					'Exportdatabyspecificauthors' => 'Exportdatabyspecificauthors',
					'Authors' => 'نویسندگان',
					'ExportdatabasedonspecificInclusions' => 'صادرکردن پایگاه داده دربرداشت های خاص',
					'DoyouwanttoSchedulethisExport' => 'آیا می خواهید این صادرات را برنامه ریزی کنید',
					'SelectTimeZone' => 'TimeZone را انتخاب کنید',
					'ScheduleExport' => 'ScheduleExport',
					'DataExported' => 'داده صادر شد',
					'FilePath' => 'مسیر فایل',
					'UltimateCSVImporterPro' => 'UltimateCSV Importer Pro',
					'loginfo' => 'اطلاعات ورود',
					'ContactusforPresaleEnquiry' => 'برای استعلام پیش فروش با ما تماس بگیرید',
					'PremiumVersion' => 'نسخه پریمیوم',
					'Thisfeatureisavailablein' => 'این ویژگی در دسترس است',
					'WPUltimateCSVImporter' => 'وارد کننده WP Ultimate CSV',
					'SampleCSV' => 'نمونه CSV',
					'Poweredby' => 'پشتیبانی شده توسط',
					'AlreadyInstalled' => 'قبلاً نصب شده است',
					'importwoocommerce' => 'واردات ووکامرس',
					'ImportanybulkWooCommerceProductsdatainCSV' => 'هرگونه داده انبوه محصولات WooCommerce را در CSV وارد کنید.',
					'Highlights' => 'نکات برجسته',
					'ProductTypessimplegroupedvariableexternaltypeimport' => 'انواع محصول ساده، گروه بندی شده، متغیر، واردات نوع خارجی.',
					'FeaturedProductImportfromURL' => 'واردات محصول ویژه از URL',
					'Galleryimageimport' => 'واردات تصویر گالری',
					'Duplicatedetection' => 'تشخیص تکراری',
					'FileType' => 'نوع فایل',
					'SupportsUTF_8CSVfile' => 'از فایل CSV UTF-8 پشتیبانی می کند',
					'Install' => 'نصب',
					'ImportUsers' => 'وارد کردن کاربران',
					'ImportUserinfointoWordPressinbulk' => 'اطلاعات کاربر را به صورت انبوه به وردپرس وارد کنید',
					'WPMembersaddonsupport' => 'پشتیبانی از افزونه WP-Members',
					'Defaultcustomfieldsimport' => 'وارد کردن فیلدهای سفارشی پیش‌فرض',
					'Sendsautomatedpasswordnotificationemailoptional' => 'ایمیل اعلان رمز عبور خودکار را ارسال می کند (اختیاری)',
					'WPUltimateExporter' => 'WP Ultimate Exporter',
					'ExportallyourWordPressdataasCSVfileforbackup' => 'تمام داده های وردپرس خود را به عنوان فایل CSV برای پشتیبان گیری صادر کنید',
					'Supportsdefaultcustomfields' => 'از فیلدهای سفارشی پیش فرض پشتیبانی می کند',
					'UTF8encodedCSVfile' => 'فایل CSV کدگذاری شده UTF-8',
					'SupportPostPageCustomPost' => 'پشتیبانی از پست، صفحه و پست سفارشی',
					'Filteredexportbasedonperiodoftimeauthors' => 'صادرات فیلتر شده بر اساس دوره زمانی و نویسندگان',
					'Addons' => 'افزونه ها',
					'Posts' => 'نوشته ها',
					'CustomPosts' => 'پست های سفارشی',
					'PostTags' => 'برچسب های پست',
					'PostCategories' => 'دسته بندی پست ها',
					'Users' => 'کاربران',
					'Taxonomies' => 'طبقه بندی ها',
					'Comments' => 'نظرات',
					'CustomerReviews' => 'نظرات مشتریان',
					'WooCommerceCoupons' => 'کوپن های ووکامرس',
					'WooCommerceRefunds' => 'بازپرداخت ووکامرس',
					'WooCommerceVariations' => 'تغییرات ووکامرس',
					'Found' => 'پیدا شد',
					'CreateTopic' => 'ایجاد موضوع',
					'Createasupport' => 'یک موضوع پشتیبانی برای کمک در اینجا ایجاد کنید',
					'Learnfrom' => 'از پست های وبلاگ ما بیاموزید',
					'TechnicalDocumentation' => 'مستندات فنی',
					'Getsampleandexamplefiles' => 'فایل های نمونه و نمونه را دریافت کنید',
					'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'لطفاً Ultimate Exporter را نصب کنید تا تمام داده های وردپرس خود را به صورت CSV صادر کنید',
					'Clickheretoinstall' => 'برای نصب اینجا کلیک کنید',
					'poweredBy' => 'پشتیبانی شده توسط',
					'Hire_us' => 'ما را استخدام کنید',
					'GetSupport' => 'دریافت پشتیبانی',
					'SampleCSVXML' => 'نمونه CSV&XML',
					'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'هشدار: برخی از افزونه ها گم شده اند، توصیه می شود',
					'DragDropyourfilesor' => 'فایل های خود را بکشید و رها کنید یا',
					'WPCompleteFields' => 'فیلدهای WPComplete',
					'ChooseUploadMethod' => 'روش آپلود را انتخاب کنید',
					'Media' => 'رسانه',
					'CsvUploadFields' => 'فایل را آپلود کنید',
					'Device' => 'دستگاه',
					'Remote' => 'راه دور',
					'SelectDeviceZIPfile' => 'دستگاه را انتخاب کنید تا تصاویر را مستقیماً از دستگاه خود به عنوان یک فایل ZIP آپلود کنید.',
					'SelectDeviceCSVfile' => 'راه دور را انتخاب کنید تا تصاویر را از URLهای وب‌سایت‌های راه دور وارد کنید.',
					'MediaContinue' => 'ادامه دهید',
					'FreshImport' => 'واردات جدید',
					'UpdateContent' => 'محتوا را به‌روزرسانی کنید',
					'UpdateThisMappingAs' => 'این نقشه را به‌روزرسانی کنید به عنوان',
					'Overwritetheavailableimages' => 'تصاویر موجود را بازنویسی کنید',
					'AlwaysCreateAsNewImage' => 'همیشه به عنوان تصویر جدید ایجاد کنید',
					'ImportCompleted' => 'واردات کامل شد!',
					'importHasFinished' => 'واردات شما با موفقیت به پایان رسید. برای دانلود و دسترسی به یک گزارش واردات دقیق روی دکمه زیر کلیک کنید.',
					'ImportLog' => 'گزارش واردات',
					'FailedMedia' => 'رسانه‌های ناموفق',
					'UseTheFailedImages' => 'از فایل CSV تصاویر ناموفق برای تصحیح URLها و واردات دوباره تصاویر استفاده کنید',
					'FeaturedFields' => 'متادیتای تصویر برجسته',
					'Summary' => 'خلاصه',
                );
                return $response;
        }
		
		public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'ارتقا به PREMIUM با استفاده از کد',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'باز کردن ویژگی هایی مانند واردات فله، صادرات پیشرفته، برنامه ریزی، به روز رسانی محتوا، و بیشتر، به علاوه پشتیبانی طول عمر',
                'upgradenow' => 'ارتقاء دهید'
        );
        return $result;
        }
	}

