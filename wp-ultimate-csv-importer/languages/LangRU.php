<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class LangRU {
        private static $russian_instance = null , $media_instance;

        public static function getInstance() {
                if (LangRU::$russian_instance == null) {
                        LangRU::$russian_instance = new LangRU;
                        return LangRU::$russian_instance;
                }
                return LangRU::$russian_instance;
        }

        public static function contents(){
                $response = array('ImportUpdate' => 'импорт',
                        'SelectAllImages' => 'Выберите все изображения в вашем файле импорта, чтобы избежать сбоев импорта',
                        'ChooseImagesToImport' => 'Выберите изображения для импорта. Используйте флажок ниже для выбора всех изображений.',
                        'FileName' => 'Имя файла:',
                        'OK' => 'ОК',
                        'Exporterwithadvancedfilters' => 'Экспортер с расширенными фильтрами',
                        'AIOWooCommerceImportSuit' => 'AIO WooCommerce Import Suit',
                        'WPMLImporter' => 'Импортер WPML',
                        'Buynow' => 'Купить сейчас!',
                        'CouponMetaFields' => 'Купонные мета-поля',
                        'Exportfiltereddata' => 'Экспорт отфильтрованных данных',
                        'Exportfiltereddatadesc' => 'Позволяет получать только необходимые данные с помощью различных расширенных фильтров.',
                        'Backupineditableformat' => 'Бэкап в редактируемом формате',
                        'Backupineditableformatdesc' => 'Резервное копирование в 4 различных форматах файлов, таких как CSV, XML, JSON, XLS.',
                        'AutoScheduledBackups' => 'Автоматическое резервное копирование по расписанию',
                        'AutoScheduledBackupsdesc' => 'Запланированный экспорт помогает создавать резервные копии в виде редактируемого текстового файла через регулярные промежутки времени.',
                        'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'Импортер данных SEO-плагинов- RankMath, SEOPress, Yoast and All in One SEO',
                        'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter' => 'JetEngine, Metabox, Toolset Types, ACF pro / Free and Pods Field/Post Импортер плагинов',
                        'AutoSchedulewithreusabletemplates' => 'Автоматическое расписание с многоразовыми шаблонами',
                        'Dashboard' => 'Панель приборов',
                        'Manager' => 'Управляющий делами',
                        'Export' => 'Экспорт',
                        'Settings' => 'Настройки',
                        'Support' => 'Поддержка',
                        'UploadfromDesktop' => 'Загрузить с рабочего стола',
                        'UploadfromFTPSFTP' => 'Загрузить с FTP / SFTP',
                        'Updateolderpostsfromsingleimport' => 'Обновление старых сообщений из одного импорта',
                        'UploadfromURL' => 'Загрузить с URL',
                        'ChoosFileintheServer' => 'Выберите файл на сервере',
                        'Drag&Dropyourfilesor' => 'Перетащите файлы или',
                        'Browse' => 'Просматривать',
                        'NewItem' => 'Новый предмет',
                        'ExistingItems' => 'Существующие предметы',
                        'ImportEachRecordAs'=> 'Импортируйте каждую запись как',
                        'Continue' => 'Продолжать',
                        'Search' => 'Поиск',
                        'FromDate' => 'С даты',
                        'ToDate' => 'Встретиться',
                        'SEARCH' => 'ПОИСК',
                        'Media' =>'СМИ',
                        'AccessKey' => 'Ключ доступа',
                        'SavedTemplate' => 'Сохраненный шаблон',
                        'TEMPLATES' => 'ШАБЛОНЫ',
                        'MATCHEDCOLUMNSCOUNT' => 'СЧЕТЧИК СООТВЕТСТВУЮЩИХ КОЛОНН',
                        'MODULE' => 'МОДУЛЬ',
                        'CREATEDTIME' => 'СОЗДАННОЕ ВРЕМЯ',
                        'ACTION' => 'ДЕЙСТВИЕ',
                        'USETEMPLATE' => 'ИСПОЛЬЗОВАТЬ ШАБЛОН',
                        'CREATENEWMAPPING' => 'СОЗДАТЬ НОВУЮ КАРТУ',
                        'BACK' => 'НАЗАД',
                        'ADVANCEDMODE' => 'РАСШИРЕННЫЙ РЕЖИМ',
                        'DRAGDROPMODE' => 'РЕЖИМ DRAG & DROP',
                        'WordpressFields' => 'Поля Wordpress',
                        'WPFIELDS' => 'Поля WP',
                        'CSVHEADER' => 'Заголовок CSV',
                        'Action' => 'Действие',
                        'Name' => 'имя',
                        'HINT' => 'ПОДСКАЗКА',
                        'Example' => 'пример',
                        'WordPressCoreFields' => 'Основные поля WordPress',
                        'ACFFreeFields' => 'Бесплатные поля ACF',
                        'ACFFields' => 'Поля ACF',
                        'ACFGroupFields' => 'Поля группы ACF',
                        'ACFProFields' => 'ACF Pro Fields',
                        'ACFRepeaterFields' => 'Поля ретранслятора ACF',
                        'TypesCustomFields' => 'Типы настраиваемых полей',
                        'PodsFields' => 'Поля стручков',
                        'JobListingFields' => 'Поля списка вакансий',
                        'CustomFieldSuite' => 'Custom Field Suite',
                        'AllInOneSeoFields' => 'Все в одном поле для SEO',
                        'MetaBoxFields' => 'Поля MetaBox',
                        'YoastSeoFields' => 'Йоаст Сео Филдс',
                        'WPMLFields' => 'Поля WPML',
                        'JetEngineFields' => 'Поля реактивных двигателей',
                        'JetEngineRFFields' => 'Поля ретранслятора реактивного двигателя',
                        'JetEngineCPTFields' => 'Поля CPT для реактивных двигателей',
                        'jetEngineCPTRFFields' => 'Поля повторителя CPT реактивного двигателя',
                        'JetEngineCCTFields' => 'Поля реактивного двигателя CCT',
                        'JetEngineCCTRFFields' => 'Поля ретранслятора CCT реактивного двигателя',
                        'jetEngineTaxonomyFields'=> 'Поля таксономии реактивных двигателей',
                        'jetEngineTaxonomyRFFields' => 'Поля повторителя таксономии реактивного двигателя',
                        'jetEngineRelationsFields' => 'Поле отношений реактивного двигателя',
                        'replyattributesfields' => 'Поля атрибутов ответа',
                        'forumattributesfields' => 'Поля атрибутов форума',
                        'topicattributesfields' => 'Поля атрибутов темы',
                        'BillingAndShippingInformation' => 'Информация о выставлении счетов и доставке',
                        'CustomFieldsWPMemberFields' => 'Настраиваемые поля Поля участников WP',
                        'CustomFieldsMemberFields' => 'Пользовательские поля Поля-члены',
                        'ProductMetaFields' => 'Мета-поля продукта',
                        'ProductAttrFields' => 'Поля атрибутов продукта',
                        'ProductBundleMetaFields' => 'Мета-поля набора продуктов',
                        'WPECommerceCustomFields' => 'Пользовательские поля WP ECommerce',
                        'EventsManagerFields' => 'Поля диспетчера событий',
                        'CMB2CustomFields' => 'CMB2 Пользовательские поля',
                        'CourseSettingsFields' => 'Поля настроек курса',
                        'CurriculumSettingsFields' => 'Поля настроек учебной программы',
                        'QuizSettingsFields' => 'Поля настроек викторины',
                        'LessonSettingsFields' => 'Поля настроек урока',
                        'QuestionSettingsFields' => 'Поля настроек вопроса',
                        'OrderSettingsFields' => 'Поля настроек заказа',
                        'WordPressCustomFields' => 'Пользовательские поля WordPress',
                        'TermsandTaxonomies' => 'Термины и таксономии',
                        'IsSerialized' => 'Сериализован',
                        'NoCustomFieldsFound' => 'Настраиваемые поля не найдены', 
                        'MediaUploadFields' => 'Поля загрузки мультимедиа',
                        'UploadMedia' => 'Загрузить медиа',
                        'UploadedListofFiles' => 'Загруженный список файлов',
                        'UploadedMediaFileLists' => 'Списки загруженных медиафайлов',
                        'SavethismappingasTemplate' => 'Сохранить это сопоставление как шаблон',
                        'Save' => 'Сохранить',
                        'Doyouneedtoupdatethecurrentmapping' => 'Вам нужно обновить текущее отображение?',
                        'Savethecurrentmappingasnewtemplate' => 'Сохранить текущее сопоставление как новый шаблон',
                        'Back' => 'Назад',
                        'Size' => 'Размер',
                        'MediaHandling' => 'Обработка СМИ',
                        'Downloadexternalimagestoyourmedia' => 'Загрузите внешние изображения на свой носитель',
                        'ImageHandling' => 'Обработка изображений',
                        'Usemediaimagesifalreadyavailable' => 'Используйте изображения мультимедиа, если они уже доступны',
                        'Doyouwanttooverwritetheexistingimages' => 'Вы хотите перезаписать существующие изображения',
                        'ImageSizes' => 'Размеры изображения',
                        'Thumbnail' => 'Эскиз',
                        'Medium' => 'средний',
                        'MediumLarge' => 'Средний Большой',
                        'Large' => 'Большой',
                        'Custom' => 'На заказ',
                        'Slug' => 'Слизняк',
                        'Width' => 'Ширина',
                        'Height' => 'Высота',
                        'PostContentImageOption' => 'Вариант изображения содержания публикации',
                        'DownloadPostContentExternalImagestoMedia' => 'Загрузить внешние изображения содержимого публикации на носитель',
                        'Addcustomsizes' => 'Добавить нестандартные размеры',
                        'MediaSEOAdvancedOptions' => 'Медиа SEO и дополнительные параметры',
                        'SetimageTitle' => 'Установить заголовок изображения',
                        'SetimageCaption' => 'Установить подпись к изображению',
                        'SetimageAltText' => 'Установить замещающий текст изображения',
                        'SetimageDescription' => 'Установить изображение Описание',
                        'Changeimagefilenameto' => 'Измените имя файла изображения на',
                        'ImportconfigurationSection' => 'Раздел конфигурации импорта',
                        'EnablesafeprestateRollback' => 'Включить безопасный откат до состояния',
                        'Backupbeforeimport' => 'Резервное копирование перед импортом',
                        'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'Вы хотите ВКЛЮЧИТЬ режим обслуживания во время импорта',
                        'Doyouwanttohandletheduplicateonexistingrecords' => 'Вы хотите обработать дубликаты существующих записей',
                        'Mentionthefieldswhichyouwanttohandleduplicates' => 'Укажите поля, в которых вы хотите обрабатывать дубликаты.',
                        'DoyouwanttoUpdateanexistingrecords' => 'Вы хотите обновить существующие записи',
                        'Updaterecordsbasedon' => 'Обновить записи на основе',
                        'DoyouwanttoSchedulethisImport' => 'Вы хотите запланировать этот импорт',
                        'ScheduleDate' => 'Дата расписания',
                        'ScheduleFrequency' => 'Расписание Частота',
                        'TimeZone' => 'Часовой пояс',
                        'ScheduleTime' => 'График времени',
                        'Schedule' => 'График',
                        'Import' => 'Начать импорт',
                        'Format' => 'Формат',
                        'OneTime' => 'Один раз',
                        'Daily' => 'Повседневная',
                        'Weekly' => 'Еженедельно',
                        'Monthly' => 'Ежемесячно',
                        'Hourly' => 'Ежечасно',
                        'Every30mins'=> 'Каждые 30 минут',
                        'Every15mins' => 'Каждые 15 минут',
                        'Every10mins' => 'Каждые 10 минут',
                        'Every5mins' => 'Каждые 5 минут',
                        'FileName' => 'Имя файла',
                        'FileSize' => 'Размер файла',
                        'Process' => 'Процесс',
                        'Totalnoofrecords' => 'Всего нет записей',
                        'CurrentProcessingRecord' => 'Текущая запись обработки',
                        'RemainingRecord' => 'Оставшаяся запись',
                        'Completed' => 'Завершено',
                        'TimeElapsed' => 'Время истекло; истекшее время',
                        'approximate' => 'приблизительный',
                        'DownloadLog' => 'Посмотреть журнал',
                        'NoRecord' => 'Нет записи',
                        'UploadedCSVFileLists' => 'Загруженные списки файлов CSV',
                        'Hostname' => 'Имя хоста',
                        'HostPort' => 'Хост-порт',
                        'HostUsername' => 'Имя пользователя хоста',
                        'HostPassword' => 'Пароль хоста',
                        'HostPath' => 'Путь к хосту',
                        'DefaultPort' => 'Порт по умолчанию',
                        'FTPUsername' => 'Имя пользователя FTP',
                        'FTPPassword' => 'Пароль FTP',
                        'ConnectionType' => 'Тип соединения',
                        'ImportersActivity' => 'Деятельность импортеров',
                        'ImportStatistics' => 'Статистика импорта',
                        'FileManager' => 'Файловый менеджер',
                        'SmartSchedule' => 'Умное расписание',
                        'ScheduledExport' => 'Запланированный экспорт',
                        'Templates' => 'Шаблоны',
                        'LogManager' => 'Менеджер журнала',
                        'NotSelectedAnyTab' => 'Не выбрана ни одна вкладка',
                        'EventInfo' => 'Информация о мероприятии',
                        'EventDate' => 'Дата события',
                        'EventStatus' => 'Статус события',
                        'Actions' => 'Действия',
                        'Date' => 'Дата',
                        'Purpose' => 'Цель',
                        'Revision' => 'Редакция',
                        'Select' => 'Выбрать',
                        'Inserted' => 'Вставлено',
                        'Updated' => 'Обновлено',
                        'Skipped' => 'Пропущено',
                        'Delete' => 'Удалить',
                        'Noeventsfound' => 'Мероприятий не найдено',
                        'ScheduleInfo' => 'Информация о расписании',
                        'ScheduledDate' => 'Запланированная дата',
                        'ScheduledTime' => 'Запланированное время',
                        'Youhavenotscheduledanyevent' => 'Вы не запланировали ни одного мероприятия',
                        'Frequency' => 'Частота',
                        'Time' => 'Время',
                        'EditSchedule' => 'Изменить расписание',
                        'SaveChanges' => 'Сохранить изменения',
                        'TemplateInfo' => 'Информация о шаблоне',
                        'TemplateName' => 'Имя Шаблона',
                        'Module' => 'Модуль',
                        'CreatedTime' => 'Время создания',
                        'NoTemplateFound' => 'Шаблон не найден',
                        'Download' => 'Скачать',
                        'NoLogRecordFound' => 'Запись в журнале не найдена',
                        'GeneralSettings' => 'общие настройки',
                        'DatabaseOptimization' => 'Оптимизация базы данных',
                        'SecurityandPerformance' => 'Безопасность и производительность',
                        'Documentation' => 'Документация',
                        'MediaReport' => 'Отчет СМИ',
                        'DropTable' => 'Drop Table',
                        'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'Если при включенной деактивации плагина будут удалены данные плагина, их нельзя будет восстановить.',
                        'Scheduledlogmails' => 'Запланированные сообщения журнала',
                        'Enabletogetscheduledlogmails' => 'Включите, чтобы получать сообщения журнала по расписанию.',
                        'Sendpasswordtouser' => 'Отправить пароль пользователю',
                        'Enabletosendpasswordinformationthroughemail' => 'Включите отправку информации о пароле по электронной почте.',
                        'WoocommerceCustomattribute' => 'Пользовательский атрибут ',
                        'Enablestoregisterwoocommercecustomattribute' => 'Позволяет зарегистрировать настраиваемый атрибут ',
                        'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'Перед тем, как приступить к оптимизации базы данных, убедитесь, что вы сделали необходимую резервную копию. Потерянные данные не могут быть восстановлены.',
                        'DeleteallorphanedPostPageMeta' => 'Удалить все потерянные мета-сообщения / страницы',
                        'Deleteallunassignedtags' => 'Удалить все неназначенные теги',
                        'DeleteallPostPagerevisions' => 'Удалить все версии сообщения / страницы',
                        'DeleteallautodraftedPostPage' => 'Удалить все автоматически составленные сообщения / страницы',
                        'DeleteallPostPageintrash' => 'Удалить все сообщения / страницы в корзине',
                        'DeleteallCommentsintrash' => 'Удалить все комментарии в корзине',
                        'DeleteallUnapprovedComments' => 'Delete all Unapproved Comments',
                        'DeleteallPingbackComments' => 'Удалить все комментарии Pingback',
                        'DeleteallTrackbackComments' => 'Удалить все комментарии к треку',
                        'DeleteallSpamComments' => 'Удалить все спам-комментарии',
                        'RunDBOptimizer' => 'Запустить оптимизатор БД',
                        'DatabaseOptimizationLog' => 'Журнал оптимизации базы данных',
                        'noofOrphanedPostPagemetahasbeenremoved' => 'ни одна мета-версия потерянного сообщения / страницы не была удалена.',
                        'noofUnassignedtagshasbeenremoved' => 'ни один из неназначенных тегов не был удален.',
                        'noofPostPagerevisionhasbeenremoved' => 'ни одна из ревизий сообщения / страницы не была удалена.',
                        'noofAutodraftedPostPagehasbeenremoved' => 'ни одна из автоматически созданных сообщений / страниц не была удалена.',
                        'noofPostPageintrashhasbeenremoved' => 'ни одна из записей / страниц в корзине не была удалена.',
                        'noofSpamcommentshasbeenremoved' => 'ни один из спам-комментариев не удален.',
                        'noofCommentsintrashhasbeenremoved' => 'ни один из комментариев в корзине не был удален.',
                        'noofUnapprovedcommentshasbeenremoved' => 'Ни один из Неутвержденных комментариев не был удален.',
                        'noofPingbackcommentshasbeenremoved' => 'ни один из комментариев Pingback не был удален.',
                        'noofTrackbackcommentshasbeenremoved' => 'ни один из комментариев к трекбэку не был удален.',
                        'Allowauthorseditorstoimport' => 'Разрешить авторам / редакторам импортировать',
                        'Allowauthorseditorstoimport' => 'Разрешить авторам / редакторам импортировать',
                        'Thisenablesauthorseditorstoimport' => 'Это позволяет авторам / редакторам импортировать.',
                        'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'Минимальные требуемые значения php.ini (значения, настроенные в Ini)',
                        'Variables' => 'Переменные',
                        'SystemValues' => 'Системные значения',
                        'MinimumRequirements' => 'Минимальные требования',
                        'RequiredtoenabledisableLoadersExtentionsandmodules' => 'Требуется для включения / отключения загрузчиков, расширений и модулей:',
                        'DebugInformation' => 'Информация об отладке:',
                        'SmackcodersGuidelines' => 'Рекомендации по Smackcoders',
                        'DevelopmentNews' => 'Новости развития',
                        'WhatsNew' => 'Что нового?',
                        'YoutubeChannel' => 'YouTube канал',
                        'OtherWordPressPlugins' => 'Другие плагины WordPress',
                        'Count' => 'Считать',
                        'ImageType' => 'Тип изображения',
                        'Status' => 'Статус',
                        'Loading' => 'Загрузка',
                        'LoveWPUltimateCSVImporterGivea5starreviewon' => 'Люблю WP Ultimate CSV Importer, дайте 5-звездочный обзор на',
                        'ContactSupport' => 'Контактная поддержка',
                        'Email' => 'Эл. адрес',
                        'Supporttype' => 'Тип поддержки',
                        'BugReporting' => 'Сообщение об ошибках',
                        'FeatureEnhancement' => 'Улучшение функции',
                        'Message' => 'Сообщение',
                        'Send' => 'послать',
                        'NewsletterSubscription' => 'Подписка на новости',
                        'Subscribe' => 'Подписывайся',
                        'Note' => 'Запись',
                        'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'Подпишитесь на рассылку Smackcoders (несколько сообщений в год)',
                        'Pleasedraftamailto' => 'Напишите письмо на адрес',
                        'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'Если вы не получите подтверждения в течение часа!',
                        'Selectyourmoduletoexportthedata' => 'Выберите модуль для экспорта данных',
                        'Toexportdatabasedonthefilters' => 'Для экспорта данных на основе фильтров',
                        'ExportFileName' => 'Имя файла экспорта',
                        'AdvancedSettings' => 'Расширенные настройки',
                        'ExportType' => 'Тип экспорта',
                        'SplittheRecord' => 'Разделить запись',
                        'AdvancedFilters'=> 'Расширенные фильтры',
                        'Exportdatawithautodelimiters' => 'Экспорт данных с автоматическими разделителями',
                        'Delimiters' => 'Разделители',
                        'OtherDelimiters' => 'Другие разделители',
                        'Exportdataforthespecificperiod' => 'Экспорт данных за определенный период',
                        'StartFrom' => 'Начинать с',
                        'EndTo' => 'Конец',
                        'Exportdatawiththespecificstatus' => 'Экспорт данных с определенным статусом',
                        'All' => 'Все',
                        'Publish' => 'Публиковать',
                        'Sticky' => 'Липкий',
                        'Private' => 'Частный',
                        'Protected' => 'Защищено',
                        'Draft' => 'Проект',
                        'Pending' => 'В ожидании',
                        'Exportdatabyspecificauthors' => 'Экспорт данных определенных авторов',
                        'Authors' => 'Авторы',
                        'ExportdatabasedonspecificInclusions' => 'Экспорт данных на основе определенных включений',
                        'DoyouwanttoSchedulethisExport' => 'Вы хотите запланировать этот экспорт?',
                        'SelectTimeZone' => 'Выберите часовой пояс',
                        'ScheduleExport' => 'График экспорта',
                        'DataExported' => 'Данные экспортированы',
                        'FilePath' => 'Путь файла',
                        'UltimateCSVImporterPro' => 'Окончательный импортер CSV Pro',
			'loginfo' => 'информация журнала',
			'ContactusforPresaleEnquiry' => 'Свяжитесь с нами для предпродажного запроса',
			'PremiumVersion' => 'Премиум-версия',
			'Thisfeatureisavailablein' => 'Эта функция доступна в',
			'WPUltimateCSVImporter' => 'WP Ultimate Импортер CSV',
			'SampleCSV' => 'Образец файла CSV',
			'Poweredby' => 'Питаться от',
			'AlreadyInstalled' => 'Уже установлено',
			'importwoocommerce' => 'импортировать woocommerce',
			'ImportanybulkWooCommerceProductsdatainCSV' => 'Импорт любых массовых данных о продуктах WooCommerce в формате CSV.',
			'Highlights' => 'Основные моменты',
			'ProductTypessimplegroupedvariableexternaltypeimport' => 'Типы продуктов простые, сгруппированные, переменные, импорт внешнего типа.',
			'FeaturedProductImportfromURL' => 'Импорт избранного продукта с URL-адреса',
			'Galleryimageimport' => 'Импорт изображений галереи',
			'Duplicatedetection' => 'Обнаружение дубликатов',
			'FileType' => 'Тип файла',
			'SupportsUTF_8CSVfile' => 'Поддерживает CSV-файл UTF-8',
			'Install' => 'Установить',
			'ImportUsers' => 'Импорт пользователей',
			'ImportUserinfointoWordPressinbulk' => 'Массовый импорт информации о пользователе в WordPress',
			'WPMembersaddonsupport' => 'Поддержка дополнений WP-Members',
			'Defaultcustomfieldsimport' => 'Импорт настраиваемых полей по умолчанию',
			'Sendsautomatedpasswordnotificationemailoptional' => 'Отправляет автоматическое уведомление о пароле по электронной почте (необязательно)',
			'WPUltimateExporter' => 'Окончательный экспортер WP',
			'ExportallyourWordPressdataasCSVfileforbackup' => 'Экспортируйте все ваши данные WordPress в виде файла CSV для резервного копирования',
			'Supportsdefaultcustomfields' => 'Поддерживает настраиваемые поля по умолчанию',
			'UTF8encodedCSVfile' => 'CSV-файл в кодировке UTF-8',
			'SupportPostPageCustomPost' => 'Сообщение поддержки, страница и пользовательское сообщение',
			'Filteredexportbasedonperiodoftimeauthors' => 'Экспорт с фильтрацией по периоду времени и авторам',
			'Addons' => 'Аддоны',
			'Posts' => 'Сообщения',
			'CustomPosts' => 'Пользовательские сообщения',
			'PostTags' => 'Почтовые теги',
			'PostCategories' => 'Категории сообщений',
			'Users' => 'Пользователи',
			'Taxonomies' => 'Таксономии',
			'Comments' => 'Комментарии',
			'CustomerReviews' => 'Отзывы клиентов',
			'WooCommerceCoupons' => 'WooCommerce купоны',
			'WooCommerceRefunds' => 'WooCommerce Возвраты',
			'WooCommerceVariations' => 'Варианты WooCommerce',
			'Found' => 'Найденный',
			'CreateTopic' => 'Создать тему',
			'Createasupport' => 'Создайте тему поддержки здесь для помощи',
			'Learnfrom' => 'Узнайте из наших сообщений в блоге',
			'TechnicalDocumentation' => 'Техническая документация',
			'Getsampleandexamplefiles' => 'Получить образцы и примеры файлов',
			'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'Пожалуйста, установите Ultimate Exporter, чтобы экспортировать все ваши данные WordPress в формате CSV',
			'Clickheretoinstall' => 'Нажмите здесь, чтобы установить',
			'poweredBy' => 'питаться от',
			'Hire_us' => 'Наймите нас',
			'GetSupport' => 'Получать поддержку',
			'SampleCSVXML' => 'Пример CSV и XML',
			'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'Предупреждение: Импорт для некоторых daПредупреждение: некоторые дополнения отсутствуют, рекомендуется отключить их сначала. Сначала установите и активируйте следующие плагины',
			'DragDropyourfilesor' => 'Перетащите файлы или',
                        'ChooseUploadMethod' => 'Выберите метод загрузки',
                        'CsvUploadFields' => 'Загрузить файл',
                        'Device' => 'Устройство',
                        'Remote' => 'Удаленно',
                        'SelectDeviceZIPfile' => 'Выберите Устройство, чтобы загрузить изображения напрямую с вашего устройства в виде ZIP-файла.',
                        'SelectDeviceCSVfile' => 'Выберите Удаленно, чтобы импортировать изображения из URL удаленных веб-сайтов.',
                        'MediaContinue' => 'Продолжить',
                        'FreshImport' => 'Новый импорт',
                        'UpdateContent' => 'Обновить контент',
                        'UpdateThisMappingAs' => 'Обновить эту сопоставление как',
                        'Overwritetheavailableimages' => 'Перезаписать доступные изображения',
                        'AlwaysCreateAsNewImage' => 'Всегда создавать как новое изображение',
                        'ImportCompleted' => 'Импорт завершен!',
                        'importHasFinished' => 'Ваш импорт успешно завершен. Нажмите кнопку ниже, чтобы скачать и получить доступ к детализированному журналу импорта.',
                        'ImportLog' => 'Журнал импорта',
                        'FailedMedia' => 'Неудачные медиа',
                        'UseTheFailedImages' => 'Используйте CSV с неудачными изображениями, чтобы исправить URL и повторно импортировать изображения',
                        'FeaturedFields' => 'Метаданные Изображения',
                        'Summary' => 'Резюме',
                );
        return $response;
        }
        public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'Upgrade to PREMIUM using code',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'Разблокировка таких функций, как импорт оптовых товаров, расширенный экспорт, планирование, обновление контента и многое другое, а также поддержка жизни',
                'upgradenow' => 'модернизируйте'
        );
        return $result;
        }
}

