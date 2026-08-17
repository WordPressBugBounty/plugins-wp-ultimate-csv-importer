<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$wpucsv_close_url = wp_nonce_url(
	add_query_arg(
		array(
			'page'                   => 'wp-addons-page',
			'wpucsv_dismiss_addons'  => '1',
		),
		admin_url( 'admin.php' )
	),
	'wpucsv_dismiss_addons'
);
$wpucsv_plugin_url = admin_url( 'admin.php?page=com.smackcoders.csvimporternew.menu' );

?>
<div class="wpucsv-addons-wrapper">
  <a href="<?php echo esc_url( $wpucsv_close_url ); ?>" class="wpucsv-addons-close" aria-label="<?php echo esc_attr__( 'Close', 'wp-ultimate-csv-importer' ); ?>">
    <?php echo esc_html__( 'Close', 'wp-ultimate-csv-importer' ); ?>
  </a>
  
  <div class="wpucsv-addons-header">
    <div class="wpucsv-logo-badge">
      <img src="<?php echo esc_url( plugins_url( 'assets/images/wp-ultimate-csv-importer.png', __FILE__ ) ); ?>" alt="Ultimate CSV Importer Logo" />
    </div>
    <h2 class="wpucsv-title"><?php echo esc_html__('Ultimate CSV Importer Free', 'wp-ultimate-csv-importer'); ?></h2>
    <p class="wpucsv-subtitle"><?php echo esc_html__('Manage Addons', 'wp-ultimate-csv-importer'); ?></p>
  </div>

  <div class="wpucsv-addons-card">
    <p class="wpucsv-card-intro"><?php echo esc_html__('Extend the core functionalities of Ultimate CSV Importer Free by activating specialized addons.', 'wp-ultimate-csv-importer'); ?></p>
    
    <div class="wpucsv-addon-list">
      
      <!-- Import Users Addon -->
      <div class="wpucsv-addon-item">
        <div class="wpucsv-addon-icon-box users-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M23 21V19C22.9985 18.11 22.6862 17.2511 22.115 16.5683C21.5438 15.8856 20.7497 15.4214 19.87 15.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 3.13C16.883 3.30312 17.6811 3.77199 18.2543 4.45781C18.8275 5.14362 19.1416 6.00762 19.1416 6.9C19.1416 7.79238 18.8275 8.65638 18.2543 9.34219C17.6811 10.028 16.883 10.4969 16 10.67" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="wpucsv-addon-details">
          <h3 class="wpucsv-addon-name"><?php echo esc_html__('Import Users', 'wp-ultimate-csv-importer'); ?></h3>
          <p class="wpucsv-addon-desc"><?php echo esc_html__('Import your user records available in the CSV/XML file with custom fields, WooCommerce Shipping and Billing details.', 'wp-ultimate-csv-importer'); ?></p>
        </div>
        <div class="wpucsv-addon-action">
          <?php if(is_plugin_active('import-users/import-users.php')){
                   print '<button name="get" data-value="Users" id="btn_install_act" class="wpucsv-btn wpucsv-btn-active" disabled="disabled"><span class="check-icon">✓</span> ' . esc_html__('Activated', 'wp-ultimate-csv-importer') . '</button>';
                } else {
                   print '<button class="wpucsv-btn wpucsv-btn-install" name="get" value="Users" id="Useraddon">' . esc_html__('Install & Activate', 'wp-ultimate-csv-importer') . '</button>';
                }
          ?>
        </div>
      </div>

      <!-- Import WooCommerce Addon -->
      <div class="wpucsv-addon-item">
        <div class="wpucsv-addon-icon-box woo-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 2L3 6V20C3 20.5304 3.21071 21.0391 3.58579 21.4142C3.96086 21.7893 4.46957 22 5 22H19C19.5304 22 20.0391 21.7893 20.4142 21.4142C20.7893 21.0391 21 20.5304 21 20V6L18 2H6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M3 6H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 10C16 11.0609 15.5786 12.0783 14.8284 12.8284C14.0783 13.5786 13.0609 14 12 14C10.9391 14 9.92172 13.5786 9.17157 12.8284C8.42143 12.0783 8 11.0609 8 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="wpucsv-addon-details">
          <h3 class="wpucsv-addon-name"><?php echo esc_html__('Import WooCommerce', 'wp-ultimate-csv-importer'); ?></h3>
          <p class="wpucsv-addon-desc"><?php echo esc_html__('Import your WooCommerce Products records with attributes, categories, tags, and images available in the CSV/XML file.', 'wp-ultimate-csv-importer'); ?></p>
        </div>
        <div class="wpucsv-addon-action">
          <?php if(is_plugin_active('import-woocommerce/import-woocommerce.php')){
                   print '<button name="get" data-value="WooCommerce" id="btn_install_act" class="wpucsv-btn wpucsv-btn-active" disabled="disabled"><span class="check-icon">✓</span> ' . esc_html__('Activated', 'wp-ultimate-csv-importer') . '</button>';
                } else {
                   print '<button class="wpucsv-btn wpucsv-btn-install" name="get" value="WooCommerce" id="WooCommerceaddon">' . esc_html__('Install & Activate', 'wp-ultimate-csv-importer') . '</button>';
                }
          ?>
        </div>
      </div>

      <!-- Export Wordpress Data Addon -->
      <div class="wpucsv-addon-item">
        <div class="wpucsv-addon-icon-box export-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="wpucsv-addon-details">
          <h3 class="wpucsv-addon-name"><?php echo esc_html__('Export Wordpress Data', 'wp-ultimate-csv-importer'); ?></h3>
          <p class="wpucsv-addon-desc"><?php echo esc_html__('Export your Posts, Pages, Custom Posts, Users, Comments, and WooCommerce Products data as CSV files from the WordPress.', 'wp-ultimate-csv-importer'); ?></p>
        </div>
        <div class="wpucsv-addon-action">
          <?php if(is_plugin_active('wp-ultimate-exporter/wp-ultimate-exporter.php')){
                   print '<button name="get" data-value="Exporter" id="btn_install_act" class="wpucsv-btn wpucsv-btn-active" disabled="disabled"><span class="check-icon">✓</span> ' . esc_html__('Activated', 'wp-ultimate-csv-importer') . '</button>';
                } else {
                   print '<button class="wpucsv-btn wpucsv-btn-install" name="get" value="Exporter" id="Exporteraddon">' . esc_html__('Install & Activate', 'wp-ultimate-csv-importer') . '</button>';
                }
          ?>
        </div>
      </div>

    </div>

    <!-- Centered Install & Activate All -->
    <div class="wpucsv-all-actions-container">
      <button class="wpucsv-btn wpucsv-btn-primary wpucsv-btn-lg" id="click_get_started">
        <?php echo esc_html__('Install & Activate All', 'wp-ultimate-csv-importer'); ?>
      </button>
    </div>

  </div>

  <div class="wpucsv-addons-footer">
    <a href="<?php echo esc_url( $wpucsv_plugin_url ); ?>" class="wpucsv-btn-back">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 6px;">
        <path d="M19 12H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <?php echo esc_html__('Back to Our Plugin', 'wp-ultimate-csv-importer'); ?>
    </a>
  </div>

