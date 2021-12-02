<?php

namespace ShopWP;

class ShopWP_Compatibility
{
    public function __construct()
    {
        add_filter('option_active_plugins', [
            $this,
            'shopwp_include_plugins',
        ]);

        add_filter('stylesheet_directory', [$this, 'shopwp_disable_theme']);
        add_filter('template_directory', [$this, 'shopwp_disable_theme']);

        add_filter('site_option_active_sitewide_plugins', [
            $this,
            'shopwp_maybe_restrict_site_plugins',
        ]);
    }

    public function shopwp_whitelisted_basenames()
    {
        return [
            'wp-shopify-pro/wp-shopify.php',
            'wpshopify/wp-shopify.php',
            'wps-beaver-builder/wps-beaver-builder.php',
            'wps-elementor/wps-elementor.php',
            'shopwp-pro/shopwp.php',
            'shopwp/shopwp.php',
            'shopwp-beaver-builder/shopwp-beaver-builder.php',
            'shopwp-elementor/shopwp-elementor.php',
            'shopwp-recharge/shopwp-recharge.php'
        ];
    }

    public function shopwp_is_rest_call()
    {
        return strpos($_SERVER['REQUEST_URI'], 'wp-json/shopwp');
    }

    public function shopwp_is_bg_processing_call()
    {
        return strpos($_SERVER['REQUEST_URI'], 'wp_shopwp_background_processing');
    }

    public function shopwp_is_template_call()
    {
        return strpos($_SERVER['REQUEST_URI'], 'components/template');
    }

    public function is_shopwp_call()
    {
        if ($this->shopwp_is_template_call()) {
            return false;
        }

        return $this->shopwp_is_rest_call() ||
            $this->shopwp_is_bg_processing_call();
    }

    public function shopwp_disable_theme($stylesheet_dir)
    {
        $force_enable_theme = apply_filters(
            'shopwp_compatibility_enable_theme',
            false
        );

        if ($this->is_shopwp_call() && !$force_enable_theme) {
            if (!defined('SHOPWP_PLUGIN_DIR')) {
                $plugins_path = WP_PLUGIN_DIR;
            } else {
                $plugins_path = SHOPWP_PLUGIN_DIR;
            }

            $theme_root = $plugins_path . 'classes/compatibility/temp-theme';

            return $theme_root;
        }

        return $stylesheet_dir;
    }

    public function shopwp_maybe_restrict_site_plugins($plugins)
    {
        if (!is_array($plugins) || empty($plugins)) {
            return $plugins;
        }

        if (!$this->is_shopwp_call()) {
            return $plugins;
        }

        $whitelisted_basenames = $this->shopwp_whitelisted_basenames();

        $only_loading = $this->shopwp_maybe_restrict_plugins(
            $plugins,
            $whitelisted_basenames,
            true
        );

        return $only_loading;
    }

    public function shopwp_maybe_restrict_plugins(
        $plugins,
        $whitelisted_basenames,
        $use_keys = false
    ) {
        foreach ($plugins as $plugin_key => $plugin_basename) {
            if ($use_keys) {
                if (!in_array($plugin_key, $whitelisted_basenames)) {
                    unset($plugins[$plugin_key]);
                }
            } else {
                if (!in_array($plugin_basename, $whitelisted_basenames)) {
                    unset($plugins[$plugin_key]);
                }
            }
        }

        if ($use_keys) {
            return $plugins;
        } else {
            return array_values(array_filter($plugins));
        }
    }

    public function shopwp_include_plugins($plugins)
    {
        if (!is_array($plugins) || empty($plugins)) {
            return $plugins;
        }

        if (!$this->is_shopwp_call()) {
            return $plugins;
        }

        $whitelisted_basenames = $this->shopwp_whitelisted_basenames();

        $only_loading = $this->shopwp_maybe_restrict_plugins(
            $plugins,
            $whitelisted_basenames
        );

        return $only_loading;
    }
}

new ShopWP_Compatibility();
