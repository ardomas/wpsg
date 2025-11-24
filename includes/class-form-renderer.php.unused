<?php
/**
 * WPSG Form Renderer
 * Render UI form dinamis berdasarkan konfigurasi JSON (settings.json)
 */

namespace WPSG;

if (!defined('ABSPATH')) {
    exit;
}

class WPSG_FormRenderer
{
    /**
     * Render seluruh section
     */
    public static function render_section($sectionData, $savedValues = [])
    {
        echo '<div class="wpsg-section">';

        foreach ($sectionData as $fieldName => $fieldConfig) {
            $value = $savedValues[$fieldConfig['key']] ?? $fieldConfig['default'] ?? '';

            self::render_field($fieldName, $fieldConfig, $value);
        }

        echo '</div>';
    }

    /**
     * Render satu field
     */
    public static function render_field($name, $config, $value)
    {
        $label = esc_html($config['label'] ?? $name);
        $description = esc_html($config['description'] ?? '');
        $type = $config['type'];

        echo '<div class="wpsg-field">';
        echo "<label><strong>{$label}</strong></label>";

        switch ($type) {
            case 'text':
                self::render_text($config, $value);
                break;

            case 'color':
                self::render_color($config, $value);
                break;

            case 'upload':
                self::render_upload($config, $value);
                break;

            case 'array_upload':
                self::render_array_upload($config, $value);
                break;

            case 'array_color':
                self::render_array_color($config, $value);
                break;

            case 'object':
                self::render_object($config, $value);
                break;

            case 'select':
                self::render_select($config, $value);
                break;

            default:
                echo "<p>Unknown field type: {$type}</p>";
        }

        if ($description) {
            echo "<p class='description'>{$description}</p>";
        }

        echo '</div>';
    }

    /** ============================================================
     * FIELD RENDERERS
     * ============================================================ */

    private static function render_text($config, $value)
    {
        $name = esc_attr($config['key']);
        echo "<input type='text' name='{$name}' value='" . esc_attr($value) . "' class='regular-text' />";
    }

    private static function render_color($config, $value)
    {
        $name = esc_attr($config['key']);
        echo "<input type='color' name='{$name}' value='" . esc_attr($value) . "' />";
    }

    private static function render_upload($config, $value)
    {
        $name = esc_attr($config['key']);

        echo "<input type='text' name='{$name}' value='" . esc_attr($value) . "' class='regular-text wpsg-upload-url' />";
        echo "<button type='button' class='button wpsg-upload-btn'>Upload</button>";
    }

    private static function render_select($config, $value)
    {
        $name = esc_attr($config['key']);
        $options = $config['options'] ?? [];

        echo "<select name='{$name}'>";
        foreach ($options as $opt) {
            $selected = ($opt == $value) ? "selected" : "";
            echo "<option value='{$opt}' {$selected}>" . ucfirst($opt) . "</option>";
        }
        echo "</select>";
    }

    private static function render_object($config, $value)
    {
        echo "<div class='wpsg-object-group'>";

        foreach ($config['fields'] as $subName => $subConfig) {
            $subValue = $value[$subName] ?? $subConfig['default'] ?? '';

            echo "<div class='wpsg-sub-field'>";
            echo "<label><em>{$subConfig['label']}</em></label>";

            $tempConfig = $subConfig;
            $tempConfig['key'] = $config['key'] . "[{$subName}]";

            self::render_field($subName, $tempConfig, $subValue);

            echo "</div>";
        }

        echo "</div>";
    }

    private static function render_array_upload($config, $value)
    {
        $key = $config['key'];

        echo "<div class='wpsg-array-upload' data-key='{$key}'>";

        // Render existing items
        if (!empty($value)) {
            foreach ($value as $index => $item) {
                self::render_array_item($config, $item, $index);
            }
        }

        echo "<button type='button' class='button wpsg-add-array-item'>+ Add Logo</button>";

        echo "</div>";
    }

    private static function render_array_color($config, $value)
    {
        $key = $config['key'];

        echo "<div class='wpsg-array-color' data-key='{$key}'>";

        if (!empty($value)) {
            foreach ($value as $index => $item) {
                self::render_array_item($config, $item, $index);
            }
        }

        echo "<button type='button' class='button wpsg-add-array-item'>+ Add Color</button>";

        echo "</div>";
    }

    /**
     * Render item untuk array_upload atau array_color
     */
    private static function render_array_item($config, $item, $index)
    {
        $fields = $config['fields'];
        $key = $config['key'];

        echo "<div class='wpsg-array-item'>";
        echo "<div class='wpsg-array-item-header'>";
        echo "<strong>Item " . ($index + 1) . "</strong>";
        echo "<button type='button' class='button-link-delete wpsg-remove-array-item'>Remove</button>";
        echo "</div>";

        foreach ($fields as $subName => $subConfig) {
            $subValue = $item[$subName] ?? '';
            $fieldKey = "{$key}[{$index}][{$subName}]";

            echo "<div class='wpsg-sub-field'>";
            echo "<label><em>{$subConfig['label']}</em></label>";

            $tempConfig = $subConfig;
            $tempConfig['key'] = $fieldKey;

            self::render_field($subName, $tempConfig, $subValue);

            echo "</div>";
        }

        echo "</div>";
    }
}