</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter+Tight:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

:root {
  --wpucsv-dash-font: 'Inter Tight', 'Inter', system-ui, -apple-system, sans-serif;
  --wpucsv-dash-bg: #f8fafc;
  --wpucsv-dash-card-bg: #ffffff;
  --wpucsv-dash-text: #0f172a;
  --wpucsv-dash-text-muted: #64748b;
  --wpucsv-dash-border: #e2e8f0;
  --wpucsv-dash-accent: #3d4fdb;
  --wpucsv-dash-accent-hover: #2f3cb0;
  --wpucsv-dash-accent-soft: rgba(61, 79, 219, 0.06);
  --wpucsv-dash-positive: #10b981;
  --wpucsv-dash-positive-soft: #ecfdf5;
  --wpucsv-dash-positive-border: #d1fae5;
  --wpucsv-dash-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08), 0 1px 3px rgba(15, 23, 42, 0.03);
  --wpucsv-dash-card-shadow-hover: 0 20px 40px -10px rgba(15, 23, 42, 0.12);
}

#wpbody-content {
  background-color: var(--wpucsv-dash-bg) !important;
}

.wpucsv-addons-wrapper {
  font-family: var(--wpucsv-dash-font);
  max-width: 720px;
  margin: 40px auto;
  padding: 0 20px;
  box-sizing: border-box;
  position: relative;
}

.wpucsv-addons-close {
  position: absolute;
  top: 0;
  right: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 36px;
  padding: 0 14px;
  border-radius: 8px;
  border: 1px solid var(--wpucsv-dash-border);
  background: #ffffff;
  color: var(--wpucsv-dash-text-muted);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  z-index: 2;
}

.wpucsv-addons-close:hover,
.wpucsv-addons-close:focus {
  color: var(--wpucsv-dash-text);
  background-color: #f1f5f9;
  border-color: #94a3b8;
}

.wpucsv-addons-header {
  text-align: center;
  margin-bottom: 32px;
}

.wpucsv-logo-badge {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid var(--wpucsv-dash-border);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 25px -8px rgba(15, 23, 42, 0.12), 0 1px 3px rgba(15, 23, 42, 0.04);
  margin: 0 auto 18px;
  transition: transform 0.3s ease;
}

.wpucsv-logo-badge:hover {
  transform: translateY(-2px) rotate(5deg);
}

.wpucsv-logo-badge img {
  width: 38px;
  height: 38px;
  object-fit: contain;
}

