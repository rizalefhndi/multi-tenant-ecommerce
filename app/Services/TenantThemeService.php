<?php

namespace App\Services;

use App\Models\TenantSetting;

class TenantThemeService
{
    /**
     * Get all theme settings
     */
    public function getThemeSettings(): array
    {
        $defaults = TenantSetting::getDefaults()[TenantSetting::GROUP_THEME];
        $saved = TenantSetting::getByGroup(TenantSetting::GROUP_THEME);

        return array_merge($defaults, $saved);
    }

    /**
     * Get CSS variables for theme
     */
    public function getCssVariables(): array
    {
        $theme = $this->getThemeSettings();

        return [
            '--color-primary' => $theme['primary_color'] ?? '#6366f1',
            '--color-secondary' => $theme['secondary_color'] ?? '#8b5cf6',
            '--color-accent' => $theme['accent_color'] ?? '#f59e0b',
            '--color-background' => $theme['background_color'] ?? '#ffffff',
            '--color-text' => $theme['text_color'] ?? '#1f2937',
            '--font-family' => $this->getFontStack($theme['font_family'] ?? 'Inter'),
        ];
    }

    /**
     * Get CSS string for injection
     */
    public function getCssString(): string
    {
        $variables = $this->getCssVariables();
        
        $css = ':root {' . PHP_EOL;
        foreach ($variables as $key => $value) {
            $css .= "  {$key}: {$value};" . PHP_EOL;
        }
        $css .= '}';

        return $css;
    }

    /**
     * Update theme settings
     */
    public function updateTheme(array $settings): void
    {
        TenantSetting::setMultiple($settings, TenantSetting::GROUP_THEME);
    }

    /**
     * Get primary color
     */
    public function getPrimaryColor(): string
    {
        return TenantSetting::getValue('primary_color', '#6366f1', TenantSetting::GROUP_THEME);
    }

    /**
     * Get logo URL
     */
    public function getLogoUrl(): ?string
    {
        return TenantSetting::getValue('logo_url', null, TenantSetting::GROUP_THEME);
    }

    /**
     * Get favicon URL
     */
    public function getFaviconUrl(): ?string
    {
        return TenantSetting::getValue('favicon_url', null, TenantSetting::GROUP_THEME);
    }

    /**
     * Get banner URL
     */
    public function getBannerUrl(): ?string
    {
        return TenantSetting::getValue('banner_url', null, TenantSetting::GROUP_THEME);
    }

    /**
     * Check if dark mode is enabled
     */
    public function isDarkMode(): bool
    {
        return (bool) TenantSetting::getValue('dark_mode', false, TenantSetting::GROUP_THEME);
    }

    /**
     * Get font family
     */
    public function getFontFamily(): string
    {
        return TenantSetting::getValue('font_family', 'Inter', TenantSetting::GROUP_THEME);
    }

    /**
     * Get font stack with fallbacks
     */
    protected function getFontStack(string $fontFamily): string
    {
        $stacks = [
            'Inter' => "'Inter', system-ui, sans-serif",
            'Roboto' => "'Roboto', system-ui, sans-serif",
            'Open Sans' => "'Open Sans', system-ui, sans-serif",
            'Poppins' => "'Poppins', system-ui, sans-serif",
            'Lato' => "'Lato', system-ui, sans-serif",
            'Montserrat' => "'Montserrat', system-ui, sans-serif",
            'Nunito' => "'Nunito', system-ui, sans-serif",
            'Raleway' => "'Raleway', system-ui, sans-serif",
        ];

        return $stacks[$fontFamily] ?? "'{$fontFamily}', system-ui, sans-serif";
    }

    /**
     * Get available fonts
     */
    public function getAvailableFonts(): array
    {
        return [
            'Inter' => 'Inter',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Poppins' => 'Poppins',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Nunito' => 'Nunito',
            'Raleway' => 'Raleway',
        ];
    }

    /**
     * Get Google Fonts URL
     */
    public function getGoogleFontsUrl(): string
    {
        $font = $this->getFontFamily();
        $encoded = urlencode($font);
        
        return "https://fonts.googleapis.com/css2?family={$encoded}:wght@300;400;500;600;700&display=swap";
    }
}
