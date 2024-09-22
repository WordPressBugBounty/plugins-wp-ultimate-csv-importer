<?php
/******************************************************************************************
 * Copyright (C) Smackcoders. - All Rights Reserved under Smackcoders Proprietary License
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

namespace Smackcoders\FCSV;

if ( ! defined( 'ABSPATH' ) )
exit; // Exit if accessed directly

class LangPT {
        private static $portuguese_instance = null , $media_instance;

        public static function getInstance() {
                if (LangPT::$portuguese_instance == null) {
                        LangPT::$portuguese_instance = new LangPT;
                        return LangPT::$portuguese_instance;
                }
                return LangPT::$portuguese_instance;
        }

        public static function contents(){
                $response = array('ImportUpdate' => 'Importar',
                        'SelectAllImages' => 'Selecione todas as imagens no seu arquivo de importação para evitar falhas de importação',
                        'ChooseImagesToImport' => 'Escolha as imagens para importar. Utilize a caixa de seleção abaixo para selecionar todas as imagens.',
                        'FileName' => 'Nome do arquivo:',
                        'OK' => 'OK',
                        'Updateolderpostsfromsingleimport' => 'Atualize postagens mais antigas de uma única importação',
                        'Exportfiltereddata' => 'Exportar dados filtrados',
                        'Exportfiltereddatadesc' => 'Permite obter apenas os dados necessários com os diferentes filtros avançados',
                        'Backupineditableformat' => 'Backup em formato editável',
                        'Backupineditableformatdesc' => 'Backup em 4 formatos de arquivo diferentes, como CSV, XML, JSON, XLS.',
                        'AutoScheduledBackups' => 'Backups agendados automaticamente',
                        'AutoScheduledBackupsdesc' => 'A exportação agendada ajuda a fazer backup como formato de arquivo de texto editável em intervalos regulares.',
                        'Exporterwithadvancedfilters' => 'Exportador com filtros avançados',
                        'SEOPluginsDataImporterRankMathYoastandAllinOneSEO' => 'Importador de dados de plug-ins de SEO - RankMath, SEOPress, Yoast and All in One SEO',
                        'AIOWooCommerceImportSuit' => 'AIO WooCommerce Import Suit',
                        'WPMLImporter' => 'Importador WPML',
                        'AutoSchedulewithreusabletemplates' => 'Agendamento automático com modelos reutilizáveis',
                        'JetEngineMetaboxToolsetTypesACFproFreeandPodsFieldPostPluginsImporter' => 'JetEngine, Metabox, Toolset Types, ACF pro / Free and Pods Field/Post Importador de plug-ins',
                        'Dashboard' => 'painel de controle',
                        'Manager' => 'Gerente',
                        'Buynow' => 'Comprar agora!',
                        'Export' => 'Exportar',
                        'Settings' => 'Configurações',
                        'Support' => 'Apoio, suporte',
                        'UploadfromDesktop' => 'Carregar do Desktop',
                        'UploadfromFTPSFTP' => 'Upload de FTP / SFTP',
                        'UploadfromURL' => 'Upload de URL',
                        'ChoosFileintheServer' => 'Escolha o arquivo no servidor',
                        'Drag&Dropyourfilesor' => 'Arraste e solte seus arquivos ou',
                        'Browse' => 'Squeaky toy',
                        'NewItem' => 'Novo item',
                        'ExistingItems' => 'Itens Existentes',
                        'ImportEachRecordAs'=> 'Importa cada registro como',
                        'Continue' => 'Continuar',
                        'Search' => 'Pesquisa',
                        'FromDate' => 'Da data',
                        'ToDate' => 'Até a presente data',
                        'SEARCH' => 'PESQUISA',
                        'AccessKey' => 'Chave de acesso',
                        'SavedTemplate' => 'Modelo Salvo',
                        'TEMPLATES' => 'MODELOS',
                        'MATCHEDCOLUMNSCOUNT' => 'COUNT DE COLUNAS CORRESPONDIDAS',
                        'MODULE' => 'MÓDULO',
                        'CREATEDTIME' => 'HORA CRIADA',
                        'ACTION' => 'AÇAO',
                        'USETEMPLATE' => 'USE MODELO',
                        'CREATENEWMAPPING' => 'CRIAR NOVO MAPEAMENTO',
                        'BACK' => 'COSTAS',
                        'ADVANCEDMODE' => 'MODO AVANÇADO',
                        'DRAGDROPMODE' => 'MODO ARRASTAR E DROP',
                        'WordpressFields' => 'Campos Wordpress',
                        'WPFIELDS' => 'Campos WP',
                        'CSVHEADER' => 'Cabeçalho CSV',
                        'Action' => 'Açao',
                        'Name' => 'Nome',
                        'HINT' => 'DICA',
                        'Example' => 'Exemplo',
                        'WordPressCoreFields' => 'WordPress Core Fields',
                        'ACFFreeFields' => 'ACF Free Fields',
                        'ACFFields' => 'Campos ACF',
                        'ACFGroupFields' => 'Campos do Grupo ACF',
                        'ACFProFields' => 'ACF Pro Fields',
                        'ACFRepeaterFields' => 'Campos Repetidores ACF',
                        'TypesCustomFields' => 'Tipos de campos personalizados',
                        'PodsFields' => 'Campos de Pods',
                        'JobListingFields' => 'Campos de lista de empregos',
                        'CustomFieldSuite' => 'Suíte Custom Field',
                        'AllInOneSeoFields' => 'All In One Seo Fields',
                        'MetaBoxFields' => 'Campos MetaBox',
                        'YoastSeoFields' => 'Yoast Seo Fields',
                        'WPMLFields' => 'Campos WPML',
                        'JetEngineFields' => 'Campos do Motor a Jato',
                        'JetEngineRFFields' => 'Campos do repetidor do motor a jato',
                        'JetEngineCPTFields' => 'Campos CPT do Motor a Jato',
                        'jetEngineCPTRFFields' => 'Campos do Repetidor CPT do Motor a Jato',
                        'jetEngineTaxonomyFields' => 'Campos de taxonomia do motor a jato',
                        'jetEngineTaxonomyRFFields' => 'Campos do repetidor de taxonomia do motor a jato',
                        'jetEngineRelationsFields' => 'Campo de relações do motor a jato',
                        'RankMathFields'=>'Rank Math Fields',
                        'RankMathProFields'=>'Rank Math Pro Fields',
                        'replyattributesfields' => 'Campos de Atributos de Resposta',
                        'forumattributesfields' => 'Campos de atributos do fórum',
                        'topicattributesfields' => 'Campos de atributos de tópico',
                        'BillingAndShippingInformation' => 'Informações de cobrança e envio',
                        'CustomFieldsWPMemberFields' => 'Campos personalizados WP Member Fields',
                        'CustomFieldsMemberFields' => 'Campos personalizados Campos de membros',
                        'ProductMetaFields' => 'Metacampos do produto',
                        'ProductAttrFields' => 'Campos de Atr do Produto',
                        'ProductBundleMetaFields' => 'Metacampos do pacote de produtos',
                        'WPECommerceCustomFields' => 'WP ECommerce Custom Fields',
                        'EventsManagerFields' => 'Campos do gerente de eventos',
                        'CMB2CustomFields' => 'Campos personalizados CMB2',
                        'CourseSettingsFields' => 'Campos de configurações do curso',
                        'CurriculumSettingsFields' => 'Campos de configurações do currículo',
                        'QuizSettingsFields' => 'Campos de configurações do questionário',
                        'LessonSettingsFields' => 'Campos de configuração da aula',
                        'QuestionSettingsFields' => 'Campos de configuração da pergunta',
                        'OrderSettingsFields' => 'Campos de configurações do pedido',
                        'WordPressCustomFields' => 'Campos personalizados do WordPress',
                        'TermsandTaxonomies' => 'Termos e Taxonomias',
                        'IsSerialized' => 'É serializado',
                        'NoCustomFieldsFound' => 'Nenhum campo personalizado encontrado', 
                        'MediaUploadFields' => 'Campos de upload de mídia',
                        'UploadMedia' => 'Upload de mídia',
                        'UploadedListofFiles' => 'Lista de arquivos carregados',
                        'UploadedMediaFileLists' => 'Listas de arquivos de mídia carregados',
                        'SavethismappingasTemplate' => 'Salve este mapeamento como modelo',
                        'Save' => 'Salve ',
                        'Doyouneedtoupdatethecurrentmapping' => 'Você precisa atualizar o mapeamento atual?',
                        'Savethecurrentmappingasnewtemplate' => 'Salve o mapeamento atual como um novo modelo',
                        'Back' => 'Costas',
                        'Size' => 'Tamanho',
                        'MediaHandling' => 'Manuseio de mídia',
                        'Downloadexternalimagestoyourmedia' => 'Baixe imagens externas para sua mídia',
                        'ImageHandling' => 'Tratamento de Imagens',
                        'Usemediaimagesifalreadyavailable' => 'Use imagens de mídia se já estiverem disponíveis',
                        'Doyouwanttooverwritetheexistingimages' => 'Você quer sobrescrever as imagens existentes',
                        'ImageSizes' => 'Tamanhos de imagem',
                        'Thumbnail' => 'Miniatura',
                        'Medium' => 'Média',
                        'MediumLarge' => 'Médio Grande',
                        'Large' => 'ampla',
                        'Custom' => 'Personalizadas',
                        'Slug' => 'Lesma',
                        'Width' => 'Largura',
                        'Height' => 'Altura',
                        'PostContentImageOption' => 'Opção de imagem de conteúdo de postagem',
                        'DownloadPostContentExternalImagestoMedia' => 'Baixar imagens externas de conteúdo de postagem na mídia',
                        'Addcustomsizes' => 'Adicionar tamanhos personalizados',
                        'MediaSEOAdvancedOptions' => 'SEO de mídia e opções avançadas',
                        'SetimageTitle' => 'Definir o título da imagem',
                        'SetimageCaption' => 'Definir a legenda da imagem',
                        'SetimageAltText' => 'Definir Texto Alt da imagem',
                        'SetimageDescription' => 'Definir descrição da imagem',
                        'Changeimagefilenameto' => 'Alterar o nome do arquivo de imagem para',
                        'ImportconfigurationSection' => 'Seção de configuração de importação',
                        'EnablesafeprestateRollback' => 'Habilitar reversão do prestate seguro',
                        'Backupbeforeimport' => 'Backup antes da importação',
                        'DoyouwanttoSWITCHONMaintenancemodewhileimport' => 'Deseja LIGAR o modo de manutenção durante a importação',
                        'Doyouwanttohandletheduplicateonexistingrecords' => 'Você quer lidar com a duplicata em registros existentes',
                        'Mentionthefieldswhichyouwanttohandleduplicates' => 'Mencione os campos com os quais deseja lidar com duplicatas',
                        'DoyouwanttoUpdateanexistingrecords' => 'Você deseja atualizar um registro existente',
                        'Updaterecordsbasedon' => 'Atualizar registros com base em',
                        'DoyouwanttoSchedulethisImport' => 'Você deseja programar esta importação',
                        'ScheduleDate' => 'Data da Programação',
                        'ScheduleFrequency' => 'Freqüência de programação',
                        'TimeZone' => 'Fuso horário',
                        'ScheduleTime' => 'Hora agendada',
                        'Schedule' => 'Cronograma',
                        'Import' => 'Iniciar importação',
                        'Format' => 'Formato',
                        'OneTime' => 'Um tempo',
                        'Daily' => 'Diariamente',
                        'Weekly' => 'Semanal',
                        'Monthly' => 'Por mês',
                        'Hourly' => 'Por hora',
                        'Every30mins'=> 'A cada 30 minutos',
                        'Every15mins' => 'A cada 15 minutos',
                        'Every10mins' => 'A cada 10 minutos',
                        'Every5mins' => 'A cada 5 minutos',
                        'FileName' => 'Nome do arquivo',
                        'FileSize' => 'Tamanho do arquivo',
                        'Process' => 'Processo',
                        'Totalnoofrecords' => 'Nº total de registros',
                        'CurrentProcessingRecord' => 'Registro de processamento atual',
                        'RemainingRecord' => 'Registro Restante',
                        'Completed' => 'Concluída',
                        'TimeElapsed' => 'Tempo decorrido',
                        'approximate' => 'aproximada',
                        'DownloadLog' => 'Ver Log',
                        'NoRecord' => 'Sem registro',
                        'UploadedCSVFileLists' => 'Listas de arquivos CSV carregados',
                        'Hostname' => 'Nome de anfitrião',
                        'HostPort' => 'Porta do host',
                        'HostUsername' => 'Nome de usuário do host',
                        'HostPassword' => 'Senha do host',
                        'HostPath' => 'Caminho do host',
                        'DefaultPort' => 'Porta Padrão',
                        'FTPUsername' => 'Nome de usuário FTP',
                        'FTPPassword' => 'Senha FTP',
                        'ConnectionType' => 'Tipo de conexão',
                        'ImportersActivity' => 'Atividade de importadores',
                        'ImportStatistics' => 'Estatísticas de importação',
                        'FileManager' => 'Gerenciador de arquivos',
                        'SmartSchedule' => 'Cronograma Inteligente',
                        'ScheduledExport' => 'Exportação programada',
                        'Templates' => 'Modelos',
                        'LogManager' => 'Log Manager',
                        'NotSelectedAnyTab' => 'Nenhuma guia selecionada',
                        'EventInfo' => 'Informação do Evento',
                        'EventDate' => 'Data do evento',
                        'EventStatus' => 'Status do Evento',
                        'Actions' => 'Ações',
                        'Date' => 'Encontro',
                        'Purpose' => 'Objetivo',
                        'Revision' => 'Revisão',
                        'Select' => 'Selecione',
                        'Inserted' => 'Inserido',
                        'Updated' => 'Atualizada',
                        'Skipped' => 'Pulada',
                        'Delete' => 'Excluir',
                        'Noeventsfound' => 'Nenhum evento encontrado',
                        'ScheduleInfo' => 'Informação de programação',
                        'ScheduledDate' => 'Data marcada',
                        'ScheduledTime' => 'Hora marcada',
                        'Youhavenotscheduledanyevent' => 'Você não programou nenhum evento',
                        'Frequency' => 'Frequência',
                        'Time' => 'Tempo',
                        'EditSchedule' => 'Editar programação',
                        'SaveChanges' => 'Salvar alterações',
                        'TemplateInfo' => 'Informação do modelo',
                        'TemplateName' => 'Nome do modelo',
                        'Module' => 'Módulo',
                        'CreatedTime' => 'Hora de Criação',
                        'NoTemplateFound' => 'Nenhum modelo encontrado',
                        'Download' => 'Baixar',
                        'NoLogRecordFound' => 'Nenhum registro de log encontrado',
                        'GeneralSettings' => 'Configurações Gerais',
                        'DatabaseOptimization' => 'Otimização de Banco de Dados',
                        'SecurityandPerformance' => 'Segurança e Desempenho',
                        'Documentation' => 'Documentação',
                        'MediaReport' => 'Relatório de mídia',
                        'DropTable' => 'Drop Table',
                        'Ifenabledplugindeactivationwillremoveplugindatathiscannotberestored' => 'Se a desativação do plug-in habilitada removerá os dados do plug-in, eles não podem ser restaurados.',
                        'Scheduledlogmails' => 'E-mails de registro programados',
                        'Enabletogetscheduledlogmails' => 'Ative para obter emails de log programados.',
                        'Sendpasswordtouser' => 'Enviar senha para o usuário',
                        'Enabletosendpasswordinformationthroughemail' => 'Ative para enviar informações de senha por e-mail.',
                        'WoocommerceCustomattribute' => 'Atributo personalizado Woocommerce',
                        'Enablestoregisterwoocommercecustomattribute' => 'Permite registrar o atributo personalizado woocommerce.',
                        'PleasemakesurethatyoutakenecessarybackupbeforeproceedingwithdatabaseoptimizationThedatalostcantbereverted' => 'Certifique-se de fazer o backup necessário antes de prosseguir com a otimização do banco de dados. Os dados perdidos não podem ser revertidos.',
                        'DeleteallorphanedPostPageMeta' => 'Excluir todos os posts órfãos / meta de página',
                        'Deleteallunassignedtags' => 'Excluir todas as tags não atribuídas',
                        'DeleteallPostPagerevisions' => 'Excluir todas as revisões de postagem / página',
                        'DeleteallautodraftedPostPage' => 'Excluir todas as postagens / páginas elaboradas automaticamente',
                        'DeleteallPostPageintrash' => 'Excluir todas as postagens / páginas da lixeira',
                        'DeleteallCommentsintrash' => 'Excluir todos os comentários da lixeira',
                        'DeleteallUnapprovedComments' => 'Excluir todos os comentários não aprovados',
                        'DeleteallPingbackComments' => 'Excluir todos os comentários de pingback',
                        'DeleteallTrackbackComments' => 'Apagar todos os comentários do Trackback',
                        'DeleteallSpamComments' => 'Excluir todos os comentários de spam',
                        'RunDBOptimizer' => 'Execute o DB Optimizer',
                        'DatabaseOptimizationLog' => 'Log de otimização de banco de dados',
                        'noofOrphanedPostPagemetahasbeenremoved' => 'nenhum meta de Post / Página órfã foi removido',
                        'noofUnassignedtagshasbeenremoved' => 'nenhuma das tags não atribuídas foi removida.',
                        'noofPostPagerevisionhasbeenremoved' => 'nenhuma revisão de Post / Página foi removida.',
                        'noofAutodraftedPostPagehasbeenremoved' => 'nenhum de Post / Página elaborado automaticamente foi removido',
                        'noofPostPageintrashhasbeenremoved' => 'nenhuma postagem / página na lixeira foi removida.',
                        'noofSpamcommentshasbeenremoved' => 'nenhum comentário de spam foi removido.',
                        'noofCommentsintrashhasbeenremoved' => 'nenhum de comentários na lixeira foi removido.',
                        'noofUnapprovedcommentshasbeenremoved' => 'nenhum dos comentários não aprovados foi removido.',
                        'noofPingbackcommentshasbeenremoved' => 'nenhum dos comentários do Pingback foi removido.',
                        'noofTrackbackcommentshasbeenremoved' => 'nenhum dos comentários do Trackback foi removido.',
                        'Allowauthorseditorstoimport' => 'Permitir que autores / editores importem',
                        'Allowauthorseditorstoimport' => 'Permitir que autores / editores importem',
                        'Thisenablesauthorseditorstoimport' => 'Isso permite que autores / editores importem.',
                        'MinimumrequiredphpinivaluesIniconfiguredvalues' => 'Valores mínimos necessários de php.ini (valores configurados Ini)',
                        'Variables' => 'Variáveis',
                        'SystemValues' => 'Valores do Sistema',
                        'MinimumRequirements' => 'Requerimentos mínimos',
                        'RequiredtoenabledisableLoadersExtentionsandmodules' => 'Necessário para habilitar / desabilitar carregadores, extensões e módulos:',
                        'DebugInformation' => 'Informações de depuração:',
                        'SmackcodersGuidelines' => 'Diretrizes Smackcoders',
                        'DevelopmentNews' => 'Notícias de Desenvolvimento',
                        'WhatsNew' => 'O que há de novo?',
                        'YoutubeChannel' => 'Canal do Youtube',
                        'OtherWordPressPlugins' => 'Outros plug-ins do WordPress',
                        'Count' => 'Contagem',
                        'ImageType' => 'Tipo de imagem',
                        'Status' => 'Status',
                        'Loading' => 'Carregando',
                        'LoveWPUltimateCSVImporterGivea5starreviewon' => 'Adoro WP Ultimate CSV Importer, dê uma avaliação de 5 estrelas em',
                        'ContactSupport' => 'Contate o Suporte',
                        'Email' => 'O email',
                        'Supporttype' => 'Tipo de suporte',
                        'BugReporting' => 'Relatório de Bug',
                        'FeatureEnhancement' => 'Melhoria de recursos',
                        'Message' => 'mensagem',
                        'Send' => 'Mandar',
                        'NewsletterSubscription' => 'Assinatura de Newsletter',
                        'Subscribe' => 'Se inscrever',
                        'Note' => 'Nota',
                        'SubscribetoSmackcodersMailinglistafewmessagesayear' => 'Inscreva-se na lista de discussão Smackcoders (algumas mensagens por ano)',
                        'Pleasedraftamailto' => 'Escreva um e-mail para',
                        'Ifyoudoesnotgetanyacknowledgementwithinanhour' => 'Se você não receber nenhum reconhecimento em uma hora!',
                        'Selectyourmoduletoexportthedata' => 'Selecione o módulo para exportar dados',
                        'Toexportdatabasedonthefilters' => 'Para exportar dados com base nos filtros',
                        'ExportFileName' => 'Exportar nome do arquivo',
                        'AdvancedSettings' => 'Configurações avançadas',
                        'ExportType' => 'Tipo de Exportação',
                        'SplittheRecord' => 'Divida o registro',
                        'AdvancedFilters'=> 'Filtros Avançados',
                        'Exportdatawithautodelimiters' => 'Exportar dados com delimitadores automáticos',
                        'Delimiters' => 'Delimitadores',
                        'OtherDelimiters' => 'Outros Delimitadores',
                        'Exportdataforthespecificperiod' => 'Exportar dados para o período específico',
                        'StartFrom' => 'Começar de',
                        'EndTo' => 'Fim para',
                        'Exportdatawiththespecificstatus' => 'Exportar dados com o status específico',
                        'All' => 'Tudo',
                        'Publish' => 'Publicar',
                        'Sticky' => 'Pegajosa',
                        'Private' => 'Privada',
                        'Protected' => 'Protegida',
                        'Draft' => 'Esboço',
                        'Pending' => 'Pendente',
                        'Exportdatabyspecificauthors' => 'Exportar dados de autores específicos',
                        'Authors' => 'Autoras',
                        'ExportdatabasedonspecificInclusions' => 'Exportar dados com base em inclusões específicas',
                        'DoyouwanttoSchedulethisExport' => 'Você deseja agendar esta exportação',
                        'SelectTimeZone' => 'Selecione TimeZone',
                        'ScheduleExport' => 'Agendar exportação',
                        'DataExported' => 'Dados exportados',
                        'FilePath' => 'Caminho de arquivo',
                        'UltimateCSVImporterPro' => 'Ultimate CSV Importer Pro',
			'loginfo' => 'informações de registro',
			'ContactusforPresaleEnquiry' => 'Entre em contato conosco para consulta de pré-venda',
			'PremiumVersion' => 'Versão Premium',
			'Thisfeatureisavailablein' => 'Este recurso está disponível em',
			'WPUltimateCSVImporter' => 'WP Ultimate Importador de CSV',
			'SampleCSV' => 'CSV de amostra',
			'Poweredby' => 'Distribuído por',
			'AlreadyInstalled' => 'Já instalado',
			'importwoocommerce' => 'importar woocommerce',
			'ImportanybulkWooCommerceProductsdatainCSV' => 'Importe todos os dados de produtos WooCommerce em massa em CSV.',
			'Highlights' => 'Destaques',
			'ProductTypessimplegroupedvariableexternaltypeimport' => 'Tipos de produto simples, agrupados, variáveis, importação de tipo externo.',
			'FeaturedProductImportfromURL' => 'Importação de produto em destaque de URL',
			'Galleryimageimport' => 'Importação de imagem da galeria',
			'Duplicatedetection' => 'Detecção duplicada',
			'FileType' => 'Tipo de arquivo',
			'SupportsUTF_8CSVfile' => 'Suporta arquivo UTF-8 CSV',
			'Install' => 'Instalar',
			'ImportUsers' => 'Importar usuários',
			'ImportUserinfointoWordPressinbulk' => 'Importar informações do usuário para o WordPress em massa',
			'WPMembersaddonsupport' => 'Suporte ao complemento WP-Members',
			'Defaultcustomfieldsimport' => 'Importação de campos personalizados padrão',
			'Sendsautomatedpasswordnotificationemailoptional' => 'Envia e-mail de notificação de senha automatizado (opcional)',
			'WPUltimateExporter' => 'Exportador WP Ultimate',
			'ExportallyourWordPressdataasCSVfileforbackup' => 'Exporte todos os seus dados do WordPress como arquivo CSV para backup',
			'Supportsdefaultcustomfields' => 'Suporta campos personalizados padrão',
			'UTF8encodedCSVfile' => 'Arquivo CSV codificado em UTF-8',
			'SupportPostPageCustomPost' => 'Postagem de suporte, página e postagem personalizada',
			'Filteredexportbasedonperiodoftimeauthors' => 'Exportação filtrada com base no período de tempo e autores',
			'Addons' => 'Complementos',
			'Posts' => 'Postagens',
			'CustomPosts' => 'Postagens personalizadas',
			'PostTags' => 'Tags de postagem',
			'PostCategories' => 'Categorias de postagem',
			'Users' => 'Usuárias',
			'Taxonomies' => 'Taxonomias',
			'Comments' => 'Comentários',
			'CustomerReviews' => 'Avaliações de Clientes',
			'WooCommerceCoupons' => 'Cupons WooCommerce',
			'WooCommerceRefunds' => 'Reembolsos WooCommerce',
			'WooCommerceVariations' => 'Variações do WooCommerce',
			'Found' => 'Encontrada',
			'CreateTopic' => 'Criar tópico',
			'Createasupport' => 'Crie um tópico de suporte aqui para obter ajuda',
			'Learnfrom' => 'Aprenda com as postagens do nosso blog',
			'TechnicalDocumentation' => 'Documentação técnica',
			'Getsampleandexamplefiles' => 'Obtenha amostras e arquivos de exemplo',
			'PleaseinstalltheUltimateExportertoexportallyourWordPressdataasCSV' => 'Por favor, instale o Ultimate Exporter para exportar todos os seus dados do WordPress como CSV',
			'Clickheretoinstall' => 'Clique aqui para instalar',
			'poweredBy' => 'distribuído por',
			'Hire_us' => 'Contrate-nos',
			'GetSupport' => 'Obtenha suporte',
			'SampleCSVXML' => 'Amostra CSV&XML',
			'WarningImportforsomedataaredisabledInstallandactivatebelowpluginsfirst' => 'Aviso: Importar para alguns daWarning: Alguns addons estão faltando, é recomendado que todos estejam desabilitados Instale e ative os plugins abaixo primeiro',
			'DragDropyourfilesor' => 'Arraste e solte seus arquivos ou',
                        'ChooseUploadMethod' => 'Escolha o método de upload',
                        'Media' => 'Mídia',
                        'CsvUploadFields' => 'Carregar arquivo',
                        'Device' => 'Dispositivo',
                        'Remote' => 'Remoto',
                        'SelectDeviceZIPfile' => 'Selecione Dispositivo para enviar imagens diretamente do seu dispositivo como um arquivo ZIP.',
                        'SelectDeviceCSVfile' => 'Escolha Remoto para importar imagens dos URLs de sites remotos.',
                        'MediaContinue' => 'Continuar',
                        'FreshImport' => 'Nova importação',
                        'UpdateContent' => 'Atualizar conteúdo',
                        'UpdateThisMappingAs' => 'Atualize este mapeamento como',
                        'Overwritetheavailableimages' => 'Sobrescreva as imagens disponíveis',
                        'AlwaysCreateAsNewImage' => 'Sempre criar como nova imagem',
                        'ImportCompleted' => 'Importação concluída!',
                        'importHasFinished' => 'Sua importação foi concluída com sucesso. Clique no botão abaixo para baixar e acessar um registro de importação detalhado.',
                        'ImportLog' => 'Registro de importação',
                        'FailedMedia' => 'Mídias falhadas',
                        'UseTheFailedImages' => 'Use o CSV de imagens falhadas para corrigir os URLs e reimportar as imagens',
                        'FeaturedFields' => 'Metadados da Imagem Destacada',
                        'Summary' => 'Resumo',
                );
        return $response;
        }
        public static function notice_contents()
        {
        $result =array(
                'UpgradetoPROusingcode' => 'Upgrade para PREMIUM utilizando o código',
                'Unlockfeatureslikebulkimportadvanced exportschedulingcontentupdatemorepluslifetimesupport'  =>'Desbloquear características como a importação a granel, exportações avançadas, horários de exportações, actualização de conteúdos, mais apoio ao longo da vida',
                'upgradenow' => 'actualizar agora'
        );
        return $result;
        }
}