.wpucsv-title {
  font-size: 24px;
  font-weight: 800;
  color: var(--wpucsv-dash-text) !important;
  margin: 0 0 6px !important;
  letter-spacing: -0.02em;
}

.wpucsv-subtitle {
  font-size: 16px;
  font-weight: 600;
  color: var(--wpucsv-dash-accent) !important;
  margin: 0 !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.wpucsv-addons-card {
  background: var(--wpucsv-dash-card-bg);
  border: 1px solid var(--wpucsv-dash-border);
  border-radius: 20px;
  box-shadow: var(--wpucsv-dash-shadow);
  padding: 32px 40px;
  margin-bottom: 24px;
}

.wpucsv-card-intro {
  font-size: 14px !important;
  color: var(--wpucsv-dash-text-muted) !important;
  text-align: center;
  margin: 0 0 32px !important;
  line-height: 1.6;
}

.wpucsv-addon-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 32px;
}

.wpucsv-addon-item {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 20px;
  border-radius: 16px;
  border: 1px solid var(--wpucsv-dash-border);
  background: #ffffff;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.wpucsv-addon-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--wpucsv-dash-card-shadow-hover);
  border-color: rgba(61, 79, 219, 0.15);
}

.wpucsv-addon-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.users-icon {
  background-color: #eff6ff;
  color: #2563eb;
}

.woo-icon {
  background-color: #faf5ff;
  color: #7c3aed;
}

.export-icon {
  background-color: #ecfdf5;
  color: #059669;
}

.wpucsv-addon-details {
  flex-grow: 1;
}

.wpucsv-addon-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--wpucsv-dash-text);
  margin: 0 0 4px !important;
}

.wpucsv-addon-desc {
  font-size: 13px !important;
  color: var(--wpucsv-dash-text-muted) !important;
  margin: 0 !important;
  line-height: 1.5;
}

.wpucsv-addon-action {
  flex-shrink: 0;
}

/* Button styles */
.wpucsv-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-family: var(--wpucsv-dash-font);
  font-weight: 600;
  font-size: 13px;
  height: 38px;
  padding: 0 16px;
  border-radius: 8px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  white-space: nowrap;
}

.wpucsv-btn-install,
.btn_install:not(#btn_install_act):not(.wpucsv-btn-active) {
  background-color: var(--wpucsv-dash-accent) !important;
  color: #ffffff !important;
  box-shadow: 0 4px 10px rgba(61, 79, 219, 0.15);
  border: none !important;
}

.wpucsv-btn-install:hover,
.btn_install:not(#btn_install_act):not(.wpucsv-btn-active):hover {
  background-color: var(--wpucsv-dash-accent-hover) !important;
  transform: translateY(-1px);
  box-shadow: 0 6px 14px rgba(61, 79, 219, 0.25);
}

.wpucsv-btn-install:active,
.btn_install:not(#btn_install_act):not(.wpucsv-btn-active):active {
  transform: translateY(0);
}

/* Activated / Active button styling */
.wpucsv-btn-active,
#btn_install_act,
.wpucsv-btn-install.wpucsv-btn-active {
  background-color: var(--wpucsv-dash-positive-soft) !important;
  color: var(--wpucsv-dash-positive) !important;
  border: 1px solid var(--wpucsv-dash-positive-border) !important;
  cursor: default !important;
  pointer-events: none !important;
  box-shadow: none !important;
  font-weight: 600;
}

.check-icon {
  margin-right: 6px;
  font-weight: bold;
}

/* Bottom Install & Activate All button */
.wpucsv-all-actions-container {
  display: flex;
  justify-content: center;
  margin-top: 24px;
}

.wpucsv-btn-primary {
  background: linear-gradient(135deg, #3d4fdb, #7c3aed) !important;
  color: #ffffff !important;
  font-size: 14px;
  font-weight: 700;
  height: 44px;
  padding: 0 28px;
  border-radius: 10px;
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.25);
  border: none;
}

.wpucsv-btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
}

.wpucsv-btn-primary:active {
  transform: translateY(0);
}

.wpucsv-btn-primary img {
  height: 20px;
  vertical-align: middle;
}

/* Back link */
.wpucsv-addons-footer {
  display: flex;
  justify-content: center;
  margin-top: 16px;
}

.wpucsv-btn-back {
  display: inline-flex;
  align-items: center;
  font-size: 13px;
  font-weight: 600;
  color: var(--wpucsv-dash-text-muted);
  text-decoration: none;
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background-color: #ffffff;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.wpucsv-btn-back:hover {
  color: var(--wpucsv-dash-text);
  background-color: #f1f5f9;
  border-color: #94a3b8;
  transform: translateY(-1px);
}

.wpucsv-btn-back:active {
  transform: translateY(0);
}

/* Spinner adjustment */
.wpucsv-btn img,
.btn_install img {
  height: 16px;
  vertical-align: middle;
}
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
