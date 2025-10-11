<?php

if (!function_exists('htmlFormSnippet')) {
    /**
     * Generate ReCaptcha HTML form snippet
     * Returns empty string if ReCaptcha is not configured
     */
    function htmlFormSnippet(): string
    {
        // Return empty string for now - ReCaptcha can be added later if needed
        return '';
    }
}

if (!function_exists('htmlScriptTagJsApi')) {
    /**
     * Generate ReCaptcha script tag for JS API
     * Returns empty string if ReCaptcha is not configured
     */
    function htmlScriptTagJsApi(array $config = []): string
    {
        // Return empty string for now - ReCaptcha can be added later if needed
        return '';
    }
}

